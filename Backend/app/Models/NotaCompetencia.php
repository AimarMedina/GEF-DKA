<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaCompetencia extends Model
{
    // Nombre de la tabla si no es el plural automático
    protected $table = 'nota_competencia'; 

    // CAMPOS PERMITIDOS (Deben coincidir con tu tabla de la BD)
    protected $fillable = [
        'ID_Competencia', 
        'ID_Alumno', 
        'Nota'
    ];
    
    public function alumno()
    {
        return $this->belongsTo(Alumno::class, "ID_Alumno", "ID_Usuario");
    }

    public function competencia()
    {
        return $this->belongsTo(Competencia::class, "ID_Competencia", "id");
    }
}
