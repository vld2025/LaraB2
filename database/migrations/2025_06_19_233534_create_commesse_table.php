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
        Schema::create('commesse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cantiere_id')->constrained('cantieri')->onDelete('cascade');
            $table->string('nome');
            $table->string('codice')->unique();
            $table->text('descrizione')->nullable();
            $table->date('data_inizio')->nullable();
            $table->date('data_fine')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->boolean('attiva')->default(true);
            $table->timestamps();
            
            // Indici per ottimizzazione
            $table->index('cantiere_id');
            $table->index('attiva');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commesse');
    }
};
