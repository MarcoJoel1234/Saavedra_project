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
        Schema::create('clases', function (Blueprint $table) {
            $table->id(); 
            $table->string('id_ot');
            $table->string('nombre');
            $table->string('tamanio')->nullable();
            $table->integer('seccion')->nullable();
            $table->float('piezas');
            $table->integer('pedido');
            $table->date('fecha_inicio');
            $table->time('hora_inicio');
            $table->date('fecha_termino')->nullable();
            $table->time('hora_termino')->nullable();
            $table->integer('finalizada')->default(0);
            $table->foreign('id_ot')->references('id')->on('orden_trabajo');  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clases');
    }
};
