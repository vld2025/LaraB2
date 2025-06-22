<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('commessa_id')->constrained('commesse')->onDelete('restrict');
            $table->date('data');
            $table->decimal('ore', 4, 2)->default(0);
            $table->integer('km')->default(0);
            $table->boolean('auto_privata')->default(false);
            $table->boolean('festivo')->default(false);
            $table->boolean('notturno')->default(false);
            $table->boolean('trasferta')->default(false);
            
            // Versioni del report
            $table->json('dati_originali')->nullable();
            $table->json('dati_cliente')->nullable();
            
            // Stato fatturazione
            $table->boolean('fatturato')->default(false);
            $table->date('data_fatturazione')->nullable();
            $table->string('numero_fattura')->nullable();
            
            $table->timestamps();
            
            // Indici
            $table->index(['user_id', 'data']);
            $table->index(['commessa_id', 'fatturato']);
            $table->index('data_fatturazione');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
