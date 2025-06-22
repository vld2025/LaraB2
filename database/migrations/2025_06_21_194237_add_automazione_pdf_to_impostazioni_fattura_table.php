<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('impostazioni_fattura', function (Blueprint $table) {
            $table->integer('giorno_automazione_pdf')->default(22)->after('invia_automatico');
            $table->time('ora_automazione_pdf')->default('08:00')->after('giorno_automazione_pdf');
            $table->string('email_automazione_pdf')->default('vlad@vldservice.ch')->after('ora_automazione_pdf');
            $table->boolean('automazione_pdf_attiva')->default(true)->after('email_automazione_pdf');
            $table->enum('mese_automazione_pdf', ['current', 'previous'])->default('previous')->after('automazione_pdf_attiva');
        });
    }

    public function down(): void
    {
        Schema::table('impostazioni_fattura', function (Blueprint $table) {
            $table->dropColumn([
                'giorno_automazione_pdf', 
                'ora_automazione_pdf', 
                'email_automazione_pdf',
                'automazione_pdf_attiva',
                'mese_automazione_pdf'
            ]);
        });
    }
};
