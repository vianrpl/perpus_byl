<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class bukus extends Model
{
    protected $table = 'bukus';
    protected $primaryKey = 'id_buku';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = ['judul', 'id_penerbit','pengarang','tahun_terbit','id_kategori','isbn', 'id_sub', 'barcode','jumlah','jumlah_tata'];

    public function items()
    {
        return $this->hasMany(buku_items::class, 'id_buku');
    }

    // Relasi balik ke penataan_bukus (INI YANG HILANG – tambahin ini!)
    public function penataan_bukus()
    {
        return $this->hasMany(penataan_bukus::class, 'id_buku', 'id_buku');
    }

    // Relasi ke penerbit
    public function penerbits()
    {
        return $this->belongsTo(penerbits::class, 'id_penerbit');
    }

    // Relasi ke kategori
    public function kategoris()
    {
        return $this->belongsTo(kategoris::class, 'id_kategori');
    }

    // Relasi ke sub kategori
    public function sub_kategoris()
    {
        return $this->belongsTo(sub_kategoris::class, 'id_sub');
    }

    // Accessor: Hitung sisa available (total jumlah - sum penataan)
    public function getAvailableAttribute()
    {
        return $this->jumlah - $this->penataan_bukus()->sum('jumlah');
    }
}
