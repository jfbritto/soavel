@extends('adminlte::page')

@section('title', $vehicle->titulo . ' — Soavel')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
#cropWrap { background:#1a1a1a; height:520px; position:relative; }
#cropWrap img { display:block; max-width:100%; }
.sortable-ghost { opacity:.35; }
.drag-handle:active { cursor:grabbing; }
</style>
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center" style="gap:10px">
            <h1 class="mb-0">{{ $vehicle->titulo }}</h1>
            <span class="badge badge-{{ $vehicle->status_color }} px-2 py-1" style="font-size:.8rem">{{ $vehicle->status_label }}</span>
            @if($vehicle->destaque)
                <span class="badge badge-warning px-2 py-1" style="font-size:.8rem"><i class="fas fa-star mr-1"></i>Destaque</span>
            @endif
        </div>
        <div>
            <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="btn btn-sm btn-outline-secondary"
               title="Editar dados do veículo">
                <i class="fas fa-edit mr-1"></i>Editar
            </a>
            <a href="{{ route('admin.vehicles.index') }}" class="btn btn-sm btn-outline-secondary ml-1"
               title="Voltar para a lista de veículos">
                <i class="fas fa-arrow-left mr-1"></i>Voltar
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">

    @if($vehicle->vendaOrigem)
    <div class="alert alert-warning alert-dismissible d-flex align-items-center py-2 mb-3" style="border-left:4px solid #ffc107;border-radius:4px">
        <i class="fas fa-exchange-alt mr-2 text-warning"></i>
        <span>
            Veículo entrou como troca na
            <a href="{{ route('admin.sales.show', $vehicle->vendaOrigem) }}"><strong>Venda #{{ $vehicle->vendaOrigem->id }}</strong></a>
            ({{ $vehicle->vendaOrigem->data_venda->format('d/m/Y') }})
            @if($vehicle->vendaOrigem->customer) — Cliente: <strong>{{ $vehicle->vendaOrigem->customer->nome }}</strong>@endif
            @if($vehicle->vendaOrigem->valor_troca) · Valor avaliado: <strong>R$ {{ number_format($vehicle->vendaOrigem->valor_troca, 0, ',', '.') }}</strong>@endif
        </span>
        <button type="button" class="close ml-auto" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    <div class="row">

        {{-- ── Coluna principal ────────────────────────────────── --}}
        <div class="col-md-8">

            {{-- Especificações --}}
            <div class="card shadow-sm">
                <div class="card-header border-bottom-0 pb-0 bg-white">
                    <h3 class="card-title text-muted text-uppercase" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Especificações</h3>
                </div>
                <div class="card-body pt-2">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row mb-0" style="row-gap:4px">
                                <dt class="col-5 text-muted font-weight-normal" style="font-size:.85rem">Marca/Modelo</dt>
                                <dd class="col-7 mb-0 font-weight-600">{{ $vehicle->titulo }}</dd>

                                <dt class="col-5 text-muted font-weight-normal" style="font-size:.85rem">Ano Fab/Mod</dt>
                                <dd class="col-7 mb-0">{{ $vehicle->ano_fabricacao }}/{{ $vehicle->ano_modelo }}</dd>

                                <dt class="col-5 text-muted font-weight-normal" style="font-size:.85rem">Quilometragem</dt>
                                <dd class="col-7 mb-0">{{ $vehicle->km_formatado }}</dd>

                                <dt class="col-5 text-muted font-weight-normal" style="font-size:.85rem">Cor</dt>
                                <dd class="col-7 mb-0">{{ $vehicle->cor }}</dd>

                                <dt class="col-5 text-muted font-weight-normal" style="font-size:.85rem">Categoria</dt>
                                <dd class="col-7 mb-0">{{ ucfirst($vehicle->categoria) }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row mb-0" style="row-gap:4px">
                                <dt class="col-5 text-muted font-weight-normal" style="font-size:.85rem">Combustível</dt>
                                <dd class="col-7 mb-0">{{ ucfirst($vehicle->combustivel) }}</dd>

                                <dt class="col-5 text-muted font-weight-normal" style="font-size:.85rem">Transmissão</dt>
                                <dd class="col-7 mb-0">{{ ucfirst($vehicle->transmissao) }}</dd>

                                <dt class="col-5 text-muted font-weight-normal" style="font-size:.85rem">Portas</dt>
                                <dd class="col-7 mb-0">{{ $vehicle->portas ?: 'Moto' }}</dd>

                                <dt class="col-5 text-muted font-weight-normal" style="font-size:.85rem">Motorização</dt>
                                <dd class="col-7 mb-0">{{ $vehicle->motorizacao ?? '—' }}</dd>

                                <dt class="col-5 text-muted font-weight-normal" style="font-size:.85rem">Placa</dt>
                                <dd class="col-7 mb-0">{{ $vehicle->placa ?? '—' }}</dd>
                            </dl>
                        </div>
                    </div>

                    @if($vehicle->descricao)
                    <hr class="my-3">
                    <p class="text-muted mb-0" style="font-size:.9rem">{{ $vehicle->descricao }}</p>
                    @endif

                    @if($vehicle->features->isNotEmpty())
                    <hr class="my-3">
                    <p class="text-muted text-uppercase mb-2" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Opcionais</p>
                    <div class="d-flex flex-wrap" style="gap:6px">
                        @foreach($vehicle->features as $feat)
                        <span class="badge badge-light border text-dark px-2 py-1" style="font-size:.8rem;font-weight:500">
                            <i class="fas fa-check text-success mr-1" style="font-size:.7rem"></i>{{ $feat->feature }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Despesas --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <h3 class="text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Despesas do Veículo</h3>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#modalDespesa">
                            <i class="fas fa-plus mr-1"></i>Nova
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 pt-2">
                    @if($expenses->isEmpty())
                        <p class="text-muted text-center py-3 mb-0">Nenhuma despesa registrada.</p>
                    @else
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr style="font-size:.75rem;text-transform:uppercase;color:#6c757d;border-top:0">
                                <th class="border-top-0">Data</th>
                                <th class="border-top-0">Descrição</th>
                                <th class="border-top-0">Categoria</th>
                                <th class="border-top-0 text-right">Valor</th>
                                <th class="border-top-0" style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenses as $exp)
                            <tr>
                                <td class="text-muted" style="font-size:.85rem">{{ $exp->data->format('d/m/Y') }}</td>
                                <td style="font-size:.9rem">{{ $exp->descricao }}</td>
                                <td><span class="badge badge-light border" style="font-size:.75rem">{{ $exp->categoria_label }}</span></td>
                                <td class="text-right font-weight-bold text-danger" style="font-size:.9rem">R$ {{ number_format($exp->valor, 2, ',', '.') }}</td>
                                <td class="text-right">
                                    <form action="{{ route('admin.expenses.destroy', $exp) }}" method="POST" class="d-inline"
                                          data-confirm="Excluir esta despesa?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#f8f9fa">
                                <td colspan="3" class="font-weight-bold text-muted" style="font-size:.85rem">Total de despesas</td>
                                <td class="text-right font-weight-bold text-danger">R$ {{ number_format($expenses->sum('valor'), 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    @endif
                </div>
            </div>

            {{-- Fotos --}}
            <div class="card shadow-sm" id="fotos">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <h3 class="card-title text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">
                        Fotos <span class="text-secondary">({{ $vehicle->photos->count() }})</span>
                    </h3>
                </div>
                <div class="card-body pt-3">
                    <form action="{{ route('admin.vehicles.photos.store', $vehicle) }}" method="POST" enctype="multipart/form-data" id="photoForm">
                        @csrf
                        <div class="input-group mb-1">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="photoInput" multiple accept="image/*">
                                <label class="custom-file-label" for="photoInput" style="font-size:.9rem">Selecionar fotos para recortar...</label>
                            </div>
                            <div class="input-group-append">
                                <button type="button" id="photoSubmitBtn" class="btn btn-secondary" disabled title="Selecione as fotos primeiro">
                                    <i class="fas fa-upload mr-1"></i>Enviar
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">JPG, PNG, WebP · máx. 5 MB · proporção 4:3 (800×600)</small>
                    </form>

                    @if($vehicle->photos->isNotEmpty())
                    <div class="row mt-3" id="photoGrid">
                        @foreach($vehicle->photos as $photo)
                        <div class="col-md-3 col-sm-4 mb-3" data-photo-id="{{ $photo->id }}">
                            <div class="position-relative" style="border-radius:6px;overflow:hidden;border:2px solid {{ $photo->principal ? '#28a745' : '#dee2e6' }}">
                                <div class="drag-handle text-center py-1" style="cursor:grab;background:#f8f9fa;font-size:.65rem;color:#999">
                                    <i class="fas fa-grip-horizontal"></i>
                                </div>
                                <img src="{{ $photo->url }}" style="width:100%;height:110px;object-fit:cover;display:block">
                                <div class="d-flex justify-content-between align-items-center px-1 py-1" style="background:#fff">
                                    @if($photo->principal)
                                        <span class="badge badge-success" style="font-size:.7rem">Principal</span>
                                    @else
                                        <form action="{{ route('admin.vehicles.photos.principal', [$vehicle, $photo]) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-link p-0 text-muted" title="Definir como foto principal do anúncio" style="font-size:.75rem">
                                                <i class="far fa-star"></i> Principal
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.vehicles.photos.destroy', [$vehicle, $photo]) }}" method="POST" class="d-inline"
                                          data-confirm="Remover esta foto?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-link p-0 text-danger" style="font-size:.75rem"
                                                title="Remover esta foto permanentemente">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- ── Sidebar ─────────────────────────────────────────── --}}
        <div class="col-md-4">

            {{-- Preços --}}
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted text-uppercase mb-1" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Preço de Venda</p>
                    <p class="text-success font-weight-bold mb-0" style="font-size:1.6rem">{{ $vehicle->preco_formatado }}</p>
                    @if($vehicle->preco_compra)
                    <p class="text-muted mb-0" style="font-size:.85rem">
                        Compra: R$ {{ number_format($vehicle->preco_compra, 0, ',', '.') }}
                        <span class="ml-2 text-secondary">·</span>
                        @php $margem = $vehicle->preco - $vehicle->preco_compra; @endphp
                        <span class="{{ $margem >= 0 ? 'text-success' : 'text-danger' }}">
                            Margem: R$ {{ number_format($margem, 0, ',', '.') }}
                        </span>
                    </p>
                    @endif
                </div>
            </div>

            {{-- Ações --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <h3 class="card-title text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Ações</h3>
                </div>
                <div class="card-body pt-2">
                    @if($vehicle->status === 'disponivel')
                    <a href="{{ route('admin.sales.create', ['vehicle_id' => $vehicle->id]) }}" class="btn btn-success btn-block mb-2"
                       title="Registrar uma nova venda para este veículo">
                        <i class="fas fa-handshake mr-1"></i>Registrar Venda
                    </a>
                    @endif
                    <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="btn btn-outline-secondary btn-block mb-2"
                       title="Editar dados e informações do veículo">
                        <i class="fas fa-edit mr-1"></i>Editar Veículo
                    </a>
                    <a href="{{ route('site.vehicles.show', $vehicle->slug) }}" target="_blank" class="btn btn-outline-secondary btn-block mb-2"
                       title="Visualizar como aparece no site público">
                        <i class="fas fa-external-link-alt mr-1"></i>Ver no Site
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-block" data-toggle="modal" data-target="#modalDespesa"
                            title="Adicionar uma despesa vinculada a este veículo">
                        <i class="fas fa-file-invoice-dollar mr-1"></i>Registrar Despesa
                    </button>
                </div>
            </div>

            {{-- Sócios --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <h3 class="text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Sócios</h3>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#modalSocio"
                                title="Vincular um sócio a este veículo">
                            <i class="fas fa-plus mr-1"></i>Adicionar
                        </button>
                    </div>
                </div>
                <div class="card-body pt-2 pb-1">
                    @php
                        $totalPercentual = $vehicle->partners->sum('pivot.percentual');
                        $percentualLoja  = max(0, 100 - $totalPercentual);
                        $cores = ['#3c8dbc','#28a745','#f39c12','#e74c3c','#6f42c1','#17a2b8'];
                        $i = 0;
                    @endphp

                    {{-- Loja sempre aparece como primeira linha --}}
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="font-size:.88rem">
                        <div>
                            <span class="font-weight-600 text-muted"><i class="fas fa-store mr-1" style="font-size:.8rem"></i>Loja</span>
                        </div>
                        <span class="font-weight-bold" style="font-size:1rem">{{ number_format($percentualLoja, 0, ',', '.') }}%</span>
                    </div>

                    @foreach($vehicle->partners as $socio)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="font-size:.88rem">
                        <div>
                            <span class="font-weight-600">{{ $socio->nome }}</span>
                            @if($socio->telefone)
                                <br><a href="https://wa.me/55{{ preg_replace('/\D/', '', $socio->telefone) }}" target="_blank" class="text-success" style="font-size:.78rem"><i class="fab fa-whatsapp mr-1"></i>{{ $socio->telefone }}</a>
                            @endif
                        </div>
                        <div class="d-flex align-items-center" style="gap:8px">
                            <span class="font-weight-bold" style="font-size:1rem">{{ number_format($socio->pivot->percentual, 0, ',', '.') }}%</span>
                            <form action="{{ route('admin.vehicles.partners.detach', [$vehicle, $socio]) }}" method="POST"
                                  class="d-inline" data-confirm="Remover {{ $socio->nome }} deste veículo?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link p-0 text-danger" title="Remover sócio deste veículo" style="font-size:.8rem">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach

                    {{-- Barra de distribuição --}}
                    <div class="pt-2 pb-1">
                        <div class="d-flex rounded overflow-hidden" style="height:6px">
                            {{-- Fatia da loja sempre em cinza --}}
                            @if($percentualLoja > 0)
                            <div style="width:{{ $percentualLoja }}%;background:#adb5bd"></div>
                            @endif
                            @foreach($vehicle->partners as $socio)
                            <div style="width:{{ $socio->pivot->percentual }}%;background:{{ $cores[$i++ % count($cores)] }}"></div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Custo real --}}
            @php
                $totalDespesas = $expenses->sum('valor');
                $custoTotal    = ($vehicle->preco_compra ?? 0) + $totalDespesas;
                $margemReal    = $vehicle->preco - $custoTotal;
            @endphp
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <h3 class="card-title text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Custo Real</h3>
                </div>
                <div class="card-body pt-2 pb-0">
                    <div class="d-flex justify-content-between py-2 border-bottom" style="font-size:.88rem">
                        <span class="text-muted">Preço de compra</span>
                        <span>R$ {{ number_format($vehicle->preco_compra ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="font-size:.88rem">
                        <span class="text-muted">Despesas</span>
                        <span class="text-danger">+ R$ {{ number_format($totalDespesas, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="font-size:.88rem">
                        <span class="text-muted">Custo total</span>
                        <span class="font-weight-bold">R$ {{ number_format($custoTotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="font-size:.88rem">
                        <span class="text-muted">Preço de venda</span>
                        <span class="text-success">R$ {{ number_format($vehicle->preco, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 mb-1" style="font-size:.95rem">
                        <span class="font-weight-bold">Margem real</span>
                        <span class="font-weight-bold {{ $margemReal >= 0 ? 'text-success' : 'text-danger' }}">
                            R$ {{ number_format($margemReal, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Histórico de vendas --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <h3 class="card-title text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Vendas</h3>
                </div>
                <div class="card-body pt-2 pb-0">
                    @forelse($vehicle->sales as $sale)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="font-size:.88rem">
                        <a href="{{ route('admin.sales.show', $sale) }}" class="text-dark">
                            {{ $sale->data_venda->format('d/m/Y') }}<br>
                            <span class="text-success font-weight-bold">{{ $sale->preco_venda_formatado }}</span>
                        </a>
                        <span class="badge badge-{{ $sale->status_color }}">{{ $sale->status_label }}</span>
                    </div>
                    @empty
                    <p class="text-muted text-center py-2 mb-0" style="font-size:.85rem">Nenhuma venda</p>
                    @endforelse
                    <div class="pb-1"></div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Modal Nova Despesa --}}
<div class="modal fade" id="modalDespesa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.expenses.store') }}" method="POST">
                @csrf
                <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size:.95rem;font-weight:600">
                        <i class="fas fa-file-invoice-dollar mr-2 text-muted"></i>Nova Despesa
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    @if($errors->any())
                    <div class="alert alert-danger py-2"><ul class="mb-0 pl-3">@foreach($errors->all() as $e)<li style="font-size:.88rem">{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <div class="form-group">
                        <label class="font-weight-bold" style="font-size:.85rem">Descrição <span class="text-danger">*</span></label>
                        <input type="text" name="descricao" class="form-control @error('descricao') is-invalid @enderror"
                            value="{{ old('descricao') }}" placeholder="Ex: Troca de óleo, IPVA..." required>
                        @error('descricao')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:.85rem">Categoria <span class="text-danger">*</span></label>
                                <select name="categoria" class="form-control" required>
                                    @foreach(['manutencao' => 'Manutenção', 'documentacao' => 'Documentação', 'limpeza' => 'Limpeza', 'combustivel' => 'Combustível', 'comissao' => 'Comissão', 'outros' => 'Outros'] as $v => $l)
                                    <option value="{{ $v }}" {{ old('categoria') === $v ? 'selected' : '' }}>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:.85rem">Data <span class="text-danger">*</span></label>
                                <input type="date" name="data" class="form-control"
                                    value="{{ old('data', now()->format('Y-m-d')) }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold" style="font-size:.85rem">Valor <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                            <input type="text" id="despesaValorDisplay" class="form-control @error('valor') is-invalid @enderror"
                                placeholder="0,00" inputmode="numeric" value="{{ old('valor') ? number_format(old('valor'), 2, ',', '.') : '' }}" required>
                            <input type="hidden" name="valor" id="despesaValor" value="{{ old('valor') }}">
                        </div>
                        @error('valor')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Adicionar Sócio --}}
<div class="modal fade" id="modalSocio" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form action="{{ route('admin.vehicles.partners.attach', $vehicle) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size:.95rem;font-weight:600">
                        <i class="fas fa-user-tie mr-2 text-muted"></i>Vincular Sócio
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold" style="font-size:.85rem">Sócio <span class="text-danger">*</span></label>
                        <select name="partner_id" class="form-control" required>
                            <option value="">— Selecione —</option>
                            @foreach($allPartners as $p)
                                <option value="{{ $p->id }}">{{ $p->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold" style="font-size:.85rem">Percentual (%) <span class="text-danger">*</span></label>
                        <input type="number" name="percentual" class="form-control"
                            min="0.01" max="100" step="0.01" placeholder="Ex: 50" required>
                        @php $livre = 100 - $vehicle->partners->sum('pivot.percentual'); @endphp
                        <small class="text-muted">Percentual da loja disponível para transferir: <strong>{{ number_format($livre, 2, ',', '.') }}%</strong></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-link mr-1"></i>Vincular</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Modal Crop Fotos --}}
<div class="modal fade" id="cropModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" style="font-size:.95rem;font-weight:600">
                    <i class="fas fa-crop-alt mr-2 text-muted"></i>Recortar Foto
                    <span id="cropCounter" class="text-muted ml-1" style="font-weight:400;font-size:.85rem"></span>
                </h5>
            </div>
            <div id="cropWrap">
                <img id="cropImage" src="" alt="Foto para recorte">
            </div>
            <div class="modal-footer justify-content-between py-2">
                <div>
                    <button type="button" id="btnCropCancel" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times mr-1"></i>Cancelar tudo
                    </button>
                    <button type="button" id="btnCropSkip" class="btn btn-outline-warning btn-sm ml-1">
                        Pular esta foto
                    </button>
                </div>
                <button type="button" id="btnCropConfirm" class="btn btn-primary btn-sm">
                    <i class="fas fa-check mr-1"></i>Confirmar recorte
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
// ── Crop flow ───────────────────────────────────────────────
(function () {
    var cropQueue    = [];
    var cropIndex    = 0;
    var croppedBlobs = [];
    var cropper      = null;

    var photoInput   = document.getElementById('photoInput');
    var submitBtn    = document.getElementById('photoSubmitBtn');
    var cropModal    = document.getElementById('cropModal');
    var cropImage    = document.getElementById('cropImage');
    var cropCounter  = document.getElementById('cropCounter');
    var btnConfirm   = document.getElementById('btnCropConfirm');
    var btnSkip      = document.getElementById('btnCropSkip');
    var btnCancel    = document.getElementById('btnCropCancel');
    var uploadUrl    = '{{ route("admin.vehicles.photos.store", $vehicle) }}';
    var csrfToken    = '{{ csrf_token() }}';

    photoInput.addEventListener('change', function () {
        if (!this.files.length) return;
        cropQueue    = Array.from(this.files);
        cropIndex    = 0;
        croppedBlobs = [];
        submitBtn.disabled = true;
        openCrop();
    });

    var CROP_OPTS = {
        aspectRatio: 4 / 3,
        viewMode: 1,
        autoCropArea: 0.95,
        movable: true,
        zoomable: true,
        rotatable: true,
        scalable: false,
    };

    function initCropper() {
        if (cropper) { cropper.destroy(); cropper = null; }
        cropper = new Cropper(cropImage, CROP_OPTS);
    }

    // First open: init after animation completes
    $('#cropModal').on('shown.bs.modal', initCropper);

    $('#cropModal').on('hidden.bs.modal', function () {
        if (cropper) { cropper.destroy(); cropper = null; }
    });

    function openCrop() {
        if (cropIndex >= cropQueue.length) {
            $(cropModal).modal('hide');
            if (croppedBlobs.length) enableSend();
            return;
        }
        var reader = new FileReader();
        reader.onload = function (e) {
            cropCounter.textContent = '— foto ' + (cropIndex + 1) + ' de ' + cropQueue.length;

            var modalOpen = $(cropModal).hasClass('show');
            if (!modalOpen) {
                // shown.bs.modal will call initCropper after animation
                cropImage.src = e.target.result;
                $(cropModal).modal('show');
            } else {
                // Modal already visible: destroy, swap image, reinit after load
                if (cropper) { cropper.destroy(); cropper = null; }
                cropImage.src = '';
                cropImage.onload = function () {
                    cropImage.onload = null;
                    initCropper();
                };
                cropImage.src = e.target.result;
            }
        };
        reader.readAsDataURL(cropQueue[cropIndex]);
    }

    btnConfirm.addEventListener('click', function () {
        if (!cropper) return;
        btnConfirm.disabled = true;
        cropper.getCroppedCanvas({ width: 800, height: 600 }).toBlob(function (blob) {
            croppedBlobs.push(blob);
            cropIndex++;
            btnConfirm.disabled = false;
            openCrop();
        }, 'image/jpeg', 0.85);
    });

    btnSkip.addEventListener('click', function () {
        cropIndex++;
        openCrop();
    });

    btnCancel.addEventListener('click', function () {
        $(cropModal).modal('hide');
        reset();
    });

    function enableSend() {
        var label = photoInput.nextElementSibling;
        label.textContent = croppedBlobs.length + ' foto(s) prontas para enviar';
        submitBtn.disabled = false;
        submitBtn.onclick = sendPhotos;
    }

    function reset() {
        photoInput.value = '';
        cropQueue = []; croppedBlobs = []; cropIndex = 0;
        if (cropper) { cropper.destroy(); cropper = null; }
        submitBtn.disabled = true;
        submitBtn.onclick = null;
        photoInput.nextElementSibling.textContent = 'Selecionar fotos para recortar...';
    }

    async function sendPhotos() {
        if (!croppedBlobs.length) return;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Enviando...';
        var fd = new FormData();
        fd.append('_token', csrfToken);
        croppedBlobs.forEach(function (blob, i) {
            fd.append('photos[' + i + ']', blob, 'foto_' + (i + 1) + '.jpg');
        });
        try {
            var resp = await fetch(uploadUrl, { method: 'POST', body: fd });
            if (resp.ok) {
                window.location.reload();
            } else {
                alert('Erro ao enviar fotos. Tente novamente.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-upload mr-1"></i>Enviar';
            }
        } catch (e) {
            alert('Erro de conexão. Tente novamente.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload mr-1"></i>Enviar';
        }
    }
})();

// Máscara de moeda no modal de despesa
(function () {
    var disp = document.getElementById('despesaValorDisplay');
    var hid  = document.getElementById('despesaValor');
    if (!disp) return;
    disp.addEventListener('input', function () {
        var raw = this.value.replace(/\D/g, '');
        if (!raw) { this.value = ''; hid.value = ''; return; }
        var num = parseInt(raw, 10) / 100;
        hid.value = num.toFixed(2);
        this.value = num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    });
})();

// ── Drag-and-drop reorder fotos ──────────────────────────────
(function () {
    var grid = document.getElementById('photoGrid');
    if (!grid) return;

    Sortable.create(grid, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'sortable-ghost',
        onEnd: function () {
            var items = grid.querySelectorAll('[data-photo-id]');
            var order = Array.from(items).map(function (el) {
                return parseInt(el.getAttribute('data-photo-id'));
            });
            fetch('{{ route("admin.vehicles.photos.reorder", $vehicle) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ order: order })
            });
        }
    });
})();

@if($errors->any())
$('#modalDespesa').modal('show');
@endif
</script>
@endsection
