@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show py-2 mb-3">
    <ul class="mb-0 pl-3">@foreach($errors->all() as $e)<li style="font-size:.88rem">{{ $e }}</li>@endforeach</ul>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

{{-- ── Busca na Tabela FIPE ─────────────────────────────────────────── --}}
<div class="card shadow-sm mb-3 border-left" style="border-left:3px solid #3c8dbc !important">
    <div class="card-header bg-white border-bottom-0 pb-0">
        <div style="display:flex;justify-content:space-between;align-items:center">
            <h3 class="text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">
                <i class="fas fa-search mr-1"></i>Buscar na Tabela FIPE
                <small class="text-muted font-weight-normal text-lowercase ml-1">— preenche o formulário automaticamente</small>
            </h3>
            <span id="fipeLoading" class="text-muted" style="font-size:.78rem;display:none">
                <i class="fas fa-spinner fa-spin mr-1"></i>Carregando...
            </span>
        </div>
    </div>
    <div class="card-body pt-2 pb-3">
        <div class="row" style="gap-row:0">
            <div class="col-md-3">
                <div class="form-group mb-2">
                    <label class="text-muted" style="font-size:.8rem;font-weight:600">Marca</label>
                    <select id="fipeMarca" class="form-control form-control-sm">
                        <option value="">— selecione —</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label class="text-muted" style="font-size:.8rem;font-weight:600">Modelo</label>
                    <select id="fipeModelo" class="form-control form-control-sm" disabled>
                        <option value="">— selecione a marca —</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-2">
                    <label class="text-muted" style="font-size:.8rem;font-weight:600">Ano</label>
                    <select id="fipeAno" class="form-control form-control-sm" disabled>
                        <option value="">— selecione o modelo —</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-end pb-2">
                <div id="fipePreco" class="d-none w-100">
                    <div class="p-2 rounded" style="background:#f0faf4;border:1px solid #c3e6cb">
                        <div class="text-muted" style="font-size:.72rem;font-weight:600;text-transform:uppercase">Preço FIPE</div>
                        <div class="font-weight-bold text-success" id="fipePrecoValor" style="font-size:1rem"></div>
                        <div class="text-muted" id="fipeMesRef" style="font-size:.72rem"></div>
                    </div>
                </div>
            </div>
        </div>
        <div id="fipeAplicar" class="d-none mt-1">
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnFipeAplicar">
                <i class="fas fa-magic mr-1"></i>Preencher formulário com estes dados
            </button>
        </div>
    </div>
</div>

