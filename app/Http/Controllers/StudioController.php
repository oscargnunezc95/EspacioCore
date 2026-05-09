<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Studio;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class StudioController extends Controller
{
    /**
     * Lista los estudios del usuario autenticado.
     */
    public function index()
    {
        $studios = Studio::where('user_id', auth()->id())->latest()->get();
        return view('studios.index', compact('studios'));
    }

    /**
     * Crea un nuevo estudio con validación de imagen y subdominio.
     */
    public function store(Request $request)
    {
        // 1. Validación (Soportando los 15MB que definimos para fotos artísticas)
        $request->validate([
            'name'      => 'required|string|max:255',
            'logo'      => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:15360', 
            'address'   => 'nullable|string|max:255',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'city'      => 'nullable|string|max:255',
            'region'    => 'nullable|string|max:255',
            'country'   => 'nullable|string|max:255',
        ]);

        // 2. Generación de Subdominio Único
        $baseSlug = Str::slug($request->name);
        $subdomain = $baseSlug;
        $counter = 1;

        while (Studio::where('subdomain', $subdomain)->exists()) {
            $subdomain = $baseSlug . '-' . $counter;
            $counter++;
        }

        // 3. Procesamiento de Imagen (Intervention Image v2.7)
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'studios/logos/' . uniqid() . '.webp';
            
            // Instanciamos el manager con GD
            $manager = new ImageManager(['driver' => 'gd']);
            
            // Comprimimos y redimensionamos para no saturar el servidor
            $image = $manager->make($file->getRealPath())
                ->resize(1920, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('webp', 80);
                             
            Storage::disk('public')->put($filename, (string) $image);
            $logoPath = $filename;
        }

        // 4. Persistencia
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

    /**
     * Actualiza un estudio existente y gestiona el reemplazo de archivos.
     */
    public function update(Request $request, Studio $studio)
{
    // 1. Auditoría de Seguridad
    if ($studio->user_id !== auth()->id()) {
        abort(403);
    }

    // 2. Validación
    $request->validate([
        'name' => 'required|string|max:255',
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:15360',
    ]);

    // 3. Preparar datos
    $data = $request->only(['name', 'address', 'latitude', 'longitude', 'city', 'region', 'country']);

    // 4. Lógica de Imagen (Intervention V2.7 Estabilizado)
    if ($request->hasFile('logo')) {
        try {
            $file = $request->file('logo');
            $newFilename = 'studios/logos/' . uniqid() . '.webp';
            
            // Sintaxis V2.7: Usamos array en lugar de instanciar el Driver
            $manager = new \Intervention\Image\ImageManager(['driver' => 'gd']);
            
            // Usamos make(), resize() y encode() en lugar de read() y scaleDown()
            $image = $manager->make($file->getRealPath())
                             ->resize(1920, null, function ($constraint) {
                                 $constraint->aspectRatio();
                                 $constraint->upsize();
                             })
                             ->encode('webp', 80);
            
            // Guardamos la nueva antes de borrar la vieja (Seguridad)
            Storage::disk('public')->put($newFilename, (string) $image);

            // Si llegamos aquí, podemos borrar la vieja con seguridad
            if ($studio->logo_path) {
                Storage::disk('public')->delete($studio->logo_path);
            }

            $data['logo_path'] = $newFilename;

        } catch (\Exception $e) {
            return redirect()->route('studios.index')->withErrors(['logo' => 'Error al procesar la imagen: ' . $e->getMessage()]);
        }
    }

    // 5. Actualización de Base de Datos
    $studio->update($data);

    // 6. REDIRECCIÓN EXPLÍCITA
    // En lugar de back(), vamos a la lista de estudios o al Dashboard
    return redirect()->route('studios.index')->with('success', 'Estudio actualizado correctamente.');
}

    /**
     * Elimina el estudio y sus archivos asociados.
     */
    public function destroy(Studio $studio)
    {
        if ($studio->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para eliminar este espacio.');
        }

        // Limpieza de archivos del almacenamiento antes de borrar el registro
        if ($studio->logo_path) {
            Storage::disk('public')->delete($studio->logo_path);
        }

        $studio->delete();

        return back()->with('success', 'Espacio eliminado permanentemente.');
    }
}