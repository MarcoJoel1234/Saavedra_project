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
        if (!Schema::hasTable('cepillado_tolerancia')) {
            Schema::create('cepillado_tolerancia', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso');
                $table->decimal('radiof_mordaza1', 8, 3);
                $table->decimal('radiof_mordaza2', 8, 3);
                $table->decimal('radiof_mayor1', 8, 3);
                $table->decimal('radiof_mayor2', 8, 3);
                $table->decimal('radiof_sufridera1', 8, 3);
                $table->decimal('radiof_sufridera2', 8, 3);
                $table->decimal('profuFinal_CFC1', 8, 3);
                $table->decimal('profuFinal_CFC2', 8, 3);
                $table->decimal('profuFinal_mitadMB1', 8, 3);
                $table->decimal('profuFinal_mitadMB2', 8, 3);
                $table->decimal('profuFinal_PCO1', 8, 3);
                $table->decimal('profuFinal_PCO2', 8, 3);
                $table->decimal('ensamble1', 8, 3);
                $table->decimal('ensamble2', 8, 3);
                $table->decimal('distancia_barrenoAli1', 8, 3);
                $table->decimal('distancia_barrenoAli2', 8, 3);
                $table->decimal('profu_barrenoAliHembra1', 8, 3);
                $table->decimal('profu_barrenoAliHembra2', 8, 3);
                $table->decimal('profu_barrenoAliMacho1', 8, 3);
                $table->decimal('profu_barrenoAliMacho2', 8, 3);
                $table->decimal('altura_venaHembra1', 8, 3);
                $table->decimal('altura_venaHembra2', 8, 3);
                $table->decimal('altura_venaMacho1', 8, 3);
                $table->decimal('altura_venaMacho2', 8, 3);
                $table->decimal('ancho_vena1', 8, 3);
                $table->decimal('ancho_vena2', 8, 3);
                $table->decimal('pin1', 8, 3);
                $table->decimal('pin2', 8, 3);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cepillado_tolerancia');
    }
};
