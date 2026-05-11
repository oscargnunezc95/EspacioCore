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

        $baseSlug = Str::slug($request->name);
        $subdomain = $baseSlug;
        $counter = 1;

        while (Studio::where('subdomain', $subdomain)->exists()) {
            $subdomain = $baseSlug . '-' . $counter;
            $counter++;
        }

        $logoPath = null;
        $iconPath = null;

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $baseFilename = uniqid();
            $filename = 'studios/logos/' . $baseFilename . '.webp';
            $iconFilename = 'studios/logos/' . $baseFilename . '_icon.webp';
            
            $manager = new ImageManager(['driver' => 'gd']);
            
            // 1. Generamos la imagen original optimizada (Max 1920px)
            $image = $manager->make($file->getRealPath())
                ->resize(1920, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('webp', 80);

            // 2. Generamos el Icono/Thumbnail (Cuadrado perfecto de 200x200px)
            $icon = $manager->make($file->getRealPath())
                ->fit(200, 200, function ($constraint) {
                    $constraint->upsize();
                })
                ->encode('webp', 80);
                             
            Storage::disk('public')->put($filename, (string) $image);
            Storage::disk('public')->put($iconFilename, (string) $icon);
            
            $logoPath = $filename;
            $iconPath = $iconFilename;
        }

        Studio::create([
            'user_id'   => auth()->id(),
            'name'      => $request->name,
            'subdomain' => $subdomain,
            'logo_path' => $logoPath,
            'icon_path' => $iconPath, // Guardamos la ruta del icono
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
        if ($studio->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:15360',
        ]);

        $data = $request->only(['name', 'address', 'latitude', 'longitude', 'city', 'region', 'country']);

        if ($request->hasFile('logo')) {
            try {
                $file = $request->file('logo');
                $baseFilename = uniqid();
                $newFilename = 'studios/logos/' . $baseFilename . '.webp';
                $newIconFilename = 'studios/logos/' . $baseFilename . '_icon.webp';
                
                $manager = new \Intervention\Image\ImageManager(['driver' => 'gd']);
                
                // Imagen principal
                $image = $manager->make($file->getRealPath())
                                 ->resize(1920, null, function ($constraint) {
                                     $constraint->aspectRatio();
                                     $constraint->upsize();
                                 })
                                 ->encode('webp', 80);

                // Icono
                $icon = $manager->make($file->getRealPath())
                                ->fit(200, 200, function ($constraint) {
                                    $constraint->upsize();
                                })
                                ->encode('webp', 80);
                
                // Guardamos las nuevas versiones
                Storage::disk('public')->put($newFilename, (string) $image);
                Storage::disk('public')->put($newIconFilename, (string) $icon);

                // Borramos las viejas si existían
                if ($studio->logo_path) {
                    Storage::disk('public')->delete($studio->logo_path);
                }
                if ($studio->icon_path) {
                    Storage::disk('public')->delete($studio->icon_path);
                }

                $data['logo_path'] = $newFilename;
                $data['icon_path'] = $newIconFilename;

            } catch (\Exception $e) {
                return redirect()->route('studios.index')->withErrors(['logo' => 'Error al procesar la imagen: ' . $e->getMessage()]);
            }
        }

        $studio->update($data);

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

        if ($studio->logo_path) {
            Storage::disk('public')->delete($studio->logo_path);
        }
        
        if ($studio->icon_path) {
            Storage::disk('public')->delete($studio->icon_path);
        }

        $studio->delete();

        return back()->with('success', 'Espacio eliminado permanentemente.');
    }
}