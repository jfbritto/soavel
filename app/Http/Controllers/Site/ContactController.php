<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\ContactRequest;
use App\Models\Lead;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(ContactRequest $request)
    {
        Lead::create([
            'nome'       => $request->nome,
            'telefone'   => $request->telefone,
            'email'      => $request->email,
            'vehicle_id' => $request->vehicle_id,
            'interesse'  => $request->mensagem,
            'origem'     => 'site',
            'status'     => 'novo',
        ]);

        return back()->with('contact_success', 'Mensagem enviada! Em breve entraremos em contato.');
    }

    public function interesse(ContactRequest $request, Vehicle $vehicle)
    {
        Lead::create([
            'nome'       => $request->nome,
            'telefone'   => $request->telefone,
            'email'      => $request->email,
            'vehicle_id' => $vehicle->id,
            'interesse'  => "Interesse no {$vehicle->titulo} ({$vehicle->ano_modelo}). " . ($request->mensagem ?? ''),
            'origem'     => 'site',
            'status'     => 'novo',
        ]);

        return back()->with('contact_success', 'Interesse registrado! Nossa equipe entrará em contato em breve.');
    }
}
