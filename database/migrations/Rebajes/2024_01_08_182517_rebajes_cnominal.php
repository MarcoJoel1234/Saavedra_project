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
        if (!Schema::hasTable('rebajes_cnominal')) {
            Schema::create('rebajes_cnominal', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso');
                $table->decimal('rebaje1', 8, 3)->nullable();
                $table->decimal('rebaje2', 8, 3)->nullable();
                $table->decimal('rebaje3', 8, 3)->nullable();
                $table->decimal('profundidad_bordonio', 8, 3)->nullable();
                $table->decimal('vena1', 8, 3)->nullable();
                $table->decimal('vena2', 8, 3)->nullable();
                $table->decimal('simetria', 8, 3)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rebajes_cnominal');
    }
};
