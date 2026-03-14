@extends('layouts.site')

@php
    $nomeLoja = \App\Models\Setting::get('nome_sistema', config('app.name'));
    $cidadeLoja = \App\Models\Setting::get('cidade_estado', '');
    $seoMarca = request('marca');
    $seoCategoria = request('categoria');
    $seoTitle = $seoMarca
        ? $seoMarca . ' Seminovos | ' . $nomeLoja
        : ($seoCategoria
            ? ucfirst($seoCategoria) . ' Seminovos | ' . $nomeLoja
            : 'Estoque de Seminovos | ' . $nomeLoja);
    $seoDesc = $seoMarca
        ? 'Encontre ' . $seoMarca . ' seminovos com ótimos preços' . ($cidadeLoja ? ' em ' . $cidadeLoja : '') . '. Financiamento facilitado.'
        : 'Confira nosso estoque de carros seminovos' . ($cidadeLoja ? ' em ' . $cidadeLoja : '') . '. Filtros por marca, modelo, preço e mais.';
@endphp
@section('meta_title', $seoTitle)
@section('meta_description', $seoDesc)

@section('seo_pagination')
@if($vehicles->currentPage() > 1)
    <link rel="prev" href="{{ $vehicles->previousPageUrl() }}">
@endif
@if($vehicles->hasMorePages())
    <link rel="next" href="{{ $vehicles->nextPageUrl() }}">
@endif
@if($vehicles->currentPage() > 1)
    @section('canonical', $vehicles->url(1))
@endif
@endsection

@section('schema')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Início", "item": "{{ route('site.home') }}" },
        { "@type": "ListItem", "position": 2, "name": "Estoque" }
    ]
}
</script>
@endsection

@section('content')

{{-- Breadcrumb --}}
<nav class="breadcrumb-site">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('site.home') }}">Início</a></li>
            <li class="breadcrumb-item active">Estoque</li>
        </ol>
    </div>
</nav>

