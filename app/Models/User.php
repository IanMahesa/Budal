<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Roles;
use App\Models\Bagians;
use App\Models\SubBagians;
use App\Models\Concerns\HasIsDelete;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, HasIsDelete;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'id_role',
        'jabatan',
        'id_bag',
        'id_subag',
        'is_delete',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Roles::class, 'id_role', 'id');
    }

    public function bagian(): BelongsTo
    {
        return $this->belongsTo(Bagians::class, 'id_bag', 'id_bag');
    }

    public function subbagian(): BelongsTo
    {
        return $this->belongsTo(SubBagians::class, 'id_subag', 'id_subag');
    }
}
