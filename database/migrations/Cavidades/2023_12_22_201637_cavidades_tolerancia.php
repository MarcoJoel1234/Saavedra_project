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
        Schema::create('cavidades_tolerancia', function (Blueprint $table) {
            $table->id();
            $table->string('id_proceso');
            $table->decimal('profundidad1_1', 8, 3)->nullable();
            $table->decimal('profundidad2_1', 8, 3)->nullable();
            $table->decimal('diametro1_1', 8, 3)->nullable();
            $table->decimal('diametro2_1', 8, 3)->nullable();
            $table->decimal('profundidad1_2', 8, 3)->nullable();
            $table->decimal('profundidad2_2', 8, 3)->nullable();
            $table->decimal('diametro1_2', 8, 3)->nullable();
            $table->decimal('diametro2_2', 8, 3)->nullable();
            $table->decimal('profundidad1_3', 8, 3)->nullable();
            $table->decimal('profundidad2_3', 8, 3)->nullable();
            $table->decimal('diametro1_3', 8, 3)->nullable();
            $table->decimal('diametro2_3', 8, 3)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cavidades_tolerancia');
    }
};
