<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVehicleRequest;
use App\Http\Requests\Admin\UpdateVehicleRequest;
use App\Models\Vehicle;
use App\Models\VehicleFeature;
use Database\Seeders\VehicleFeatureSeeder;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::withCount(['photos', 'sales'])
            ->with('principalPhoto');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('marca')) {
            $query->where('marca', $request->marca);
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('marca', 'like', "%{$s}%")
                  ->orWhere('modelo', 'like', "%{$s}%")
                  ->orWhere('versao', 'like', "%{$s}%")
                  ->orWhere('placa', 'like', "%{$s}%");
            });
        }

        $vehicles = $query->latest()->paginate(15)->withQueryString();
        $marcas   = Vehicle::distinct()->orderBy('marca')->pluck('marca');

        return view('admin.vehicles.index', compact('vehicles', 'marcas'));
    }

    public function create()
    {
        $features = VehicleFeatureSeeder::$features;
        return view('admin.vehicles.create', compact('features'));
    }

    public function store(StoreVehicleRequest $request)
    {
        $data           = $request->validated();
        $data['destaque'] = $request->boolean('destaque');
        $data['slug']   = Vehicle::generateSlug($data['marca'], $data['modelo'], $data['ano_modelo']);

        $features = $data['features'] ?? [];
        unset($data['features']);

        $vehicle = Vehicle::create($data);

        foreach ($features as $feature) {
            VehicleFeature::create(['vehicle_id' => $vehicle->id, 'feature' => $feature]);
        }

        return redirect()->route('admin.vehicles.show', $vehicle)
            ->with('success', 'Veículo cadastrado com sucesso! Adicione as fotos abaixo.');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['photos', 'features', 'sales.customer', 'leads', 'partners', 'vendaOrigem.vehicle.principalPhoto', 'vendaOrigem.customer']);
        $expenses    = $vehicle->expenses()->latest('data')->get();
        $allPartners = \App\Models\Partner::orderBy('nome')->get(['id', 'nome']);
        return view('admin.vehicles.show', compact('vehicle', 'expenses', 'allPartners'));
    }

    public function edit(Vehicle $vehicle)
    {
        $vehicle->load('features');
        $features          = VehicleFeatureSeeder::$features;
        $currentFeatures   = $vehicle->features->pluck('feature')->toArray();

        return view('admin.vehicles.edit', compact('vehicle', 'features', 'currentFeatures'));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        $data             = $request->validated();
        $data['destaque'] = $request->boolean('destaque');

        $features = $data['features'] ?? [];
        unset($data['features']);

        $vehicle->update($data);

        // Resync features
        $vehicle->features()->delete();
        foreach ($features as $feature) {
            VehicleFeature::create(['vehicle_id' => $vehicle->id, 'feature' => $feature]);
        }

        return redirect()->route('admin.vehicles.show', $vehicle)
            ->with('success', 'Veículo atualizado com sucesso!');
    }

    public function destroy(Vehicle $vehicle)
    {
        if ($vehicle->sales()->exists()) {
            return back()->with('error', 'Não é possível excluir um veículo com vendas registradas.');
        }

        // Photos are deleted via cascade in DB; also remove from storage
        foreach ($vehicle->photos as $photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($photo->path);
        }

        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Veículo excluído com sucesso.');
    }
}
