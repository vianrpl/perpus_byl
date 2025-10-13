<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class raks extends Model
{
    protected $table='raks';
    protected $primaryKey='id_rak';
    public $timestamps=false;
    protected $fillable=['barcode','nama','kolom','baris','kapasitas','id_lokasi','id_kategori'];
    public function lokasi_raks()
    {
        return $this->belongsTo(lokasi_raks::class,'id_lokasi');
    }
    public function kategoris()
    {
        return $this->belongsTo(kategoris::class, 'id_kategori');
    }

    public function penataan_bukus()
    {
        return $this->hasMany(penataan_bukus::class,'id_rak');
    }
}
