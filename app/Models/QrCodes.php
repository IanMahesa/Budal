<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIsDelete;

class QrCodes extends Model
{
    use HasFactory, HasIsDelete;

    protected $table = 'qrcode';

    protected $primaryKey = 'id_qrcode';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'jenis_qr',
        'nama_kartu',
        'nomor_kartu',
        'id_peg',
        'id_subag',
        'id_ijin',
        'status',
        'ket',
        'tanggal_dinas',
        'tanggal_generate',
        'tanggal_cetak',
        'is_delete',
    ];

     protected $casts = [
          'tanggal_dinas'    => 'date',
        'tanggal_generate' => 'datetime',
        'tanggal_cetak'    => 'datetime',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_peg', 'id_peg');
    }

    public function subbag()
    {
        return $this->belongsTo(SubBagians::class, 'id_subag', 'id_subag');
    }

    public function perijinan()
    {
        return $this->belongsTo(Perijinan::class, 'id_ijin', 'id_ijin');
    }

    public function ijinKeluar()
    {
        return $this->hasMany(IjinKeluar::class, 'id_qrcode', 'id_qrcode');
    }

    public function getIsPegawaiAttribute()
    {
        return $this->jenis_qr === 'PEGAWAI';
    }

public function getIsSubbagAttribute()
    {
        return $this->jenis_qr === 'SUBBAG';
    }
}
