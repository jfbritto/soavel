<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['vehicle', 'user'])->latest('data');

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('mes')) {
            $query->whereMonth('data', $request->mes);
        }

        $expenses = $query->paginate(20)->withQueryString();

        $totalMes = Expense::whereMonth('data', now()->month)
            ->whereYear('data', now()->year)
            ->sum('valor');

        $vehicles = Vehicle::orderBy('marca')->get(['id', 'marca', 'modelo', 'versao', 'ano_modelo']);

        return view('admin.expenses.index', compact('expenses', 'totalMes', 'vehicles'));
    }

    public function create()
    {
        $vehicles = Vehicle::orderBy('marca')->get(['id', 'marca', 'modelo', 'versao', 'ano_modelo']);
        return view('admin.expenses.create', compact('vehicles'));
    }

    public function store(StoreExpenseRequest $request)
    {
        $data            = $request->validated();
        $data['user_id'] = auth()->id();

        $expense = Expense::create($data);

        if ($expense->vehicle_id) {
            return redirect()->route('admin.vehicles.show', $expense->vehicle_id)
                ->with('success', 'Despesa registrada com sucesso!');
        }

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Despesa registrada com sucesso!');
    }

    public function edit(Expense $expense)
    {
        $vehicles = Vehicle::orderBy('marca')->get(['id', 'marca', 'modelo', 'versao', 'ano_modelo']);
        return view('admin.expenses.edit', compact('expense', 'vehicles'));
    }

    public function update(StoreExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());

        if ($expense->vehicle_id) {
            return redirect()->route('admin.vehicles.show', $expense->vehicle_id)
                ->with('success', 'Despesa atualizada com sucesso!');
        }

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Despesa atualizada com sucesso!');
    }

    public function destroy(Expense $expense)
    {
        $vehicleId = $expense->vehicle_id;
        $expense->delete();

        if ($vehicleId) {
            return redirect()->route('admin.vehicles.show', $vehicleId)
                ->with('success', 'Despesa excluída com sucesso.');
        }

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Despesa excluída com sucesso.');
    }
}
