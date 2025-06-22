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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('name');
            $table->string('telefono')->nullable()->after('email');
            $table->string('indirizzo')->nullable()->after('telefono');
            $table->string('cap', 10)->nullable()->after('indirizzo');
            $table->string('citta')->nullable()->after('cap');
            $table->string('provincia', 2)->nullable()->after('citta');
            
            // Taglie abbigliamento
            $table->string('taglia_giacca', 10)->nullable();
            $table->string('taglia_pantaloni', 10)->nullable();
            $table->string('taglia_maglietta', 10)->nullable();
            $table->string('taglia_scarpe', 10)->nullable();
            $table->text('note_abbigliamento')->nullable();
            
            // Dati contrattuali
            $table->integer('ore_settimanali')->default(40);
            $table->decimal('costo_orario', 8, 2)->nullable();
            $table->boolean('attivo')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'telefono',
                'indirizzo',
                'cap',
                'citta',
                'provincia',
                'taglia_giacca',
                'taglia_pantaloni',
                'taglia_maglietta',
                'taglia_scarpe',
                'note_abbigliamento',
                'ore_settimanali',
                'costo_orario',
                'attivo'
            ]);
        });
    }
};
