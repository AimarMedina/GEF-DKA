<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaTransversal extends Model
{
    protected $table = "nota_transversal";
    protected $fillable = [
        'ID_Transversal',
        'ID_Alumno',
        'Nota'
    ];

    public function transversal(){
        return $this->belongsTo(Transversal::class,'ID_Transversal','id');
    }
    public function alumno(){
        return $this->belongsTo(Alumno::class,'ID_Alumno','ID_Usuario');
    }
}
