<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Bagians;
use App\Models\SubBagians;
use App\Models\Pegawai;
use App\Models\User;

class Lembur extends Model
{
   protected $table = 'lembur';

    protected $primaryKey = 'id_lembur';

    protected $fillable = [
        'id_bag',
        'id_subag',
        'id_atasan',
        'tanggal_lembur',
        'jenis_lembur',
        'jam_mulai',
        'jam_selesai',
        'durasi_menit',
        'keterangan',
        'status',
        'created_by',
        'updated_by',
        'is_delete',
    ];

    protected $casts = [
        'tanggal_lembur' => 'date',
        'durasi_menit' => 'integer',
        'is_delete' => 'boolean',
    ];

    // Relasi ke bagian
    public function bagian()
    {
        return $this->belongsTo(
            Bagians::class,
            'id_bag',
            'id_bag'
        );
    }

    // Relasi ke subbagian
    public function subag()
    {
        return $this->belongsTo(
            SubBagians::class,
            'id_subag',
            'id_subag'
        );
    }

    // Relasi ke atasan
    public function atasan()
    {
        return $this->belongsTo(
            Pegawai::class,
            'id_atasan',
            'id_peg'
        );
    }

    // Relasi ke admin pembuat
    public function pembuat()
    {
        return $this->belongsTo(
            User::class,
            'created_by',
            'id'
        );
    }

    // Relasi ke admin pengubah
    public function pengubah()
    {
        return $this->belongsTo(
            User::class,
            'updated_by',
            'id'
        );
    }

    // Relasi ke detail lembur
    public function detailLembur()
    {
        return $this->hasMany(
            DetailLembur::class,
            'id_lembur',
            'id_lembur'
        );
    }
}