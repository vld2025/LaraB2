<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spese', function (Blueprint $table) {
            // Aggiungiamo il campo foto
            $table->string('foto_scontrino')->nullable()->after('note');
            
            // Rendiamo opzionali i campi che non servono più
            $table->integer('report_id')->nullable()->change();
            $table->date('data')->nullable()->change();
            $table->string('tipo')->nullable()->change();
            $table->decimal('importo', 10, 2)->nullable()->change();
            
            // Aggiungiamo campi per il nuovo sistema
            $table->integer('mese')->after('user_id');
            $table->integer('anno')->after('mese');
        });
    }

    public function down(): void
    {
        Schema::table('spese', function (Blueprint $table) {
            $table->dropColumn(['foto_scontrino', 'mese', 'anno']);
            
            // Ripristiniamo i campi come obbligatori
            $table->integer('report_id')->nullable(false)->change();
            $table->date('data')->nullable(false)->change();
            $table->string('tipo')->nullable(false)->change();
            $table->decimal('importo', 10, 2)->nullable(false)->change();
        });
    }
};
