<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VehiclePhotoController extends Controller
{
    public function store(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'photos'   => 'required|array|min:1',
            'photos.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $nextOrdem      = VehiclePhoto::where('vehicle_id', $vehicle->id)->max('ordem') + 1;
        $hasPrincipal   = VehiclePhoto::where('vehicle_id', $vehicle->id)->where('principal', true)->exists();
        $created        = [];

        foreach ($request->file('photos') as $index => $file) {
            $filename    = Str::uuid() . '.jpg';
            $storagePath = "vehicles/{$vehicle->id}/{$filename}";

            try {
                $img = \Intervention\Image\Facades\Image::make($file);

                // Full quality photo (1920px longest side, 92% quality)
                $img->resize(1920, 1920, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                Storage::disk('public')->put($storagePath, $img->encode('jpg', 92));

                // Thumbnail for cards/listings (480px longest side, 80% quality)
                $thumbPath = "vehicles/{$vehicle->id}/thumbs/{$filename}";
                $thumb = \Intervention\Image\Facades\Image::make($file);
                $thumb->resize(480, 480, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                Storage::disk('public')->put($thumbPath, $thumb->encode('jpg', 80));
            } catch (\Throwable) {
                $file->storeAs("vehicles/{$vehicle->id}", $filename, 'public');
            }

            $isPrincipal = !$hasPrincipal && $index === 0;

            $photo = VehiclePhoto::create([
                'vehicle_id' => $vehicle->id,
                'path'       => $storagePath,
                'ordem'      => $nextOrdem + $index,
                'principal'  => $isPrincipal,
            ]);

            if ($isPrincipal) {
                $hasPrincipal = true;
            }

            $created[] = ['id' => $photo->id, 'url' => $photo->url];
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'photos' => $created]);
        }

        return back()->with('success', count($created) . ' foto(s) adicionada(s) com sucesso!');
    }

    public function destroy(Vehicle $vehicle, VehiclePhoto $photo)
    {
        Storage::disk('public')->delete($photo->path);
        $thumbPath = str_replace(basename($photo->path), 'thumbs/' . basename($photo->path), $photo->path);
        Storage::disk('public')->delete($thumbPath);
        $wasPrincipal = $photo->principal;
        $photo->delete();

        // Promote next photo to principal if needed
        if ($wasPrincipal) {
            $next = VehiclePhoto::where('vehicle_id', $vehicle->id)->orderBy('ordem')->first();
            $next?->update(['principal' => true]);
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Foto removida com sucesso.');
    }

    public function setPrincipal(Vehicle $vehicle, VehiclePhoto $photo)
    {
        VehiclePhoto::where('vehicle_id', $vehicle->id)->update(['principal' => false]);
        $photo->update(['principal' => true]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Foto principal atualizada.');
    }

    public function emPreparacao(Vehicle $vehicle)
    {
        $source = public_path('images/em-preparacao.jpg');

        if (!file_exists($source)) {
            return back()->with('error', 'Imagem "Em Preparação" não encontrada no sistema.');
        }

        // Remove todas as fotos atuais do veículo
        foreach ($vehicle->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
            $thumbPath = str_replace(basename($photo->path), 'thumbs/' . basename($photo->path), $photo->path);
            Storage::disk('public')->delete($thumbPath);
            $photo->delete();
        }

        $filename    = Str::uuid() . '.jpg';
        $storagePath = "vehicles/{$vehicle->id}/{$filename}";
        $thumbPath   = "vehicles/{$vehicle->id}/thumbs/{$filename}";

        // Copia a imagem como foto principal
        Storage::disk('public')->put($storagePath, file_get_contents($source));

        // Gera thumbnail
        try {
            $thumb = \Intervention\Image\Facades\Image::make($source);
            $thumb->resize(480, 480, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            Storage::disk('public')->put($thumbPath, $thumb->encode('jpg', 80));
        } catch (\Throwable) {
            Storage::disk('public')->put($thumbPath, file_get_contents($source));
        }

        VehiclePhoto::create([
            'vehicle_id' => $vehicle->id,
            'path'       => $storagePath,
            'ordem'      => 0,
            'principal'  => true,
        ]);

        return back()->with('success', 'Foto "Em Preparação" definida com sucesso!');
    }

    public function reorder(Request $request, Vehicle $vehicle)
    {
        $request->validate(['order' => 'required|array']);

        foreach ($request->order as $index => $photoId) {
            VehiclePhoto::where('id', $photoId)
                ->where('vehicle_id', $vehicle->id)
                ->update(['ordem' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
