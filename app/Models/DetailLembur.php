<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Lembur;
use App\Models\Pegawai;
use App\Models\QrCodes;

class DetailLembur extends Model
{
    protected $table = 'detail_lembur';

    protected $primaryKey = 'id_detail_lembur';

    protected $fillable = [
        'id_lembur',
        'id_peg',
        'id_qrcode',
        'jam_mulai',
        'jam_selesai',
        'total_menit',
        'status_kehadiran',
        'is_delete',
    ];

    protected $casts = [
        'jam_mulai' => 'datetime',
        'jam_selesai' => 'datetime',
        'total_menit' => 'integer',
        'is_delete' => 'boolean',
    ];

    // Relasi ke kegiatan lembur
    public function lembur()
    {
        return $this->belongsTo(
            Lembur::class,
            'id_lembur',
            'id_lembur'
        );
    }

    // Relasi ke pegawai
    public function pegawai()
    {
        return $this->belongsTo(
            Pegawai::class,
            'id_peg',
            'id_peg'
        );
    }

    // Relasi ke QR Code pegawai
    public function qrcode()
    {
        return $this->belongsTo(
            QrCodes::class,
            'id_qrcode',
            'id_qrcode'
        );
    }
}
