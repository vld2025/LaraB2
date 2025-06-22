<?php

namespace App\Http\Controllers;

use App\Models\Fattura;
use Illuminate\Http\Request;

class FatturaPdfController extends Controller
{
    public function viewPdf(Fattura $fattura)
    {
        return response()->json(['message' => 'PDF Fattura visualizzato']);
    }

    public function downloadPdf(Fattura $fattura)
    {
        return response()->json(['message' => 'PDF Fattura scaricato']);
    }
}
