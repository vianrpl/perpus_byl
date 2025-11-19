<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'photo',
        'is_verified_member',
        'is_member',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified_member' => 'boolean',
            'is_member' => 'boolean',
        ];
    }

    public function penataan_bukus()
    {
        return $this->hasMany(penataan_bukus::class, 'id_penataan');
    }

    public function peminjaman()
    {
        return $this->hasMany(peminjaman::class, 'id_user');
    }

    // Relasi ke member profile
    public function MemberProfile()
    {
        return $this->hasOne(MemberProfile::class, 'user_id', 'id_user');
    }
}
