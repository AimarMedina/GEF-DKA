<?php

namespace Database\Factories;

use App\Models\Alumno;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlumnoFactory extends Factory
{
    protected $model = Alumno::class;

    public function definition()
    {
        return [
            'ID_Usuario' => null,
            'ID_Grado' => null,
            'ID_Tutor' => null,
            'ID_Instructor' => null,
        ];
    }

    public function forTutor($tutorId)
    {
        return $this->state(fn() => ['ID_Tutor' => $tutorId]);
    }

    public function forInstructor($instructorId)
    {
        return $this->state(fn() => ['ID_Instructor' => $instructorId]);
    }
}
