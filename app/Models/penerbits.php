<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class penerbits extends Model
{
    protected $table='penerbits';
    protected $primaryKey='id_penerbit';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps=false;
    protected $fillable=['nama_penerbit','alamat','no_telepon','email'];

    public function bukus()
    {
        return $this->hasMany(bukus::class, 'id_penerbit', 'id_penerbit');
    }


}
