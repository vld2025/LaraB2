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
        if (!Schema::hasTable('categorie_documenti')) {
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

            // Inserisci categorie di default
            DB::table('categorie_documenti')->insert([
                ['nome' => 'Contratti', 'slug' => 'contratti', 'colore' => '#10B981', 'ordine' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['nome' => 'Fatture', 'slug' => 'fatture', 'colore' => '#3B82F6', 'ordine' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['nome' => 'Documenti Legali', 'slug' => 'documenti-legali', 'colore' => '#6366F1', 'ordine' => 3, 'created_at' => now(), 'updated_at' => now()],
                ['nome' => 'Certificati', 'slug' => 'certificati', 'colore' => '#8B5CF6', 'ordine' => 4, 'created_at' => now(), 'updated_at' => now()],
                ['nome' => 'Report', 'slug' => 'report', 'colore' => '#F59E0B', 'ordine' => 5, 'created_at' => now(), 'updated_at' => now()],
                ['nome' => 'Altro', 'slug' => 'altro', 'colore' => '#6B7280', 'ordine' => 99, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Aggiungi campi alla tabella documenti esistente
        Schema::table('documenti', function (Blueprint $table) {
            if (!Schema::hasColumn('documenti', 'categoria_id')) {
                $table->foreignId('categoria_id')->nullable()->after('id')
                    ->constrained('categorie_documenti')->nullOnDelete();
            }
            if (!Schema::hasColumn('documenti', 'hash_sha256')) {
                $table->string('hash_sha256', 64)->nullable()->after('dimensione');
            }
            if (!Schema::hasColumn('documenti', 'file_originale')) {
                $table->string('file_originale')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('documenti', 'data_documento')) {
                $table->date('data_documento')->nullable()->after('descrizione');
            }
            if (!Schema::hasColumn('documenti', 'data_scadenza')) {
                $table->date('data_scadenza')->nullable()->after('data_documento');
            }
            if (!Schema::hasColumn('documenti', 'importante')) {
                $table->boolean('importante')->default(false)->after('interno');
            }
            if (!Schema::hasColumn('documenti', 'metadata')) {
                $table->json('metadata')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('documenti', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropColumn([
                'categoria_id', 'hash_sha256', 'file_originale',
                'data_documento', 'data_scadenza', 'importante', 'metadata'
            ]);
        });
        
        Schema::dropIfExists('categorie_documenti');
    }
};
