<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExploreController extends Controller
{
    public function index()
    {
        // En el futuro, aquí haremos la consulta a la base de datos:
        // $clases = Clase::where('is_public', true)->latest()->get();
        // return view('explore.index', compact('clases'));

        return view('explore.index');
    }
}