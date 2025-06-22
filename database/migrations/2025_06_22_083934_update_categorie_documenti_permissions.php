<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Aggiungi campi per gestione permessi
        Schema::table('categorie_documenti', function (Blueprint $table) {
            if (!Schema::hasColumn('categorie_documenti', 'tipo_accesso')) {
                $table->enum('tipo_accesso', ['user_upload', 'manager_upload', 'buste_paga'])->default('user_upload')->after('colore');
            }
            if (!Schema::hasColumn('categorie_documenti', 'icona')) {
                $table->string('icona')->default('heroicon-o-folder')->after('tipo_accesso');
            }
        });

        // Aggiorna categorie esistenti invece di truncate
        DB::table('categorie_documenti')->update(['attiva' => false]);

        // Aggiorna o inserisci le categorie principali
        $categorie = [
            [
                'nome' => 'Buste Paga',
                'slug' => 'buste-paga',
                'descrizione' => 'Buste paga mensili caricate dal manager',
                'colore' => '#10B981',
                'tipo_accesso' => 'buste_paga',
                'icona' => 'heroicon-o-banknotes',
                'ordine' => 1,
                'attiva' => true,
            ],
            [
                'nome' => 'Documenti Personali',
                'slug' => 'documenti-personali',
                'descrizione' => 'Documenti personali del dipendente (ID, patente, certificati)',
                'colore' => '#3B82F6',
                'tipo_accesso' => 'user_upload',
                'icona' => 'heroicon-o-identification',
                'ordine' => 2,
                'attiva' => true,
            ],
            [
                'nome' => 'Documenti Aziendali',
                'slug' => 'documenti-aziendali',
                'descrizione' => 'Documenti aziendali relativi al dipendente',
                'colore' => '#6366F1',
                'tipo_accesso' => 'manager_upload',
                'icona' => 'heroicon-o-building-office',
                'ordine' => 3,
                'attiva' => true,
            ]
        ];

        foreach ($categorie as $cat) {
            DB::table('categorie_documenti')->updateOrInsert(
                ['slug' => $cat['slug']],
                array_merge($cat, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        // Aggiorna le categorie esistenti con tipo_accesso appropriato
        DB::table('categorie_documenti')
            ->where('slug', 'contratti')
            ->update(['tipo_accesso' => 'manager_upload', 'icona' => 'heroicon-o-document-text']);
            
        DB::table('categorie_documenti')
            ->where('slug', 'fatture')
            ->update(['tipo_accesso' => 'manager_upload', 'icona' => 'heroicon-o-receipt-percent']);
            
        DB::table('categorie_documenti')
            ->where('slug', 'certificati')
            ->update(['tipo_accesso' => 'user_upload', 'icona' => 'heroicon-o-academic-cap']);
    }

    public function down(): void
    {
        // Rimuovi solo le nuove categorie
        DB::table('categorie_documenti')
            ->whereIn('slug', ['buste-paga', 'documenti-personali', 'documenti-aziendali'])
            ->delete();
            
        // Riattiva le vecchie categorie
        DB::table('categorie_documenti')->update(['attiva' => true]);
        
        Schema::table('categorie_documenti', function (Blueprint $table) {
            $table->dropColumn(['tipo_accesso', 'icona']);
        });
    }
};
