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
        Schema::create('acabadoBombillo_cnominal', function (Blueprint $table) {
            $table->id();
            $table->string('id_proceso');
            $table->decimal('diametro_mordaza', 8, 3)->nullable();
            $table->decimal('diametro_ceja', 8, 3)->nullable();
            $table->decimal('diametro_sufridera', 8, 3)->nullable();
            $table->decimal('altura_mordaza', 8, 3)->nullable();
            $table->decimal('altura_ceja', 8, 3)->nullable();
            $table->decimal('altura_sufridera', 8, 3)->nullable();
            $table->decimal('diametro_boca', 8, 3)->nullable();
            $table->decimal('diametro_asiento_corona', 8, 3)->nullable();
            $table->decimal('diametro_llanta', 8, 3)->nullable();
            $table->decimal('diametro_caja_corona', 8, 3)->nullable();
            $table->decimal('profundidad_corona', 8, 3)->nullable();
            $table->decimal('angulo_30', 8, 3)->nullable();
            $table->decimal('profundidad_caja_corona', 8, 3)->nullable();
            $table->decimal('simetria', 8, 3)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acabadoBombillo_cnominal');
    }
};