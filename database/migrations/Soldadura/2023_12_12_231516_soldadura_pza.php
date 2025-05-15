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
        Schema::create('soldadura_pza', function (Blueprint $table) {
            $table->id();
            $table->string('id_pza');
            $table->unsignedBigInteger('id_meta')->nullable();
            $table->unsignedBigInteger('id_proceso');
            $table->integer('estado')->default(0);
            $table->string('n_juego');
            $table->decimal('pesoxpieza', 8, 3)->nullable();
            $table->decimal('temperatura_precalentado', 8, 3)->nullable();
            $table->decimal('tiempo_aplicacion', 8, 3)->nullable();
            $table->string('tipo_soldadura')->nullable();
            $table->string('lote')->nullable();
            $table->string('error')->nullable();
            $table->string('observaciones')->nullable();
            $table->timestamps();
            $table->foreign('id_meta')->references('id')->on('metas');
            $table->foreign('id_proceso')->references('id')->on('soldadura');
        });
    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soldadura_pza');
    }
};
