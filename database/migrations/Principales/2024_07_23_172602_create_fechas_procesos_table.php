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
        Schema::create('fechas_procesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clase')->constrained('clases');
            $table->string('proceso');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fechas_procesos');
    }
};
