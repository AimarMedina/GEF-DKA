<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaCuaderno extends Model
{
    protected $table= 'notas_cuaderno';

    protected $fillable = [
        'ID_Cuaderno',
        'ID_Tutor',
        'Fecha',
        'Nota'
    ];

    public function cuaderno(){
        return $this-> belongsTo(AlumnoEntrega::class, 'ID_Cuaderno');
    }
}
