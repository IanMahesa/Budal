<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJenisScanToIjinKeluarTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('ijin_keluar', 'jenis_scan')) {
            Schema::table('ijin_keluar', function (Blueprint $table) {
                $table->string('jenis_scan')->nullable()->after('keterangan');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('ijin_keluar', 'jenis_scan')) {
            Schema::table('ijin_keluar', function (Blueprint $table) {
                $table->dropColumn('jenis_scan');
            });
        }
    }
}
