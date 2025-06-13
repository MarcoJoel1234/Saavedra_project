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
        if (!Schema::hasTable('copiado_cnominal')) {
            Schema::create('copiado_cnominal', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso');
                $table->decimal('diametro1_cilindrado', 8, 3)->nullable();
                $table->decimal('profundidad1_cilindrado', 8, 3)->nullable();
                $table->decimal('diametro2_cilindrado', 8, 3)->nullable();
                $table->decimal('profundidad2_cilindrado', 8, 3)->nullable();
                $table->decimal('diametro_sufridera', 8, 3)->nullable();
                $table->decimal('diametro_ranura', 8, 3)->nullable();
                $table->decimal('profundidad_ranura', 8, 3)->nullable();
                $table->decimal('profundidad_sufridera', 8, 3)->nullable();
                $table->decimal('altura_total', 8, 3)->nullable();
                $table->decimal('diametro1_cavidades', 8, 3)->nullable();
                $table->decimal('profundidad1_cavidades', 8, 3)->nullable();
                $table->decimal('diametro2_cavidades', 8, 3)->nullable();
                $table->decimal('profundidad2_cavidades', 8, 3)->nullable();
                $table->decimal('diametro3', 8, 3)->nullable();
                $table->decimal('profundidad3', 8, 3)->nullable();
                $table->decimal('diametro4', 8, 3)->nullable();
                $table->decimal('profundidad4', 8, 3)->nullable();
                $table->decimal('volumen', 8, 3)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('copiado_cnominal');
    }
};
