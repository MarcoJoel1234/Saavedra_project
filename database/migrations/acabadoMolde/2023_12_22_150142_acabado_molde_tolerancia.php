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
        if (!Schema::hasTable('acabadoMolde_tolerancia')) {
            Schema::create('acabadoMolde_tolerancia', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso');
                $table->decimal('diametro_mordaza1', 8, 3)->nullable();
                $table->decimal('diametro_mordaza2', 8, 3)->nullable();
                $table->decimal('diametro_ceja1', 8, 3)->nullable();
                $table->decimal('diametro_ceja2', 8, 3)->nullable();
                $table->decimal('diametro_sufridera1', 8, 3)->nullable();
                $table->decimal('diametro_sufridera2', 8, 3)->nullable();
                $table->decimal('altura_mordaza1', 8, 3)->nullable();
                $table->decimal('altura_mordaza2', 8, 3)->nullable();
                $table->decimal('altura_ceja1', 8, 3)->nullable();
                $table->decimal('altura_ceja2', 8, 3)->nullable();
                $table->decimal('altura_sufridera1', 8, 3)->nullable();
                $table->decimal('altura_sufridera2', 8, 3)->nullable();
                $table->decimal('diametro_conexion_fondo1', 8, 3)->nullable();
                $table->decimal('diametro_conexion_fondo2', 8, 3)->nullable();
                $table->decimal('diametro_llanta1', 8, 3)->nullable();
                $table->decimal('diametro_llanta2', 8, 3)->nullable();
                $table->decimal('diametro_caja_fondo1', 8, 3)->nullable();
                $table->decimal('diametro_caja_fondo2', 8, 3)->nullable();
                $table->decimal('altura_conexion_fondo1', 8, 3)->nullable();
                $table->decimal('altura_conexion_fondo2', 8, 3)->nullable();
                $table->decimal('profundidad_llanta1', 8, 3)->nullable();
                $table->decimal('profundidad_llanta2', 8, 3)->nullable();
                $table->decimal('profundidad_caja_fondo1', 8, 3)->nullable();
                $table->decimal('profundidad_caja_fondo2', 8, 3)->nullable();
                $table->decimal('simetria1', 8, 3)->nullable();
                $table->decimal('simetria2', 8, 3)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acabadoMolde_tolerancia');
    }
};
