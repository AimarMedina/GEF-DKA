<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntregaCuaderno extends Model
{
    protected $table = 'entrega_cuaderno';
    
    protected $fillable = [
        'Fecha_creacion',
        'Fecha_Limite',
        'ID_Grado '
    ];

    public function alumnoEntrega(){
        return $this->hasMany(AlumnoEntrega::class, 'ID_Entrega','id');
    }

    public function grado (){
        return $this->belongsTo(Grado::class, 'ID_Grado');
    }
}
