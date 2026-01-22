<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstanciaAlumno extends Model
{
    protected $table = "estancia_alumno";
    protected $fillable = [
        'ID_Alumno',
        'CIF_Empresa',
        'Fecha_inicio',
        'Fecha_fin'
    ];

    public function horario()
    {
        return $this->hasMany(Horario::class, 'ID_Estancia', 'id');
    }
    public function seguimiento()
    {
        return $this->hasOne(Seguimiento::class, 'ID_Estancia');
    }
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'CIF_Empresa', 'CIF');
    }
    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'ID_Alumno', 'ID_Usuario');
    }
    public function competencias()
    {
        return $this->belongsToMany(
            Competencia::class,
            'comp_estancia',
            'ID_Estancia',
            'ID_Comp'
        );
    }
}
