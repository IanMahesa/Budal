<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateRolesForSpatie extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE users DROP INDEX users_id_role_foreign');
        DB::statement("ALTER TABLE roles
            DROP PRIMARY KEY,
            CHANGE id_role id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE nama_role name VARCHAR(255) NOT NULL,
            ADD guard_name VARCHAR(255) NOT NULL DEFAULT 'web' AFTER name,
            ADD is_delete TINYINT NULL AFTER updated_at,
            ADD PRIMARY KEY (id),
            ADD UNIQUE KEY roles_name_guard_name_unique (name, guard_name)");
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_id_role_foreign
            FOREIGN KEY (id_role) REFERENCES roles (id)
            ON UPDATE CASCADE ON DELETE RESTRICT');
    }

    public function down()
    {
        DB::statement('ALTER TABLE users DROP FOREIGN KEY users_id_role_foreign');
        DB::statement("ALTER TABLE roles
            DROP INDEX roles_name_guard_name_unique,
            DROP PRIMARY KEY,
            DROP COLUMN guard_name,
            DROP COLUMN is_delete,
            CHANGE name nama_role VARCHAR(20) NOT NULL,
            CHANGE id id_role BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            ADD PRIMARY KEY (id_role)");
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_id_role_foreign
            FOREIGN KEY (id_role) REFERENCES roles (id_role)
            ON UPDATE CASCADE ON DELETE RESTRICT');
    }
}