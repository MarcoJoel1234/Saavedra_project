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
        Schema::create('revLaterales_tolerancia', function (Blueprint $table) {
            $table->id();
            $table->string('id_proceso');
            $table->decimal('desfasamiento_entrada1', 8, 3);
            $table->decimal('desfasamiento_entrada2', 8, 3);
            $table->decimal('desfasamiento_salida1', 8, 3);
            $table->decimal('desfasamiento_salida2', 8, 3);
            $table->decimal('ancho_simetriaEntrada1', 8, 3);
            $table->decimal('ancho_simetriaEntrada2', 8, 3);
            $table->decimal('ancho_simetriaSalida1', 8, 3);
            $table->decimal('ancho_simetriaSalida2', 8, 3);
            $table->decimal('angulo_corte1', 8, 3);
            $table->decimal('angulo_corte2', 8, 3);
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revLaterales_tolerancia');
    }
};
