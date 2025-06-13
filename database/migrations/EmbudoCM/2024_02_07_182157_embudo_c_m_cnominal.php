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
        if (!Schema::hasTable('embudoCM_cnominal')) {
            Schema::create('embudoCM_cnominal', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso');
                $table->decimal('conexion_lineaPartida', 8, 3)->nullable();
                $table->decimal('conexion_90G', 8, 3)->nullable();
                $table->decimal('altura_conexion', 8, 3)->nullable();
                $table->decimal('diametro_embudo', 8, 3)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embudoCM_cnominal');
    }
};
