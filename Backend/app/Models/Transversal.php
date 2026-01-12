<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transversal extends Model
{
    protected $table = "transversal";
    protected $fillable = [
        'Descripcion'
    ];

    public function notasTransversales(){
        return $this->hasMany(NotaTransversal::class,'ID_Transversal');
    }
}
