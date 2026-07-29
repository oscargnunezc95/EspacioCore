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
     * Crea un nuevo estudio con validación de imágenes, subdominio y redes.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'logo'          => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:15360', 
            'cover'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:15360', 
            'address'       => 'nullable|string|max:255',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'city'          => 'nullable|string|max:255',
            'region'        => 'nullable|string|max:255',
            'country'       => 'nullable|string|max:255',
            'description'   => 'nullable|string|max:1000',
            'instagram_url' => 'nullable|url|max:255',
            'tiktok_url'    => 'nullable|url|max:255',
            'youtube_url'   => 'nullable|url|max:255',
            'email'         => 'nullable|email|max:255',
            'whatsapp'      => 'nullable|string|max:50',
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
        $coverPath = null;

        $manager = new ImageManager(['driver' => 'gd']);

        // 1. Procesamiento de Logo (Cuadrado / Icono)
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $baseFilename = uniqid();
            $filename = 'studios/logos/' . $baseFilename . '.webp';
            $iconFilename = 'studios/logos/' . $baseFilename . '_icon.webp';
            
            $image = $manager->make($file->getRealPath())
                ->resize(1920, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('webp', 80);

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

        // 2. Procesamiento de Foto de Portada / Card (Horizontal 16:9 -> 1200x675)
        if ($request->hasFile('cover')) {
            $file = $request->file('cover');
            $coverFilename = 'studios/covers/' . uniqid() . '_cover.webp';
            
            $coverImage = $manager->make($file->getRealPath())
                ->fit(1200, 675, function ($constraint) {
                    $constraint->upsize();
                })
                ->encode('webp', 80);

            Storage::disk('public')->put($coverFilename, (string) $coverImage);
            $coverPath = $coverFilename;
        }

        Studio::create([
            'user_id'       => auth()->id(),
            'name'          => $request->name,
            'subdomain'     => $subdomain,
            'logo_path'     => $logoPath,
            'icon_path'     => $iconPath,
            'cover_path'    => $coverPath,
            'address'       => $request->address,
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude,
            'city'          => $request->city,
            'region'        => $request->region,
            'country'       => $request->country,
            'description'   => $request->description,
            'instagram_url' => $request->instagram_url,
            'tiktok_url'    => $request->tiktok_url,
            'youtube_url'   => $request->youtube_url,
            'email'         => $request->email,
            'whatsapp'      => $request->whatsapp,
        ]);

        return back()->with('success', '¡Tu nuevo Espacio ha sido creado con éxito!');
    }

    /**
     * Actualiza un estudio existente y gestiona el reemplazo independiente de archivos.
     */
    public function update(Request $request, Studio $studio)
    {
        if ($studio->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'name'          => 'required|string|max:255',
            'logo'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:15360',
            'cover'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:15360',
            'description'   => 'nullable|string|max:1000',
            'instagram_url' => 'nullable|url|max:255',
            'tiktok_url'    => 'nullable|url|max:255',
            'youtube_url'   => 'nullable|url|max:255',
            'email'         => 'nullable|email|max:255',
            'whatsapp'      => 'nullable|string|max:50',
        ]);

        $data = $request->only([
            'name', 'address', 'latitude', 'longitude', 'city', 'region', 'country',
            'description', 'instagram_url', 'tiktok_url', 'youtube_url', 'email', 'whatsapp'
        ]);

        $manager = new ImageManager(['driver' => 'gd']);

        // Actualización de Logo
        if ($request->hasFile('logo')) {
            try {
                $file = $request->file('logo');
                $baseFilename = uniqid();
                $newFilename = 'studios/logos/' . $baseFilename . '.webp';
                $newIconFilename = 'studios/logos/' . $baseFilename . '_icon.webp';
                
                $image = $manager->make($file->getRealPath())
                    ->resize(1920, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('webp', 80);

                $icon = $manager->make($file->getRealPath())
                    ->fit(200, 200, function ($constraint) {
                        $constraint->upsize();
                    })
                    ->encode('webp', 80);
                
                Storage::disk('public')->put($newFilename, (string) $image);
                Storage::disk('public')->put($newIconFilename, (string) $icon);

                if ($studio->logo_path) Storage::disk('public')->delete($studio->logo_path);
                if ($studio->icon_path) Storage::disk('public')->delete($studio->icon_path);

                $data['logo_path'] = $newFilename;
                $data['icon_path'] = $newIconFilename;
            } catch (\Exception $e) {
                return redirect()->route('studios.index')->withErrors(['logo' => 'Error en logo: ' . $e->getMessage()]);
            }
        }

        // Actualización de Portada / Card
        if ($request->hasFile('cover')) {
            try {
                $file = $request->file('cover');
                $newCoverFilename = 'studios/covers/' . uniqid() . '_cover.webp';
                
                $coverImage = $manager->make($file->getRealPath())
                    ->fit(1200, 675, function ($constraint) {
                        $constraint->upsize();
                    })
                    ->encode('webp', 80);

                Storage::disk('public')->put($newCoverFilename, (string) $coverImage);

                if ($studio->cover_path) Storage::disk('public')->delete($studio->cover_path);

                $data['cover_path'] = $newCoverFilename;
            } catch (\Exception $e) {
                return redirect()->route('studios.index')->withErrors(['cover' => 'Error en portada: ' . $e->getMessage()]);
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

        if ($studio->logo_path) Storage::disk('public')->delete($studio->logo_path);
        if ($studio->icon_path) Storage::disk('public')->delete($studio->icon_path);
        if ($studio->cover_path) Storage::disk('public')->delete($studio->cover_path);

        $studio->delete();

        return back()->with('success', 'Espacio eliminado permanentemente.');
    }
}