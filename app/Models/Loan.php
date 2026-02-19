<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $table = 'prestamos';

    protected $fillable = [
        'nombre_solicitante',
        'fecha_hora_prestamo',
        'libro_id',
        'user_id',
    ];

    public $timestamps = true;
}
