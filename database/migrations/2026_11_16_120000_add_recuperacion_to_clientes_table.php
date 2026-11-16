<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Correo del cliente y datos del código para recuperar la contraseña.
 * El código se guarda con hash (nunca en texto plano).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('correo', 100)->nullable()->unique()->after('usuario');
            $table->string('codigo_recuperacion', 255)->nullable();
            $table->dateTime('codigo_expira')->nullable();
            $table->unsignedTinyInteger('codigo_intentos')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique(['correo']);
            $table->dropColumn(['correo', 'codigo_recuperacion', 'codigo_expira', 'codigo_intentos']);
        });
    }
};
