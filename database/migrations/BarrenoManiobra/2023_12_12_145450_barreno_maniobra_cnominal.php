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
        Schema::create('barrenoManiobra_cnominal', function (Blueprint $table) {
            $table->id();
            $table->string('id_proceso');
            $table->decimal('profundidad_barreno', 8, 3)->nullable();
            $table->decimal('diametro_machuelo', 8, 3)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barrenoManiobra_cnominal');
    }
};
