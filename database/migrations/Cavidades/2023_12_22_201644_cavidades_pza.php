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
        if (!Schema::hasTable('cavidades_pza')) {
            Schema::create('cavidades_pza', function (Blueprint $table) {
                $table->id();
                $table->string('id_pza');
                $table->unsignedBigInteger('id_meta')->nullable();
                $table->unsignedBigInteger('id_proceso');
                $table->integer('correcto')->nullable();
                $table->integer('estado')->default(0);
                $table->string('n_juego')->nullable();
                $table->decimal('profundidad1', 8, 3)->nullable();
                $table->decimal('diametro1', 8, 3)->nullable();
                $table->decimal('profundidad2', 8, 3)->nullable();
                $table->decimal('diametro2', 8, 3)->nullable();
                $table->decimal('profundidad3', 8, 3)->nullable();
                $table->decimal('diametro3', 8, 3)->nullable();
                $table->string('acetatoBM')->nullable();
                $table->string('observaciones')->nullable();
                $table->string('error')->nullable();
                $table->timestamps();
                $table->foreign('id_meta')->references('id')->on('metas');
                $table->foreign('id_proceso')->references('id')->on('cavidades');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cavidades_pza');
    }
};
