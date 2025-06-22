<?php
// Controlliamo la struttura delle tabelle
$commessa = \App\Models\Commessa::with('cantiere')->first();
if ($commessa && $commessa->cantiere) {
    echo "Commessa -> Cantiere -> Cliente funziona\n";
    echo "Cantiere ID: " . $commessa->cantiere->id . "\n";
    echo "Cliente ID: " . $commessa->cantiere->cliente_id . "\n";
} else {
    echo "Problema nella relazione\n";
}
