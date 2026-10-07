<?php

namespace Tests\Feature;

use App\Models\Perijinan;
use App\Models\QrCodes;
use Tests\TestCase;

class ScanPageTest extends TestCase
{
    public function test_scan_page_displays_modal_choice_for_leave_type()
    {
        $response = $this->get(route('scan.index'));

        $response->assertStatus(200);
        $response->assertSee('modalPegawai');
        $response->assertSee('Pilih Izin');
    }

    public function test_scan_proses_returns_available_perijinan_options()
    {
        $perijinan = Perijinan::create([
            'jenis' => 'PRIBADI',
            'izin' => 'Izin Sakit',
            'kode' => 'SAKIT',
            'id_subag' => null,
        ]);

        $qr = QrCodes::create([
            'jenis_qr' => 'PEGAWAI',
            'nama_kartu' => 'Kartu Uji',
            'nomor_kartu' => 'TEST-001',
            'id_peg' => null,
            'id_subag' => null,
            'id_ijin' => null,
            'status' => 'Aktif',
        ]);

        $response = $this->postJson(route('scan.proses'), [
            'kode_qr' => $qr->nomor_kartu,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'data' => [
                'perijinan_options' => [
                    '*' => ['id_ijin', 'izin', 'jenis']
                ]
            ]
        ]);

        $this->assertSame('Izin Sakit', $response->json('data.perijinan_options.0.izin'));
    }
}
