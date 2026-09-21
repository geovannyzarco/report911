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
        // Turno de un despacho con fecha y hora de inicio y fin; agrupa los recursos
        // (agentes y vehiculos) que estuvieron disponibles durante ese periodo.
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('despacho_id')->constrained('despachos');
            $table->dateTime('inicio');
            $table->dateTime('fin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
