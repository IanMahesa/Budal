<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\QrCodes;
use App\Models\SubBagians;
use App\Models\ijinKeluar;
use App\Models\Concerns\HasIsDelete;

class Pegawai extends Model
{
    use HasFactory, HasIsDelete;

    protected static function booted(): void
    {
        static::updated(function (Pegawai $pegawai) {
            if ($pegawai->wasChanged('is_delete')) {
                QrCodes::withoutGlobalScopes()
                    ->where('id_peg', $pegawai->id_peg)
                    ->where('jenis_qr', 'PEGAWAI')
                    ->update(['is_delete' => $pegawai->is_delete]);
            }

            if ($pegawai->wasChanged('status')) {
                QrCodes::withoutGlobalScopes()
                    ->where('id_peg', $pegawai->id_peg)
                    ->where('jenis_qr', 'PEGAWAI')
                    ->update([
                        'status' => $pegawai->status === 'Aktif' ? 'Aktif' : 'Nonaktif',
                    ]);
            }

        });
    }

    protected $table = 'pegawai';

    protected $primaryKey = 'id_peg';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_bag',
        'id_subag',
        'nama',
        'nik',
        'jenis_kelamin',
        'jabatan',
        'status',
        'is_delete',
    ];

    public function subbag()
    {
        return $this->belongsTo(SubBagians::class,'id_subag','id_subag');
    }
    
    public function qrcode()
    {
        return $this->hasMany(QrCodes::class, 'id_peg', 'id_peg');
    }

    public function ijinKeluar()
    {
        return $this->hasMany(IjinKeluar::class,'id_peg','id_peg');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id_peg', 'id_peg');
    }
}