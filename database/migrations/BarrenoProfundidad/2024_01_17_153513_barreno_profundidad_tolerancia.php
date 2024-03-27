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
        Schema::create('barrenoProfundidad_tolerancia', function (Blueprint $table) {
            $table->id();
            $table->string('id_proceso');
            $table->decimal('broca1', 8, 3)->nullable();
            $table->decimal('tiempo1', 8, 3)->nullable();
            $table->decimal('broca2', 8, 3)->nullable();
            $table->decimal('tiempo2', 8, 3)->nullable();
            $table->decimal('broca3', 8, 3)->nullable();
            $table->decimal('tiempo3', 8, 3)->nullable();
            $table->decimal('entrada', 8, 3)->nullable();
            $table->decimal('salida', 8, 3)->nullable();
            $table->decimal('diametro_arrastre1', 8, 3)->nullable();
            $table->decimal('diametro_arrastre2', 8, 3)->nullable();
            $table->decimal('diametro_arrastre3', 8, 3)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barrenoProfundidad_tolerancia');
    }
};
