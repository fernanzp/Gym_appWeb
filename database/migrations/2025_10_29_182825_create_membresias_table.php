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
    Schema::create('membresias', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedInteger('usuario_id');
        $table->unsignedInteger('plan_id');
        $table->date('fecha_ini');
        $table->date('fecha_fin');
        $table->enum('estatus', ['vigente', 'vencida', 'congelada'])->default('congelada');
        $table->timestamps();

        $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        $table->foreign('plan_id')->references('id')->on('planes')->onDelete('restrict');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membresias');
    }
};
