<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePegawaiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pegawai', function (Blueprint $table) {

            $table->bigIncrements('id_peg');

            $table->unsignedBigInteger('id_subag');

            $table->string('nama', 100);
            $table->string('nik', 20)->unique();

            $table->enum('jenis_kelamin',[
                'L',
                'P'
            ]);

            $table->enum('jabatan', [
                'Direktur',
                'Manajer',
                'A.Manajer',
                'Staff'
            ]);

            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');

            // Waktu dibuat & diubah
            $table->timestamps();

            $table->foreign('id_subag')
                ->references('id_subag')
                ->on('subag')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pegawai');
    }
}
