<?php

namespace Tests\Feature;

use App\Models\PengajuanSpj;
use App\Services\Spj\SpjDocumentService;
use Tests\TestCase;

class DokumenTrackingTest extends TestCase
{
    public function test_can_track_valid_document_publicly(): void
    {
        $spj = PengajuanSpj::first();
        if (! $spj) {
            $this->markTestSkipped('Database pengajuan SPJ sedang dikosongkan.');
        }

        $response = $this->get(route('dokumen.tracking', ['nomor' => $spj->nomor_pengajuan]));

        $response->assertOk();
        $response->assertSee('DOKUMEN VALID & TERCATAT RESMI', false);
        $response->assertSee($spj->nomor_pengajuan);
        $response->assertSee($spj->status_label);
    }

    public function test_tracking_invalid_document_shows_warning(): void
    {
        $response = $this->get(route('dokumen.tracking', ['nomor' => 'SP/TIDAK_TERDAFTAR/999']));

        $response->assertOk();
        $response->assertSee('DOKUMEN TIDAK DITEMUKAN / TIDAK VALID', false);
        $response->assertSee('SP/TIDAK_TERDAFTAR/999');
    }

    public function test_pdf_generation_includes_qr_code(): void
    {
        $spj = PengajuanSpj::where('status', '!=', 'draft')->first() ?? PengajuanSpj::first();
        if (! $spj) {
            $this->markTestSkipped('Database pengajuan SPJ sedang dikosongkan.');
        }

        $qr = $spj->getQrTrackingBase64();
        $this->assertNotNull($qr);
        $this->assertStringStartsWith('data:image/png;base64,', $qr);

        $service = new SpjDocumentService();
        $pdfPesanan = $service->generatePdf($spj, 'pesanan');
        $this->assertNotEmpty($pdfPesanan->output());

        $pdfKwitansi = $service->generatePdf($spj, 'kwitansi');
        $this->assertNotEmpty($pdfKwitansi->output());
    }
}
