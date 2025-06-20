<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spese', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->date('data');
            $table->enum('tipo', ['pranzo', 'pernottamento']);
            $table->decimal('importo', 8, 2)->default(0);
            $table->text('note')->nullable();
            
            // Stato fatturazione
            $table->boolean('fatturato')->default(false);
            $table->date('data_fatturazione')->nullable();
            $table->string('numero_fattura')->nullable();
            
            $table->timestamps();
            
            // Vincolo: solo una spesa per tipo per utente per giorno
            $table->unique(['user_id', 'data', 'tipo']);
            
            // Indici
            $table->index(['user_id', 'data']);
            $table->index('fatturato');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spese');
    }
};
