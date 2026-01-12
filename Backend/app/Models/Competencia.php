<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competencia extends Model
{
    protected $table="competencia";
    protected $primary="ID";
    protected $incrementing=false;
    protected $fillable=[
        "Descripcion"
    ];

    public function ras(){
        return $this->belongsToMany(Ra::class,"comp_ra","ID_Comp","ID_Ra");
    }
    public function notasCompentencias(){
        return $this->hasMany(NotaCompetencia::class,"ID_Compentencia","ID");
    }

}
