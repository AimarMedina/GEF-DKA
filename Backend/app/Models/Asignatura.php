<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\CssSelector\Node\FunctionNode;

class Asignatura extends Model
{
    protected $table = 'asignatura';

    protected $fillable = [
        'Nombre',
        'ID_Tutor'
    ];

    public function grado(){
        return $this-> belongsTo(Grado::class, 'ID_Grado');
    }

    public function compsRa(){
        return $this -> hasMany(CompRa::class, 'ID_Grado');
    }

    public function notaEgibide(){
        return $this -> hasOne('ID_Asignatura');
    }
}
