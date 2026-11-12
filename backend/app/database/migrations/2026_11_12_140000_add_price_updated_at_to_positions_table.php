<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->timestamp('price_updated_at')->nullable()->after('current_price');
        });

        // Reprise : pour les positions déjà valorisées, la dernière mise à jour
        // connue est la meilleure approximation de la date du cours.
        DB::table('positions')
            ->where('current_price', '>', 0)
            ->update(['price_updated_at' => DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('price_updated_at');
        });
    }
};
