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
        // Catalogo maestro de vehiculos: recurso identificado por numero de equipo,
        // clasificado por tipo (moto, patrulla, lancha, etc.) y sector de operacion.
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_equipo')->unique();
            $table->foreignId('tipo_vehiculo_id')->constrained('tipo_vehiculos');
            $table->foreignId('sector_id')->nullable()->constrained('sectores');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
