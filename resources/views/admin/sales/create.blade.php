@extends('adminlte::page')

@section('title', 'Nova Venda — Soavel')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-handshake mr-2"></i>Registrar Nova Venda</h1>
        <a href="{{ route('admin.sales.index') }}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Voltar</a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <h5><i class="icon fas fa-ban"></i> Corrija os erros abaixo:</h5>
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('admin.sales.store') }}" method="POST" id="saleForm">
        @csrf

        {{-- ── Dados da Venda ─────────────────────────────────────────────── --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-invoice mr-2"></i>Dados da Venda</h3>
            </div>
            <div class="card-body">

                {{-- Veículo + Cliente --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vehicle_id">Veículo *</label>
                            @php $selectedVehicleId = old('vehicle_id', $selectedVehicle->id ?? null); @endphp
                            <select name="vehicle_id" id="vehicle_id" class="form-control @error('vehicle_id') is-invalid @enderror" required>
                                <option value="">— Selecionar veículo —</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}"
                                        {{ $selectedVehicleId == $v->id ? 'selected' : '' }}
                                        data-preco="{{ $v->preco }}">
                                        {{ $v->marca }} {{ $v->modelo }} {{ $v->versao }} ({{ $v->ano_modelo }}) — R$ {{ number_format($v->preco, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vehicle_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_id">Cliente *</label>
                            @php $selectedCustomerId = old('customer_id'); @endphp
                            <select name="customer_id" id="customer_id" class="form-control @error('customer_id') is-invalid @enderror" required>
                                <option value="">— Selecionar cliente —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ $selectedCustomerId == $c->id ? 'selected' : '' }}>
                                        {{ $c->nome }}{{ $c->cpf ? ' — ' . $c->cpf : '' }}{{ $c->telefone ? ' · ' . $c->telefone : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <small class="form-text">
                                <a href="#" data-toggle="modal" data-target="#modalNovoCliente">
                                    <i class="fas fa-user-plus mr-1"></i>Cadastrar novo cliente
                                </a>
                            </small>
                        </div>
                    </div>
                </div>

                <hr class="mt-0 mb-3">

                {{-- Valores e tipo --}}
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="labelPrecoVenda" id="labelPrecoVenda">Preço de Venda *</label>
                            {{-- Campo visível com máscara --}}
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                <input type="text" id="precoVendaDisplay"
                                    class="form-control @error('preco_venda') is-invalid @enderror"
                                    placeholder="0,00"
                                    value="{{ old('preco_venda') ? number_format(old('preco_venda'), 2, ',', '.') : ($selectedVehicle ? number_format($selectedVehicle->preco, 2, ',', '.') : '') }}"
                                    autocomplete="off">
                            </div>
                            {{-- Hidden com valor numérico para o servidor --}}
                            <input type="hidden" name="preco_venda" id="precoVenda" value="{{ old('preco_venda', $selectedVehicle?->preco) }}">
                            @error('preco_venda')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="data_venda">Data da Venda *</label>
                            <input type="date" name="data_venda" id="data_venda"
                                class="form-control"
                                value="{{ old('data_venda', now()->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipoPagamento">Tipo de Pagamento *</label>
                            <select name="tipo_pagamento" id="tipoPagamento" class="form-control" required onchange="togglePaymentFields()">
                                <option value="a_vista"    {{ (old('tipo_pagamento','a_vista') === 'a_vista') ? 'selected' : '' }}>À Vista</option>
                                <option value="financiado" {{ (old('tipo_pagamento') === 'financiado') ? 'selected' : '' }}>Financiado</option>
                                <option value="consorcio"  {{ (old('tipo_pagamento') === 'consorcio') ? 'selected' : '' }}>Consórcio</option>
                                <option value="permuta"    {{ (old('tipo_pagamento') === 'permuta') ? 'selected' : '' }}>Permuta (Troca)</option>
                                <option value="misto"      {{ (old('tipo_pagamento') === 'misto') ? 'selected' : '' }}>Misto (Troca + Dinheiro)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="pendente"  {{ (old('status','pendente') === 'pendente') ? 'selected' : '' }}>Pendente</option>
                                <option value="concluida" {{ (old('status') === 'concluida') ? 'selected' : '' }}>Concluída</option>
                                <option value="cancelada" {{ (old('status') === 'cancelada') ? 'selected' : '' }}>Cancelada</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Financiamento --}}
                <div id="financiamentoFields" class="row" style="display:none">
                    <div class="col-12"><hr class="mt-0 mb-3"><p class="text-muted font-weight-bold mb-2"><i class="fas fa-university mr-1"></i>Dados do Financiamento</p></div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="financiadora">Financiadora</label>
                            <input type="text" name="financiadora" id="financiadora" class="form-control"
                                value="{{ old('financiadora') }}" placeholder="Ex: Banco do Brasil, Santander...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="entradaDisplay">Valor de Entrada (R$)</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                <input type="text" id="entradaDisplay" class="form-control" placeholder="0,00"
                                    value="{{ old('entrada') ? number_format(old('entrada'), 2, ',', '.') : '' }}"
                                    autocomplete="off">
                            </div>
                            <input type="hidden" name="entrada" id="entrada" value="{{ old('entrada') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="parcelas">Nº de Parcelas</label>
                            <div class="input-group">
                                <input type="number" name="parcelas" id="parcelas" class="form-control"
                                    value="{{ old('parcelas') }}" min="1" max="120" placeholder="Ex: 48">
                                <div class="input-group-append"><span class="input-group-text">x</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label for="observacoes">Observações</label>
                    <textarea name="observacoes" id="observacoes" class="form-control" rows="2"
                        placeholder="Informações adicionais sobre a negociação...">{{ old('observacoes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── Veículo de Troca ────────────────────────────────────────────── --}}
        <div id="cardTroca" style="display:none">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exchange-alt mr-2"></i>Veículo de Troca</h3>
                    <div class="card-tools">
                        <span class="badge badge-warning px-2 py-1">Será cadastrado no estoque como Disponível</span>
                    </div>
                </div>
                <div class="card-body">

                    {{-- Valor Avaliado (destaque) --}}
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="valorTrocaDisplay"><i class="fas fa-tag mr-1 text-warning"></i>Valor Avaliado na Troca *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text font-weight-bold text-warning">R$</span></div>
                                    <input type="text" id="valorTrocaDisplay"
                                        class="form-control font-weight-bold @error('valor_troca') is-invalid @enderror"
                                        placeholder="0,00"
                                        value="{{ old('valor_troca') ? number_format(old('valor_troca'), 2, ',', '.') : '' }}"
                                        autocomplete="off">
                                </div>
                                <input type="hidden" name="valor_troca" id="valorTroca" value="{{ old('valor_troca') }}">
                                @error('valor_troca')<span class="text-danger small">{{ $message }}</span>@enderror
                                <small class="text-muted">Quanto este veículo vale na troca</small>
                            </div>
                        </div>
                    </div>

                    <hr class="mt-2 mb-3">
                    <p class="text-muted font-weight-bold mb-2 small text-uppercase"><i class="fas fa-car mr-1"></i>Identificação</p>

                    {{-- Identificação --}}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="troca_marca">Marca *</label>
                                <input type="text" name="troca_marca" id="troca_marca"
                                    class="form-control @error('troca_marca') is-invalid @enderror"
                                    value="{{ old('troca_marca') }}"
                                    placeholder="Digite para buscar..."
                                    list="fipe_marcas_list"
                                    autocomplete="off">
                                <datalist id="fipe_marcas_list"></datalist>
                                @error('troca_marca')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="troca_modelo">Modelo *</label>
                                <input type="text" name="troca_modelo" id="troca_modelo"
                                    class="form-control @error('troca_modelo') is-invalid @enderror"
                                    value="{{ old('troca_modelo') }}"
                                    placeholder="— Selecione a marca primeiro —"
                                    list="fipe_modelos_list"
                                    autocomplete="off" disabled>
                                <datalist id="fipe_modelos_list"></datalist>
                                @error('troca_modelo')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="troca_cor">Cor *</label>
                                <input type="text" name="troca_cor" id="troca_cor"
                                    class="form-control @error('troca_cor') is-invalid @enderror"
                                    value="{{ old('troca_cor') }}" placeholder="Ex: Prata">
                                @error('troca_cor')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="troca_versao">Versão</label>
                                <input type="text" name="troca_versao" id="troca_versao"
                                    class="form-control"
                                    value="{{ old('troca_versao') }}" placeholder="Opcional">
                            </div>
                        </div>
                    </div>

                    {{-- FIPE: Ano + preço de referência (aparece ao selecionar modelo) --}}
                    <div id="fipeAnoRow" class="rounded mb-3 p-3" style="display:none; background:#f0f4ff; border:1px solid #c8d8f8">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="fipe_ano_select" class="text-primary font-weight-bold small"><i class="fas fa-search-dollar mr-1"></i>Tabela FIPE — Selecionar ano</label>
                                    <select id="fipe_ano_select" class="form-control">
                                        <option value="">— Selecionar ano —</option>
                                    </select>
                                    <small class="text-muted">Preenche o ano e sugere o valor</small>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div id="fipePrecoInfo"></div>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted font-weight-bold mb-2 small text-uppercase"><i class="fas fa-cog mr-1"></i>Dados Técnicos</p>

                    {{-- Dados técnicos --}}
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="troca_ano_fabricacao">Ano Fab. *</label>
                                <input type="text" name="troca_ano_fabricacao" id="troca_ano_fabricacao"
                                    class="form-control mask-ano @error('troca_ano_fabricacao') is-invalid @enderror"
                                    value="{{ old('troca_ano_fabricacao') }}" placeholder="{{ date('Y') }}"
                                    maxlength="4" inputmode="numeric">
                                @error('troca_ano_fabricacao')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="troca_ano_modelo">Ano Mod. *</label>
                                <input type="text" name="troca_ano_modelo" id="troca_ano_modelo"
                                    class="form-control mask-ano @error('troca_ano_modelo') is-invalid @enderror"
                                    value="{{ old('troca_ano_modelo') }}" placeholder="{{ date('Y') + 1 }}"
                                    maxlength="4" inputmode="numeric">
                                @error('troca_ano_modelo')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="troca_km_display">Quilometragem *</label>
                                <div class="input-group">
                                    <input type="text" id="troca_km_display"
                                        class="form-control @error('troca_km') is-invalid @enderror"
                                        value="{{ old('troca_km') ? number_format(old('troca_km'), 0, ',', '.') : '' }}"
                                        placeholder="45.000" autocomplete="off" inputmode="numeric">
                                    <div class="input-group-append"><span class="input-group-text">km</span></div>
                                </div>
                                <input type="hidden" name="troca_km" id="troca_km" value="{{ old('troca_km') }}">
                                @error('troca_km')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="troca_categoria">Categoria *</label>
                                <select name="troca_categoria" id="troca_categoria"
                                    class="form-control @error('troca_categoria') is-invalid @enderror">
                                    <option value="">— Selec. —</option>
                                    @foreach(['hatch'=>'Hatch','sedan'=>'Sedan','suv'=>'SUV','pickup'=>'Pickup','van'=>'Van','esportivo'=>'Esportivo','outro'=>'Outro'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ (old('troca_categoria') === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                                @error('troca_categoria')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="troca_combustivel">Combustível *</label>
                                <select name="troca_combustivel" id="troca_combustivel"
                                    class="form-control @error('troca_combustivel') is-invalid @enderror">
                                    <option value="">— Selecionar —</option>
                                    @foreach(['flex'=>'Flex','gasolina'=>'Gasolina','etanol'=>'Etanol','diesel'=>'Diesel','gnv'=>'GNV','hibrido'=>'Híbrido','eletrico'=>'Elétrico'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ (old('troca_combustivel') === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                                @error('troca_combustivel')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="troca_transmissao">Transmissão *</label>
                                <select name="troca_transmissao" id="troca_transmissao"
                                    class="form-control @error('troca_transmissao') is-invalid @enderror">
                                    <option value="">— Selecionar —</option>
                                    @foreach(['manual'=>'Manual','automatico'=>'Automático','cvt'=>'CVT','semi_automatico'=>'Semi-automático'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ (old('troca_transmissao') === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                                @error('troca_transmissao')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="callout callout-info mb-0 mt-3" style="padding:10px 14px">
                                <i class="fas fa-info-circle mr-1"></i>
                                O veículo será criado no estoque como <strong>Disponível</strong>. Edite-o depois para adicionar fotos e opcionais.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Botões --}}
        <div class="pb-4">
            <button type="submit" class="btn btn-success btn-lg px-4">
                <i class="fas fa-save mr-2"></i>Registrar Venda
            </button>
            <a href="{{ route('admin.sales.index') }}" class="btn btn-default btn-lg ml-2">Cancelar</a>
        </div>

    </form>

    {{-- ── Modal Novo Cliente ─────────────────────────────────────────────── --}}
    <div class="modal fade" id="modalNovoCliente" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white"><i class="fas fa-user-plus mr-2"></i>Cadastrar Novo Cliente</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="modalClienteErros" class="alert alert-danger d-none"></div>

                    {{-- Card: cliente existente com mesmo CPF --}}
                    <div id="modalClienteExistente" class="d-none">
                        <div class="callout callout-warning">
                            <h6 class="mb-1"><i class="fas fa-user-check mr-1"></i>CPF já cadastrado</h6>
                            <p class="mb-2 text-muted small">Este CPF pertence ao cliente abaixo. Deseja usá-lo?</p>
                            <div id="clienteExistenteInfo" class="mb-3 font-weight-bold"></div>
                            <button type="button" class="btn btn-success btn-sm" id="btnUsarClienteExistente">
                                <i class="fas fa-check mr-1"></i>Sim, usar este cliente
                            </button>
                            <button type="button" class="btn btn-default btn-sm ml-2" id="btnIgnorarClienteExistente">
                                Cancelar
                            </button>
                        </div>
                    </div>

                    <div id="modalClienteForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nome Completo *</label>
                                <input type="text" id="mc_nome" class="form-control" placeholder="Nome do cliente" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>CPF</label>
                                <input type="text" id="mc_cpf" class="form-control" placeholder="000.000.000-00" maxlength="14" inputmode="numeric">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Telefone *</label>
                                <input type="text" id="mc_telefone" class="form-control" placeholder="(27) 99999-9999" maxlength="15" inputmode="numeric" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label>E-mail</label>
                                <input type="email" id="mc_email" class="form-control" placeholder="email@exemplo.com">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label>Cidade</label>
                                <input type="text" id="mc_cidade" class="form-control" placeholder="Ex: Vitória">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label>Estado</label>
                                <input type="text" id="mc_estado" class="form-control" placeholder="ES" maxlength="2" style="text-transform:uppercase">
                            </div>
                        </div>
                    </div>
                    </div>{{-- /modalClienteForm --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary btn-lg px-4" id="btnSalvarCliente">
                        <i class="fas fa-save mr-1"></i>Salvar e Selecionar
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('js')
<script>
// ── Máscara de moeda (R$ 52.900,00) ──────────────────────────────────────────
function applyCurrencyMask(displayEl, hiddenEl) {
    function format(val) {
        val = val.replace(/\D/g, '');
        if (!val) return '';
        return (parseInt(val, 10) / 100).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    displayEl.addEventListener('input', function() {
        const raw = this.value.replace(/\D/g, '');
        this.value = raw ? format(this.value) : '';
        hiddenEl.value = raw ? (parseInt(raw, 10) / 100).toFixed(2) : '';
    });
    // Inicializar hidden a partir do display (para old() na volta de validação)
    if (displayEl.value) {
        const raw = displayEl.value.replace(/\D/g, '');
        hiddenEl.value = raw ? (parseInt(raw, 10) / 100).toFixed(2) : '';
    }
}

// ── Máscara de KM (45.000) ────────────────────────────────────────────────────
function applyKmMask(displayEl, hiddenEl) {
    displayEl.addEventListener('input', function() {
        const raw = this.value.replace(/\D/g, '');
        this.value = raw ? parseInt(raw, 10).toLocaleString('pt-BR') : '';
        hiddenEl.value = raw || '';
    });
    if (displayEl.value) {
        hiddenEl.value = displayEl.value.replace(/\D/g, '');
    }
}

// ── Máscara de ano (4 dígitos numéricos) ──────────────────────────────────────
document.querySelectorAll('.mask-ano').forEach(function(el) {
    el.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 4);
    });
});

// ── Aplicar máscaras ──────────────────────────────────────────────────────────
applyCurrencyMask(
    document.getElementById('precoVendaDisplay'),
    document.getElementById('precoVenda')
);
applyCurrencyMask(
    document.getElementById('entradaDisplay'),
    document.getElementById('entrada')
);
applyCurrencyMask(
    document.getElementById('valorTrocaDisplay'),
    document.getElementById('valorTroca')
);
applyKmMask(
    document.getElementById('troca_km_display'),
    document.getElementById('troca_km')
);

// ── Preenche preço ao selecionar veículo ──────────────────────────────────────
document.getElementById('vehicle_id').addEventListener('change', function() {
    const opt  = this.options[this.selectedIndex];
    const preco = opt.dataset.preco;
    if (preco) {
        const num = parseFloat(preco);
        document.getElementById('precoVendaDisplay').value = num.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('precoVenda').value = num.toFixed(2);
    }
});

// ── Toggle de campos por tipo de pagamento ────────────────────────────────────
function togglePaymentFields() {
    const tipo = document.getElementById('tipoPagamento').value;

    document.getElementById('financiamentoFields').style.display =
        (tipo === 'financiado') ? 'flex' : 'none';

    var mostrarTroca = tipo === 'permuta' || tipo === 'misto';
    document.getElementById('cardTroca').style.display = mostrarTroca ? 'block' : 'none';
    if (mostrarTroca) fipeInicializar();

    const labels = {
        'permuta':    'Valor do Negócio *',
        'misto':      'Valor em Dinheiro *',
    };
    document.getElementById('labelPrecoVenda').textContent = labels[tipo] || 'Preço de Venda *';
}

// Inicializar ao carregar (restaura estado após erro de validação)
togglePaymentFields();

// ── Máscaras + Title Case do modal de cliente ─────────────────────────────────
var mcSmallWords = ['de','da','do','das','dos','e','em','com','por','para','a','o','as','os'];
function mcTitleCase(str) {
    return str.toLowerCase().split(' ').map(function(word, i) {
        if (!word) return word;
        if (i > 0 && mcSmallWords.includes(word)) return word;
        return word.charAt(0).toUpperCase() + word.slice(1);
    }).join(' ');
}
document.getElementById('mc_nome').addEventListener('blur', function() {
    if (this.value.trim()) this.value = mcTitleCase(this.value.trim());
});

var cpfCheckTimer = null;
document.getElementById('mc_cpf').addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 9)      v = v.replace(/^(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
    else if (v.length > 6) v = v.replace(/^(\d{3})(\d{3})(\d{1,3})/,        '$1.$2.$3');
    else if (v.length > 3) v = v.replace(/^(\d{3})(\d{1,3})/,               '$1.$2');
    this.value = v;

    clearTimeout(cpfCheckTimer);
    if (v.replace(/\D/g, '').length === 11) {
        cpfCheckTimer = setTimeout(function() { verificarCpf(v); }, 300);
    }
});

function verificarCpf(cpf) {
    fetch('{{ route('admin.customers.cpf-check') }}?cpf=' + encodeURIComponent(cpf), {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data && data.id) {
            selecionarClienteExistente(data);
        }
    });
}

document.getElementById('mc_telefone').addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 10)     v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
    else if (v.length > 6) v = v.replace(/^(\d{2})(\d{4,5})(\d{0,4})/, '($1) $2-$3');
    else if (v.length > 2) v = v.replace(/^(\d{2})(\d+)/,              '($1) $2');
    this.value = v;
});

document.getElementById('mc_estado').addEventListener('input', function() {
    this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');
});

// ── Submit do modal via AJAX ──────────────────────────────────────────────────
document.getElementById('btnSalvarCliente').addEventListener('click', function() {
    const btn     = this;
    const errosEl = document.getElementById('modalClienteErros');
    const nome    = document.getElementById('mc_nome').value.trim();
    const cpf     = document.getElementById('mc_cpf').value.trim();
    const tel     = document.getElementById('mc_telefone').value.trim();
    const email   = document.getElementById('mc_email').value.trim();
    const cidade  = document.getElementById('mc_cidade').value.trim();
    const estado  = document.getElementById('mc_estado').value.trim();

    errosEl.classList.add('d-none');
    errosEl.innerHTML = '';

    if (!nome || !tel) {
        errosEl.innerHTML = 'Nome e Telefone são obrigatórios.';
        errosEl.classList.remove('d-none');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Salvando...';

    fetch('{{ route('admin.customers.quick-store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                         || '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ nome, cpf, telefone: tel, email, cidade, estado }),
    })
    .then(r => r.json().then(data => ({ status: r.status, data })))
    .then(({ status, data }) => {
        if (data.errors) {
            const msgs = Object.values(data.errors).flat().join('<br>');
            errosEl.innerHTML = msgs;
            errosEl.classList.remove('d-none');
            return;
        }

        adicionarClienteNoSelect(data);
        showToast('Cliente cadastrado e selecionado!', 'success');
    })
    .catch(() => {
        errosEl.innerHTML = 'Erro ao salvar. Tente novamente.';
        errosEl.classList.remove('d-none');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save mr-1"></i>Salvar e Selecionar';
    });
});

// ── Helpers do modal ──────────────────────────────────────────────────────────
function adicionarClienteNoSelect(data) {
    const select = document.getElementById('customer_id');
    const label  = data.nome + (data.cpf ? ' — ' + data.cpf : '') + (data.telefone ? ' · ' + data.telefone : '');
    const opt    = new Option(label, data.id, false, false);

    if (typeof $ !== 'undefined' && $(select).data('select2')) {
        $(select).append(opt);
        $(select).val(data.id).trigger('change');
    } else {
        opt.selected = true;
        select.appendChild(opt);
    }

    $('#modalNovoCliente').modal('hide');
    ['mc_nome','mc_cpf','mc_telefone','mc_email','mc_cidade','mc_estado'].forEach(function(id) {
        document.getElementById(id).value = '';
    });
}

var clienteExistenteData = null;

function selecionarClienteExistente(data) {
    clienteExistenteData = data;
    var localidade = [data.cidade, data.estado].filter(Boolean).join('/');
    document.getElementById('clienteExistenteInfo').innerHTML =
        '<i class="fas fa-user mr-1 text-warning"></i>' + data.nome +
        (data.cpf      ? ' &mdash; CPF: <code>' + data.cpf + '</code>' : '') +
        (data.telefone ? ' &middot; ' + data.telefone : '') +
        (localidade    ? ' &middot; ' + localidade : '');
    document.getElementById('modalClienteExistente').classList.remove('d-none');
    document.getElementById('modalClienteForm').classList.add('d-none');
    document.getElementById('btnSalvarCliente').classList.add('d-none');
}

document.getElementById('btnUsarClienteExistente').addEventListener('click', function() {
    if (clienteExistenteData) {
        adicionarClienteNoSelect(clienteExistenteData);
        showToast('Cliente existente selecionado!', 'success');
        clienteExistenteData = null;
    }
});

document.getElementById('btnIgnorarClienteExistente').addEventListener('click', function() {
    document.getElementById('modalClienteExistente').classList.add('d-none');
    document.getElementById('modalClienteForm').classList.remove('d-none');
    document.getElementById('btnSalvarCliente').classList.remove('d-none');
    clienteExistenteData = null;
});

// ── Integração FIPE ────────────────────────────────────────────────────────────
var fipeMarcaCodigoAtual  = null;
var fipeModeloCodigoAtual = null;
var fipeMarcasCarregadas  = false;
var fipeMarcasMap  = {};   // nome → codigo
var fipeModelosMap = {};   // nome → codigo
var fipeOldMarca  = '{{ addslashes(old("troca_marca", "")) }}';
var fipeOldModelo = '{{ addslashes(old("troca_modelo", "")) }}';

function fipeInicializar() {
    if (fipeMarcasCarregadas) return;
    fipeMarcasCarregadas = true;

    var marcaInput = document.getElementById('troca_marca');
    marcaInput.placeholder = 'Carregando marcas...';
    marcaInput.disabled = true;

    fetch('https://parallelum.com.br/fipe/api/v1/carros/marcas')
        .then(function(r) { return r.json(); })
        .then(function(marcas) {
            var dl = document.getElementById('fipe_marcas_list');
            dl.innerHTML = '';
            marcas.forEach(function(m) {
                fipeMarcasMap[m.nome] = m.codigo;
                var opt = document.createElement('option');
                opt.value = m.nome;
                dl.appendChild(opt);
            });
            marcaInput.placeholder = 'Digite para buscar...';
            marcaInput.disabled = false;
            // Restaurar valor antigo após erro de validação
            if (fipeOldMarca && fipeMarcasMap[fipeOldMarca]) {
                fipeMarcaCodigoAtual = fipeMarcasMap[fipeOldMarca];
                fipeCarregarModelos(fipeMarcaCodigoAtual, fipeOldModelo || null);
            }
        })
        .catch(function() {
            fipeMarcasCarregadas = false;
            marcaInput.placeholder = 'Erro ao carregar — recarregue';
            marcaInput.disabled = false;
        });
}

function fipeCarregarModelos(codigoMarca, selecionarModelo) {
    fipeModelosMap = {};
    fipeModeloCodigoAtual = null;
    var modeloInput = document.getElementById('troca_modelo');
    modeloInput.disabled = true;
    modeloInput.placeholder = 'Carregando modelos...';
    document.getElementById('fipe_modelos_list').innerHTML = '';

    fetch('https://parallelum.com.br/fipe/api/v1/carros/marcas/' + codigoMarca + '/modelos')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var dl = document.getElementById('fipe_modelos_list');
            data.modelos.forEach(function(m) {
                fipeModelosMap[m.nome] = m.codigo;
                var opt = document.createElement('option');
                opt.value = m.nome;
                dl.appendChild(opt);
            });
            modeloInput.disabled = false;
            modeloInput.placeholder = 'Digite para buscar...';
            if (selecionarModelo) {
                modeloInput.value = selecionarModelo;
                fipeModeloCodigoAtual = fipeModelosMap[selecionarModelo] || null;
            }
        })
        .catch(function() {
            modeloInput.placeholder = 'Erro ao carregar modelos';
            modeloInput.disabled = false;
        });
}

document.getElementById('troca_marca').addEventListener('input', function() {
    var val = this.value.trim();
    var code = fipeMarcasMap[val] || null;
    document.getElementById('fipeAnoRow').style.display = 'none';
    document.getElementById('fipePrecoInfo').innerHTML = '';

    if (code && code !== fipeMarcaCodigoAtual) {
        fipeMarcaCodigoAtual = code;
        document.getElementById('troca_modelo').value = '';
        fipeCarregarModelos(code, null);
    } else if (!code) {
        fipeMarcaCodigoAtual = null;
        fipeModeloCodigoAtual = null;
        fipeModelosMap = {};
        document.getElementById('fipe_modelos_list').innerHTML = '';
        document.getElementById('troca_modelo').value = '';
        document.getElementById('troca_modelo').disabled = true;
        document.getElementById('troca_modelo').placeholder = '— Selecione a marca primeiro —';
    }
});

document.getElementById('troca_modelo').addEventListener('input', function() {
    var val = this.value.trim();
    var code = fipeModelosMap[val] || null;
    document.getElementById('fipePrecoInfo').innerHTML = '';

    if (code && fipeMarcaCodigoAtual) {
        fipeModeloCodigoAtual = code;
        var anoSel = document.getElementById('fipe_ano_select');
        anoSel.innerHTML = '<option value="">Carregando anos...</option>';
        document.getElementById('fipeAnoRow').style.display = 'block';

        fetch('https://parallelum.com.br/fipe/api/v1/carros/marcas/' + fipeMarcaCodigoAtual + '/modelos/' + code + '/anos')
            .then(function(r) { return r.json(); })
            .then(function(anos) {
                anoSel.innerHTML = '<option value="">— Selecionar ano —</option>';
                anos.forEach(function(a) {
                    var opt = document.createElement('option');
                    opt.value = a.codigo;
                    opt.textContent = a.nome;
                    anoSel.appendChild(opt);
                });
            })
            .catch(function() {
                anoSel.innerHTML = '<option value="">Erro ao carregar anos</option>';
            });
    } else {
        fipeModeloCodigoAtual = null;
        document.getElementById('fipeAnoRow').style.display = 'none';
    }
});

document.getElementById('fipe_ano_select').addEventListener('change', function() {
    var codigoAno = this.value;
    var infoEl = document.getElementById('fipePrecoInfo');

    if (!codigoAno || !fipeModeloCodigoAtual || !fipeMarcaCodigoAtual) {
        infoEl.innerHTML = '';
        return;
    }

    // Auto-preenche Ano Fabricação/Modelo a partir do código FIPE "YYYY-N"
    var anoFab = codigoAno.split('-')[0];
    if (/^\d{4}$/.test(anoFab)) {
        document.getElementById('troca_ano_fabricacao').value = anoFab;
        document.getElementById('troca_ano_modelo').value = anoFab;
    }

    infoEl.innerHTML = '<span class="text-muted small"><i class="fas fa-spinner fa-spin mr-1"></i>Consultando tabela FIPE...</span>';

    fetch('https://parallelum.com.br/fipe/api/v1/carros/marcas/' + fipeMarcaCodigoAtual + '/modelos/' + fipeModeloCodigoAtual + '/anos/' + codigoAno)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var precoStr  = data.Valor || '';
            var precoNum  = precoStr.replace('R$', '').trim().replace(/\./g, '').replace(',', '.');
            var precoFloat = parseFloat(precoNum);
            infoEl.innerHTML =
                '<div class="d-flex align-items-center flex-wrap" style="gap:10px">' +
                    '<span class="badge badge-info px-2 py-1" style="font-size:0.9rem">' +
                        '<i class="fas fa-chart-line mr-1"></i>Tabela FIPE: <strong>' + precoStr + '</strong>' +
                    '</span>' +
                    (precoFloat > 0
                        ? '<button type="button" class="btn btn-sm btn-outline-warning" id="btnUsarFipe" data-valor="' + precoFloat.toFixed(2) + '">' +
                              '<i class="fas fa-tag mr-1"></i>Usar como valor de troca' +
                          '</button>'
                        : '') +
                '</div>';
            var btnFipe = document.getElementById('btnUsarFipe');
            if (btnFipe) {
                btnFipe.addEventListener('click', function() {
                    var val = parseFloat(this.dataset.valor);
                    document.getElementById('valorTrocaDisplay').value = val.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
                    document.getElementById('valorTroca').value = val.toFixed(2);
                    document.getElementById('valorTrocaDisplay').focus();
                });
            }
        })
        .catch(function() {
            infoEl.innerHTML = '<span class="text-danger small"><i class="fas fa-exclamation-triangle mr-1"></i>Erro ao consultar tabela FIPE.</span>';
        });
});

// Limpar erros ao abrir o modal
$('#modalNovoCliente').on('show.bs.modal', function() {
    document.getElementById('modalClienteErros').classList.add('d-none');
    document.getElementById('modalClienteExistente').classList.add('d-none');
    document.getElementById('modalClienteForm').classList.remove('d-none');
    document.getElementById('btnSalvarCliente').classList.remove('d-none');
    clienteExistenteData = null;
});
</script>
@endsection
