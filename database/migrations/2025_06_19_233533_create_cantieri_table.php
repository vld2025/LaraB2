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
        Schema::create('cantieri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clienti')->onDelete('cascade');
            $table->string('nome');
            $table->string('codice')->unique();
            $table->string('indirizzo')->nullable();
            $table->string('cap', 10)->nullable();
            $table->string('citta')->nullable();
            $table->string('provincia', 2)->nullable();
            $table->string('nazione', 2)->default('CH');
            $table->text('note')->nullable();
            $table->boolean('attivo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cantieri');
    }
};
