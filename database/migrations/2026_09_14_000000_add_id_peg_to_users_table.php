<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdPegToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('id_peg')->nullable()->unique()->after('id_role');
            $table->foreign('id_peg')
                ->references('id_peg')
                ->on('pegawai')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_peg']);
            $table->dropUnique(['id_peg']);
            $table->dropColumn('id_peg');
        });
    }
}
