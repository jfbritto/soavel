<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSaleRequest;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $trocas          = $data['trocas'] ?? [];
        $data['user_id'] = auth()->id();
        unset($data['trocas']);

        $sale = DB::transaction(function () use ($data, $trocas) {
            $sale = Sale::create($data);

            // Cada veículo de troca entra no estoque como Disponível e fica
            // vinculado à venda com o seu valor avaliado
            foreach ($trocas as $troca) {
                $valorTroca = $troca['valor_troca'] ?? null;

                $trocaVehicle = Vehicle::create([
                    'marca'          => $troca['marca'],
                    'modelo'         => $troca['modelo'],
                    'versao'         => $troca['versao'] ?? null,
                    'ano_fabricacao' => $troca['ano_fabricacao'],
                    'ano_modelo'     => $troca['ano_modelo'],
                    'km'             => $troca['km'],
                    'cor'            => $troca['cor'],
                    'categoria'      => $troca['categoria'],
                    'combustivel'    => $troca['combustivel'],
                    'transmissao'    => $troca['transmissao'],
                    'portas'         => 4,
                    'preco_compra'   => $valorTroca,
                    'preco'          => $valorTroca ?? 0,
                    'status'         => 'disponivel',
                    'slug'           => Vehicle::generateSlug(
                        $troca['marca'],
                        $troca['modelo'],
                        (int) $troca['ano_modelo']
                    ),
                ]);

                $sale->trocaVehicles()->attach($trocaVehicle->id, ['valor_troca' => $valorTroca]);
            }

            // Sincronizar status do veículo vendido
            if ($sale->status === 'concluida') {
                $sale->vehicle->update(['status' => 'vendido']);
            } elseif ($sale->status === 'pendente') {
                $sale->vehicle->update(['status' => 'reservado']);
            }

            return $sale;
        });

        return redirect()->route('admin.sales.show', $sale)
            ->with('success', 'Venda registrada com sucesso!');
    }

    public function show(Sale $sale)
    {
        $sale->load(['vehicle.principalPhoto', 'trocaVehicles.principalPhoto', 'customer', 'user']);
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
