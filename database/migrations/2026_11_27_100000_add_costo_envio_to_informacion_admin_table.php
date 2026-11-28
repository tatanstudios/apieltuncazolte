<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Costo de envío de los pedidos por WhatsApp (app sin cuenta de cliente):
 *   envio_limite: si el subtotal es igual o menor a este monto se cobra el envío
 *   envio_costo:  lo que se suma al pedido en ese caso (0 = envío gratis siempre)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('informacion_admin', function (Blueprint $table) {
            $table->decimal('envio_limite', 10, 2)->default(6.00);
            $table->decimal('envio_costo', 10, 2)->default(1.00);
        });
    }

    public function down(): void
    {
        Schema::table('informacion_admin', function (Blueprint $table) {
            $table->dropColumn(['envio_limite', 'envio_costo']);
        });
    }
};