{{-- ── Informações principais ───────────────────────────────────────── --}}
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white border-bottom-0 pb-0">
        <h3 class="card-title text-muted text-uppercase" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Identificação</h3>
    </div>
    <div class="card-body pt-2">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Marca <span class="text-danger">*</span></label>
                    <input type="text" name="marca" class="form-control @error('marca') is-invalid @enderror"
                        value="{{ old('marca', $vehicle?->marca) }}" placeholder="Ex: Chevrolet" required>
                    @error('marca')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Modelo <span class="text-danger">*</span></label>
                    <input type="text" name="modelo" class="form-control @error('modelo') is-invalid @enderror"
                        value="{{ old('modelo', $vehicle?->modelo) }}" placeholder="Ex: Onix" required>
                    @error('modelo')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Versão</label>
                    <input type="text" name="versao" class="form-control"
                        value="{{ old('versao', $vehicle?->versao) }}" placeholder="Ex: LT 1.0 Turbo">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Ano Fabricação <span class="text-danger">*</span></label>
                    <input type="number" name="ano_fabricacao" class="form-control @error('ano_fabricacao') is-invalid @enderror"
                        value="{{ old('ano_fabricacao', $vehicle?->ano_fabricacao) }}" min="1990" max="{{ date('Y')+1 }}" required>
                    @error('ano_fabricacao')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Ano Modelo <span class="text-danger">*</span></label>
                    <input type="number" name="ano_modelo" class="form-control @error('ano_modelo') is-invalid @enderror"
                        value="{{ old('ano_modelo', $vehicle?->ano_modelo) }}" min="1990" max="{{ date('Y')+1 }}" required>
                    @error('ano_modelo')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Quilometragem <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="kmDisplay"
                            class="form-control @error('km') is-invalid @enderror"
                            value="{{ old('km', $vehicle?->km) ? number_format(old('km', $vehicle?->km), 0, ',', '.') : '' }}"
                            placeholder="0" inputmode="numeric">
                        <div class="input-group-append"><span class="input-group-text text-muted" style="font-size:.85rem">km</span></div>
                        <input type="hidden" name="km" id="kmHidden" value="{{ old('km', $vehicle?->km) }}">
                    </div>
                    @error('km')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Cor <span class="text-danger">*</span></label>
                    <input type="text" name="cor" class="form-control @error('cor') is-invalid @enderror"
                        value="{{ old('cor', $vehicle?->cor) }}" placeholder="Ex: Branco" required>
                    @error('cor')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Categoria <span class="text-danger">*</span></label>
                    <select name="categoria" class="form-control @error('categoria') is-invalid @enderror" required>
                        @foreach(['hatch','sedan','suv','pickup','van','esportivo','outro'] as $cat)
                            <option value="{{ $cat }}" {{ (old('categoria', $vehicle?->categoria) === $cat) ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Especificações técnicas ──────────────────────────────────────── --}}
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white border-bottom-0 pb-0">
        <h3 class="card-title text-muted text-uppercase" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Especificações Técnicas</h3>
    </div>
    <div class="card-body pt-2">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Combustível <span class="text-danger">*</span></label>
                    <select name="combustivel" class="form-control" required>
                        @foreach(['flex','gasolina','etanol','diesel','gnv','hibrido','eletrico'] as $c)
                            <option value="{{ $c }}" {{ (old('combustivel', $vehicle?->combustivel ?? 'flex') === $c) ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Transmissão <span class="text-danger">*</span></label>
                    <select name="transmissao" class="form-control" required>
                        <option value="manual"      {{ (old('transmissao', $vehicle?->transmissao ?? 'manual') === 'manual')      ? 'selected' : '' }}>Manual</option>
                        <option value="automatico"  {{ (old('transmissao', $vehicle?->transmissao) === 'automatico')  ? 'selected' : '' }}>Automático</option>
                        <option value="automatizado"{{ (old('transmissao', $vehicle?->transmissao) === 'automatizado')? 'selected' : '' }}>Automatizado</option>
                        <option value="cvt"         {{ (old('transmissao', $vehicle?->transmissao) === 'cvt')         ? 'selected' : '' }}>CVT</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Portas</label>
                    <select name="portas" class="form-control">
                        <option value="0" {{ (old('portas', $vehicle?->portas ?? 4) == 0) ? 'selected' : '' }}>Moto</option>
                        <option value="2" {{ (old('portas', $vehicle?->portas) == 2)       ? 'selected' : '' }}>2</option>
                        <option value="4" {{ (old('portas', $vehicle?->portas ?? 4) == 4)  ? 'selected' : '' }}>4</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Motorização</label>
                    <input type="text" name="motorizacao" class="form-control"
                        value="{{ old('motorizacao', $vehicle?->motorizacao) }}" placeholder="Ex: 1.0, 2.0 Turbo...">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Preços e status ──────────────────────────────────────────────── --}}
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white border-bottom-0 pb-0">
        <h3 class="card-title text-muted text-uppercase" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Preços e Status</h3>
    </div>
    <div class="card-body pt-2">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Preço de Venda <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text font-weight-bold">R$</span></div>
                        <input type="text" id="precoDisplay"
                            class="form-control @error('preco') is-invalid @enderror"
                            value="{{ old('preco', $vehicle?->preco) ? number_format((float)old('preco', $vehicle?->preco), 2, ',', '.') : '' }}"
                            placeholder="0,00" inputmode="numeric" required>
                        <input type="hidden" name="preco" id="precoHidden" value="{{ old('preco', $vehicle?->preco) }}">
                    </div>
                    @error('preco')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Preço de Compra <small class="text-muted font-weight-normal">(interno)</small></label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text font-weight-bold">R$</span></div>
                        <input type="text" id="precoCompraDisplay"
                            class="form-control"
                            value="{{ old('preco_compra', $vehicle?->preco_compra) ? number_format(old('preco_compra', $vehicle?->preco_compra), 2, ',', '.') : '' }}"
                            placeholder="0,00" inputmode="numeric">
                        <input type="hidden" name="preco_compra" id="precoCompraHidden" value="{{ old('preco_compra', $vehicle?->preco_compra) }}">
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="disponivel" {{ (old('status', $vehicle?->status ?? 'disponivel') === 'disponivel') ? 'selected' : '' }}>Disponível</option>
                        <option value="reservado"  {{ (old('status', $vehicle?->status) === 'reservado')  ? 'selected' : '' }}>Reservado</option>
                        <option value="vendido"    {{ (old('status', $vehicle?->status) === 'vendido')    ? 'selected' : '' }}>Vendido</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">&nbsp;</label>
                    <div class="d-flex align-items-center" style="height:38px">
                        <label style="cursor:pointer;margin:0;font-size:.88rem;font-weight:600;display:flex;align-items:center;gap:6px">
                            <input type="checkbox" name="destaque" value="1"
                                {{ old('destaque', $vehicle?->destaque) ? 'checked' : '' }}
                                style="width:16px;height:16px;cursor:pointer">
                            <i class="fas fa-star text-warning"></i>Destaque no site
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Dados internos ───────────────────────────────────────────────── --}}
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white border-bottom-0 pb-0">
        <h3 class="card-title text-muted text-uppercase" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Dados Internos <small class="text-muted font-weight-normal text-lowercase">(não exibidos no site)</small></h3>
    </div>
    <div class="card-body pt-2">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Placa</label>
                    <input type="text" name="placa" class="form-control"
                        value="{{ old('placa', $vehicle?->placa) }}" placeholder="ABC-1234">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">RENAVAM</label>
                    <input type="text" name="renavam" class="form-control"
                        value="{{ old('renavam', $vehicle?->renavam) }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Chassi</label>
                    <input type="text" name="chassi" class="form-control"
                        value="{{ old('chassi', $vehicle?->chassi) }}">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Descrição ────────────────────────────────────────────────────── --}}
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white border-bottom-0 pb-0">
        <h3 class="card-title text-muted text-uppercase" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Descrição</h3>
    </div>
    <div class="card-body pt-2">
        <textarea name="descricao" class="form-control" rows="4"
            placeholder="Descreva o veículo para o site...">{{ old('descricao', $vehicle?->descricao) }}</textarea>
    </div>
