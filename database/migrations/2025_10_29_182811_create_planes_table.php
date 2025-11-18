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
    Schema::create('planes', function (Blueprint $table) {
        $table->increments('id');
        $table->string('nombre', 120);
        $table->text('descripcion')->nullable();
        $table->integer('duracion_dias');
        $table->integer('precio')->default(0);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
