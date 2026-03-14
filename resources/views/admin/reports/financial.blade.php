@extends('adminlte::page')

@section('title', 'Relatório Financeiro — ' . config('adminlte.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-chart-line mr-2"></i>Relatório Financeiro</h1>
    </div>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Filtros --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="form-inline flex-wrap" style="gap:8px">
                <select name="ano" class="form-control form-control-sm">
                    @foreach($anos->isNotEmpty() ? $anos : [date('Y')] as $a)
                        <option value="{{ $a }}" {{ ($ano == $a) ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
                <select name="mes" class="form-control form-control-sm">
                    <option value="">Ano completo</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ ($mes == $m) ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->monthName }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Aplicar filtros">Filtrar</button>
                <a href="{{ route('admin.reports.financial.export', ['ano' => $ano, 'mes' => $mes]) }}"
                   class="btn btn-sm btn-success" title="Exportar este relatório em formato CSV">
                    <i class="fas fa-download mr-1"></i>Exportar CSV
                </a>
            </form>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="row mb-3">
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="info-box mb-0 shadow-sm bg-success">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="font-size:.8rem">Faturamento</span>
                    <span class="info-box-number" style="font-size:1.3rem;font-weight:700">
                        R$ {{ number_format($faturamento, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="info-box mb-0 shadow-sm bg-warning">
                <span class="info-box-icon"><i class="fas fa-car"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="font-size:.8rem">Custo dos Veículos</span>
                    <span class="info-box-number" style="font-size:1.3rem;font-weight:700">
                        R$ {{ number_format($custoVeiculos, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="info-box mb-0 shadow-sm bg-danger">
                <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="font-size:.8rem">Despesas Operacionais</span>
                    <span class="info-box-number" style="font-size:1.3rem;font-weight:700">
                        R$ {{ number_format($totalDespesas, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="info-box mb-0 shadow-sm {{ $lucro >= 0 ? 'bg-teal' : 'bg-danger' }}">
                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="font-size:.8rem">Lucro Líquido</span>
                    <span class="info-box-number" style="font-size:1.3rem;font-weight:700">
                        R$ {{ number_format($lucro, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if($resumoPorMes->isNotEmpty() && !$mes)
    {{-- Resumo mensal --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white" style="border-bottom:1px solid #f0f0f0">
            <h3 class="card-title text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">
                Resumo por Mês — {{ $ano }}
            </h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6c757d;border-top:0">
                        <th class="border-top-0 pl-3">Mês</th>
                        <th class="border-top-0">Qtd Vendas</th>
                        <th class="border-top-0">Faturamento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumoPorMes as $r)
                    <tr>
                        <td class="pl-3 align-middle" style="font-size:.88rem">
                            {{ \Carbon\Carbon::create()->month($r->mes)->locale('pt_BR')->monthName }}
                        </td>
                        <td class="align-middle text-muted" style="font-size:.88rem">{{ $r->qtd_vendas }}</td>
                        <td class="align-middle font-weight-bold" style="font-size:.9rem;color:#1a7a3c">
                            R$ {{ number_format($r->faturamento, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                    <tr style="background:#f8f9fa">
                        <td class="pl-3 align-middle font-weight-bold" style="font-size:.88rem">TOTAL</td>
                        <td class="align-middle font-weight-bold" style="font-size:.88rem">{{ $resumoPorMes->sum('qtd_vendas') }}</td>
                        <td class="align-middle font-weight-bold" style="font-size:.9rem;color:#1a7a3c">
                            R$ {{ number_format($resumoPorMes->sum('faturamento'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Vendas detalhadas --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white" style="border-bottom:1px solid #f0f0f0">
            <h3 class="card-title text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">
                Vendas Concluídas ({{ $vendas->count() }})
            </h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6c757d;border-top:0">
                        <th class="border-top-0 pl-3">Data</th>
                        <th class="border-top-0">Veículo</th>
                        <th class="border-top-0">Cliente</th>
                        <th class="border-top-0">Tipo Pgto.</th>
                        <th class="border-top-0">Preço Venda</th>
                        <th class="border-top-0">Custo</th>
                        <th class="border-top-0">Lucro</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendas as $venda)
                    <tr>
                        <td class="pl-3 align-middle text-muted" style="font-size:.82rem;white-space:nowrap">
                            {{ $venda->data_venda->format('d/m/Y') }}
                        </td>
                        <td class="align-middle" style="font-size:.88rem">
                            {{ $venda->vehicle?->titulo ?? '—' }}
                        </td>
                        <td class="align-middle" style="font-size:.88rem">
                            {{ $venda->customer?->nome ?? '—' }}
                        </td>
                        <td class="align-middle text-muted" style="font-size:.85rem">
                            {{ $venda->tipo_pagamento_label }}
                        </td>
                        <td class="align-middle font-weight-bold" style="font-size:.9rem;color:#1a7a3c;white-space:nowrap">
                            R$ {{ number_format($venda->preco_venda, 0, ',', '.') }}
                        </td>
                        <td class="align-middle text-muted" style="font-size:.85rem;white-space:nowrap">
                            R$ {{ number_format($venda->vehicle?->preco_compra ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="align-middle font-weight-bold" style="font-size:.88rem;white-space:nowrap">
                            @php $lucroVenda = $venda->preco_venda - ($venda->vehicle?->preco_compra ?? 0); @endphp
                            <span class="{{ $lucroVenda >= 0 ? 'text-success' : 'text-danger' }}">
                                R$ {{ number_format($lucroVenda, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-chart-bar fa-2x d-block mb-2" style="color:#dee2e6"></i>
                            Nenhuma venda no período.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
