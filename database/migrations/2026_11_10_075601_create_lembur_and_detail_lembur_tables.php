
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ==========================================
        // TABEL LEMBUR
        // ==========================================
        Schema::create('lembur', function (Blueprint $table) {
            $table->id('id_lembur');

            // Relasi unit kerja
            $table->unsignedBigInteger('id_bag');
            $table->unsignedBigInteger('id_subag')->nullable();

            // Atasan otomatis berdasarkan bagian dan subbagian
            $table->unsignedBigInteger('id_atasan')->nullable();

            // Informasi lembur
            $table->date('tanggal_lembur');

            $table->enum('jenis_lembur', [
                'TERENCANA',
                'DADAKAN'
            ])->default('TERENCANA');

            // Jadwal lembur (boleh kosong untuk lembur dadakan)
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();

            // Durasi rencana dalam menit
            $table->unsignedInteger('durasi_menit')->nullable();

            $table->text('keterangan')->nullable();

            $table->enum('status', [
                'Terjadwal',
                'Berlangsung',
                'Selesai'
            ])->default('Terjadwal');

            // Audit admin
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->boolean('is_delete')->default(0);

            $table->timestamps();

            // Foreign key
            $table->foreign('id_bag')
                ->references('id_bag')
                ->on('bagian')
                ->restrictOnDelete();

            $table->foreign('id_subag')
                ->references('id_subag')
                ->on('subag')
                ->nullOnDelete();

            $table->foreign('id_atasan')
                ->references('id_peg')
                ->on('pegawai')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['tanggal_lembur', 'status']);
            $table->index('is_delete');
        });


        // ==========================================
        // TABEL DETAIL LEMBUR
        // ==========================================
        Schema::create('detail_lembur', function (Blueprint $table) {
            $table->id('id_detail_lembur');

            // Relasi kegiatan lembur
            $table->unsignedBigInteger('id_lembur');

            // Identitas pegawai
            $table->unsignedBigInteger('id_peg');

            // QR Code pegawai
            $table->unsignedBigInteger('id_qrcode')->nullable();

            // Waktu aktual hasil scan QR
            $table->dateTime('jam_mulai')->nullable();
            $table->dateTime('jam_selesai')->nullable();

            // Durasi aktual dalam menit
            $table->unsignedInteger('total_menit')->nullable();

            $table->enum('status_kehadiran', [
                'Belum Hadir',
                'Hadir',
                'Selesai'
            ])->default('Belum Hadir');

            $table->boolean('is_delete')->default(0);

            $table->timestamps();

            // Foreign key
            $table->foreign('id_lembur')
                ->references('id_lembur')
                ->on('lembur')
                ->cascadeOnDelete();

            $table->foreign('id_peg')
                ->references('id_peg')
                ->on('pegawai')
                ->restrictOnDelete();

            $table->foreign('id_qrcode')
                ->references('id_qrcode')
                ->on('qrcode')
                ->nullOnDelete();

            // Index
            $table->index(['id_lembur', 'id_peg']);
            $table->index('status_kehadiran');
            $table->index('is_delete');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_lembur');
        Schema::dropIfExists('lembur');
    }
};
