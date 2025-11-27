<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('membresias', function (Blueprint $table) {
            // 1. Columna de Precio (Decimal 10,2). 
            // Ponemos default(0) para que las filas viejas no den error.
            $table->decimal('precio_pagado', 10, 2)->default(0)->after('estatus');

            // 2. Columna de Días Congelados (Entero).
            // Ponemos nullable() porque pediste que acepte nulos.
            // Al ser nullable, las filas viejas se quedarán con valor NULL automáticamente.
            $table->integer('dias_congelados')->nullable()->after('precio_pagado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membresias', function (Blueprint $table) {
            // Si revertimos, borramos las dos columnas de un jalón
            $table->dropColumn(['precio_pagado', 'dias_congelados']);
        });
    }
};