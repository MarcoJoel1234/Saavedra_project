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
        Schema::create('offSet_tolerancia', function (Blueprint $table) {
            $table->id();
            $table->string('id_proceso');
            $table->decimal('anchoRanura', 8, 3)->nullable();
            $table->decimal('profuTaconHembra', 8, 3)->nullable();
            $table->decimal('profuTaconMacho', 8, 3)->nullable();
            $table->decimal('simetriaHembra', 8, 3)->nullable();
            $table->decimal('simetriaMacho', 8, 3)->nullable();
            $table->decimal('anchoTacon', 8, 3)->nullable();
            $table->decimal('barrenoLateralHembra', 8, 3)->nullable();
            $table->decimal('barrenoLateralMacho', 8, 3)->nullable();
            $table->decimal('alturaTaconInicial', 8, 3)->nullable();
            $table->decimal('alturaTaconIntermedia', 8, 3)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offSet_tolerancia');
    }
};
