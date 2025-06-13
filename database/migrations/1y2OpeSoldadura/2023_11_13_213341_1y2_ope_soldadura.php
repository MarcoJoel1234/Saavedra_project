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
        if (!Schema::hasTable('PySOpeSoldadura')) {
            Schema::create('PySOpeSoldadura', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso')->unique();
                $table->string('id_ot');
                $table->unsignedBigInteger('id_clase');
                $table->integer('operacion');
                $table->foreign('id_ot')->references('id')->on('orden_trabajo');
                $table->foreign('id_clase')->references('id')->on('clases');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PySOpeSoldadura');
    }
};
