<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;

Route::get('/v1/books', [BookController::class, 'index']);
Route::post('/loans', [BookController::class, 'storeLoan']);
