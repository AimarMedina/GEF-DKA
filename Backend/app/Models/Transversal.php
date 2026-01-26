<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transversal extends Model
{
    protected $table = "transversales";
    protected $fillable = [
        'descripcion'
    ];

    public function notasTransversales(){
        return $this->hasMany(NotaTransversal::class,'ID_Transversal');
    }
}
