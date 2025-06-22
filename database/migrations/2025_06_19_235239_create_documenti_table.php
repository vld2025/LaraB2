<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documenti', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentabile'); // crea automaticamente type, id e index
            $table->string('nome');
            $table->string('descrizione')->nullable();
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->integer('dimensione')->nullable();
            $table->boolean('interno')->default(false); // true = solo manager/admin
            $table->foreignId('caricato_da')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            
            // Indici aggiuntivi
            $table->index('interno');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documenti');
    }
};
