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
        if (!Schema::hasTable('revLaterales_cnominal')) {
            Schema::create('revLaterales_cnominal', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso');
                $table->decimal('desfasamiento_entrada', 8, 3);
                $table->decimal('desfasamiento_salida', 8, 3);
                $table->decimal('ancho_simetriaEntrada', 8, 3);
                $table->decimal('ancho_simetriaSalida', 8, 3);
                $table->decimal('angulo_corte', 8, 3);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revLaterales_cnominal');
    }
};
