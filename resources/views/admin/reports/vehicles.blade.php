@extends('adminlte::page')

@section('title', 'Relatório de Veículos — Soavel')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-list-alt mr-2"></i>Relatório de Veículos</h1>
    </div>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Filtros --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="form-inline flex-wrap" style="gap:8px">
                <select name="status" class="form-control form-control-sm">
                    <option value="todos"      {{ ($status === 'todos')      ? 'selected' : '' }}>Todos</option>
                    <option value="disponivel" {{ ($status === 'disponivel') ? 'selected' : '' }}>Disponíveis</option>
                    <option value="reservado"  {{ ($status === 'reservado')  ? 'selected' : '' }}>Reservados</option>
                    <option value="vendido"    {{ ($status === 'vendido')    ? 'selected' : '' }}>Vendidos</option>
                </select>
                <select name="categoria" class="form-control form-control-sm">
                    <option value="">Todas categorias</option>
                    @foreach(['hatch','sedan','suv','pickup','van','esportivo','outro'] as $cat)
                        <option value="{{ $cat }}" {{ ($categoria === $cat) ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Aplicar filtros">Filtrar</button>
            </form>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="row mb-3">
        @foreach(['disponivel' => ['success','Disponíveis'], 'reservado' => ['warning','Reservados'], 'vendido' => ['danger','Vendidos'], 'total' => ['info','Total']] as $key => [$color, $label])
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="info-box mb-0 shadow-sm bg-{{ $color }}">
                <span class="info-box-icon"><i class="fas fa-car"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="font-size:.8rem">{{ $label }}</span>
                    <span class="info-box-number" style="font-size:1.4rem;font-weight:700">{{ $resumo[$key] }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Tabela --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6c757d;border-top:0">
                        <th class="border-top-0 pl-3" style="width:70px">Foto</th>
                        <th class="border-top-0">Veículo</th>
                        <th class="border-top-0">Ano</th>
                        <th class="border-top-0">KM</th>
                        <th class="border-top-0">Categoria</th>
                        <th class="border-top-0">Preço Compra</th>
                        <th class="border-top-0">Preço Venda</th>
                        <th class="border-top-0">Margem</th>
                        <th class="border-top-0">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $vehicle)
                    <tr>
                        <td class="pl-3 align-middle" style="padding-top:8px;padding-bottom:8px">
                            @if($vehicle->principalPhoto)
                                <img src="{{ $vehicle->principalPhoto->url }}" width="54" height="40"
                                     style="object-fit:cover;border-radius:4px;border:1px solid #e9ecef;display:block">
                            @else
                                <div style="width:54px;height:40px;background:#f0f0f0;border-radius:4px;display:flex;align-items:center;justify-content:center;border:1px solid #e9ecef">
                                    <i class="fas fa-car" style="color:#ced4da;font-size:.85rem"></i>
                                </div>
                            @endif
                        </td>
                        <td class="align-middle" style="font-size:.88rem">
                            <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="font-weight-600 text-dark"
                               title="Ver detalhes do veículo">
                                {{ $vehicle->titulo }}
                            </a>
                        </td>
                        <td class="align-middle text-muted" style="font-size:.85rem;white-space:nowrap">{{ $vehicle->ano_modelo }}</td>
                        <td class="align-middle text-muted" style="font-size:.85rem;white-space:nowrap">{{ $vehicle->km_formatado }}</td>
                        <td class="align-middle text-muted" style="font-size:.85rem">{{ ucfirst($vehicle->categoria) }}</td>
                        <td class="align-middle text-muted" style="font-size:.85rem;white-space:nowrap">
                            {{ $vehicle->preco_compra ? 'R$ '.number_format($vehicle->preco_compra,0,',','.') : '—' }}
                        </td>
                        <td class="align-middle font-weight-bold" style="font-size:.88rem;color:#1a7a3c;white-space:nowrap">
                            {{ $vehicle->preco_formatado }}
                        </td>
                        <td class="align-middle font-weight-bold" style="font-size:.88rem;white-space:nowrap">
                            @if($vehicle->preco_compra)
                                @php $margem = $vehicle->preco - $vehicle->preco_compra; @endphp
                                <span class="{{ $margem >= 0 ? 'text-success' : 'text-danger' }}">
                                    R$ {{ number_format($margem, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="align-middle">
                            <span class="badge badge-{{ $vehicle->status_color }}" style="font-size:.72rem;padding:3px 8px">
                                {{ $vehicle->status_label }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="fas fa-car fa-2x d-block mb-2" style="color:#dee2e6"></i>
                            Nenhum veículo encontrado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
