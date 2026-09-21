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
        // Pivot turno-vehiculo: registra que vehiculos tuvo disponibles el despacho en su turno,
        // con el estado del recurso y una nota opcional (motivo de no disponibilidad o mision asignada).
        Schema::create('turno_vehiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('turnos');
            $table->foreignId('vehiculo_id')->constrained('vehiculos');
            $table->foreignId('estado_recurso_id')->constrained('estado_recursos');
            $table->text('nota')->nullable();
            $table->unique(['turno_id', 'vehiculo_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turno_vehiculos');
    }
};
