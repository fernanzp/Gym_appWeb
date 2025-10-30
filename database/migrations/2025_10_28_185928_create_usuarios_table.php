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
    Schema::create('usuarios', function (Blueprint $table) {
        $table->increments('id'); // 'int(10) UNSIGNED NOT NULL AUTO_INCREMENT'
        $table->integer('fingerprint_id')->unique()->nullable(); // ¡La nueva columna!

        $table->string('nombre_comp', 160);
        $table->string('email', 160)->unique()->nullable();
        $table->string('telefono', 15)->nullable();
        $table->string('contrasena', 255);
        $table->date('fecha_nac')->nullable();
        $table->tinyInteger('estatus')->default(1);
        $table->rememberToken(); // Esto crea la columna 'remember_token'
        $table->timestamps(); // Crea 'created_at' y 'updated_at'
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
