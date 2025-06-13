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
        if (!Schema::hasTable('desbaste_tolerancia')) {
            Schema::create('desbaste_tolerancia', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso');
                $table->decimal('diametro_mordaza1', 8, 3);
                $table->decimal('diametro_mordaza2', 8, 3);
                $table->decimal('diametro_ceja1', 8, 3);
                $table->decimal('diametro_ceja2', 8, 3);
                $table->decimal('diametro_sufrideraExtra1', 8, 3);
                $table->decimal('diametro_sufrideraExtra2', 8, 3);
                $table->decimal('simetria_ceja1', 8, 3);
                $table->decimal('simetria_ceja2', 8, 3);
                $table->decimal('simetria_mordaza1', 8, 3);
                $table->decimal('simetria_mordaza2', 8, 3);
                $table->decimal('altura_ceja1', 8, 3);
                $table->decimal('altura_ceja2', 8, 3);
                $table->decimal('altura_sufridera1', 8, 3);
                $table->decimal('altura_sufridera2', 8, 3);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desbaste_tolerancia');
    }
};
