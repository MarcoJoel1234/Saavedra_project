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
        Schema::create('procesos', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('id_clase');
            $table->integer('cepillado')->default(0);
            $table->integer('desbaste_exterior')->default(0);
            $table->integer('revision_laterales')->default(0);
            $table->integer('pOperacion')->default(0);
            $table->integer('barreno_maniobra')->default(0);
            $table->integer('sOperacion')->default(0);
            $table->integer('soldadura')->default(0);
            $table->integer('soldaduraPTA')->default(0);
            $table->integer('rectificado')->default(0);
            $table->integer('asentado')->default(0);
            $table->integer('calificado')->default(0);
            $table->integer('acabadoBombillo')->default(0);
            $table->integer('acabadoMolde')->default(0);
            $table->integer('barreno_profundidad')->default(0);
            $table->integer('cavidades')->default(0);
            $table->integer('copiado')->default(0);
            $table->integer('offSet')->default(0);
            $table->integer('palomas')->default(0);
            $table->integer('rebajes')->default(0);
            $table->integer('grabado')->default(0);
            $table->integer('operacionEquipo')->default(0);
            $table->integer('embudoCM')->default(0);
            $table->foreign('id_clase')->references('id')->on('clases');  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procesos');
    }
};
