<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Crea tabella categorie
        Schema::create('categorie_documenti', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->string('descrizione')->nullable();
            $table->string('colore', 7)->default('#3B82F6');
            $table->integer('ordine')->default(0);
            $table->boolean('attiva')->default(true);
            $table->timestamps();
        });

        // Aggiungi campi alla tabella documenti esistente
        Schema::table('documenti', function (Blueprint $table) {
            $table->foreignId('categoria_id')->nullable()->after('id')
                ->constrained('categorie_documenti')->nullOnDelete();
            $table->string('hash_sha256', 64)->nullable()->after('dimensione');
            $table->string('file_originale')->nullable()->after('file_path');
            $table->date('data_documento')->nullable()->after('descrizione');
            $table->date('data_scadenza')->nullable()->after('data_documento');
            $table->boolean('importante')->default(false)->after('interno');
            $table->json('metadata')->nullable()->after('importante');
            
            // Aggiungi indici per performance
            $table->index('hash_sha256');
            $table->index('data_scadenza');
            $table->index(['documentabile_type', 'documentabile_id', 'categoria_id'], 'doc_type_id_cat_idx');
        });

        // Inserisci categorie di default
        DB::table('categorie_documenti')->insert([
            ['nome' => 'Contratti', 'slug' => 'contratti', 'colore' => '#10B981', 'ordine' => 1],
            ['nome' => 'Fatture', 'slug' => 'fatture', 'colore' => '#3B82F6', 'ordine' => 2],
            ['nome' => 'Documenti Legali', 'slug' => 'documenti-legali', 'colore' => '#6366F1', 'ordine' => 3],
            ['nome' => 'Certificati', 'slug' => 'certificati', 'colore' => '#8B5CF6', 'ordine' => 4],
            ['nome' => 'Corrispondenza', 'slug' => 'corrispondenza', 'colore' => '#EC4899', 'ordine' => 5],
            ['nome' => 'Report', 'slug' => 'report', 'colore' => '#F59E0B', 'ordine' => 6],
            ['nome' => 'Altro', 'slug' => 'altro', 'colore' => '#6B7280', 'ordine' => 99],
        ]);
    }

    public function down(): void
    {
        Schema::table('documenti', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropColumn([
                'categoria_id', 'hash_sha256', 'file_originale',
                'data_documento', 'data_scadenza', 'importante', 'metadata'
            ]);
            $table->dropIndex('hash_sha256');
            $table->dropIndex('data_scadenza');
            $table->dropIndex('doc_type_id_cat_idx');
        });
        
        Schema::dropIfExists('categorie_documenti');
    }
};
