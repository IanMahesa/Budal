<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIsDelete;

class Perijinan extends Model
{
    use HasFactory, HasIsDelete;

    protected $table = 'perijinan';

    protected $primaryKey = 'id_ijin';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'izin',
        'kode',
        'jenis',
        'id_subag',
        'is_delete',
    ];

    public function ijinKeluar()
    {
        return $this->hasMany(IjinKeluar::class, 'id_ijin', 'id_ijin');
    }

    public function subbag()
    {
        return $this->belongsTo(SubBagians::class, 'id_subag', 'id_subag');
    }

    public function qrcode()
    {
        return $this->hasMany(QrCodes::class, 'id_ijin', 'id_ijin');
    }
}
