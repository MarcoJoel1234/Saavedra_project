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
        if (!Schema::hasTable('revCalificado_cnominal')) {
            Schema::create('revCalificado_cnominal', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso');
                $table->decimal('diametro_ceja', 8, 3)->nullable();
                $table->decimal('diametro_sufridera', 8, 3)->nullable();
                $table->decimal('altura_sufridera')->nullable();
                $table->decimal('diametro_conexion')->nullable();
                $table->decimal('altura_conexion')->nullable();
                $table->decimal('diametro_caja')->nullable();
                $table->decimal('altura_caja')->nullable();
                $table->decimal('altura_total')->nullable();
                $table->decimal('simetria')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revCalificado_cnominal');
    }
};
