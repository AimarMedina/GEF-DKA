<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $primaryKey = 'CIF';
    protected $table = 'empresa';
    protected $fillable = [
        'CIF',
        'Nombre',
        'Direccion',
        'Email',
        'N_Tel'
    ];

    public function estancia(){
        return $this->hasOne(EstanciaAlumno::class);
    }
    public function instructor(){
        return $this->hasMany(Instructor::class,'ID_Usuario');
    }
}
