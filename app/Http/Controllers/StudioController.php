<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Studio;
use Illuminate\Support\Str;

class StudioController extends Controller
{
    public function index()
    {
        $studios = Studio::where('user_id', auth()->id())->latest()->get();
        return view('studios.index', compact('studios'));
    }

    public function store(Request $request)
    {
        // 1. Validamos solo el nombre (ya no pedimos el subdominio)
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // 2. Generamos el subdominio base a partir del nombre
        $baseSlug = Str::slug($request->name);
        $subdomain = $baseSlug;
        $counter = 1;

        // 3. Bucle de seguridad: Si el subdominio ya existe, le agregamos un número
        while (Studio::where('subdomain', $subdomain)->exists()) {
            $subdomain = $baseSlug . '-' . $counter;
            $counter++;
        }

        // 4. Guardamos el estudio
        Studio::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'subdomain' => $subdomain // Guardado de forma 100% automática y única
        ]);

        return back()->with('success', '¡Tu nuevo Espacio ha sido creado con éxito!');
    }
}