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
        // Catalogo maestro de agentes: recurso identificado por ONI, con rango policial,
        // sector (delegacion/puesto destacado) y telefono ONI de contacto en sitio.
        Schema::create('agentes', function (Blueprint $table) {
            $table->id();
            $table->string('oni')->unique();
            $table->string('nombre');
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->foreignId('sector_id')->constrained('sectores');
            $table->string('telefono_oni')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agentes');
    }
};
