<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Tutor extends Model
{
    protected $table = "tutor";
    protected $primaryKey = "ID_Usuario";
    protected $fillable = ['ID_Usuario'];

    public function user()
    {
        return $this->belongsTo(User::class, "ID_Usuario", "id");
    }

    public function grados()
    {
        return $this->belongsToMany(Grado::class, "tutor_grado", "ID_Tutor", "ID_Grado");
    }
    public function instructores()
    {
        return $this->belongsToMany(Instructor::class, "tutor_instructor", "ID_Tutor", "ID_Instructor");
    }

    public function alumnos()
    {
        return $this->hasMany(Alumno::class, 'ID_Tutor', 'ID_Usuario');
    }
}
