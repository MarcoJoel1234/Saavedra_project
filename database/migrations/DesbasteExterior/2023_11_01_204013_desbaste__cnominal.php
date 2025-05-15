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
        Schema::create('desbaste_cnominal', function (Blueprint $table) {
            $table->id();
            $table->string('id_proceso');
            $table->decimal('diametro_mordaza', 8, 3);
            $table->decimal('diametro_ceja', 8, 3);
            $table->decimal('diametro_sufrideraExtra', 8, 3);
            $table->decimal('simetria_ceja', 8, 3);
            $table->decimal('simetria_mordaza', 8, 3);
            $table->decimal('altura_ceja', 8, 3);
            $table->decimal('altura_sufridera', 8, 3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desbaste_cnominal');
    }
};
