<?php

namespace Tests\Feature;

use App\Models\Bagians;
use App\Models\IjinKeluar;
use App\Models\Pegawai;
use App\Models\QrCodes;
use App\Models\SubBagians;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScanPageTest extends TestCase
{
    public function test_scan_page_scans_directly_without_a_choice_modal()
    {
        $this->withoutMiddleware();
        $response = $this->get(route('scan.index'));

        $response->assertStatus(200);
        $response->assertDontSee('modalPegawai');
        $response->assertDontSee('scan.simpan');
        $response->assertSee('/scan/proses');
        $response->assertDontSee('Foto Pegawai');
        $response->assertDontSee('foto_pegawai');
    }

    public function test_scan_proses_immediately_saves_transaction_for_valid_qr()
    {
        $this->withoutMiddleware();
        $suffix = Str::upper(Str::random(5));
        $bagian = Bagians::create([
            'bag' => 'Bagian ' . $suffix,
            'kode_bag' => 'B' . $suffix,
        ]);
        $subbag = SubBagians::create([
            'id_bag' => $bagian->id_bag,
            'sub_bag' => 'Subbag ' . $suffix,
            'kode_subag' => 'S' . $suffix,
        ]);
        $pegawai = Pegawai::create([
            'id_subag' => $subbag->id_subag,
            'nama' => 'Pegawai ' . $suffix,
            'nik' => $suffix . Str::upper(Str::random(5)),
            'jenis_kelamin' => 'L',
            'jabatan' => 'Staff',
            'status' => 'Aktif',
        ]);
        $qr = QrCodes::create([
            'jenis_qr' => 'PEGAWAI',
            'nama_kartu' => 'Kartu Uji ' . $suffix,
            'nomor_kartu' => 'TEST-' . $suffix,
            'id_peg' => $pegawai->id_peg,
            'id_subag' => $subbag->id_subag,
            'id_ijin' => null,
            'ket' => 'Keperluan tes',
            'status' => 'Aktif',
        ]);

        $response = $this->postJson(route('scan.proses'), [
            'kode_qr' => $qr->nomor_kartu,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.status', IjinKeluar::STATUS_KELUAR);
        $this->assertDatabaseHas('ijin_keluar', [
            'id_qrcode' => $qr->id_qrcode,
            'id_peg' => $pegawai->id_peg,
            'id_ijin' => null,
            'keterangan' => 'Keperluan tes',
            'status' => IjinKeluar::STATUS_KELUAR,
        ]);

        $this->postJson(route('scan.proses'), [
            'kode_qr' => $qr->nomor_kartu,
        ])->assertOk()
            ->assertJsonPath('data.status', IjinKeluar::STATUS_KEMBALI);

        $this->postJson(route('rekap.data'), [
            'filter_applied' => true,
            'periode' => 'harian',
            'tanggal' => now()->toDateString(),
        ])->assertOk()
            ->assertJsonFragment([
                'id_peg' => $pegawai->id_peg,
                'jml_pribadi' => 1,
            ]);
    }

    public function test_lembur_scan_records_start_and_finish_without_creating_ijin()
    {
        $this->withoutMiddleware();
        Carbon::setTestNow('2026-11-11 18:00:00');

        try {
            $suffix = Str::upper(Str::random(8));
            $bagian = Bagians::create([
                'bag' => 'Bagian Lembur ' . $suffix,
                'kode_bag' => 'BL' . $suffix,
            ]);
            $subbag = SubBagians::create([
                'id_bag' => $bagian->id_bag,
                'sub_bag' => 'Subbag Lembur ' . $suffix,
                'kode_subag' => 'SL' . $suffix,
            ]);
            $pegawai = Pegawai::create([
                'id_subag' => $subbag->id_subag,
                'nama' => 'Pegawai Lembur ' . $suffix,
                'nik' => $suffix,
                'jenis_kelamin' => 'L',
                'jabatan' => 'Staff',
                'status' => 'Aktif',
            ]);
            $qr = QrCodes::create([
                'jenis_qr' => 'PEGAWAI',
                'nama_kartu' => 'QR Lembur ' . $suffix,
                'nomor_kartu' => 'LEMBUR-' . $suffix,
                'id_peg' => $pegawai->id_peg,
                'id_subag' => $subbag->id_subag,
                'status' => 'Aktif',
            ]);
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'test-' . $suffix,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $userId = DB::table('users')->insertGetId([
                'id_role' => $roleId,
                'name' => 'Admin Lembur ' . $suffix,
                'username' => 'admin-' . $suffix,
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $lemburId = DB::table('lembur')->insertGetId([
                'id_bag' => $bagian->id_bag,
                'id_subag' => $subbag->id_subag,
                'tanggal_lembur' => '2026-11-11',
                'jenis_lembur' => 'TERENCANA',
                'jam_mulai' => '17:00:00',
                'jam_selesai' => '21:00:00',
                'created_by' => $userId,
                'status' => 'Terjadwal',
                'is_delete' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('detail_lembur')->insert([
                'id_lembur' => $lemburId,
                'id_peg' => $pegawai->id_peg,
                'id_qrcode' => $qr->id_qrcode,
                'status_kehadiran' => 'Belum Hadir',
                'is_delete' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->postJson(route('scan.proses'), [
                'kode_qr' => $qr->nomor_kartu,
                'mode' => 'lembur',
            ])->assertOk()
                ->assertJsonPath('data.status', 'Hadir');

            $lemburLainId = DB::table('lembur')->insertGetId([
                'id_bag' => $bagian->id_bag,
                'id_subag' => $subbag->id_subag,
                'tanggal_lembur' => '2026-11-11',
                'jenis_lembur' => 'DADAKAN',
                'created_by' => $userId,
                'status' => 'Terjadwal',
                'is_delete' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('detail_lembur')->insert([
                'id_lembur' => $lemburLainId,
                'id_peg' => $pegawai->id_peg,
                'id_qrcode' => $qr->id_qrcode,
                'status_kehadiran' => 'Belum Hadir',
                'is_delete' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->postJson(route('scan.proses'), [
                'kode_qr' => $qr->nomor_kartu,
                'mode' => 'lembur',
            ])->assertStatus(409)
                ->assertJsonPath('requires_selection', true)
                ->assertJsonFragment(['id' => $lemburId])
                ->assertJsonFragment(['id' => $lemburLainId]);

            Carbon::setTestNow('2026-11-11 21:15:00');
            $this->postJson(route('scan.proses'), [
                'kode_qr' => $qr->nomor_kartu,
                'mode' => 'lembur',
                'id_lembur' => $lemburId,
            ])->assertOk()
                ->assertJsonPath('data.status', 'Selesai')
                ->assertJsonPath('data.total_menit', 195);

            $this->assertDatabaseHas('detail_lembur', [
                'id_lembur' => $lemburId,
                'id_peg' => $pegawai->id_peg,
                'status_kehadiran' => 'Selesai',
                'total_menit' => 195,
            ]);
            $this->assertDatabaseMissing('ijin_keluar', [
                'id_qrcode' => $qr->id_qrcode,
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_dinas_qr_enforces_exit_start_but_allows_late_return_scan()
    {
        $this->withoutMiddleware();
        Carbon::setTestNow('2026-10-15 07:59:59');

        try {
            $suffix = Str::upper(Str::random(8));
            $bagian = Bagians::create([
                'bag' => 'Bagian ' . $suffix,
                'kode_bag' => 'B' . $suffix,
            ]);
            $subbag = SubBagians::create([
                'id_bag' => $bagian->id_bag,
                'sub_bag' => 'Subbag ' . $suffix,
                'kode_subag' => 'S' . $suffix,
            ]);
            $pegawai = Pegawai::create([
                'id_subag' => $subbag->id_subag,
                'nama' => 'Pegawai ' . $suffix,
                'nik' => $suffix,
                'jenis_kelamin' => 'L',
                'jabatan' => 'Staff',
                'status' => 'Aktif',
            ]);
            $qr = QrCodes::create([
                'jenis_qr' => 'SUBBAG',
                'nama_kartu' => 'QR Dinas ' . $suffix,
                'nomor_kartu' => 'DINAS-' . $suffix,
                'id_peg' => $pegawai->id_peg,
                'id_subag' => $subbag->id_subag,
                'status' => 'Aktif',
                'tanggal_dinas' => '2026-10-15',
                'waktu_keluar' => '08:00',
                'waktu_masuk' => '17:00',
            ]);

            $this->postJson(route('scan.proses'), ['kode_qr' => $qr->nomor_kartu])
                ->assertStatus(422)
                ->assertJsonPath('success', false);
            $this->assertDatabaseMissing('ijin_keluar', [
                'id_qrcode' => $qr->id_qrcode,
            ]);

            Carbon::setTestNow('2026-10-15 08:00:00');
            $this->postJson(route('scan.proses'), ['kode_qr' => $qr->nomor_kartu])
                ->assertOk()
                ->assertJsonPath('success', true);

            Carbon::setTestNow('2026-10-15 17:15:00');
            $this->postJson(route('scan.proses'), ['kode_qr' => $qr->nomor_kartu])
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.status', IjinKeluar::STATUS_KEMBALI);
            $this->assertDatabaseHas('ijin_keluar', [
                'id_qrcode' => $qr->id_qrcode,
                'jam_masuk' => '17:15:00',
                'status' => IjinKeluar::STATUS_KEMBALI,
            ]);

            $this->get(route('keluar.index', ['tanggal' => '2026-10-15']))
                ->assertOk()
                ->assertSee('-15 menit');
        } finally {
            Carbon::setTestNow();
        }
    }
}
