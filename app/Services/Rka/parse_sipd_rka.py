import sys
import os
import pypdf
import re
import json

def clean_num(val):
    if not val:
        return 0.0
    val = val.strip().replace('Rp.', '').replace('Rp', '').replace(' ', '')
    val = val.replace('.', '').replace(',', '.')
    try:
        return float(val)
    except:
        return 0.0

def parse_sipd_pdf(pdf_path):
    if not os.path.exists(pdf_path):
        raise FileNotFoundError(f"File not found: {pdf_path}")

    reader = pypdf.PdfReader(pdf_path)
    full_text = '\n'.join([p.extract_text() for p in reader.pages])

    # Extract Metadata
    tahun_m = re.search(r'Tahun Anggaran\s+(\d{4})', full_text)
    prog_m = re.search(r'Program\s*:\s*([0-9\.]+)\s+(.+)', full_text)
    keg_m = re.search(r'Kegiatan\s*:\s*([0-9\.]+)\s+(.+)', full_text)
    sub_m = re.search(r'Sub Kegiatan\s*:\s*([0-9\.]+)\s+(.+)', full_text)
    sumber_m = re.search(r'Sumber Pendanaan\s*:\s*(.+)', full_text)

    meta = {
        'tahun': int(tahun_m.group(1)) if tahun_m else 2026,
        'program_kode': prog_m.group(1).strip() if prog_m else '',
        'program_nama': prog_m.group(2).strip() if prog_m else '',
        'kegiatan_kode': keg_m.group(1).strip() if keg_m else '',
        'kegiatan_nama': keg_m.group(2).strip() if keg_m else '',
        'sub_kegiatan_kode': sub_m.group(1).strip() if sub_m else '',
        'sub_kegiatan_nama': sub_m.group(2).strip() if sub_m else '',
        'sumber_dana': sumber_m.group(1).strip() if sumber_m else 'Dana Alokasi Umum (DAU)'
    }

    lines = full_text.split('\n')
    current_rek = None
    current_paket = None
    items = []

    i = 0
    while i < len(lines):
        line = lines[i].strip()
        
        # Check rekening
        m_rek = re.match(r'^(5\.1\.\d{2}\.\d{2}\.\d{3}\.\d{5})', line)
        if m_rek:
            current_rek = m_rek.group(1)
            i += 1
            continue
            
        # Check paket [#]
        m_paket = re.match(r'^\[\s*#\s*\]\s*(.+)', line)
        if m_paket:
            current_paket = m_paket.group(1).strip()
            i += 1
            continue
            
        # Check spesifikasi
        if 'Spesifikasi :' in line:
            spec_text = line
            while i + 1 < len(lines) and not re.search(r'\d+\s*%\s*Rp\.', spec_text) and not lines[i+1].strip().startswith('5.1.') and not lines[i+1].strip().startswith('[ # ]'):
                i += 1
                spec_text += ' ' + lines[i].strip()
                
            m_item = re.search(r'Spesifikasi\s*:\s*(.+?)\s+([\d\.,]+|-)\s+([A-Za-z\/\s]+?)\s+([\d\.,]+)\s+\d+\s*%\s+Rp\.\s*([\d\.,]+)', spec_text)
            if m_item:
                nama = m_item.group(1).strip()
                vol = clean_num(m_item.group(2))
                satuan = m_item.group(3).strip()
                harga = clean_num(m_item.group(4))
                total = clean_num(m_item.group(5))
                items.append({
                    'kode_rekening': current_rek,
                    'paket': current_paket,
                    'nama_item': nama,
                    'volume': vol,
                    'satuan': satuan,
                    'harga_satuan': harga,
                    'pagu_total': total
                })
        i += 1

    return {
        'meta': meta,
        'total_items': len(items),
        'total_pagu': sum(it['pagu_total'] for it in items),
        'items': items
    }

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'No file path provided'}))
        sys.exit(1)
        
    pdf_path = sys.argv[1]
    try:
        res = parse_sipd_pdf(pdf_path)
        print(json.dumps(res, ensure_ascii=False))
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)