</div>

{{-- ── Opcionais ────────────────────────────────────────────────────── --}}
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white border-bottom-0 pb-0">
        <h3 class="card-title text-muted text-uppercase" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Opcionais / Equipamentos</h3>
    </div>
    <div class="card-body pt-2">
        @php $featIdx = 0; @endphp
        @foreach($featuresByCategory as $category => $items)
        <h6 class="font-weight-bold text-muted mt-3 mb-2 pb-1 border-bottom" style="font-size:.8rem;letter-spacing:.04em">
            @if($category === 'Conforto / Interior')
                <i class="fas fa-couch mr-1"></i>
            @elseif($category === 'Segurança')
                <i class="fas fa-shield-alt mr-1"></i>
            @elseif($category === 'Tecnologia / Entretenimento')
                <i class="fas fa-laptop mr-1"></i>
            @elseif($category === 'Motor / Mecânica')
                <i class="fas fa-cogs mr-1"></i>
            @elseif($category === 'Estética / Exterior')
                <i class="fas fa-car mr-1"></i>
            @endif
            {{ $category }}
        </h6>
        <div class="row">
            @foreach($items as $feature)
            <div class="col-md-3 col-sm-4 col-6">
                <div class="custom-control custom-checkbox mb-2">
                    <input type="checkbox" class="custom-control-input" id="feat_{{ $featIdx }}"
                        name="features[]" value="{{ $feature }}"
                        {{ in_array($feature, $currentFeatures ?? []) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="feat_{{ $featIdx }}" style="font-size:.88rem">{{ $feature }}</label>
                </div>
            </div>
            @php $featIdx++; @endphp
            @endforeach
        </div>
        @endforeach
    </div>
</div>
