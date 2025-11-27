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
            // Agregamos la columna. 
            // Usamos 'default(0)' para que las membresías viejas tengan valor 0.00 y no falle.
            // 'after' sirve para acomodarla después de una columna específica (opcional).
            $table->decimal('precio_pagado', 10, 2)->default(0)->after('estatus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membresias', function (Blueprint $table) {
            $table->dropColumn('precio_pagado');
        });
    }
};