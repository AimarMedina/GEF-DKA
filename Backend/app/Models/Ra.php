<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ra extends Model
{
    protected $table="ra";
    protected $fillable=[
        "Descripcion",
        "ID_Asignatura"
    ];

    public function asignatura(){
        return $this-> belongsTo(Asignatura::class, 'ID_Asignatura');
    }

    public function compRas(){
        return $this->hasMany(compRa::class,"ID_Ra");
    }

}
