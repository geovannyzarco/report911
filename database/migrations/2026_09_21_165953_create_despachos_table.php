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
        // Alta de despachadores: se enlaza 1-a-1 (user_id unico) con el usuario creado
        // automaticamente en "users" (login con ONI, email oni@pnc.gob.sv, rol despacho).
        Schema::create('despachos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users');
            $table->string('oni')->unique();
            $table->string('nombre');
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->foreignId('sector_id')->constrained('sectores');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despachos');
    }
};
