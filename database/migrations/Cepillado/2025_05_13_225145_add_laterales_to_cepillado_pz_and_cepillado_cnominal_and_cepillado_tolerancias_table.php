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
        Schema::table('cepillado_pza', function (Blueprint $table) {
            $table->decimal('laterales', 8, 3)->nullable()->default(0)->after('ancho_vena');
        });

        Schema::table('cepillado_cnominal', function (Blueprint $table) {
            $table->decimal('laterales', 8, 3)->nullable()->default(0)->after('ancho_vena');
        });

        Schema::table('cepillado_tolerancia', function (Blueprint $table) {
            $table->decimal('laterales1', 8, 3)->nullable()->default(0)->after('ancho_vena2');
            $table->decimal('laterales2', 8, 3)->nullable()->default(0)->after('laterales1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cepillado_pza', function (Blueprint $table) {
            $table->dropColumn('laterales');
        });

        Schema::table('cepillado_cnominal', function (Blueprint $table) {
            $table->dropColumn('laterales');
        });

        Schema::table('cepillado_tolerancia', function (Blueprint $table) {
            $table->dropColumn('laterales1');
            $table->dropColumn('laterales2');
        });
    }
};
