@extends('adminlte::page')

@section('title', 'Veículos — Soavel')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-car mr-2"></i>Veículos</h1>
        <a href="{{ route('admin.vehicles.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i>Novo Veículo
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Filtros --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="form-inline flex-wrap" style="gap:8px">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control"
                        placeholder="Buscar marca, modelo, placa..."
                        value="{{ request('search') }}" style="min-width:200px">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <select name="status" class="form-control form-control-sm">
                    <option value="">Todos os status</option>
                    <option value="disponivel" {{ (request('status') === 'disponivel') ? 'selected' : '' }}>Disponível</option>
                    <option value="reservado"  {{ (request('status') === 'reservado')  ? 'selected' : '' }}>Reservado</option>
                    <option value="vendido"    {{ (request('status') === 'vendido')    ? 'selected' : '' }}>Vendido</option>
                </select>
                <select name="marca" class="form-control form-control-sm">
                    <option value="">Todas as marcas</option>
                    @foreach($marcas as $m)
                        <option value="{{ $m }}" {{ (request('marca') === $m) ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
                <select name="categoria" class="form-control form-control-sm">
                    <option value="">Todas as categorias</option>
                    @foreach(['hatch','sedan','suv','pickup','van','esportivo','outro'] as $cat)
                        <option value="{{ $cat }}" {{ (request('categoria') === $cat) ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary">Filtrar</button>
                <a href="{{ route('admin.vehicles.index') }}" class="btn btn-sm btn-link text-muted">Limpar</a>
            </form>
        </div>
    </div>

    {{-- Lista --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6c757d;border-top:0">
                        <th class="border-top-0 pl-3" style="width:76px">Foto</th>
                        <th class="border-top-0">Veículo</th>
                        <th class="border-top-0 d-none d-md-table-cell">Ano</th>
                        <th class="border-top-0 d-none d-md-table-cell">KM</th>
                        <th class="border-top-0">Preço</th>
                        <th class="border-top-0">Status</th>
                        <th class="border-top-0" style="width:100px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $vehicle)
                    <tr class="vehicle-row" style="cursor:pointer"
                        onclick="window.location='{{ route('admin.vehicles.show', $vehicle) }}'">
                        <td class="pl-3 align-middle" style="padding-top:10px;padding-bottom:10px">
                            @if($vehicle->principalPhoto)
                                <img src="{{ $vehicle->principalPhoto->url }}" width="64" height="48"
                                     style="object-fit:cover;border-radius:5px;display:block;border:1px solid #e9ecef">
                            @else
                                <div style="width:64px;height:48px;background:#f0f0f0;border-radius:5px;display:flex;align-items:center;justify-content:center;border:1px solid #e9ecef">
                                    <i class="fas fa-car" style="color:#ced4da;font-size:.9rem"></i>
                                </div>
                            @endif
                        </td>
                        <td class="align-middle">
                            <div style="font-size:.9rem;font-weight:600;color:#212529">
                                {{ $vehicle->titulo }}
                                @if($vehicle->destaque)
                                    <span class="badge badge-light border ml-1" style="font-size:.7rem;font-weight:500">Destaque</span>
                                @endif
                            </div>
                            <div class="text-muted" style="font-size:.78rem">{{ $vehicle->cor }} · {{ ucfirst($vehicle->categoria) }}</div>
                        </td>
                        <td class="align-middle text-muted d-none d-md-table-cell" style="font-size:.88rem;white-space:nowrap">
                            {{ $vehicle->ano_fabricacao }}/{{ $vehicle->ano_modelo }}
                        </td>
                        <td class="align-middle text-muted d-none d-md-table-cell" style="font-size:.88rem;white-space:nowrap">
                            {{ $vehicle->km_formatado }}
                        </td>
                        <td class="align-middle" style="white-space:nowrap">
                            <span style="font-size:.92rem;font-weight:600;color:#1a7a3c">{{ $vehicle->preco_formatado }}</span>
                        </td>
                        <td class="align-middle">
                            <span class="badge badge-{{ $vehicle->status_color }}" style="font-size:.75rem;padding:3px 8px">
                                {{ $vehicle->status_label }}
                            </span>
                        </td>
                        <td class="align-middle text-right pr-3" style="white-space:nowrap" onclick="event.stopPropagation()">
                            <button class="btn btn-sm btn-link p-0 btn-destaque"
                                    data-vehicle-id="{{ $vehicle->id }}"
                                    data-destaque="{{ $vehicle->destaque ? '1' : '0' }}"
                                    title="{{ $vehicle->destaque ? 'Remover destaque' : 'Marcar como destaque' }}"
                                    style="font-size:1rem;line-height:1;vertical-align:middle">
                                <i class="fa{{ $vehicle->destaque ? 's' : 'r' }} fa-star text-{{ $vehicle->destaque ? 'warning' : 'muted' }}"></i>
                            </button>
                            <a href="{{ route('admin.vehicles.show', $vehicle) }}"
                               class="btn btn-sm btn-outline-secondary ml-1" style="font-size:.78rem;padding:2px 10px;vertical-align:middle"
                               title="Ver detalhes do veículo">
                                Abrir
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-car fa-2x mb-2 d-block" style="color:#dee2e6"></i>
                            Nenhum veículo encontrado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vehicles->hasPages())
        <div class="card-footer border-top-0" style="background:#fafafa">
            {{ $vehicles->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@section('css')
<style>
    .vehicle-row:hover { background-color: #f8f9fa !important; }
    .btn-destaque:hover i.fa-star { transform: scale(1.2); }
    .btn-destaque i { transition: all .15s ease; }
</style>
@endsection

@section('js')
<script>
document.querySelectorAll('.btn-destaque').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var id = this.getAttribute('data-vehicle-id');
        var icon = this.querySelector('i');
        var badge = this.closest('tr').querySelector('.badge-light');

        fetch('/admin/vehicles/' + id + '/destaque', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.destaque) {
                icon.className = 'fas fa-star text-warning';
                btn.setAttribute('data-destaque', '1');
                btn.title = 'Remover destaque';
                if (!badge) {
                    var nameEl = btn.closest('tr').querySelector('td:nth-child(2) div:first-child');
                    nameEl.insertAdjacentHTML('beforeend', ' <span class="badge badge-light border ml-1" style="font-size:.7rem;font-weight:500">Destaque</span>');
                }
            } else {
                icon.className = 'far fa-star text-muted';
                btn.setAttribute('data-destaque', '0');
                btn.title = 'Marcar como destaque';
                if (badge) badge.remove();
            }
        });
    });
});
</script>
@endsection
