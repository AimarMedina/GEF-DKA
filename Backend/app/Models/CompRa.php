<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompRa extends Model
{
    protected $table = 'comp_ra';
    public $timestamps = false;

    protected $fillable = ['ID_Comp', 'ID_Ra', 'ID_Asignatura'];

    public function competencia()
    {
        return $this->belongsTo(Competencia::class, 'ID_Comp', 'ID_Comp');
    }

    public function ra()
    {
        return $this->belongsTo(Ra::class, 'ID_Ra', 'ID_Ra');
    }

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'ID_Asignatura', 'id');
    }
}
