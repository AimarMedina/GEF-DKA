<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = "horario";
    protected $fillable = [
        'ID_Estancia',
        'Dias',
        'Horario1',
        'Horario2',
    ];

    public function estancia(){
        return $this->belongsTo(EstanciaAlumno::class);
    }
}
