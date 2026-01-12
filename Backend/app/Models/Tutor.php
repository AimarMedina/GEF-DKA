<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{
    protected $table = "tutor";
    protected $primary="ID_Usuario";
<<<<<<< HEAD
=======
    public $incrementing = false;
>>>>>>> 2147e41d40a0579ca9c927e7e412719f4a65c7e5
    protected function tutor(){
        return $this->belongsTo(User::class,"ID_Usuario","ID");
    }

    protected function grados(){
        return $this->belongsToMany(Grado::class,"tutor_grado","ID_Tutor","ID_Grado");
    }
    protected function instructores(){
        return $this->belongsToMany(Instructor::class,"tutor_instructor","ID_Tutor","ID_Instructor");
    }
    
}
