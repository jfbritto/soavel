<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSaleRequest;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['vehicle', 'customer', 'user'])->latest('data_venda');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('mes')) {
            $query->whereMonth('data_venda', $request->mes);
        }

        if ($request->filled('ano')) {
            $query->whereYear('data_venda', $request->ano);
        }

        $sales = $query->paginate(20)->withQueryString();

        $totalMes = Sale::whereMonth('data_venda', now()->month)
            ->whereYear('data_venda', now()->year)
            ->where('status', 'concluida')
            ->sum('preco_venda');

        // Veículos marcados como "vendido" sem registro de venda
        $vendidosSemVenda = Vehicle::where('status', 'vendido')
            ->whereDoesntHave('sales')
            ->with('principalPhoto')
            ->get();

        return view('admin.sales.index', compact('sales', 'totalMes', 'vendidosSemVenda'));
    }

    public function create(Request $request)
    {
        $vehicles  = Vehicle::whereIn('status', ['disponivel', 'reservado', 'vendido'])
            ->whereDoesntHave('sales', fn ($q) => $q->where('status', '!=', 'cancelada'))
            ->orderBy('marca')
            ->get(['id', 'marca', 'modelo', 'versao', 'ano_modelo', 'preco']);

        $customers = Customer::orderBy('nome')->get(['id', 'nome', 'cpf', 'telefone']);

        $selectedVehicle = $request->filled('vehicle_id')
            ? Vehicle::find($request->vehicle_id)
            : null;

        return view('admin.sales.create', compact('vehicles', 'customers', 'selectedVehicle'));
    }

    public function store(StoreSaleRequest $request)
    {
        $data            = $request->validated();
        $data['user_id'] = auth()->id();

        // Se há veículo de troca, cadastrá-lo antes de criar a venda
        if (in_array($data['tipo_pagamento'], ['permuta', 'misto']) && $request->filled('troca_marca')) {
            $request->validate([
                'troca_marca'          => 'required|string|max:50',
                'troca_modelo'         => 'required|string|max:60',
                'troca_versao'         => 'nullable|string|max:80',
                'troca_ano_fabricacao' => 'required|integer|min:1950|max:' . (date('Y') + 1),
                'troca_ano_modelo'     => 'required|integer|min:1950|max:' . (date('Y') + 1),
                'troca_km'             => 'required|integer|min:0',
                'troca_cor'            => 'required|string|max:30',
                'troca_categoria'      => 'required|in:hatch,sedan,suv,pickup,van,esportivo,outro',
                'troca_combustivel'    => 'required|in:gasolina,etanol,flex,diesel,gnv,hibrido,eletrico',
                'troca_transmissao'    => 'required|in:manual,automatico,cvt,semi_automatico',
            ]);

            $slug = Vehicle::generateSlug(
                $request->troca_marca,
                $request->troca_modelo,
                (int) $request->troca_ano_modelo
            );

            $trocaVehicle = Vehicle::create([
                'marca'          => $request->troca_marca,
                'modelo'         => $request->troca_modelo,
                'versao'         => $request->troca_versao,
                'ano_fabricacao' => $request->troca_ano_fabricacao,
                'ano_modelo'     => $request->troca_ano_modelo,
                'km'             => $request->troca_km,
                'cor'            => $request->troca_cor,
                'categoria'      => $request->troca_categoria,
                'combustivel'    => $request->troca_combustivel,
                'transmissao'    => $request->troca_transmissao,
                'portas'         => 4,
                'preco_compra'   => $data['valor_troca'] ?? null,
                'preco'          => $data['valor_troca'] ?? 0,
                'status'         => 'disponivel',
                'slug'           => $slug,
            ]);

            $data['troca_vehicle_id'] = $trocaVehicle->id;
        }

        $sale = Sale::create($data);

        // Sincronizar status do veículo vendido
        if ($sale->status === 'concluida') {
            $sale->vehicle->update(['status' => 'vendido']);
        } elseif ($sale->status === 'pendente') {
            $sale->vehicle->update(['status' => 'reservado']);
        }

        return redirect()->route('admin.sales.show', $sale)
            ->with('success', 'Venda registrada com sucesso!');
    }

    public function show(Sale $sale)
    {
        $sale->load(['vehicle.principalPhoto', 'trocaVehicle.principalPhoto', 'customer', 'user']);
        return view('admin.sales.show', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        $vehicle = $sale->vehicle;
        $sale->delete();

        if ($vehicle) {
            $vehicle->update(['status' => 'disponivel']);
        }

        return redirect()->route('admin.sales.index')
            ->with('success', 'Venda excluída com sucesso.');
    }

    public function updateStatus(Request $request, Sale $sale)
    {
        $request->validate(['status' => 'required|in:pendente,concluida,cancelada']);

        $oldStatus = $sale->status;
        $sale->update(['status' => $request->status]);

        // Sync vehicle status
        if ($request->status === 'concluida') {
            $sale->vehicle->update(['status' => 'vendido']);
        } elseif ($request->status === 'cancelada') {
            $sale->vehicle->update(['status' => 'disponivel']);
        } elseif ($request->status === 'pendente') {
            $sale->vehicle->update(['status' => 'reservado']);
        }

        return back()->with('success', 'Status da venda atualizado.');
    }
}
