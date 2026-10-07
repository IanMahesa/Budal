<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubBagians;
use App\Models\Concerns\HasIsDelete;

class Bagians extends Model
{
    use HasFactory, HasIsDelete;

    protected $table = 'bagian';

    protected $primaryKey = 'id_bag';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'bag',
        'kode_bag',
        'is_delete',
    ];

    public function subag()
    {
        return $this->hasMany(SubBagians::class, 'id_bag', 'id_bag');
    }
}
