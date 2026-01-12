<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ra extends Model
{
    protected $table="ra";
    protected $primary="ID";
    protected $incrementing=false;
    protected $fillable=[
        "Descripcion"
    ];

    public function competencias(){
        return $this->belongsToMany(Competencia::class,"comp_ra","ID_Ra","ID_Comp");
    }
    public function asignaturas(){
        return $this->belongsToMany(Asignatura::class,"comp_ra","ID_Ra","ID_Asignatura");
    }
}
