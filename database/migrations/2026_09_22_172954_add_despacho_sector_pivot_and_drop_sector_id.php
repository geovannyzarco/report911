<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations:
     * Crea la tabla pivot despacho_sector (relacion muchos-a-muchos) y elimina
     * la columna sector_id de despachos, porque ahora un despacho se asigna a
     * varios sectores.
     */
    public function up(): void
    {
        Schema::create('despacho_sector', function (Blueprint $table) {
            $table->foreignId('despacho_id')->constrained('despachos')->cascadeOnDelete();
            $table->foreignId('sector_id')->constrained('sectores')->cascadeOnDelete();
            $table->primary(['despacho_id', 'sector_id']);
        });

        Schema::table('despachos', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropColumn('sector_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('despachos', function (Blueprint $table) {
            $table->foreignId('sector_id')->constrained('sectores');
        });

        Schema::dropIfExists('despacho_sector');
    }
};
