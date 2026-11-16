<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloqueServicios extends Model
{
    use HasFactory;
    protected $table = 'bloque_servicios';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'imagen',
        'posicion',
        'activo',
        'tipo_vista', // 1 = lista, 2 = tienda
    ];

    const VISTA_LISTA = 1;
    const VISTA_TIENDA = 2;
}
