<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Loan;
use App\Http\Requests\StoreLoanRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
        public function index(Request $request)
        {
            $query = Book::query();

            if ($request->has('titulo')) {
                $query->where('titulo', 'like', '%' . $request->titulo . '%');
            }
            if ($request->has('isbn')) {
                $query->where('isbn', $request->isbn);
            }

            return response()->json($query->paginate(10));
        }

        public function store(Request $request)
        {
            $validated = $request->validate([
                'titulo' => 'required|string',
                'descripcion' => 'nullable|string',
                'isbn' => 'required|numeric|unique:libros,isbn',
                'copias_totales' => 'required|integer|min:0',
                'copias_disponibles' => 'sometimes|integer|min:0',
            ]);

            if (isset($validated['copias_disponibles'])) {
                $validated['copias_disponibles'] = min($validated['copias_disponibles'], $validated['copias_totales']);
            } else {
                $validated['copias_disponibles'] = $validated['copias_totales'];
            }

            $validated['disponible'] = $validated['copias_disponibles'] > 0;

            $book = Book::create($validated);

            return response()->json($book, 201);
        }

        public function show($id)
        {
            $book = Book::find($id);
            if (!$book) return response()->json(['message' => 'Libro no encontrado'], 404);
            return response()->json($book);
        }

        public function update(Request $request, $id)
        {
            $book = Book::find($id);
            if (!$book) return response()->json(['message' => 'Libro no encontrado'], 404);

            $isPut = $request->isMethod('put');
            $rules = [
                'titulo' => [$isPut ? 'required' : 'sometimes', 'string'],
                'descripcion' => ['nullable', 'string'],
                'isbn' => [$isPut ? 'required' : 'sometimes', 'numeric', Rule::unique('libros')->ignore($book->id)],
                'copias_totales' => [$isPut ? 'required' : 'sometimes', 'integer', 'min:0'],
                'copias_disponibles' => ['sometimes', 'integer', 'min:0'],
            ];

            $validated = $request->validate($rules);

            if (isset($validated['copias_totales']) && isset($validated['copias_disponibles'])) {
                $validated['copias_disponibles'] = min($validated['copias_disponibles'], $validated['copias_totales']);
            } elseif (isset($validated['copias_totales'])) {
                $validated['copias_disponibles'] = min($book->copias_disponibles, $validated['copias_totales']);
            }

            if (isset($validated['copias_disponibles'])) {
                $validated['disponible'] = $validated['copias_disponibles'] > 0;
            }

            $book->update($validated);

            return response()->json($book);
        }

        public function destroy($id)
        {
            $book = Book::find($id);
            if (!$book) return response()->json(['error' => 'Libro no encontrado'], 404);

            $book->delete();

            return response()->json(['message' => 'El libro ha sido eliminado correctamente.']);
        }


        public function storeLoan(StoreLoanRequest $request)
        {
            $data = $request->validated();

            $book = Book::lockForUpdate()->find($data['libro_id']);
            if (!$book) {
                return response()->json(['message' => 'Libro no encontrado'], 404);
            }


            if ($book->copias_disponibles <= 0) {
                return response()->json(['message' => 'No hay copias disponibles'], 422);
            }


            $loan = null;
            DB::transaction(function () use ($book, $data, &$loan) {
                $book->copias_disponibles = max(0, $book->copias_disponibles - 1);
                $book->disponible = $book->copias_disponibles > 0;
                $book->save();

                $loan = Loan::create([
                    'nombre_solicitante' => $data['nombre_solicitante'],
                    'fecha_hora_prestamo' => $data['fecha_hora_prestamo'] ?? now(),
                    'libro_id' => $book->id,
                    'user_id' => $data['user_id'] ?? null,
                ]);
            });

            return response()->json($loan, 201);
        }

}
