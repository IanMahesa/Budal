<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJabatanBagianSubagToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('users', function (Blueprint $table) {

            $table->string('jabatan')->nullable()->after('username');

            $table->unsignedBigInteger('id_bag')
                ->nullable()
                ->after('jabatan');

            $table->unsignedBigInteger('id_subag')
                ->nullable()
                ->after('id_bag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'jabatan',
                'id_bag',
                'id_subag',
            ]);
        });
    }
}
