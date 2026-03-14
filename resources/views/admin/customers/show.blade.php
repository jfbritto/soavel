@extends('adminlte::page')
@section('title', $customer->nome . ' — ' . config('adminlte.title'))
@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-user mr-2"></i>{{ $customer->nome }}</h1>
        <div>
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-warning" title="Editar dados do cliente"><i class="fas fa-edit mr-1"></i>Editar</a>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-default ml-1" title="Voltar para a lista de clientes"><i class="fas fa-arrow-left mr-1"></i>Voltar</a>
        </div>
    </div>
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body text-center pt-4">
                    <i class="fas fa-user-circle fa-4x text-muted mb-3"></i>
                    <h3>{{ $customer->nome }}</h3>
                    <p class="text-muted">{{ $customer->cidade ? $customer->cidade.'/'.$customer->estado : '' }}</p>
                    <a href="https://wa.me/55{{ preg_replace('/\D/', '', $customer->telefone) }}" target="_blank" class="btn btn-success btn-sm"
                       title="Abrir conversa no WhatsApp">
                        <i class="fab fa-whatsapp mr-1"></i>{{ $customer->telefone }}
                    </a>
                </div>
                <div class="card-footer">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th>CPF</th><td>{{ $customer->cpf ?? '—' }}</td></tr>
                        <tr><th>E-mail</th><td>{{ $customer->email ?? '—' }}</td></tr>
                        <tr><th>Endereço</th><td>{{ $customer->endereco_completo ?: '—' }}</td></tr>
                        @if($customer->observacoes)
                        <tr><th>Obs.</th><td>{{ $customer->observacoes }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-history mr-1"></i>Histórico de Compras ({{ $customer->sales->count() }})</h3></div>
                <div class="card-body p-0">
                    <table class="table table-hover">
                        <thead><tr><th>Foto</th><th>Veículo</th><th>Data</th><th>Valor</th><th>Pagamento</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($customer->sales as $sale)
                            <tr>
                                <td>
                                    @if($sale->vehicle?->principalPhoto)
                                        <img src="{{ $sale->vehicle->principalPhoto->url }}" width="50" height="40" style="object-fit:cover;border-radius:4px">
                                    @endif
                                </td>
                                <td><a href="{{ route('admin.sales.show', $sale) }}">{{ $sale->vehicle?->titulo ?? '—' }}</a></td>
                                <td>{{ $sale->data_venda->format('d/m/Y') }}</td>
                                <td>{{ $sale->preco_venda_formatado }}</td>
                                <td>{{ $sale->tipo_pagamento_label }}</td>
                                <td><span class="badge badge-{{ $sale->status_color }}">{{ $sale->status_label }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Nenhuma compra registrada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
