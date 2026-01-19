<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumnoEntrega extends Model
{
    protected $table = 'alumno_entrega';

    protected $fillable = [
        'URL_Cuaderno',
        'Fecha_Entrega',
        'ID_Alumno',
        'ID_Entrega',
        'Observaciones',
        'Feedback'
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class,'ID_Alumno','ID_Usuario');
    }


    public function entrega()
    {
        return $this->belongsTo(EntregaCuaderno::class,'ID_Entrega','id');
    }

    public function nota()
    {
        return $this->hasOne(NotaCuaderno::class, 'ID_Alumno', 'ID_Usuario');
    }

}
