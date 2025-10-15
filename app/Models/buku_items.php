<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class buku_items extends Model
{
    protected $table = 'buku_items';
    protected $primaryKey = 'id_item';
    protected $fillable = ['id_buku', 'kondisi', 'status','sumber','id_rak','barcode'];

    protected $dates = ['insert_date', 'modified_date'];

    public $timestamps=true;

    const CREATED_AT = 'insert_date';
    const UPDATED_AT = 'modified_date';

    //relasi ke model buku
    public function bukus()
    {
        return $this->belongsTo(bukus::class, 'id_buku');
    }

    //relasi ke model rak
    public function raks()
    {
        return $this->belongsTo(raks::class, 'id_rak');
    }

    public function penataan_bukus()
    {
        // penataan_bukus model biasanya bernama PenataanBukus atau penataan_bukus
        return $this->hasMany(\App\Models\penataan_bukus::class, 'id_buku', 'id_buku');
    }
    public function peminjaman()
    {
        return $this->hasMany(peminjaman::class, 'id_item');
    }

}
