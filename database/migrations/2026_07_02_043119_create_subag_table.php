<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subag', function (Blueprint $table) {
            $table->bigIncrements('id_subag');
            $table->unsignedBigInteger('id_bag');
            $table->string('sub_bag', 100);
            $table->string('kode_subag', 10)->unique();
            $table->timestamps();

             $table->foreign('id_bag')
                  ->references('id_bag')
                  ->on('bagian')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subag');
    }
}
