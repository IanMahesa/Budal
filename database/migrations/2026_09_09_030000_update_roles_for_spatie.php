<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRolesForSpatie extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_role']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropPrimary(['id_role']);
            $table->renameColumn('id_role', 'id');
            $table->renameColumn('nama_role', 'name');
            $table->string('guard_name')->after('name');
            $table->tinyInteger('is_delete')->nullable()->after('updated_at');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->primary('id');
            $table->unique(['name', 'guard_name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('id_role')
                ->references('id')
                ->on('roles')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_role']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['name', 'guard_name']);
            $table->dropPrimary(['id']);
            $table->dropColumn(['guard_name', 'is_delete']);
            $table->renameColumn('name', 'nama_role');
            $table->renameColumn('id', 'id_role');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->primary('id_role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('id_role')
                ->references('id_role')
                ->on('roles')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }
}