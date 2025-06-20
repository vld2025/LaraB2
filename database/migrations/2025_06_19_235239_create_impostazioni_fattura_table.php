<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impostazioni_fattura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->nullable()->constrained('clienti')->onDelete('cascade');
            $table->decimal('costo_orario', 8, 2)->default(80);
            $table->decimal('costo_km', 5, 2)->default(0.70);
            $table->decimal('costo_pranzo', 6, 2)->default(25);
            $table->decimal('costo_pernottamento', 8, 2)->default(120);
            $table->integer('giorno_fatturazione')->default(22);
            $table->string('email_destinatario')->nullable();
            $table->boolean('invia_automatico')->default(false);
            $table->timestamps();
            
            // Se cliente_id è NULL, sono le impostazioni di default
            $table->index('cliente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impostazioni_fattura');
    }
};
