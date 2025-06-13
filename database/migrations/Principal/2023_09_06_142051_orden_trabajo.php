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
        if (!Schema::hasTable('orden_trabajo')) {
            Schema::create('orden_trabajo', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->unsignedBigInteger('id_moldura');
                $table->timestamps();
                $table->foreign('id_moldura')->references('id')->on('molduras');
            });
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_trabajo');
    }
};
