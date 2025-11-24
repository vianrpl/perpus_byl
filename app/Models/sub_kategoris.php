<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sub_kategoris extends Model
{
    use HasFactory;

    protected $table = 'sub_kategoris';
    protected $primaryKey = 'id_sub';
    public $timestamps = false;

    protected $fillable = [
        'nama_sub_kategori',
    ];

    // Relasi ke bukus (SUDAH ADA, JANGAN DIHAPUS)
    public function bukus()
    {
        return $this->hasMany(bukus::class, 'id_sub', 'id_sub');
    }

    // Relasi Many to Many ke kategoris (TAMBAHKAN INI)
    // Ini yang bikin sub kategori bisa dipake banyak kategori
    public function kategoris()
    {
        return $this->belongsToMany(
            kategoris::class,          // Model tujuan
            'kategori_sub_kategori',   // Nama tabel pivot
            'id_sub',                  // Foreign key sub kategori di tabel pivot
            'id_kategori'              // Foreign key kategori di tabel pivot
        )->withTimestamps();           // Simpan created_at & updated_at
    }
}
