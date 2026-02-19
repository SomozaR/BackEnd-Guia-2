<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BooksSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('seeders/books.csv');

        if (file_exists($csvPath)) {
            if (($handle = fopen($csvPath, 'r')) !== false) {
                $header = fgetcsv($handle);
                while (($row = fgetcsv($handle)) !== false) {
                    if (!$header || !is_array($header) || count($header) !== count($row)) {
                        continue;
                    }

                    $data = array_combine($header, $row);

                    $total = isset($data['copias_totales']) ? (int) $data['copias_totales'] : 0;
                    $available = isset($data['copias_disponibles']) ? (int) $data['copias_disponibles'] : 0;
                    $available = min($available, $total);

                    Book::create([
                        'titulo' => $data['titulo'] ?? '',
                        'descripcion' => $data['descripcion'] ?? null,
                        'isbn' => isset($data['isbn']) && $data['isbn'] !== '' ? (int) $data['isbn'] : null,
                        'copias_totales' => $total,
                        'copias_disponibles' => $available,
                        'disponible' => $available > 0,
                    ]);
                }
                fclose($handle);
            }
        }

    }
}
