<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Controlla se la richiesta contiene un parametro locale
            if ($request->has('locale')) {
                $locale = $request->get('locale');
                if (in_array($locale, ['it', 'en', 'de', 'ru'])) {
                    session(['locale' => $locale]);
                }
            }

            // Prova a ottenere il locale dalla sessione, con fallback alla configurazione
            $locale = session('locale', config('app.locale', 'it'));
            
            // Assicurati che il locale sia valido
            if (!in_array($locale, ['it', 'en', 'de', 'ru'])) {
                $locale = 'it';
            }
            
            app()->setLocale($locale);
            
        } catch (\Exception $e) {
            // Se c'è un errore, usa il locale di default
            app()->setLocale(config('app.locale', 'it'));
        }

        return $next($request);
    }
}
