<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\QrCodes;
use App\Models\Concerns\HasIsDelete;

class SubBagians extends Model
{
    use HasFactory, HasIsDelete;

    protected $table = 'subag';

    protected $primaryKey = 'id_subag';

    protected $fillable = [
        'id_bag',
        'sub_bag',
        'kode_subag',
        'is_delete',
    ];

    public function bagian()
    {
        return $this->belongsTo(Bagians::class, 'id_bag', 'id_bag');
    }

    public function pegawai()
    {
        return $this->hasMany(Pegawai::class,'id_subag','id_subag');
    }

    public function ijinKeluar()
    {
        return $this->hasMany(IjinKeluar::class,'id_subag','id_subag');
    }

    public function qrcode()
    {
        return $this->hasMany(QrCodes::class, 'id_subag', 'id_subag');
    }

    public function perijinan()
    {
        return $this->hasMany(Perijinan::class, 'id_subag', 'id_subag');
    }
}
