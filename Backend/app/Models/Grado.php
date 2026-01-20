<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grado extends Model
{
    protected $table = 'grado';

    protected $fillable = [
        'Nombre',
        'Curso',
        'ID_Tutor'
    ];

    public function tutor(){
        return $this->belongsTo(Tutor::class, 'ID_Tutor','ID_Usuario');
    }
    public function asignaturas() {
        return $this->hasMany(Asignatura::class, 'ID_Grado', 'id');
    }
    public function competencias(){
        return $this->hasMany(Competencia::class, 'ID_Grado');
    }
} 
