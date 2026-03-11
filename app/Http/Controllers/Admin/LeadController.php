<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLeadRequest;
use App\Models\Lead;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with(['vehicle', 'user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('origem')) {
            $query->where('origem', $request->origem);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nome', 'like', "%{$s}%")
                  ->orWhere('telefone', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $leads = $query->paginate(20)->withQueryString();

        $counts = [
            'novo'       => Lead::where('status', 'novo')->count(),
            'em_contato' => Lead::where('status', 'em_contato')->count(),
            'convertido' => Lead::where('status', 'convertido')->count(),
            'perdido'    => Lead::where('status', 'perdido')->count(),
        ];

        return view('admin.leads.index', compact('leads', 'counts'));
    }

    public function create()
    {
        $vehicles = Vehicle::where('status', '!=', 'vendido')->orderBy('marca')->get(['id', 'marca', 'modelo', 'versao', 'ano_modelo']);
        $users    = User::orderBy('name')->get(['id', 'name']);

        return view('admin.leads.create', compact('vehicles', 'users'));
    }

    public function store(StoreLeadRequest $request)
    {
        $lead = Lead::create($request->validated());

        return redirect()->route('admin.leads.index')
            ->with('success', 'Lead criado com sucesso!');
    }

    public function edit(Lead $lead)
    {
        $vehicles = Vehicle::where('status', '!=', 'vendido')->orderBy('marca')->get(['id', 'marca', 'modelo', 'versao', 'ano_modelo']);
        $users    = User::orderBy('name')->get(['id', 'name']);

        return view('admin.leads.edit', compact('lead', 'vehicles', 'users'));
    }

    public function update(StoreLeadRequest $request, Lead $lead)
    {
        $lead->update($request->validated());

        return redirect()->route('admin.leads.index')
            ->with('success', 'Lead atualizado com sucesso!');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->route('admin.leads.index')
            ->with('success', 'Lead excluído com sucesso!');
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $request->validate(['status' => 'required|in:novo,em_contato,convertido,perdido']);
        $lead->update(['status' => $request->status]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $lead->status_label]);
        }

        return back()->with('success', 'Status do lead atualizado.');
    }

    public function assign(Request $request, Lead $lead)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $lead->update(['user_id' => $request->user_id]);

        return back()->with('success', 'Lead atribuído com sucesso.');
    }
}
