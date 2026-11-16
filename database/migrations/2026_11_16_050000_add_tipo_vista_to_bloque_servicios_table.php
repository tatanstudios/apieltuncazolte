<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cómo muestra la app los productos de un servicio (bloque del menú principal):
 * 1 = lista (por categorías, uno debajo de otro)   2 = tienda (cuadrícula con filtro por categoría)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bloque_servicios', function (Blueprint $table) {
            $table->tinyInteger('tipo_vista')->default(1)->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('bloque_servicios', function (Blueprint $table) {
            $table->dropColumn('tipo_vista');
        });
    }
};
