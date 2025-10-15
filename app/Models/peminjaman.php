<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';
    protected $primaryKey = 'id_peminjaman';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_buku',
        'id_item',
        'pinjam',
        'pengembalian',
        'status',
        'kondisi',
        'alamat'
    ];

    // relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // relasi ke buku
    public function buku()
    {
        return $this->belongsTo(Buku::class, 'id_buku');
    }

    // relasi ke buku_item
    public function item()
    {
        return $this->belongsTo(BukuItem::class, 'id_item');
    }
}
