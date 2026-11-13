<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttendanceTimesToQrcodeTable extends Migration
{
    public function up()
    {
        Schema::table('qrcode', function (Blueprint $table) {
            $table->time('waktu_keluar')->nullable()->after('tanggal_dinas');
            $table->time('waktu_masuk')->nullable()->after('waktu_keluar');
        });
    }

    public function down()
    {
        Schema::table('qrcode', function (Blueprint $table) {
            $table->dropColumn(['waktu_keluar', 'waktu_masuk']);
        });
    }
}
