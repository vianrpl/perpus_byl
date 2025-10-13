<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class lokasi_raks extends Model
{
    protected $table = 'lokasi_raks';
    protected $primaryKey = 'id_lokasi';
    public $timestamps = false;

    protected $fillable = ['lantai', 'ruang', 'sisi'];

    // ✅ Tambahin ini biar binding pakai kolom id_lokasi
    public function getRouteKeyName()
    {
        return 'id_lokasi';
    }
}
