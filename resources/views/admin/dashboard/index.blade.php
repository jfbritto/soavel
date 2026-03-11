@extends('adminlte::page')

@section('title', 'Dashboard — Soavel Veículos')

@section('content_header')
    <h1 class="m-0">Dashboard</h1>
@endsection

@section('content')
<div class="container-fluid">

    {{-- KPIs principais --}}
    <div class="row mb-3">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm h-100" style="border-left:3px solid #3c8dbc">
                <div class="card-body py-3">
                    <p class="text-muted text-uppercase mb-1" style="font-size:.68rem;letter-spacing:.08em;font-weight:700">Veículos Disponíveis</p>
                    <p class="font-weight-bold mb-1" style="font-size:1.8rem;line-height:1">{{ $stats['total_disponiveis'] }}</p>
                    <a href="{{ route('admin.vehicles.index', ['status' => 'disponivel']) }}"
                       class="text-muted" style="font-size:.78rem">Ver estoque →</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm h-100" style="border-left:3px solid #28a745">
                <div class="card-body py-3">
                    <p class="text-muted text-uppercase mb-1" style="font-size:.68rem;letter-spacing:.08em;font-weight:700">Vendas este Mês</p>
                    <p class="font-weight-bold mb-1" style="font-size:1.8rem;line-height:1">{{ $stats['total_vendidos_mes'] }}</p>
                    <a href="{{ route('admin.sales.index') }}"
                       class="text-muted" style="font-size:.78rem">Ver vendas →</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm h-100" style="border-left:3px solid #6c757d">
                <div class="card-body py-3">
                    <p class="text-muted text-uppercase mb-1" style="font-size:.68rem;letter-spacing:.08em;font-weight:700">Leads Novos</p>
                    <p class="font-weight-bold mb-1" style="font-size:1.8rem;line-height:1">{{ $stats['leads_novos'] }}</p>
                    <a href="{{ route('admin.leads.index', ['status' => 'novo']) }}"
                       class="text-muted" style="font-size:.78rem">Ver leads →</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm h-100" style="border-left:3px solid #1a7a3c">
                <div class="card-body py-3">
                    <p class="text-muted text-uppercase mb-1" style="font-size:.68rem;letter-spacing:.08em;font-weight:700">Faturamento do Mês</p>
                    <p class="font-weight-bold mb-1" style="font-size:1.4rem;line-height:1;color:#1a7a3c">
                        R$ {{ number_format($stats['faturamento_mes'], 0, ',', '.') }}
                    </p>
                    <a href="{{ route('admin.reports.financial') }}"
                       class="text-muted" style="font-size:.78rem">Ver relatório →</a>
                </div>
            </div>
        </div>
    </div>

    {{-- KPIs secundários --}}
    <div class="row mb-3">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center py-3" style="gap:14px">
                    <div style="width:38px;height:38px;border-radius:8px;background:#f0f4ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-chart-line" style="color:#3c8dbc;font-size:1rem"></i>
                    </div>
                    <div>
                        <p class="text-muted text-uppercase mb-0" style="font-size:.68rem;letter-spacing:.06em;font-weight:700">Lucro Estimado (Mês)</p>
                        <p class="font-weight-bold mb-0" style="font-size:1rem">R$ {{ number_format($stats['lucro_mes'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center py-3" style="gap:14px">
                    <div style="width:38px;height:38px;border-radius:8px;background:#fff5f5;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-file-invoice-dollar" style="color:#dc3545;font-size:1rem"></i>
                    </div>
                    <div>
                        <p class="text-muted text-uppercase mb-0" style="font-size:.68rem;letter-spacing:.06em;font-weight:700">Despesas do Mês</p>
                        <p class="font-weight-bold mb-0 text-danger" style="font-size:1rem">R$ {{ number_format($stats['despesas_mes'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center py-3" style="gap:14px">
                    <div style="width:38px;height:38px;border-radius:8px;background:#f5f0ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-clock" style="color:#6f42c1;font-size:1rem"></i>
                    </div>
                    <div>
                        <p class="text-muted text-uppercase mb-0" style="font-size:.68rem;letter-spacing:.06em;font-weight:700">Leads em Contato</p>
                        <p class="font-weight-bold mb-0" style="font-size:1rem">{{ $stats['leads_em_contato'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Estoque: valores e divisão loja/sócios --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <h3 class="card-title text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">
                        <i class="fas fa-warehouse mr-1"></i>Estoque em Carteira — {{ $estoque['qtd'] }} veículo{{ $estoque['qtd'] != 1 ? 's' : '' }}
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div class="row">

                        {{-- Coluna Total --}}
                        <div class="col-md-4 mb-3 mb-md-0">
                            <p class="text-muted text-uppercase mb-2" style="font-size:.68rem;letter-spacing:.08em;font-weight:700">Total em Estoque</p>
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <span class="text-muted" style="font-size:.82rem">Preço de venda</span>
                                <span class="font-weight-bold text-success" style="font-size:.95rem">R$ {{ number_format($estoque['total_venda'], 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline">
                                <span class="text-muted" style="font-size:.82rem">Custo de compra</span>
                                <span class="font-weight-bold" style="font-size:.95rem">R$ {{ number_format($estoque['total_custo'], 0, ',', '.') }}</span>
                            </div>
                            @if($estoque['total_custo'] > 0)
                            <div class="d-flex justify-content-between align-items-baseline mt-1 pt-1 border-top">
                                <span class="text-muted" style="font-size:.78rem">Margem potencial</span>
                                @php $margem = $estoque['total_venda'] - $estoque['total_custo']; @endphp
                                <span class="font-weight-bold {{ $margem >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:.88rem">
                                    R$ {{ number_format($margem, 0, ',', '.') }}
                                </span>
                            </div>
                            @endif
                        </div>

                        <div class="col-md-1 d-none d-md-flex align-items-center justify-content-center">
                            <div style="width:1px;height:60px;background:#dee2e6"></div>
                        </div>

                        {{-- Coluna Loja --}}
                        <div class="col-md-3 mb-3 mb-md-0">
                            <p class="text-muted text-uppercase mb-2" style="font-size:.68rem;letter-spacing:.08em;font-weight:700">
                                <i class="fas fa-store mr-1"></i>Parte da Loja
                            </p>
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <span class="text-muted" style="font-size:.82rem">Venda</span>
                                <span class="font-weight-bold text-success" style="font-size:.95rem">R$ {{ number_format($estoque['loja_venda'], 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline">
                                <span class="text-muted" style="font-size:.82rem">Custo</span>
                                <span class="font-weight-bold" style="font-size:.95rem">R$ {{ number_format($estoque['loja_custo'], 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="col-md-1 d-none d-md-flex align-items-center justify-content-center">
                            <div style="width:1px;height:60px;background:#dee2e6"></div>
                        </div>

                        {{-- Coluna Sócios --}}
                        <div class="col-md-3">
                            <p class="text-muted text-uppercase mb-2" style="font-size:.68rem;letter-spacing:.08em;font-weight:700">
                                <i class="fas fa-user-tie mr-1"></i>Parte dos Sócios
                            </p>
                            @if($estoque['socios_venda'] > 0)
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <span class="text-muted" style="font-size:.82rem">Venda</span>
                                <span class="font-weight-bold text-primary" style="font-size:.95rem">R$ {{ number_format($estoque['socios_venda'], 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline">
                                <span class="text-muted" style="font-size:.82rem">Custo</span>
                                <span class="font-weight-bold" style="font-size:.95rem">R$ {{ number_format($estoque['socios_custo'], 0, ',', '.') }}</span>
                            </div>
                            @else
                            <p class="text-muted mb-0" style="font-size:.85rem">Nenhum veículo com sócios.</p>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="row mb-3">
        <div class="col-md-8 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <h3 class="card-title text-muted text-uppercase" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">
                        Vendas — últimos 6 meses
                    </h3>
                </div>
                <div class="card-body pt-2">
                    <canvas id="chartVendas" height="110"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <h3 class="card-title text-muted text-uppercase" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">
                        Estoque por Categoria
                    </h3>
                </div>
                <div class="card-body pt-2">
                    <canvas id="chartCategoria" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabelas recentes --}}
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <h3 class="text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Últimas Vendas</h3>
                        <a href="{{ route('admin.sales.index') }}" class="text-muted" style="font-size:.78rem">Ver todas →</a>
                    </div>
                </div>
                <div class="card-body p-0 pt-1">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:#adb5bd">
                                <th class="border-top-0 pl-3">Data</th>
                                <th class="border-top-0">Veículo</th>
                                <th class="border-top-0">Cliente</th>
                                <th class="border-top-0 text-right pr-3">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendasRecentes as $venda)
                            <tr>
                                <td class="pl-3 text-muted" style="font-size:.8rem">{{ $venda->data_venda->format('d/m/Y') }}</td>
                                <td style="font-size:.85rem">
                                    <a href="{{ route('admin.sales.show', $venda) }}" class="text-dark">
                                        {{ $venda->vehicle?->titulo }}
                                    </a>
                                </td>
                                <td class="text-muted" style="font-size:.85rem">{{ $venda->customer?->nome }}</td>
                                <td class="text-right pr-3 font-weight-600" style="font-size:.85rem;color:#1a7a3c;white-space:nowrap">
                                    {{ $venda->preco_venda_formatado }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3" style="font-size:.85rem">Nenhuma venda registrada</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <h3 class="text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Leads Novos</h3>
                        <a href="{{ route('admin.leads.index', ['status' => 'novo']) }}" class="text-muted" style="font-size:.78rem">Ver todos →</a>
                    </div>
                </div>
                <div class="card-body p-0 pt-1">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:#adb5bd">
                                <th class="border-top-0 pl-3">Nome</th>
                                <th class="border-top-0">Telefone</th>
                                <th class="border-top-0">Veículo</th>
                                <th class="border-top-0 pr-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leadsRecentes as $lead)
                            <tr>
                                <td class="pl-3" style="font-size:.85rem">{{ $lead->nome }}</td>
                                <td style="font-size:.85rem;white-space:nowrap">
                                    <a href="https://wa.me/55{{ preg_replace('/\D/', '', $lead->telefone) }}"
                                       target="_blank" class="text-success">
                                        <i class="fab fa-whatsapp mr-1"></i>{{ $lead->telefone }}
                                    </a>
                                </td>
                                <td class="text-muted" style="font-size:.82rem">{{ $lead->vehicle?->titulo ?? '—' }}</td>
                                <td class="text-right pr-3">
                                    <a href="{{ route('admin.leads.edit', $lead) }}"
                                       class="btn btn-sm btn-outline-secondary" style="font-size:.7rem;padding:1px 7px">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3" style="font-size:.85rem">Nenhum lead novo</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('js')
<script>
const meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
const vendasData    = @json($vendasPorMes);
const categoriaData = @json($veiculosPorCategoria);

new Chart(document.getElementById('chartVendas'), {
    type: 'bar',
    data: {
        labels: vendasData.map(v => meses[v.mes - 1]),
        datasets: [{
            label: 'Vendas',
            data: vendasData.map(v => v.total),
            backgroundColor: 'rgba(60,141,188,0.15)',
            borderColor: 'rgba(60,141,188,0.7)',
            borderWidth: 1.5
        }, {
            label: 'Faturamento (R$)',
            data: vendasData.map(v => v.faturamento),
            backgroundColor: 'transparent',
            borderColor: 'rgba(26,122,60,0.7)',
            borderWidth: 2,
            type: 'line',
            yAxisID: 'y2',
            tension: 0.3,
            pointRadius: 3
        }]
    },
    options: {
        plugins: { legend: { labels: { font: { size: 11 }, color: '#6c757d' } } },
        scales: {
            y:  { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { color: '#adb5bd', font: { size: 10 } } },
            y2: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { color: '#adb5bd', font: { size: 10 } } }
        }
    }
});

new Chart(document.getElementById('chartCategoria'), {
    type: 'doughnut',
    data: {
        labels: categoriaData.map(c => c.categoria.charAt(0).toUpperCase() + c.categoria.slice(1)),
        datasets: [{
            data: categoriaData.map(c => c.total),
            backgroundColor: ['#3c8dbc','#00a65a','#f39c12','#dd4b39','#605ca8','#d2d6de','#39cccc'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, color: '#6c757d', padding: 10 } } }
    }
});
</script>
@endsection
