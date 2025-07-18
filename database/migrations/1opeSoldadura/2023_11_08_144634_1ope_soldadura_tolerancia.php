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
        if (!Schema::hasTable('PrimeraOpeSoldadura_tolerancia')) {
            Schema::create('PrimeraOpeSoldadura_tolerancia', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso');
                $table->decimal('diametro1', 8, 3);
                $table->decimal('profundidad1', 8, 3);
                $table->decimal('diametro2', 8, 3);
                $table->decimal('profundidad2', 8, 3);
                $table->decimal('diametro3', 8, 3);
                $table->decimal('profundidad3', 8, 3);
                $table->decimal('diametroSoldadura', 8, 3);
                $table->decimal('profundidadSoldadura', 8, 3);
                $table->decimal('diametroBarreno1', 8, 3);
                $table->decimal('diametroBarreno2', 8, 3);
                $table->decimal('simetriaLinea_partida1', 8, 3);
                $table->decimal('simetriaLinea_partida2', 8, 3);
                $table->decimal('pernoAlineacion', 8, 3);
                $table->decimal('Simetria90G', 8, 3);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PrimeraOpeSoldadura_tolerancia');
    }
};
