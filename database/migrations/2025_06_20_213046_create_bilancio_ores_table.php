<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bilancio_ores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('mese');
            $table->integer('anno');
            $table->decimal('ore_lavorate', 8, 2)->default(0);
            $table->decimal('ore_previste', 8, 2)->default(0);
            $table->decimal('differenza', 8, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'mese', 'anno']);
            $table->index(['anno', 'mese']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bilancio_ores');
    }
};
