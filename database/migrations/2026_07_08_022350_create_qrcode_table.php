<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQrcodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qrcode', function (Blueprint $table) {

            $table->bigIncrements('id_qrcode');

            $table->enum('jenis_qr', [
                'PEGAWAI',
                'SUBBAG'
            ]);

            $table->string('nama_kartu',100);

            $table->string('nomor_kartu',30)->unique();

            $table->unsignedBigInteger('id_peg')->nullable();

            $table->unsignedBigInteger('id_subag')->nullable();

            $table->unsignedBigInteger('id_ijin')->nullable();

            $table->enum('status',[
                'Aktif',
                'Nonaktif'
            ])->default('Aktif');

            $table->timestamp('tanggal_generate')->nullable();

            $table->timestamp('tanggal_cetak')->nullable();

            $table->timestamps();

            $table->foreign('id_peg')
                    ->references('id_peg')
                    ->on('pegawai')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();

            $table->foreign('id_subag')
                    ->references('id_subag')
                    ->on('subag')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();      
                    
            $table->foreign('id_ijin')
                    ->references('id_ijin')
                    ->on('perijinan')
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
        Schema::dropIfExists('qrcode');
    }
}
