import os
import re
import csv
import pypdf

def extract_kode_rekening():
    pdf_path = r'c:\Users\donia\Downloads\kode rekening belanja.pdf'
    if not os.path.exists(pdf_path):
        print(f"Error: File {pdf_path} tidak ditemukan!")
        return

    reader = pypdf.PdfReader(pdf_path)
    total_pages = len(reader.pages)
    print(f"Membaca {total_pages} halaman PDF...")

    records = []
    seen_codes = set()

    # Akun Utama 5
    records.append({
        'kode': '5',
        'uraian': 'BELANJA DAERAH'
    })
    seen_codes.add('5')

    code_pattern = re.compile(r'^5(?:\.\d+)+$')

    for page_idx in range(total_pages):
        page = reader.pages[page_idx]
        parts = []

        def visitor(text, cm, tm, fontDict, fontSize):
            t = text.strip()
            if t and t not in ('KODE', 'URAIAN', 'KODE URAIAN'):
                parts.append((tm[4], tm[5], t))

        page.extract_text(visitor_text=visitor)
        sorted_parts = sorted(parts, key=lambda p: (-round(p[1], 1), p[0]))

        current_code = None
        current_desc = []

        for x, y, text in sorted_parts:
            # Lewati narasi penjelasan kaki
            if text.startswith('Digunakan untuk') or text.startswith('s.d 5.'):
                continue

            clean_text = text.replace(' ', '')
            if x < 135 and code_pattern.match(clean_text):
                if current_code and current_code not in seen_codes:
                    desc_str = ' '.join(current_desc).strip()
                    # Filter narasi panjang penjelasan
                    if desc_str and not desc_str.startswith('Digunakan untuk'):
                        records.append({'kode': current_code, 'uraian': desc_str})
                        seen_codes.add(current_code)
                current_code = clean_text
                current_desc = []
            else:
                m = re.match(r'^(5(?:\.\d+)+)\s+(.*)$', text)
                if m and x < 135:
                    if current_code and current_code not in seen_codes:
                        desc_str = ' '.join(current_desc).strip()
                        if desc_str and not desc_str.startswith('Digunakan untuk'):
                            records.append({'kode': current_code, 'uraian': desc_str})
                            seen_codes.add(current_code)
                    current_code = m.group(1).strip()
                    current_desc = [m.group(2).strip()]
                else:
                    if current_code:
                        current_desc.append(text)

        if current_code and current_code not in seen_codes:
            desc_str = ' '.join(current_desc).strip()
            if desc_str and not desc_str.startswith('Digunakan untuk'):
                records.append({'kode': current_code, 'uraian': desc_str})
                seen_codes.add(current_code)

    print(f"Berhasil mengekstrak {len(records)} rekening belanja.")

    # Target folder
    base_dir = r'c:\laragon\www\sakti'
    data_dir = os.path.join(base_dir, 'database', 'seeders', 'data')
    public_dir = os.path.join(base_dir, 'public', 'downloads')
    os.makedirs(data_dir, exist_ok=True)
    os.makedirs(public_dir, exist_ok=True)

    csv_file = os.path.join(data_dir, 'kode_rekening_belanja.csv')
    sql_file = os.path.join(data_dir, 'kode_rekening_belanja.sql')
    csv_pub = os.path.join(public_dir, 'kode_rekening_belanja.csv')
    sql_pub = os.path.join(public_dir, 'kode_rekening_belanja.sql')

    parsed_rows = []
    for r in records:
        kode = r['kode']
        uraian = r['uraian']
        parts = kode.split('.')

        akun = parts[0] if len(parts) > 0 else '5'
        kelompok = parts[1] if len(parts) > 1 else ''
        jenis = parts[2] if len(parts) > 2 else ''
        objek = parts[3] if len(parts) > 3 else ''
        rincian_objek = parts[4] if len(parts) > 4 else ''
        sub_rincian_objek = parts[5] if len(parts) > 5 else ''
        sub_sub_rincian_objek = parts[6] if len(parts) > 6 else ''

        parsed_rows.append({
            'akun': akun,
            'kelompok': kelompok,
            'jenis': jenis,
            'objek': objek,
            'rincian_objek': rincian_objek,
            'sub_rincian_objek': sub_rincian_objek,
            'sub_sub_rincian_objek': sub_sub_rincian_objek,
            'kode_lengkap': kode,
            'uraian': uraian
        })

    # Tulis CSV
    fieldnames = ['akun', 'kelompok', 'jenis', 'objek', 'rincian_objek', 'sub_rincian_objek', 'sub_sub_rincian_objek', 'kode_lengkap', 'uraian']
    with open(csv_file, 'w', newline='', encoding='utf-8') as f:
        writer = csv.DictWriter(f, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(parsed_rows)

    with open(csv_pub, 'w', newline='', encoding='utf-8') as f:
        writer = csv.DictWriter(f, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(parsed_rows)

    # Tulis SQL
    with open(sql_file, 'w', encoding='utf-8') as f:
        f.write("-- DUMP KODE REKENING BELANJA STANDAR SIPD RI (KABUPATEN SIJUNJUNG)\n")
        f.write("-- Total Data: " + str(len(parsed_rows)) + "\n\n")
        
        # Insert per batch 200 baris
        batch_size = 200
        for i in range(0, len(parsed_rows), batch_size):
            batch = parsed_rows[i:i + batch_size]
            f.write("INSERT INTO `kode_rekenings` (`akun`, `kelompok`, `jenis`, `objek`, `rincian_objek`, `sub_rincian_objek`, `sub_sub_rincian_objek`, `kode_lengkap`, `uraian`, `created_at`, `updated_at`) VALUES\n")
            values_list = []
            for item in batch:
                safe_uraian = item['uraian'].replace("'", "''").replace("\\", "\\\\")
                values_list.append(
                    f"('{item['akun']}', '{item['kelompok']}', '{item['jenis']}', '{item['objek']}', '{item['rincian_objek']}', '{item['sub_rincian_objek']}', '{item['sub_sub_rincian_objek']}', '{item['kode_lengkap']}', '{safe_uraian}', NOW(), NOW())"
                )
            f.write(",\n".join(values_list))
            f.write(" ON DUPLICATE KEY UPDATE `uraian` = VALUES(`uraian`), `updated_at` = NOW();\n\n")

    # Salin SQL ke public
    with open(sql_pub, 'w', encoding='utf-8') as f:
        with open(sql_file, 'r', encoding='utf-8') as sf:
            f.write(sf.read())

    print(f"File CSV tersimpan di: {csv_file}")
    print(f"File SQL tersimpan di: {sql_file}")
    print(f"File Public CSV tersimpan di: {csv_pub}")
    print(f"File Public SQL tersimpan di: {sql_pub}")

if __name__ == '__main__':
    extract_kode_rekening()

