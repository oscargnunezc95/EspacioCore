<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Studio;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage; // Añadido para manejar el borrado de logos futuros

class StudioController extends Controller
{
    public function index()
    {
        $studios = Studio::where('user_id', auth()->id())->latest()->get();
        return view('studios.index', compact('studios'));
    }

    public function store(Request $request)
    {
        // 1. Validación de todos los nuevos campos
        $request->validate([
            'name'      => 'required|string|max:255',
            'logo'      => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048', // Máximo 2MB
            'address'   => 'nullable|string|max:255',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'city'      => 'nullable|string|max:255',
            'region'    => 'nullable|string|max:255',
            'country'   => 'nullable|string|max:255',
        ]);

        // 2. Lógica del Subdominio Seguro
        $baseSlug = Str::slug($request->name);
        $subdomain = $baseSlug;
        $counter = 1;

        while (Studio::where('subdomain', $subdomain)->exists()) {
            $subdomain = $baseSlug . '-' . $counter;
            $counter++;
        }

        // 3. Subida del Logo (si el usuario adjuntó uno)
        $logoPath = null;
        if ($request->hasFile('logo')) {
            // Se guardará en storage/app/public/studios/logos
            $logoPath = $request->file('logo')->store('studios/logos', 'public');
        }

        // 4. Creación del Estudio con toda la data maestra
        Studio::create([
            'user_id'   => auth()->id(),
            'name'      => $request->name,
            'subdomain' => $subdomain,
            'logo_path' => $logoPath,
            'address'   => $request->address,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
            'city'      => $request->city,
            'region'    => $request->region,
            'country'   => $request->country,
        ]);

        return back()->with('success', '¡Tu nuevo Espacio ha sido creado con éxito!');
    }

    public function update(Request $request, Studio $studio)
    {
        if ($studio->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para editar este espacio.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            // Aquí en el futuro puedes agregar la validación para actualizar logo y dirección
        ]);

        $studio->update([
            'name' => $request->name
        ]);

        return back()->with('success', 'Nombre del espacio actualizado.');
    }

    public function destroy(Studio $studio)
    {
        if ($studio->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para eliminar este espacio.');
        }

        // Buena práctica: Eliminar el archivo del logo del servidor antes de borrar el registro
        if ($studio->logo_path) {
            Storage::disk('public')->delete($studio->logo_path);
        }

        $studio->delete();

        return back()->with('success', 'Espacio eliminado permanentemente.');
    }
}