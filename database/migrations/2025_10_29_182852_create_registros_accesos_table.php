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
    Schema::create('registros_accesos', function (Blueprint $table) {
        $table->increments('id');
        $table->dateTime('fecha');
        $table->unsignedInteger('usuario_id')->nullable();
        $table->boolean('acceso');
        $table->string('observaciones')->nullable();
        $table->tinyInteger('direccion');

        $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros_accesos');
    }
};
