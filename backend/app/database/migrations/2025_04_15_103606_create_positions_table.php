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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('isin'); // ex: FR0000120628
            $table->string('name'); // ex: VINC.PA
            $table->decimal('quantity', 10)->default(0);
            $table->decimal('current_price', 10, 4)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'isin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
