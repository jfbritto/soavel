@extends('adminlte::page')

@section('title', 'Venda #' . $sale->id . ' — ' . config('adminlte.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center" style="gap:10px">
            <h1 class="mb-0">Venda #{{ $sale->id }}</h1>
            <span class="badge badge-{{ $sale->status_color }} px-2 py-1" style="font-size:.8rem">{{ $sale->status_label }}</span>
        </div>
        <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Voltar
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">

        {{-- ── Coluna principal ────────────────────────────────── --}}
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">

                    {{-- Veículo + Cliente --}}
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted text-uppercase mb-2" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Veículo</p>
                            @if($sale->vehicle)
                            <div class="d-flex align-items-center">
                                @if($sale->vehicle->principalPhoto)
                                <img src="{{ $sale->vehicle->principalPhoto->url }}" width="72" height="54"
                                     style="object-fit:cover;border-radius:6px;margin-right:12px;flex-shrink:0;border:1px solid #dee2e6">
                                @else
                                <div style="width:72px;height:54px;background:#f0f0f0;border-radius:6px;margin-right:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center">
                                    <i class="fas fa-car text-muted"></i>
                                </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.vehicles.show', $sale->vehicle) }}" class="font-weight-bold text-dark" style="font-size:.95rem">
                                        {{ $sale->vehicle->titulo }}
                                    </a><br>
                                    <span class="text-muted" style="font-size:.82rem">
                                        {{ $sale->vehicle->ano_fabricacao }}/{{ $sale->vehicle->ano_modelo }}
                                        · {{ $sale->vehicle->km_formatado }}
                                    </span>
                                </div>
                            </div>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <p class="text-muted text-uppercase mb-2" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Cliente</p>
                            @if($sale->customer)
                            <span class="font-weight-bold" style="font-size:.95rem">{{ $sale->customer->nome }}</span><br>
                            <span class="text-muted" style="font-size:.82rem">{{ $sale->customer->telefone }}</span><br>
                            <span class="text-muted" style="font-size:.82rem">{{ $sale->customer->email }}</span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Valores e dados financeiros --}}
                    <div class="row">
                        <div class="col-6 col-md-3 mb-3">
                            <p class="text-muted text-uppercase mb-1" style="font-size:.68rem;letter-spacing:.08em;font-weight:700">Preço de Venda</p>
                            <p class="text-success font-weight-bold mb-0" style="font-size:1.2rem">{{ $sale->preco_venda_formatado }}</p>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <p class="text-muted text-uppercase mb-1" style="font-size:.68rem;letter-spacing:.08em;font-weight:700">Data</p>
                            <p class="mb-0" style="font-size:.9rem">{{ $sale->data_venda->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <p class="text-muted text-uppercase mb-1" style="font-size:.68rem;letter-spacing:.08em;font-weight:700">Pagamento</p>
                            <p class="mb-0" style="font-size:.9rem">{{ $sale->tipo_pagamento_label }}</p>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <p class="text-muted text-uppercase mb-1" style="font-size:.68rem;letter-spacing:.08em;font-weight:700">Vendedor</p>
                            <p class="mb-0" style="font-size:.9rem">{{ $sale->user?->name ?? '—' }}</p>
                        </div>
                    </div>

                    @if(in_array($sale->tipo_pagamento, ['financiado', 'misto']))
                    <div class="row" style="background:#f8f9fa;border-radius:6px;padding:10px 0;margin:0">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <p class="text-muted text-uppercase mb-1" style="font-size:.68rem;letter-spacing:.08em;font-weight:700">Financiadora</p>
                            <p class="mb-0" style="font-size:.9rem">{{ $sale->financiadora ?? '—' }}</p>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <p class="text-muted text-uppercase mb-1" style="font-size:.68rem;letter-spacing:.08em;font-weight:700">Entrada</p>
                            <p class="mb-0" style="font-size:.9rem">{{ $sale->entrada ? 'R$ ' . number_format($sale->entrada, 0, ',', '.') : '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted text-uppercase mb-1" style="font-size:.68rem;letter-spacing:.08em;font-weight:700">Parcelas</p>
                            <p class="mb-0" style="font-size:.9rem">{{ $sale->parcelas ? $sale->parcelas . 'x' : '—' }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Veículo de Troca --}}
                    @if($sale->trocaVehicle)
                    <hr class="my-3">
                    <p class="text-muted text-uppercase mb-2" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">
                        <i class="fas fa-exchange-alt mr-1"></i>Veículo de Troca
                    </p>
                    <div class="d-flex align-items-center p-3" style="background:#fafafa;border:1px solid #e9ecef;border-radius:6px">
                        @if($sale->trocaVehicle->principalPhoto)
                            <img src="{{ $sale->trocaVehicle->principalPhoto->url }}" width="72" height="54"
                                 style="object-fit:cover;border-radius:6px;margin-right:12px;flex-shrink:0;border:1px solid #dee2e6">
                        @else
                            <div style="width:72px;height:54px;background:#e9ecef;border-radius:6px;margin-right:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center">
                                <i class="fas fa-car text-muted"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <span class="font-weight-bold" style="font-size:.95rem">{{ $sale->trocaVehicle->titulo }}</span>
                            <span class="text-muted ml-1" style="font-size:.85rem">{{ $sale->trocaVehicle->ano_fabricacao }}/{{ $sale->trocaVehicle->ano_modelo }}</span><br>
                            <span class="text-muted" style="font-size:.82rem">
                                {{ $sale->trocaVehicle->km_formatado }} · {{ ucfirst($sale->trocaVehicle->combustivel) }} · {{ $sale->trocaVehicle->cor }}
                            </span>
                        </div>
                        <div class="text-right ml-3" style="flex-shrink:0">
                            @if($sale->valor_troca)
                                <div class="font-weight-bold" style="font-size:1rem">R$ {{ number_format($sale->valor_troca, 0, ',', '.') }}</div>
                                <small class="text-muted d-block mb-2">valor avaliado</small>
                            @endif
                            <a href="{{ route('admin.vehicles.show', $sale->trocaVehicle) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-eye mr-1"></i>Ver no estoque
                            </a>
                        </div>
                    </div>
                    @endif

                    {{-- Observações --}}
                    @if($sale->observacoes)
                    <hr class="my-3">
                    <p class="text-muted text-uppercase mb-1" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Observações</p>
                    <p class="mb-0 text-muted" style="font-size:.9rem">{{ $sale->observacoes }}</p>
                    @endif

                </div>
            </div>
        </div>

        {{-- ── Sidebar ─────────────────────────────────────────── --}}
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <h3 class="card-title text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Alterar Status</h3>
                </div>
                <div class="card-body pt-2">
                    <form action="{{ route('admin.sales.status', $sale) }}" method="POST"
                          data-confirm="Confirmar alteração de status da venda?">
                        @csrf @method('PATCH')
                        <select name="status" class="form-control mb-2" style="font-size:.9rem">
                            <option value="pendente"  {{ $sale->status === 'pendente'  ? 'selected' : '' }}>Pendente</option>
                            <option value="concluida" {{ $sale->status === 'concluida' ? 'selected' : '' }}>Concluída</option>
                            <option value="cancelada" {{ $sale->status === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                        <button type="submit" class="btn btn-outline-secondary btn-block" style="font-size:.88rem"
                                title="Salvar o novo status da venda">
                            Atualizar Status
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
