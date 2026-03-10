<?php

namespace Database\Factories;

use App\Models\Grado;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradoFactory extends Factory
{
    protected $model = Grado::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->word,
            'curso' => $this->faker->numberBetween(1, 6),
            'ID_Tutor' => null, // se puede asignar luego en los tests
        ];
    }
}
