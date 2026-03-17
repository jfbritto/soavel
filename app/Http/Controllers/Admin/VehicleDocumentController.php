<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleDocumentController extends Controller
{
    public function store(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'documents'   => 'required|array|min:1',
            'documents.*' => 'file|max:10240', // 10 MB
            'name'        => 'nullable|string|max:255',
            'categoria'   => 'required|string|in:' . implode(',', array_keys(VehicleDocument::CATEGORIAS)),
        ]);

        $created = 0;

        foreach ($request->file('documents') as $file) {
            $path = $file->store("vehicle-documents/{$vehicle->id}", 'local');

            VehicleDocument::create([
                'vehicle_id'    => $vehicle->id,
                'name'          => $request->input('name') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'original_name' => $file->getClientOriginalName(),
                'path'          => $path,
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'categoria'     => $request->input('categoria'),
            ]);

            $created++;
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'count' => $created]);
        }

        return back()->with('success', $created . ' documento(s) adicionado(s) com sucesso!');
    }

    public function destroy(Vehicle $vehicle, VehicleDocument $document)
    {
        abort_if($document->vehicle_id !== $vehicle->id, 404);

        $document->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Documento removido com sucesso.');
    }

    public function preview(Vehicle $vehicle, VehicleDocument $document)
    {
        abort_if($document->vehicle_id !== $vehicle->id, 404);
        abort_unless(Storage::disk('local')->exists($document->path), 404);

        return response(Storage::disk('local')->get($document->path))
            ->header('Content-Type', $document->mime_type)
            ->header('Content-Disposition', 'inline; filename="' . $document->original_name . '"');
    }

    public function download(Vehicle $vehicle, VehicleDocument $document)
    {
        abort_if($document->vehicle_id !== $vehicle->id, 404);
        abort_unless(Storage::disk('local')->exists($document->path), 404);

        return Storage::disk('local')->download($document->path, $document->original_name);
    }
}
