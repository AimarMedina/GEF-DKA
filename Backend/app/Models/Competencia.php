<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competencia extends Model
{
    protected $table = 'competencia';
    public $timestamps = false;

    protected $fillable = ['Descripcion'];

    public function compRas()
    {
        return $this->hasMany(CompRa::class, 'ID_Comp', 'id');
    }

}

