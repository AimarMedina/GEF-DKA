<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorAsignatura extends Model {
    protected $table = 'tutor_asignatura';
    public $timestamps = false;

    protected $fillable = [
        'ID_Tutor',
        'ID_Asignatura'
    ];

    /**
     * Relación: Tutor
     */
    public function tutor() {
        return $this->belongsTo(Tutor::class, 'ID_Tutor', 'ID_Usuario');
    }

    /**
     * Relación: Asignatura
     */
    public function asignatura() {
        return $this->belongsTo(Asignatura::class, 'ID_Asignatura');
    }
}
