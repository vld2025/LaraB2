<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ImpostazioniFattura;

class ImpostazioniSeeder extends Seeder
{
    public function run(): void
    {
        // Crea le impostazioni di default
        ImpostazioniFattura::creaDefault();
        
        $this->command->info('Impostazioni fattura di default create con successo!');
    }
}
