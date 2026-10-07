<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use App\Models\Concerns\HasIsDelete;

class Roles extends SpatieRole
{
    use HasIsDelete;

    protected $fillable = [
        'name',
        'guard_name',
        'is_delete',
    ];
}
