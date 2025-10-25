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
        'id_buku',
        'id_user',
        'pinjam',
        'pengembalian',
        'kondisi',
        'status',
        'id_item',
        'alamat',
        'request_status',
        'approved_by',
        'approved_at',
        'nama_peminjam'];

    // relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // relasi ke buku
    public function bukus()
    {
        return $this->belongsTo(bukus::class, 'id_buku');
    }

    // relasi ke buku_item
    public function item()
    {
        return $this->belongsTo(buku_items::class, 'id_item');
    }

    public function approvedBy() {
        return $this->belongsTo(User::class, 'approved_by', 'id_user');
    }
}
