<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerijinanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perijinan', function (Blueprint $table) {

            $table->bigIncrements('id_ijin');
            
            $table->enum('jenis',[
                'PRIBADI',
                'DINAS'
            ]);
            
            $table->unsignedBigInteger('id_subag')->nullable();
            
            $table->string('izin', 100);
            
            $table->string('kode', 5)->unique();
            
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
        Schema::dropIfExists('perijinan');
    }
}
