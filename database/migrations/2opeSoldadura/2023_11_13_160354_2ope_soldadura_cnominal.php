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
        if (!Schema::hasTable('segundaOpeSoldadura_cnominal')) {
            Schema::create('segundaOpeSoldadura_cnominal', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso');
                $table->decimal('diametro1', 8, 3)->nullable();
                $table->decimal('profundidad1', 8, 3)->nullable();
                $table->decimal('diametro2', 8, 3)->nullable();
                $table->decimal('profundidad2', 8, 3)->nullable();
                $table->decimal('diametro3', 8, 3)->nullable();
                $table->decimal('profundidad3', 8, 3)->nullable();
                $table->decimal('diametroSoldadura', 8, 3)->nullable();
                $table->decimal('profundidadSoldadura', 8, 3)->nullable();
                $table->decimal('alturaTotal', 8, 3)->nullable();
                $table->decimal('simetria90G', 8, 3)->nullable();
                $table->decimal('simetriaLinea_Partida', 8, 3)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segundaOpeSoldadura_cnominal');
    }
};
