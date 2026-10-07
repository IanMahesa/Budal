<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTanggalDinasToQrcodeTable extends Migration
{
    public function up()
    {
        Schema::table('qrcode', function (Blueprint $table) {
            $table->date('tanggal_dinas')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('qrcode', function (Blueprint $table) {
            $table->dropColumn('tanggal_dinas');
        });
    }
}