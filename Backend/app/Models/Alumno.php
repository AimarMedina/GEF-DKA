<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    protected $table = 'alumno';
    protected $primaryKey = 'ID_Usuario';
    public $incrementing = false;

    protected $fillable = [
        'ID_Usuario',
        'ID_Grado',
        'ID_Tutor',
        'ID_Instructor'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'ID_Usuario', 'id');
    }

    public function grado()
    {
        return $this->belongsTo(Grado::class, 'ID_Grado');
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'ID_Tutor', 'ID_Usuario');
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'ID_Instructor', 'ID_Usuario');
    }

    public function notasCompetencias()
    {
        return $this->hasMany(NotaCompetencia::class, 'ID_Alumno', 'ID_Usuario');
    }

    public function notasTransversales()
    {
        return $this->hasMany(NotaTransversal::class, 'ID_Alumno', 'ID_Usuario');
    }

    public function estancias()
    {
        return $this->hasMany(EstanciaAlumno::class, 'ID_Alumno', 'ID_Usuario')
            ->orderBy('Fecha_inicio', 'desc');
    }
    public function estanciaActual()
    {
        return $this->hasOne(EstanciaAlumno::class, 'ID_Alumno', 'ID_Usuario')
            ->latestOfMany();
    }

    public function cuadernos()
    {
        return $this->hasMany(
            AlumnoEntrega::class,
            'ID_Alumno',
            'ID_Usuario'
        );
    }

    public function notasEgibide()
    {
        return $this->hasMany(NotaEgibide::class, 'ID_Alumno', 'ID_Usuario');
    }

    public function notaCuaderno()
    {
        return $this->hasOne(NotaCuaderno::class, 'ID_Alumno', 'ID_Usuario');
    }
}
