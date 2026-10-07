<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ijin_keluar', function (Blueprint $table) {
            $table->timestamp('notification_read_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('ijin_keluar', function (Blueprint $table) {
            $table->dropColumn('notification_read_at');
        });
    }
};
