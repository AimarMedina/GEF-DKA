<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competencia extends Model
{
    protected $table="competencia";
    protected $fillable=[
        "descripcion",
        "ID_Grado"
    ];
    public function compRas()
    {
        return $this->hasMany(CompRa::class, 'ID_Comp', 'id');
    }
    public function grado(){
        return $this-> belongsTo(Grado::class, "ID_Grado");
    }

}

