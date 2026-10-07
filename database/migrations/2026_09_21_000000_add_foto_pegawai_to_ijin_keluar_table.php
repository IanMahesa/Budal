<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ijin_keluar', 'foto_pegawai')) {
            Schema::table('ijin_keluar', function (Blueprint $table) {
                $table->string('foto_pegawai')->nullable()->after('keterangan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ijin_keluar', 'foto_pegawai')) {
            Schema::table('ijin_keluar', function (Blueprint $table) {
                $table->dropColumn('foto_pegawai');
            });
        }
    }
};