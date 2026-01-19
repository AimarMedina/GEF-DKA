<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seguimiento extends Model
{
    protected $table = 'Seguimiento';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID_Estancia',
        'Fecha',
        'Hora',
        'Accion_seguimiento',
        'Seguimiento_actividad'
    ];

    public function estancia(){
        return $this->belongsTo(EstanciaAlumno::class, 'ID_Estancia', 'id'); 
    }
}
