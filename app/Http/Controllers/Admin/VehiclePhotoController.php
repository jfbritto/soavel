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

            // Photos arrive pre-cropped from Cropper.js — just optimize quality
            try {
                $img = \Intervention\Image\Facades\Image::make($file);
                // Limit max dimension to 800px on longest side, keep aspect ratio
                $img->resize(800, 800, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                Storage::disk('public')->put($storagePath, $img->encode('jpg', 85));
            } catch (\Throwable $e) {
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
