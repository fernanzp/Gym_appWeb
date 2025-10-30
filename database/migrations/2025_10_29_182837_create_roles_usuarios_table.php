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
    Schema::create('roles_usuarios', function (Blueprint $table) {
        $table->increments('id');
        $table->unsignedInteger('usuario_id');
        $table->unsignedInteger('rol_id');

        $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        $table->foreign('rol_id')->references('id')->on('roles')->onDelete('cascade');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles_usuarios');
    }
};
