<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'bagian',
            'subag',
            'perijinan',
            'pegawai',
            'qrcode',
            'ijin_keluar',
            'users',
            'roles',
            'password_resets',
            'failed_jobs',
            'personal_access_tokens',
            config('permission.table_names.permissions'),
            config('permission.table_names.model_has_permissions'),
            config('permission.table_names.model_has_roles'),
            config('permission.table_names.role_has_permissions'),
        ];

        foreach (array_unique(array_filter($tables)) as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'is_delete')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->boolean('is_delete')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'bagian',
            'subag',
            'perijinan',
            'pegawai',
            'qrcode',
            'ijin_keluar',
            'users',
            'roles',
            'password_resets',
            'failed_jobs',
            'personal_access_tokens',
            config('permission.table_names.permissions'),
            config('permission.table_names.model_has_permissions'),
            config('permission.table_names.model_has_roles'),
            config('permission.table_names.role_has_permissions'),
        ];

        foreach (array_unique(array_filter($tables)) as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'is_delete')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('is_delete');
                });
            }
        }
    }
};
