<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddBagianToPegawaiTable extends Migration
{
    public function up()
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->unsignedBigInteger('id_bag')->nullable()->after('id_subag');
            $table->foreign('id_bag')
                ->references('id_bag')
                ->on('bagian')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        DB::table('pegawai')
            ->join('subag', 'pegawai.id_subag', '=', 'subag.id_subag')
            ->update(['pegawai.id_bag' => DB::raw('subag.id_bag')]);

        Schema::table('pegawai', function (Blueprint $table) {
            $table->unsignedBigInteger('id_subag')->nullable()->change();
        });
    }

    public function down()
    {
        if (DB::table('pegawai')->whereNull('id_subag')->exists()) {
            throw new RuntimeException(
                'Cannot roll back the pegawai bagian migration while employees have no subbagian.'
            );
        }

        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropForeign(['id_bag']);
            $table->dropColumn('id_bag');
            $table->unsignedBigInteger('id_subag')->nullable(false)->change();
        });
    }
}
