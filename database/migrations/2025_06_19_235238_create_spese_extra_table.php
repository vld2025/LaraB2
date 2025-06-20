<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spese_extra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('commessa_id')->constrained('commesse')->onDelete('restrict');
            $table->date('data');
            $table->string('descrizione');
            $table->decimal('importo', 10, 2);
            $table->string('foto_path')->nullable();
            $table->decimal('importo_ai', 10, 2)->nullable();
            $table->text('risposta_ai')->nullable();
            $table->boolean('verificato')->default(false);
            
            // Stato fatturazione
            $table->boolean('fatturato')->default(false);
            $table->date('data_fatturazione')->nullable();
            $table->string('numero_fattura')->nullable();
            
            $table->timestamps();
            
            // Indici
            $table->index(['user_id', 'data']);
            $table->index(['commessa_id', 'fatturato']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spese_extra');
    }
};
