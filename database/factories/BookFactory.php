<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        $total = $this->faker->numberBetween(1, 10);
        $available = $this->faker->numberBetween(0, $total);

        return [
            'titulo' => $this->faker->sentence(3, true),
            'descripcion' => $this->faker->paragraph(),
            'isbn' => (int) $this->faker->unique()->numberBetween(1000000000000, 9999999999999),
            'copias_totales' => $total,
            'copias_disponibles' => $available,
            'disponible' => $available > 0,
        ];
    }
}
