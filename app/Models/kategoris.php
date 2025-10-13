<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class kategoris extends Model
{
    protected $table='kategoris';
    protected $primaryKey='id_kategori';
    public $timestamps=false;
    protected $fillable=['nama_kategori'];
}
