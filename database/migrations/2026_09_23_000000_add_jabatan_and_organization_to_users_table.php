<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('jabatan', 20)->nullable()->after('id_role');
            $table->unsignedBigInteger('id_bag')->nullable()->after('jabatan');
            $table->unsignedBigInteger('id_subag')->nullable()->after('id_bag');

            $table->foreign('id_bag')
                ->references('id_bag')
                ->on('bagian')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreign('id_subag')
                ->references('id_subag')
                ->on('subag')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_bag']);
            $table->dropForeign(['id_subag']);
            $table->dropColumn(['jabatan', 'id_bag', 'id_subag']);
        });
    }
};