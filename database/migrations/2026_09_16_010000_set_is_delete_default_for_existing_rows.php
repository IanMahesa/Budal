<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
            DB::table($tableName)->whereNull('is_delete')->update(['is_delete' => 0]);
        }
    }

    public function down(): void
    {
        // Existing NULL values cannot be distinguished from rows created after this migration.
    }
};