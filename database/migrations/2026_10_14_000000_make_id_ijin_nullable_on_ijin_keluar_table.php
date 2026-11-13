<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakeIdIjinNullableOnIjinKeluarTable extends Migration
{
    public function up()
    {
        $foreignKeyName = $this->getIjinForeignKeyName();
        if ($foreignKeyName) {
            Schema::table('ijin_keluar', function (Blueprint $table) use ($foreignKeyName) {
                $table->dropForeign($foreignKeyName);
            });
        }

        DB::table('ijin_keluar')
            ->whereNotNull('id_ijin')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('perijinan')
                    ->whereColumn('perijinan.id_ijin', 'ijin_keluar.id_ijin');
            })
            ->update(['id_ijin' => null]);

        Schema::table('ijin_keluar', function (Blueprint $table) {
            $table->unsignedBigInteger('id_ijin')->nullable()->change();
        });

        $this->addIjinForeignKey();
    }

    public function down()
    {
        if (DB::table('ijin_keluar')->whereNull('id_ijin')->exists()) {
            throw new RuntimeException(
                'Cannot make ijin_keluar.id_ijin required while transactions without an id_ijin exist.'
            );
        }

        $foreignKeyName = $this->getIjinForeignKeyName();
        if ($foreignKeyName) {
            Schema::table('ijin_keluar', function (Blueprint $table) use ($foreignKeyName) {
                $table->dropForeign($foreignKeyName);
            });
        }

        Schema::table('ijin_keluar', function (Blueprint $table) {
            $table->unsignedBigInteger('id_ijin')->nullable(false)->change();
        });

        $this->addIjinForeignKey();
    }

    private function addIjinForeignKey()
    {
        if ($this->getIjinForeignKeyName()) {
            return;
        }

        Schema::table('ijin_keluar', function (Blueprint $table) {
            $table->foreign('id_ijin')
                ->references('id_ijin')
                ->on('perijinan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    private function getIjinForeignKeyName()
    {
        $foreignKeys = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableForeignKeys('ijin_keluar');

        foreach ($foreignKeys as $foreignKey) {
            if (in_array('id_ijin', $foreignKey->getLocalColumns(), true)) {
                return $foreignKey->getName();
            }
        }

        return null;
    }
}