<div class="container py-4">

    {{-- Mobile filter toggle --}}
    <button type="button" class="btn-filter-mobile" id="btnFilterMobile">
        <i class="fas fa-sliders-h"></i> Filtros
    </button>

    <div class="row">

        {{-- ── FILTER SIDEBAR ── --}}
        <div class="col-lg-3 mb-4" id="filterSidebarWrap">
            <div class="filter-sidebar" id="filterSidebar">
                <div class="filter-sidebar-title"><i class="fas fa-sliders-h mr-2"></i>Filtros</div>

                <form method="GET" action="{{ route('site.vehicles.index') }}" id="filterForm">

                    <div class="filter-group">
                        <label>Marca</label>
                        <select name="marca" class="form-control">
                            <option value="">Todas as marcas</option>
                            @foreach($marcas as $marca)
                                <option value="{{ $marca }}" {{ request('marca') === $marca ? 'selected' : '' }}>{{ $marca }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Categoria</label>
                        <select name="categoria" class="form-control">
                            <option value="">Todas</option>
                            @foreach(['hatch','sedan','suv','pickup','van','esportivo','outro'] as $cat)
                                <option value="{{ $cat }}" {{ request('categoria') === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Ano Mínimo</label>
                        <input type="number" name="ano_min" class="form-control"
                               value="{{ request('ano_min') }}"
                               min="1990" max="{{ date('Y') + 1 }}"
                               placeholder="Ex: 2018">
                    </div>

                    <div class="filter-group">
                        <label>Preço Máx.</label>
                        <input type="number" name="preco_max" class="form-control"
                               value="{{ request('preco_max') }}"
                               placeholder="Preço máx.">
                    </div>

                    <div class="filter-group">
                        <label>KM Máximo</label>
                        <input type="number" name="km_max" class="form-control"
                               value="{{ request('km_max') }}"
                               placeholder="Ex: 80000">
                    </div>

                    <div class="filter-group">
                        <label>Combustível</label>
                        <select name="combustivel" class="form-control">
                            <option value="">Todos</option>
                            @foreach(['flex','gasolina','etanol','diesel','gnv','hibrido','eletrico'] as $c)
                                <option value="{{ $c }}" {{ request('combustivel') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Transmissão</label>
                        <select name="transmissao" class="form-control">
                            <option value="">Todas</option>
                            <option value="manual"       {{ request('transmissao') === 'manual' ? 'selected' : '' }}>Manual</option>
                            <option value="automatico"   {{ request('transmissao') === 'automatico' ? 'selected' : '' }}>Automático</option>
                            <option value="automatizado" {{ request('transmissao') === 'automatizado' ? 'selected' : '' }}>Automatizado</option>
                            <option value="cvt"          {{ request('transmissao') === 'cvt' ? 'selected' : '' }}>CVT</option>
                        </select>
                    </div>

                    {{-- Preserve sort param if set --}}
                    @if(request('ordenar'))
                        <input type="hidden" name="ordenar" value="{{ request('ordenar') }}">
                    @endif

                    <button type="submit" class="btn-filter-apply">
                        <i class="fas fa-search mr-1"></i>Aplicar Filtros
                    </button>
                    <a href="{{ route('site.vehicles.index') }}" class="btn-filter-clear">Limpar filtros</a>
                </form>
            </div>
        </div>

        {{-- ── MAIN CONTENT ── --}}
        <div class="col-lg-9">

            <h1 class="h4 font-weight-bold mb-3" style="color:var(--text-1)">
                @if(request('marca'))
                    {{ request('marca') }} Seminovos
                @elseif(request('categoria'))
                    {{ ucfirst(request('categoria')) }} Seminovos
                @else
                    Carros Seminovos{{ $cidadeLoja ? ' em ' . $cidadeLoja : '' }}
                @endif
            </h1>

            {{-- Sort bar --}}
            <div class="sort-bar">
                <div class="sort-bar-count">
                    @if($vehicles->total() > 0)
                        Exibindo <strong>{{ $vehicles->firstItem() }}–{{ $vehicles->lastItem() }}</strong> de <strong>{{ $vehicles->total() }}</strong> veículo{{ $vehicles->total() != 1 ? 's' : '' }}
                    @else
                        <strong>0</strong> veículos encontrados
                    @endif
                </div>
                <div class="d-flex align-items-center" style="gap:8px">
                    <span style="font-size:.8rem;color:var(--text-3);white-space:nowrap">Ordenar por:</span>
                    <select onchange="window.location.href=this.value">
                        @php
                            $baseParams = request()->except(['ordenar', 'page']);
                        @endphp
                        <option value="{{ route('site.vehicles.index', array_merge($baseParams, ['ordenar' => 'recente'])) }}"
                            {{ request('ordenar', 'recente') === 'recente' ? 'selected' : '' }}>Mais recentes</option>
                        <option value="{{ route('site.vehicles.index', array_merge($baseParams, ['ordenar' => 'preco_asc'])) }}"
                            {{ request('ordenar') === 'preco_asc' ? 'selected' : '' }}>Menor preço</option>
                        <option value="{{ route('site.vehicles.index', array_merge($baseParams, ['ordenar' => 'preco_desc'])) }}"
                            {{ request('ordenar') === 'preco_desc' ? 'selected' : '' }}>Maior preço</option>
                        <option value="{{ route('site.vehicles.index', array_merge($baseParams, ['ordenar' => 'ano_desc'])) }}"
                            {{ request('ordenar') === 'ano_desc' ? 'selected' : '' }}>Mais novo</option>
                        <option value="{{ route('site.vehicles.index', array_merge($baseParams, ['ordenar' => 'km_asc'])) }}"
                            {{ request('ordenar') === 'km_asc' ? 'selected' : '' }}>Menor KM</option>
                    </select>
                </div>
            </div>

            {{-- Vehicle Grid --}}
            <div class="row">
                @forelse($vehicles as $vehicle)
                    @include('site.partials._vehicle_card', ['vehicle' => $vehicle])
                @empty
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <h4>Nenhum veículo encontrado</h4>
                            <p>Tente ajustar ou limpar os filtros para ver mais resultados.</p>
                            <a href="{{ route('site.vehicles.index') }}" class="btn-ver-detalhes" style="display:inline-block;max-width:220px;margin-top:12px">
                                Ver todo o estoque
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($vehicles->hasPages())
                <div class="d-flex justify-content-center mt-4 mb-2">
                    {{ $vehicles->links() }}
                </div>
            @endif

        </div>
    </div>
</div>

@endsection

@section('extra_js')
<script>
// Mobile filter toggle
document.getElementById('btnFilterMobile').addEventListener('click', function () {
    var sidebar = document.getElementById('filterSidebar');
    sidebar.classList.toggle('filter-open');
    this.innerHTML = sidebar.classList.contains('filter-open')
        ? '<i class="fas fa-times"></i> Fechar Filtros'
        : '<i class="fas fa-sliders-h"></i> Filtros';
});
</script>
@endsection
