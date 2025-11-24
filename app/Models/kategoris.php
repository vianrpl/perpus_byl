<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class kategoris extends Model
{
    use HasFactory;

    protected $table = 'kategoris';
    protected $primaryKey = 'id_kategori';
    public $timestamps = false;

    protected $fillable = [
        'nama_kategori',
    ];

    // Relasi ke bukus (SUDAH ADA, JANGAN DIHAPUS)
    public function bukus()
    {
        return $this->hasMany(bukus::class, 'id_kategori', 'id_kategori');
    }

    // Relasi Many to Many ke sub_kategoris (TAMBAHKAN INI)
    // Ini yang bikin kategori bisa punya banyak sub kategori
    public function sub_kategoris()
    {
        return $this->belongsToMany(
            sub_kategoris::class,      // Model tujuan
            'kategori_sub_kategori',   // Nama tabel pivot
            'id_kategori',             // Foreign key kategori di tabel pivot
            'id_sub'                   // Foreign key sub kategori di tabel pivot
        )->withTimestamps();           // Simpan created_at & updated_at
    }
}
