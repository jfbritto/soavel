<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('sales');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nome', 'like', "%{$s}%")
                  ->orWhere('cpf', 'like', "%{$s}%")
                  ->orWhere('telefone', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $customers = $query->orderBy('nome')->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function cpfCheck(Request $request)
    {
        $cpf = $request->query('cpf');
        if (!$cpf) return response()->json(null);

        $customer = Customer::where('cpf', $cpf)->first();
        if (!$customer) return response()->json(null);

        return response()->json([
            'id'       => $customer->id,
            'nome'     => $customer->nome,
            'cpf'      => $customer->cpf,
            'telefone' => $customer->telefone,
            'cidade'   => $customer->cidade,
            'estado'   => $customer->estado,
        ]);
    }

    public function quickStore(Request $request)
    {
        $request->validate([
            'nome'     => 'required|string|max:100',
            'cpf'      => 'nullable|string|max:14',
            'telefone' => 'required|string|max:20',
            'email'    => 'nullable|email|max:150',
            'cidade'   => 'nullable|string|max:80',
            'estado'   => 'nullable|string|size:2',
        ]);

        $customer = Customer::create($request->only('nome','cpf','telefone','email','cidade','estado'));

        return response()->json([
            'id'       => $customer->id,
            'nome'     => $customer->nome,
            'cpf'      => $customer->cpf,
            'telefone' => $customer->telefone,
        ]);
    }

    public function show(Customer $customer)
    {
        $customer->load(['sales.vehicle.principalPhoto', 'documents']);
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(StoreCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            return back()->with('error', 'Não é possível excluir um cliente com vendas registradas.');
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Cliente excluído com sucesso.');
    }
}
