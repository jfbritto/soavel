<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::withCount('vehicles')
            ->with(['vehicles' => fn($q) => $q->with('principalPhoto')->select('vehicles.id', 'marca', 'modelo', 'versao', 'ano_modelo', 'status', 'preco')])
            ->orderBy('nome')
            ->paginate(20);
        return view('admin.partners.index', compact('partners'));
    }

    public function create()
    {
        return view('admin.partners.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'        => 'required|string|max:150',
            'cpf'         => 'nullable|string|max:14|unique:partners,cpf',
            'telefone'    => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:150',
            'observacoes' => 'nullable|string',
        ]);

        Partner::create($data);

        return redirect()->route('admin.partners.index')
            ->with('success', 'Sócio cadastrado com sucesso!');
    }

    public function edit(Partner $partner)
    {
        $partner->load('vehicles');
        return view('admin.partners.edit', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $data = $request->validate([
            'nome'        => 'required|string|max:150',
            'cpf'         => 'nullable|string|max:14|unique:partners,cpf,' . $partner->id,
            'telefone'    => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:150',
            'observacoes' => 'nullable|string',
        ]);

        $partner->update($data);

        return redirect()->route('admin.partners.index')
            ->with('success', 'Sócio atualizado com sucesso!');
    }

    public function destroy(Partner $partner)
    {
        $partner->delete();

        return redirect()->route('admin.partners.index')
            ->with('success', 'Sócio removido com sucesso!');
    }

    // ── Vincular sócio a um veículo ───────────────────────────────────────────

    public function attachVehicle(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'percentual' => 'required|numeric|min:0.01|max:100',
        ]);

        $totalAtual = $vehicle->partners()
            ->where('partner_id', '!=', $data['partner_id'])
            ->sum('percentual');

        if ($totalAtual + $data['percentual'] > 100) {
            return back()->with('error', 'A soma dos percentuais ultrapassaria 100%. Total já alocado: ' . number_format($totalAtual, 2, ',', '.') . '%');
        }

        $vehicle->partners()->syncWithoutDetaching([
            $data['partner_id'] => ['percentual' => $data['percentual']],
        ]);

        return back()->with('success', 'Sócio vinculado ao veículo!');
    }

    public function detachVehicle(Vehicle $vehicle, Partner $partner)
    {
        $vehicle->partners()->detach($partner->id);

        return back()->with('success', 'Sócio removido do veículo.');
    }
}
