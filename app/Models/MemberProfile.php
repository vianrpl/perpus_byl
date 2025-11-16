<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberProfile extends Model
{
    protected $table = 'member_profiles';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id_user');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by', 'id_user');
    }
}
