<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function home()
    {
        $destaques = Vehicle::disponivel()
            ->destaque()
            ->with('principalPhoto')
            ->latest()
            ->take(6)
            ->get();

        $totalVehiculos = Vehicle::disponivel()->count();

        $marcas = Vehicle::disponivel()
            ->distinct()
            ->pluck('marca')
            ->sort()
            ->values();

        return view('welcome', compact('destaques', 'totalVehiculos', 'marcas'));
    }

    public function index(Request $request)
    {
        $sortMap = [
            'preco_asc'  => ['preco', 'asc'],
            'preco_desc' => ['preco', 'desc'],
            'ano_desc'   => ['ano_modelo', 'desc'],
            'km_asc'     => ['km', 'asc'],
            'recente'    => ['created_at', 'desc'],
        ];

        [$sortCol, $sortDir] = $sortMap[$request->get('ordenar', 'recente')] ?? ['created_at', 'desc'];

        $query = Vehicle::disponivel()->with('principalPhoto');

        $query
            ->when($request->marca, fn($q, $v) => $q->where('marca', $v))
            ->when($request->modelo, fn($q, $v) => $q->where('modelo', 'like', "%{$v}%"))
            ->when($request->ano_min, fn($q, $v) => $q->where('ano_modelo', '>=', $v))
            ->when($request->ano_max, fn($q, $v) => $q->where('ano_modelo', '<=', $v))
            ->when($request->preco_max, fn($q, $v) => $q->where('preco', '<=', $v))
            ->when($request->km_max, fn($q, $v) => $q->where('km', '<=', $v))
            ->when($request->categoria, fn($q, $v) => $q->where('categoria', $v))
            ->when($request->combustivel, fn($q, $v) => $q->where('combustivel', $v))
            ->when($request->transmissao, fn($q, $v) => $q->where('transmissao', $v))
            ->orderBy($sortCol, $sortDir);

        $vehicles = $query->paginate(12)->withQueryString();

        // Filter options
        $marcas    = Vehicle::disponivel()->distinct()->orderBy('marca')->pluck('marca');
        $precoMax  = Vehicle::disponivel()->max('preco');
        $kmMax     = Vehicle::disponivel()->max('km');

        return view('site.vehicles.index', compact('vehicles', 'marcas', 'precoMax', 'kmMax'));
    }

    public function show(string $slug)
    {
        $vehicle = Vehicle::where('slug', $slug)
            ->with(['photos', 'features'])
            ->firstOrFail();

        $similares = Vehicle::disponivel()
            ->where('categoria', $vehicle->categoria)
            ->where('id', '!=', $vehicle->id)
            ->with('principalPhoto')
            ->take(3)
            ->get();

        return view('site.vehicles.show', compact('vehicle', 'similares'));
    }
}
