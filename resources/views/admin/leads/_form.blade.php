@if($errors->any())
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <h5><i class="icon fas fa-ban"></i> Corrija os erros abaixo:</h5>
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="card card-primary card-outline shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-tag mr-2"></i>Identificação</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Nome *</label>
                    <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                        value="{{ old('nome', $lead?->nome) }}" placeholder="Nome completo" required>
                    @error('nome')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Telefone *</label>
                    <input type="text" name="telefone" id="telefone" class="form-control @error('telefone') is-invalid @enderror"
                        value="{{ old('telefone', $lead?->telefone) }}" placeholder="(27) 99999-9999" required>
                    @error('telefone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">E-mail</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $lead?->email) }}" placeholder="email@exemplo.com">
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline shadow-sm" style="border-top-color:#6c757d">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-car mr-2"></i>Interesse</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Veículo de Interesse</label>
                    <select name="vehicle_id" class="form-control">
                        <option value="">— Nenhum específico —</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" {{ (old('vehicle_id', $lead?->vehicle_id) == $v->id) ? 'selected' : '' }}>
                                {{ $v->marca }} {{ $v->modelo }} {{ $v->versao }} ({{ $v->ano_modelo }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Interesse Geral</label>
                    <input type="text" name="interesse" class="form-control"
                        value="{{ old('interesse', $lead?->interesse) }}"
                        placeholder="Ex: SUV até R$ 80.000, financiado...">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline shadow-sm" style="border-top-color:#6c757d">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-sliders-h mr-2"></i>Status e Atribuição</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Origem *</label>
                    <select name="origem" class="form-control" required>
                        @foreach(['site' => 'Site', 'whatsapp' => 'WhatsApp', 'presencial' => 'Presencial', 'indicacao' => 'Indicação', 'instagram' => 'Instagram', 'outro' => 'Outro'] as $val => $lbl)
                            <option value="{{ $val }}" {{ (old('origem', $lead?->origem ?? 'site') === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Status *</label>
                    <select name="status" class="form-control" required>
                        @foreach(['novo' => 'Novo', 'em_contato' => 'Em Contato', 'convertido' => 'Convertido', 'perdido' => 'Perdido'] as $val => $lbl)
                            <option value="{{ $val }}" {{ (old('status', $lead?->status ?? 'novo') === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">Atribuído a</label>
                    <select name="user_id" class="form-control">
                        <option value="">— Nenhum —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ (old('user_id', $lead?->user_id) == $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:.85rem">&nbsp;</label>
                    <div class="d-flex align-items-center" style="height:38px">
                        @if($lead)
                        <a href="https://wa.me/55{{ preg_replace('/\D/', '', $lead->telefone) }}"
                           target="_blank" class="btn btn-sm btn-success">
                            <i class="fab fa-whatsapp mr-1"></i>Abrir WhatsApp
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group mb-0">
                    <label class="font-weight-bold" style="font-size:.85rem">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3"
                        placeholder="Anotações sobre o contato, negociação, preferências...">{{ old('observacoes', $lead?->observacoes) }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>
