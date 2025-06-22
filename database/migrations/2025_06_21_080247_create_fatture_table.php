<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fatture', function (Blueprint $table) {
            $table->id();
            
            // Identificazione fattura
            $table->string('numero_fattura')->unique(); // FATTURA 1-2025
            $table->date('data_fattura');
            $table->date('data_scadenza');
            $table->enum('stato', ['bozza', 'inviata', 'pagata', 'scaduta'])->default('bozza');
            
            // Cliente
            $table->foreignId('cliente_id')->constrained('clienti')->onDelete('cascade');
            
            // Periodo di riferimento
            $table->integer('mese');
            $table->integer('anno');
            
            // Importi
            $table->decimal('subtotale', 10, 2)->default(0);
            $table->decimal('sconto', 10, 2)->default(0);
            $table->decimal('totale_pre_iva', 10, 2)->default(0);
            $table->decimal('aliquota_iva', 5, 2)->default(8.1);
            $table->decimal('importo_iva', 10, 2)->default(0);
            $table->decimal('totale_finale', 10, 2)->default(0);
            
            // Dettagli aggregati
            $table->decimal('ore_totali', 8, 2)->default(0);
            $table->decimal('importo_manodopera', 10, 2)->default(0);
            $table->decimal('giorni_trasferta', 8, 2)->default(0);
            $table->decimal('importo_trasferte', 10, 2)->default(0);
            $table->decimal('km_totali', 8, 2)->default(0);
            $table->decimal('importo_km', 10, 2)->default(0);
            $table->decimal('importo_spese_extra', 10, 2)->default(0);
            
            // Email
            $table->timestamp('data_invio_email')->nullable();
            $table->string('email_destinatario')->nullable();
            
            // Note
            $table->text('note')->nullable();
            
            $table->timestamps();
            
            // Indici
            $table->index(['cliente_id', 'mese', 'anno']);
            $table->index(['data_fattura']);
            $table->unique(['cliente_id', 'mese', 'anno']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fatture');
    }
};
