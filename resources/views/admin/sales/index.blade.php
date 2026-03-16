@extends('adminlte::page')

@section('title', 'Vendas — ' . config('adminlte.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-handshake mr-2"></i>Vendas</h1>
        <a href="{{ route('admin.sales.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i>Nova Venda
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Stat --}}
    <div class="d-flex align-items-baseline mb-3" style="gap:6px">
        <span class="text-muted" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.06em;font-weight:600">Faturamento do mês</span>
        <span class="font-weight-bold text-success" style="font-size:1.1rem">R$ {{ number_format($totalMes, 0, ',', '.') }}</span>
    </div>

    {{-- Filtros --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="form-inline flex-wrap" style="gap:8px">
                <select name="status" class="form-control form-control-sm">
                    <option value="">Todos os status</option>
                    <option value="pendente"  {{ (request('status') === 'pendente')  ? 'selected' : '' }}>Pendente</option>
                    <option value="concluida" {{ (request('status') === 'concluida') ? 'selected' : '' }}>Concluída</option>
                    <option value="cancelada" {{ (request('status') === 'cancelada') ? 'selected' : '' }}>Cancelada</option>
                </select>
                <select name="mes" class="form-control form-control-sm">
                    <option value="">Todos os meses</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ (request('mes') == $m) ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->monthName }}
                        </option>
                    @endforeach
                </select>
                <select name="ano" class="form-control form-control-sm">
                    @foreach(range(date('Y'), date('Y')-3) as $y)
                        <option value="{{ $y }}" {{ (request('ano', date('Y')) == $y) ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary">Filtrar</button>
                <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-link text-muted">Limpar</a>
            </form>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6c757d;border-top:0">
                        <th class="border-top-0 pl-3 d-none d-md-table-cell">Data</th>
                        <th class="border-top-0">Veículo</th>
                        <th class="border-top-0">Cliente</th>
                        <th class="border-top-0 d-none d-lg-table-cell">Pagamento</th>
                        <th class="border-top-0">Valor</th>
                        <th class="border-top-0 d-none d-lg-table-cell">Vendedor</th>
                        <th class="border-top-0">Status</th>
                        <th class="border-top-0" style="width:60px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td class="pl-3 align-middle text-muted d-none d-md-table-cell" style="font-size:.85rem;white-space:nowrap">
                            {{ $sale->data_venda->format('d/m/Y') }}
                        </td>
                        <td class="align-middle" style="font-size:.88rem">
                            {{ $sale->vehicle?->titulo ?? '—' }}
                        </td>
                        <td class="align-middle" style="font-size:.88rem">
                            {{ $sale->customer?->nome ?? '—' }}
                        </td>
                        <td class="align-middle text-muted d-none d-lg-table-cell" style="font-size:.85rem">
                            {{ $sale->tipo_pagamento_label }}
                        </td>
                        <td class="align-middle font-weight-600" style="font-size:.9rem;color:#1a7a3c;white-space:nowrap">
                            {{ $sale->preco_venda_formatado }}
                        </td>
                        <td class="align-middle text-muted d-none d-lg-table-cell" style="font-size:.85rem">
                            {{ $sale->user?->name ?? '—' }}
                        </td>
                        <td class="align-middle">
                            <span class="badge badge-{{ $sale->status_color }}" style="font-size:.75rem;padding:3px 8px">
                                {{ $sale->status_label }}
                            </span>
                        </td>
                        <td class="align-middle text-right pr-3">
                            <a href="{{ route('admin.sales.show', $sale) }}"
                               class="btn btn-sm btn-outline-secondary" style="font-size:.75rem;padding:2px 10px"
                               title="Ver detalhes desta venda">
                                Abrir
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-handshake fa-2x d-block mb-2" style="color:#dee2e6"></i>
                            Nenhuma venda encontrada.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center py-3" style="background:#f8f9fa;border-top:1px solid #eee">
            <small class="text-muted">{{ $sales->total() }} {{ $sales->total() === 1 ? 'registro' : 'registros' }}</small>
            @if($sales->hasPages()) {{ $sales->withQueryString()->links() }} @endif
        </div>
    </div>

</div>
@endsection
