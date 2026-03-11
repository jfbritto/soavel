@extends('adminlte::page')

@section('title', 'Despesas — Soavel')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-file-invoice-dollar mr-2"></i>Despesas</h1>
        <button type="button" class="btn btn-sm btn-primary" id="btnNovaDespesa">
            <i class="fas fa-plus mr-1"></i>Nova Despesa
        </button>
    </div>
@endsection

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-baseline mb-3" style="gap:6px">
        <span class="text-muted" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.06em;font-weight:600">Total do mês</span>
        <span class="font-weight-bold text-danger" style="font-size:1.1rem">R$ {{ number_format($totalMes, 2, ',', '.') }}</span>
    </div>

    <div class="card shadow-sm">
        <div class="card-body py-2">
            <form method="GET" class="form-inline flex-wrap" style="gap:8px">
                <select name="categoria" class="form-control form-control-sm">
                    <option value="">Todas as categorias</option>
                    @foreach(['manutencao' => 'Manutenção', 'documentacao' => 'Documentação', 'limpeza' => 'Limpeza', 'combustivel' => 'Combustível', 'comissao' => 'Comissão', 'outros' => 'Outros'] as $val => $label)
                        <option value="{{ $val }}" {{ (request('categoria') === $val) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="vehicle_id" class="form-control form-control-sm" style="max-width:200px">
                    <option value="">Todos os veículos</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" {{ (request('vehicle_id') == $v->id) ? 'selected' : '' }}>{{ $v->marca }} {{ $v->modelo }} ({{ $v->ano_modelo }})</option>
                    @endforeach
                </select>
                <select name="mes" class="form-control form-control-sm">
                    <option value="">Todos os meses</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ (request('mes') == $m) ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->monthName }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary">Filtrar</button>
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-sm btn-link text-muted">Limpar</a>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6c757d;border-top:0">
                        <th class="border-top-0 pl-3 d-none d-md-table-cell">Data</th>
                        <th class="border-top-0">Descrição</th>
                        <th class="border-top-0 d-none d-md-table-cell">Categoria</th>
                        <th class="border-top-0 d-none d-lg-table-cell">Veículo</th>
                        <th class="border-top-0 text-right">Valor</th>
                        <th class="border-top-0" style="width:80px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                    <tr>
                        <td class="pl-3 align-middle text-muted d-none d-md-table-cell" style="font-size:.85rem;white-space:nowrap">{{ $expense->data->format('d/m/Y') }}</td>
                        <td class="align-middle" style="font-size:.9rem">{{ $expense->descricao }}</td>
                        <td class="align-middle d-none d-md-table-cell"><span class="badge badge-light border" style="font-size:.75rem">{{ $expense->categoria_label }}</span></td>
                        <td class="align-middle d-none d-lg-table-cell" style="font-size:.88rem">
                            @if($expense->vehicle_id && $expense->vehicle)
                                <a href="{{ route('admin.vehicles.show', $expense->vehicle->id) }}" class="text-dark">{{ $expense->vehicle->titulo }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="align-middle text-right font-weight-bold" style="font-size:.9rem;color:#c0392b;white-space:nowrap">{{ $expense->valor_formatado }}</td>
                        <td class="align-middle pr-3" style="white-space:nowrap;text-align:right">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-editar"
                                style="font-size:.75rem;padding:2px 8px"
                                title="Editar esta despesa"
                                data-id="{{ $expense->id }}"
                                data-descricao="{{ $expense->descricao }}"
                                data-categoria="{{ $expense->categoria }}"
                                data-valor="{{ number_format($expense->valor, 2, ',', '.') }}"
                                data-valor-raw="{{ $expense->valor }}"
                                data-data="{{ $expense->data->format('Y-m-d') }}"
                                data-vehicle="{{ $expense->vehicle_id ?? '' }}"
                                data-url="{{ route('admin.expenses.update', $expense) }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST"
                                class="d-inline" data-confirm="Excluir esta despesa?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                    style="font-size:.75rem;padding:2px 8px;color:#dc3545;border-color:#dee2e6"
                                    title="Excluir esta despesa permanentemente">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma despesa encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
        <div class="card-footer border-top-0" style="background:#fafafa">{{ $expenses->links() }}</div>
        @endif
    </div>
</div>

{{-- ── Modal Nova/Editar Despesa ───────────────────────────── --}}
<div class="modal fade" id="modalDespesa" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formDespesa" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="modalTitulo">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>Nova Despesa
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        <strong>Corrija os erros abaixo:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="font-weight-bold">Descrição <span class="text-danger">*</span></label>
                                <input type="text" name="descricao" id="modalDescricao"
                                    class="form-control @error('descricao') is-invalid @enderror"
                                    value="{{ old('descricao') }}"
                                    placeholder="Ex: Troca de óleo, IPVA, Lavagem..."
                                    maxlength="200" required>
                                @error('descricao')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Categoria <span class="text-danger">*</span></label>
                                <select name="categoria" id="modalCategoria" class="form-control @error('categoria') is-invalid @enderror" required>
                                    @foreach(['manutencao' => 'Manutenção', 'documentacao' => 'Documentação', 'limpeza' => 'Limpeza', 'combustivel' => 'Combustível', 'comissao' => 'Comissão', 'outros' => 'Outros'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('categoria') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label class="font-weight-bold">Valor <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text font-weight-bold">R$</span>
                                    </div>
                                    <input type="text" id="modalValorDisplay"
                                        class="form-control @error('valor') is-invalid @enderror"
                                        placeholder="0,00"
                                        inputmode="numeric"
                                        value="{{ old('valor') ? number_format(old('valor'), 2, ',', '.') : '' }}"
                                        required>
                                    <input type="hidden" name="valor" id="modalValor" value="{{ old('valor') }}">
                                </div>
                                @error('valor')
                                <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Data <span class="text-danger">*</span></label>
                                {{-- campo visual com máscara DD/MM/AAAA --}}
                                <input type="text" id="modalData"
                                    class="form-control @error('data') is-invalid @enderror"
                                    placeholder="DD/MM/AAAA"
                                    maxlength="10"
                                    inputmode="numeric"
                                    value="{{ old('data') ? \Carbon\Carbon::parse(old('data'))->format('d/m/Y') : '' }}"
                                    required>
                                {{-- campo real enviado ao servidor em formato Y-m-d --}}
                                <input type="hidden" name="data" id="modalDataISO"
                                    value="{{ old('data', now()->format('Y-m-d')) }}">
                                @error('data')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Veículo Relacionado <small class="text-muted font-weight-normal">(opcional)</small></label>
                        <select name="vehicle_id" id="modalVehicle" class="form-control">
                            <option value="">— Nenhum —</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>
                                    {{ $v->marca }} {{ $v->modelo }} {{ $v->versao }} ({{ $v->ano_modelo }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i><span id="btnSalvarTxt">Salvar Despesa</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
(function () {
    var storeUrl = '{{ route('admin.expenses.store') }}';

    // ── Máscara de moeda ──────────────────────────────────────
    var valDisplay = document.getElementById('modalValorDisplay');
    var valHidden  = document.getElementById('modalValor');

    function formatarMoeda(input) {
        var raw = input.value.replace(/\D/g, '');
        if (!raw) { input.value = ''; valHidden.value = ''; return; }
        var num = parseInt(raw, 10) / 100;
        valHidden.value = num.toFixed(2);
        input.value = num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    valDisplay.addEventListener('input', function () { formatarMoeda(this); });
    valDisplay.addEventListener('paste', function () { setTimeout(function () { formatarMoeda(valDisplay); }, 10); });

    // ── Máscara de data DD/MM/AAAA ────────────────────────────
    var dataInput = document.getElementById('modalData');
    var dataISO   = document.getElementById('modalDataISO');

    dataInput.addEventListener('input', function () {
        var v = this.value.replace(/\D/g, '');
        if (v.length > 2) v = v.slice(0,2) + '/' + v.slice(2);
        if (v.length > 5) v = v.slice(0,5) + '/' + v.slice(5,9);
        this.value = v;
        if (v.length === 10) {
            var p = v.split('/');
            dataISO.value = p[2] + '-' + p[1] + '-' + p[0];
        }
    });

    function isoParaDisplay(iso) {
        var p = iso.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    }

    // ── Abrir modal "Nova Despesa" ────────────────────────────
    document.getElementById('btnNovaDespesa').addEventListener('click', function () {
        document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-plus mr-2"></i>Nova Despesa';
        document.getElementById('btnSalvarTxt').textContent = 'Salvar Despesa';
        document.getElementById('formDespesa').action = storeUrl;
        document.getElementById('formMethod').value = 'POST';

        document.getElementById('modalDescricao').value = '';
        document.getElementById('modalCategoria').value = 'manutencao';
        valDisplay.value = '';
        valHidden.value  = '';
        var todayISO = new Date().toISOString().slice(0,10);
        dataISO.value   = todayISO;
        dataInput.value = isoParaDisplay(todayISO);
        document.getElementById('modalVehicle').value = '';

        $('#modalDespesa').modal('show');
    });

    // ── Abrir modal "Editar Despesa" ──────────────────────────
    document.querySelectorAll('.btn-editar').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var d = this.dataset;
            document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Despesa';
            document.getElementById('btnSalvarTxt').textContent = 'Atualizar Despesa';
            document.getElementById('formDespesa').action = d.url;
            document.getElementById('formMethod').value = 'PUT';

            document.getElementById('modalDescricao').value = d.descricao;
            document.getElementById('modalCategoria').value = d.categoria;
            valDisplay.value = d.valor;
            valHidden.value  = d.valorRaw;
            dataISO.value    = d.data;
            dataInput.value  = isoParaDisplay(d.data);
            document.getElementById('modalVehicle').value = d.vehicle || '';

            $('#modalDespesa').modal('show');
        });
    });

    // ── Reabrir modal se houver erros de validação ────────────
    @if($errors->any())
    $('#modalDespesa').modal('show');
    @endif

})();
</script>
@endsection
