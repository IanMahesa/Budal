<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIjinKeluarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ijin_keluar', function (Blueprint $table) {
            $table->bigIncrements('id_transaksi');

            $table->unsignedBigInteger('id_qrcode');

            $table->unsignedBigInteger('id_peg');

            $table->unsignedBigInteger('id_ijin');

            $table->unsignedBigInteger('id_bag');
            $table->unsignedBigInteger('id_subag');

            $table->date('tanggal_keluar');
            $table->time('jam_keluar');

            $table->date('tanggal_masuk')->nullable();
            $table->time('jam_masuk')->nullable();

            $table->integer('durasi_menit')->nullable();

            $table->enum('status', [
                'Keluar',
                'Kembali'
            ])->default('Keluar');

            $table->text('keterangan')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->foreign('id_qrcode')
                ->references('id_qrcode')
                ->on('qrcode')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('id_peg')
                ->references('id_peg')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('id_ijin')
                ->references('id_ijin')
                ->on('perijinan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('id_bag')
                ->references('id_bag')
                ->on('bagian')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('id_subag')
                ->references('id_subag')
                ->on('subag')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ijin_keluar');
    }
}
