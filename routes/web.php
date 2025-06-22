<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

// Route per scontrini
Route::middleware(['auth'])->group(function () {
    Route::get('/scontrini/{spesa}/download', function (App\Models\Spesa $spesa) {
        abort_unless(auth()->user()->id === $spesa->user_id || auth()->user()->hasRole(['admin', 'manager']), 403);
        
        if (!$spesa->foto_scontrino || !Storage::disk('public')->exists($spesa->foto_scontrino)) {
            abort(404, 'File non trovato');
        }
        
        $extension = pathinfo($spesa->foto_scontrino, PATHINFO_EXTENSION);
        $filename = 'scontrino_' . $spesa->id . '_' . $spesa->mese . '_' . $spesa->anno . '.' . $extension;
        
        return Storage::disk('public')->download($spesa->foto_scontrino, $filename);
    })->name('scontrini.download');
});

// Route temporanea per testare la view PDF
Route::get('/test-pdf-view', function() {
    $user = auth()->user() ?: \App\Models\User::find(2);
    $scontrini = \App\Models\Spesa::where('user_id', $user->id)
        ->where('mese', 6)
        ->where('anno', 2025)
        ->whereNotNull('foto_scontrino')
        ->get();
    
    $scontriniConImmagini = $scontrini->map(function ($scontrino) {
        $extension = strtolower(pathinfo($scontrino->foto_scontrino, PATHINFO_EXTENSION));
        $filePath = storage_path('app/public/' . $scontrino->foto_scontrino);
        
        $scontrino->extension = $extension;
        $scontrino->file_exists = file_exists($filePath);
        
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp']) && file_exists($filePath)) {
            $imageData = file_get_contents($filePath);
            $mimeType = match($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png', 
                'webp' => 'image/webp',
                default => 'image/jpeg'
            };
            $scontrino->base64_image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        } else {
            $scontrino->base64_image = null;
        }
        
        return $scontrino;
    });
    
    $dati = [
        'user' => $user,
        'scontrini' => $scontriniConImmagini,
        'mese' => 'Giugno',
        'anno' => 2025,
        'totaleFiles' => $scontrini->count(),
        'dataGenerazione' => now()->format('d/m/Y H:i')
    ];
    
    return view('pdf.scontrini-mensili', $dati);
});

Route::get("/fatture/{fattura}/pdf", [App\Http\Controllers\FatturaPdfController::class, "viewPdf"])->name("fatture.pdf");
Route::get("/fatture/{fattura}/download", [App\Http\Controllers\FatturaPdfController::class, "downloadPdf"])->name("fatture.download");


Route::get('/api/server-time', function () {
    return response()->json([
        'time' => now()->format('d/m/Y H:i:s'),
        'timezone' => config('app.timezone')
    ]);
});
