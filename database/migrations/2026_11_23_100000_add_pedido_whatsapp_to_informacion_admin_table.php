<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cómo puede enviar el cliente su pedido desde la app:
 *   modo_pedido: 1 = solo orden normal (queda en el panel)
 *                2 = solo por WhatsApp
 *                3 = ambas opciones
 *   whatsapp_numero: número que recibe los pedidos (código de país + número, solo dígitos)
 *   minimo_consumo: mínimo para poder enviar el pedido (aplica a ambos modos)
 *   activo_slider: lo usaba el panel de configuración pero la columna no existía
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('informacion_admin', function (Blueprint $table) {
            if (!Schema::hasColumn('informacion_admin', 'activo_slider')) {
                $table->boolean('activo_slider')->default(1);
            }
            $table->tinyInteger('modo_pedido')->default(1);
            $table->string('whatsapp_numero', 20)->default('50375825072');
            $table->decimal('minimo_consumo', 10, 2)->default(6.00);
        });
    }

    public function down(): void
    {
        Schema::table('informacion_admin', function (Blueprint $table) {
            $table->dropColumn(['modo_pedido', 'whatsapp_numero', 'minimo_consumo']);
        });
    }
};
