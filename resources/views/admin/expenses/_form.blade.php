@if($errors->any())
<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card">
    <div class="card-header"><h3 class="card-title">Dados da Despesa</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label>Descrição *</label>
                    <input type="text" name="descricao" class="form-control @error('descricao') is-invalid @enderror"
                        value="{{ old('descricao', $expense?->descricao) }}" required>
                    @error('descricao')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Categoria *</label>
                    <select name="categoria" class="form-control" required>
                        @foreach(['manutencao' => 'Manutenção', 'documentacao' => 'Documentação', 'limpeza' => 'Limpeza', 'combustivel' => 'Combustível', 'comissao' => 'Comissão', 'outros' => 'Outros'] as $val => $label)
                            <option value="{{ $val }}" {{ (old('categoria', $expense?->categoria) === $val) ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Valor *</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                        <input type="number" name="valor" step="0.01" class="form-control @error('valor') is-invalid @enderror"
                            value="{{ old('valor', $expense?->valor) }}" required>
                    </div>
                    @error('valor')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Data *</label>
                    <input type="date" name="data" class="form-control" value="{{ old('data', $expense?->data?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Veículo Relacionado <small class="text-muted">(opcional)</small></label>
            <select name="vehicle_id" class="form-control">
                <option value="">Nenhum</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" {{ (old('vehicle_id', $expense?->vehicle_id) == $v->id) ? 'selected' : '' }}>
                        {{ $v->marca }} {{ $v->modelo }} {{ $v->versao }} ({{ $v->ano_modelo }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>
