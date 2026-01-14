<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstanciaAlumno extends Model
{
    protected $table = "estancia_alumno";
    protected $fillable = [
        'ID_Alumno',
        'CIF_Empresa',
        'Fecha_Inicio',
        'Fecha_Fin'
    ];

    public function horario(){
        return $this->hasMany(Horario::class,'ID_Horario');
    }
    public function seguimiento(){
        return $this->hasOne(Seguimiento::class,'ID_Estancia');
    }
    public function empresa(){
        return $this->belongsTo(Empresa::class,'CIF');
    }
    public function alumno(){
        return $this->belongsTo(Alumno::class,'ID_ALumno','ID_Usuario');
    }
}
