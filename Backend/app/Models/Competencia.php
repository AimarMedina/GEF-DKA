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

    public function compRa(){
        return $this->hasOne(compRa::class,"comp_ra","ID","ID_Comp");
    }
    public function notasCompentencias(){
        return $this->hasMany(NotaCompetencia::class,"ID_Compentencia","ID");
    }

}
