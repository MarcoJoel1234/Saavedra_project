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
        Schema::create('palomas_tolerancia', function (Blueprint $table) {
            $table->id();
            $table->string('id_proceso');
            $table->decimal('anchoPaloma', 8, 3)->nullable();
            $table->decimal('gruesoPaloma', 8, 3)->nullable();
            $table->decimal('profundidadPaloma', 8, 3)->nullable();
            $table->decimal('rebajeLlanta', 8, 3)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('palomas_tolerancia');
    }
};
