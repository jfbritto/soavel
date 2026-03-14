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
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Identificação</h3>
            <button type="button" id="btnReviewAI" class="btn btn-sm btn-outline-info" onclick="reviewVehicleAI()">
                <i class="fas fa-robot mr-1"></i>Revisar com IA
            </button>
        </div>
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
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Opcionais / Equipamentos</h3>
            <button type="button" id="btnSuggestFeatures" class="btn btn-sm btn-outline-primary" onclick="suggestFeaturesAI()">
                <i class="fas fa-magic mr-1"></i>Preencher com IA
            </button>
        </div>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function suggestFeaturesAI() {
    var marca  = document.querySelector('[name="marca"]');
    var modelo = document.querySelector('[name="modelo"]');
    var ano    = document.querySelector('[name="ano_modelo"]');

    if (!marca || !modelo || !ano || !marca.value || !modelo.value || !ano.value) {
        Swal.fire({
            icon: 'warning',
            title: 'Campos obrigatórios',
            text: 'Preencha marca, modelo e ano do veículo antes de usar a IA.',
        });
        return;
    }

    var btn = document.getElementById('btnSuggestFeatures');
    var originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Consultando IA...';

    Swal.fire({
        title: 'Consultando IA...',
        html: '<p class="mb-1">Analisando opcionais do <b>' + marca.value + ' ' + modelo.value + ' ' + ano.value + '</b></p><small class="text-muted">Isso pode levar alguns segundos</small>',
        allowOutsideClick: false,
        didOpen: function() { Swal.showLoading(); }
    });

    fetch('{{ route("admin.vehicles.suggestFeatures") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            marca: marca.value,
            modelo: modelo.value,
            ano: ano.value
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.error) {
            Swal.fire({ icon: 'error', title: 'Erro', text: data.error });
            return;
        }

        // Desmarca todos primeiro
        document.querySelectorAll('input[name="features[]"]').forEach(function(cb) {
            cb.checked = false;
        });

        // Marca os sugeridos
        var found = 0;
        var list = '';
        data.features.forEach(function(feat) {
            document.querySelectorAll('input[name="features[]"]').forEach(function(cb) {
                if (cb.value === feat) {
                    cb.checked = true;
                    found++;
                    list += '<li style="font-size:.85rem">' + feat + '</li>';
                }
            });
        });

        Swal.fire({
            icon: 'success',
            title: found + ' opcionais sugeridos',
            html: '<p class="mb-2">Revise os itens antes de salvar:</p>'
                + '<div style="max-height:250px;overflow-y:auto;text-align:left">'
                + '<ul class="pl-3 mb-0">' + list + '</ul></div>',
            confirmButtonText: 'Entendi',
            confirmButtonColor: '#3085d6',
        });
    })
    .catch(function(err) {
        Swal.fire({ icon: 'error', title: 'Erro', text: 'Falha ao consultar IA: ' + err.message });
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function reviewVehicleAI() {
    var marca = document.querySelector('[name="marca"]');
    var modelo = document.querySelector('[name="modelo"]');

    if (!marca || !modelo || !marca.value || !modelo.value) {
        Swal.fire({ icon: 'warning', title: 'Campos obrigatórios', text: 'Preencha pelo menos marca e modelo.' });
        return;
    }

    var btn = document.getElementById('btnReviewAI');
    var originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Revisando...';

    Swal.fire({
        title: 'Revisando cadastro...',
        html: '<p class="mb-1">Analisando <b>' + marca.value + ' ' + modelo.value + '</b></p><small class="text-muted">A IA está verificando os dados</small>',
        allowOutsideClick: false,
        didOpen: function() { Swal.showLoading(); }
    });

    var precoEl = document.getElementById('precoHidden');
    var precoCompraEl = document.getElementById('precoCompraHidden');
    var kmEl = document.getElementById('kmHidden');

    fetch('{{ route("admin.vehicles.review") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            marca: marca.value,
            modelo: modelo.value,
            versao: (document.querySelector('[name="versao"]') || {}).value || '',
            ano_fabricacao: (document.querySelector('[name="ano_fabricacao"]') || {}).value || '',
            ano_modelo: (document.querySelector('[name="ano_modelo"]') || {}).value || '',
            km: kmEl ? kmEl.value : '',
            cor: (document.querySelector('[name="cor"]') || {}).value || '',
            combustivel: (document.querySelector('[name="combustivel"]') || {}).value || '',
            transmissao: (document.querySelector('[name="transmissao"]') || {}).value || '',
            motorizacao: (document.querySelector('[name="motorizacao"]') || {}).value || '',
            portas: (document.querySelector('[name="portas"]') || {}).value || '',
            categoria: (document.querySelector('[name="categoria"]') || {}).value || '',
            preco: precoEl ? precoEl.value : '',
            preco_compra: precoCompraEl ? precoCompraEl.value : '',
            descricao: (document.querySelector('[name="descricao"]') || {}).value || ''
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.error) {
            Swal.fire({ icon: 'error', title: 'Erro', text: data.error });
            return;
        }

        var r = data.review;
        var html = '';

        // Monta lista de todas as correções de campos
        var corrections = [];
        var fieldMap = {
            marca:       { label: 'Marca',       el: '[name="marca"]' },
            modelo:      { label: 'Modelo',       el: '[name="modelo"]' },
            versao:      { label: 'Versão',       el: '[name="versao"]' },
            cor:         { label: 'Cor',           el: '[name="cor"]' },
            motorizacao: { label: 'Motorização',   el: '[name="motorizacao"]' },
        };

        Object.keys(fieldMap).forEach(function(key) {
            if (r[key] !== undefined && r[key] !== null) {
                var input = document.querySelector(fieldMap[key].el);
                var current = input ? input.value.trim() : '';
                var suggested = (r[key] || '').trim();
                if (suggested && suggested !== current) {
                    corrections.push({
                        field: key,
                        label: fieldMap[key].label,
                        from: current || '(vazio)',
                        to: suggested
                    });
                }
            }
        });

        // Campos corrigidos
        if (corrections.length) {
            html += '<div class="text-left mb-3"><h6 class="font-weight-bold" style="color:#3085d6"><i class="fas fa-pen mr-1"></i>Campos corrigidos</h6>';
            corrections.forEach(function(c) {
                html += '<div class="d-flex align-items-center flex-wrap mb-2 p-2 rounded" style="background:#f0f7ff;gap:6px">'
                    + '<span class="badge badge-secondary" style="min-width:80px">' + c.label + '</span>'
                    + '<span style="text-decoration:line-through;color:#999;font-size:.85rem">' + c.from + '</span>'
                    + '<i class="fas fa-arrow-right text-muted" style="font-size:.65rem"></i>'
                    + '<strong style="color:#2563eb;font-size:.9rem">' + c.to + '</strong>'
                    + '</div>';
            });
            html += '</div>';
        }

        // Descrição
        if (r.descricao_sugerida) {
            var descAtual = (document.querySelector('[name="descricao"]') || {}).value || '';
            html += '<div class="text-left mb-3"><h6 class="font-weight-bold" style="color:#059669"><i class="fas fa-file-alt mr-1"></i>Descrição ' + (descAtual ? 'melhorada' : 'sugerida') + '</h6>'
                + '<div class="p-2 rounded mb-1" style="background:#f0fdf4;font-size:.88rem">' + r.descricao_sugerida + '</div>'
                + '</div>';
        }

        // Alertas
        if (r.alertas && r.alertas.length) {
            html += '<div class="text-left mb-3"><h6 class="font-weight-bold" style="color:#dc2626"><i class="fas fa-exclamation-triangle mr-1"></i>Alertas</h6>';
            r.alertas.forEach(function(a) {
                html += '<div class="p-2 rounded mb-1" style="background:#fef2f2;font-size:.85rem"><i class="fas fa-times-circle text-danger mr-1"></i>' + a + '</div>';
            });
            html += '</div>';
        }

        // Dicas
        if (r.dicas && r.dicas.length) {
            html += '<div class="text-left mb-3"><h6 class="font-weight-bold" style="color:#d97706"><i class="fas fa-lightbulb mr-1"></i>Dicas</h6>';
            r.dicas.forEach(function(d) {
                html += '<div class="p-2 rounded mb-1" style="background:#fffbeb;font-size:.85rem"><i class="fas fa-info-circle text-warning mr-1"></i>' + d + '</div>';
            });
            html += '</div>';
        }

        var hasSuggestions = corrections.length > 0 || r.descricao_sugerida;

        if (!html) {
            html = '<p class="text-success"><i class="fas fa-check-circle mr-1"></i>Cadastro está ótimo! Nenhuma sugestão.</p>';
        }

        // Função para aplicar tudo
        function applyAll() {
            corrections.forEach(function(c) {
                var input = document.querySelector('[name="' + c.field + '"]');
                if (input) input.value = c.to;
            });
            if (r.descricao_sugerida) {
                var ta = document.querySelector('[name="descricao"]');
                if (ta) ta.value = r.descricao_sugerida;
            }
            if (r.motorizacao) {
                var mot = document.querySelector('[name="motorizacao"]');
                if (mot) mot.value = r.motorizacao;
            }
        }

        Swal.fire({
            title: 'Revisão do Cadastro',
            html: '<div style="max-height:400px;overflow-y:auto">' + html + '</div>',
            width: 620,
            showDenyButton: hasSuggestions,
            showCancelButton: true,
            denyButtonText: '<i class="fas fa-magic"></i> Aplicar tudo',
            denyButtonColor: '#2563eb',
            confirmButtonText: '<i class="fas fa-check"></i> Aplicar tudo e salvar',
            confirmButtonColor: '#28a745',
            cancelButtonText: 'Fechar sem aplicar',
            cancelButtonColor: '#6c757d',
            focusConfirm: false,
            focusDeny: true,
            preDeny: function() {
                applyAll();
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                applyAll();
                // Submete o formulário automaticamente
                var form = document.querySelector('form[method="POST"]');
                if (form) form.submit();
            }
            if (result.isDenied) {
                Swal.fire({
                    icon: 'success',
                    title: 'Campos atualizados!',
                    text: 'Revise os valores e clique em Salvar/Atualizar.',
                    confirmButtonColor: '#2563eb',
                    timer: 2500,
                    timerProgressBar: true,
                });
            }
        });
    })
    .catch(function(err) {
        Swal.fire({ icon: 'error', title: 'Erro', text: 'Falha ao consultar IA: ' + err.message });
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>
