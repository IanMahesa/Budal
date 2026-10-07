<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIsDelete;

class IjinKeluar extends Model
{
    use HasFactory, HasIsDelete;

    protected $table = 'ijin_keluar';

    protected $primaryKey = 'id_transaksi';

    const STATUS_KELUAR = 'Keluar';

    const STATUS_KEMBALI = 'Kembali';

    const SCAN_PEGAWAI = 'PEGAWAI';

    const SCAN_SUBBAG = 'SUBBAG';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_qrcode',
        'id_peg',
        'id_ijin',
        'id_bag',
        'id_subag',
        'jenis_scan',
        'tanggal_keluar',
        'jam_keluar',
        'tanggal_masuk',
        'jam_masuk',
        'durasi_menit',
        'durasi_jam',
        'status',
        'notification_read_at',
        'keterangan',
        'foto_pegawai',
        'created_by',
        'is_delete',
    ];

    protected $casts = [
        'tanggal_keluar'=>'date',
        'tanggal_masuk'=>'date',
        'notification_read_at'=>'datetime',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class,'id_peg','id_peg');
    }

    public function qrcode()
    {
        return $this->belongsTo(QrCodes::class, 'id_qrcode', 'id_qrcode');
    }

    public function perijinan()
    {
        return $this->belongsTo(Perijinan::class,'id_ijin','id_ijin');
    }

    public function bagian()
    {
        return $this->belongsTo(Bagians::class, 'id_bag', 'id_bag');
    }

    public function subbag()
    {
        return $this->belongsTo(SubBagians::class,'id_subag','id_subag');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }
}
