<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sub_kategoris extends Model
{
    protected $table='sub_kategoris';
    protected $primaryKey='id_sub';
    public $timestamps=false;
    protected $fillable=['nama_sub_kategori'];
}
