<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ra extends Model
{
    protected $table = 'ra';
    public $timestamps = false;

    protected $fillable = ['Descripcion', 'ID_Asignatura'];

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'ID_Asignatura');
    }

    public function compRas()
    {
        return $this->hasMany(CompRa::class, 'ID_Ra', 'id');
    }
}

