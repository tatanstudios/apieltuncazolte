<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MODIFICADORES DE PRODUCTO (sin inventario)
 *
 * modificador_grupo      -> bloque de opciones de un producto (ej. "Preparación de huevo")
 * modificador_opcion     -> cada opción del bloque (ej. "Estrellado", "Revuelto")
 * carrito_extra_opcion   -> opciones que el cliente eligió para una fila del carrito
 * ordenes_descripcion_opcion -> copia de las opciones al enviar la orden (no cambia si luego se edita el producto)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modificador_grupo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_producto')->constrained('producto')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->boolean('requerido')->default(false);
            $table->unsignedInteger('minimo')->default(0); // unidades mínimas a elegir en el bloque
            $table->unsignedInteger('maximo')->default(1); // unidades máximas a elegir en el bloque
            $table->integer('posicion')->default(0);
        });

        Schema::create('modificador_opcion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_grupo')->constrained('modificador_grupo')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->decimal('precio', 10, 2)->default(0); // precio extra
            $table->unsignedInteger('maximo')->default(1); // veces que se puede elegir esta opción
            $table->boolean('obligatorio')->default(false); // siempre va incluida
            $table->boolean('una_sola')->default(false); // si se elige, no se puede combinar con otras del bloque
            $table->boolean('activo')->default(true);
            $table->integer('posicion')->default(0);
        });

        Schema::create('carrito_extra_opcion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_carrito_extra')->constrained('carrito_extra')->cascadeOnDelete();
            $table->foreignId('id_opcion')->constrained('modificador_opcion')->cascadeOnDelete();
            $table->unsignedInteger('cantidad')->default(1);
        });

        Schema::create('ordenes_descripcion_opcion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_ordenes_descripcion')->constrained('ordenes_descripcion')->cascadeOnDelete();
            $table->unsignedBigInteger('id_opcion')->nullable(); // referencia informativa, sin llave
            $table->string('grupo', 100);
            $table->string('opcion', 100);
            $table->decimal('precio', 10, 2)->default(0);
            $table->unsignedInteger('cantidad')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_descripcion_opcion');
        Schema::dropIfExists('carrito_extra_opcion');
        Schema::dropIfExists('modificador_opcion');
        Schema::dropIfExists('modificador_grupo');
    }
};
