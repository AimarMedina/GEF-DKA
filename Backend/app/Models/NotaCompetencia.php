<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaCompetencia extends Model
{
    protected $table = "nota_competencia";
    protected $primary="ID";
    protected $incrementing=false;
    protected $fillable=[
        "Nota"
    ];

    
    public function alumno()
    {
        return $this->belongsTo(Alumno::class, "ID_Alumno", "ID_Usuario");
    }

    public function competencia()
    {
        return $this->belongsTo(Competencia::class, "ID_Competencia", "ID");
    }
}
