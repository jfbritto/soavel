@extends('adminlte::page')

@section('title', $partner->nome . ' — ' . config('adminlte.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-user-tie mr-2"></i>{{ $partner->nome }}</h1>
        <a href="{{ route('admin.partners.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Voltar
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <h3 class="card-title text-muted text-uppercase" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">Dados do Sócio</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.partners.update', $partner) }}">
                        @csrf @method('PUT')

                        <div class="form-group">
                            <label class="font-weight-bold">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                                value="{{ old('nome', $partner->nome) }}" maxlength="150" required>
                            @error('nome')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">CPF</label>
                                    <input type="text" name="cpf" id="cpfInput"
                                        class="form-control @error('cpf') is-invalid @enderror"
                                        value="{{ old('cpf', $partner->cpf) }}" maxlength="14" placeholder="000.000.000-00">
                                    @error('cpf')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Telefone</label>
                                    <input type="text" name="telefone" id="telefoneInput"
                                        class="form-control @error('telefone') is-invalid @enderror"
                                        value="{{ old('telefone', $partner->telefone) }}" maxlength="20" placeholder="(00) 00000-0000">
                                    @error('telefone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">E-mail</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $partner->email) }}" maxlength="150">
                            @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Observações</label>
                            <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes', $partner->observacoes) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end" style="gap:8px">
                            <a href="{{ route('admin.partners.index') }}" class="btn btn-default">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Veículos vinculados --}}
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <h3 class="card-title text-muted text-uppercase" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">
                        Veículos com Participação
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($partner->vehicles->isEmpty())
                        <p class="text-muted text-center py-4 mb-0" style="font-size:.88rem">
                            Nenhum veículo vinculado.
                        </p>
                    @else
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6c757d">
                                <th class="border-top-0 pl-3">Veículo</th>
                                <th class="border-top-0 text-right">Participação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($partner->vehicles as $vehicle)
                            <tr>
                                <td class="pl-3 align-middle" style="font-size:.88rem">
                                    <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="text-dark">
                                        {{ $vehicle->titulo }}
                                    </a>
                                    <span class="badge badge-{{ $vehicle->status_color }} ml-1" style="font-size:.7rem">{{ $vehicle->status_label }}</span>
                                </td>
                                <td class="align-middle text-right pr-3">
                                    <span class="font-weight-bold" style="font-size:.9rem">{{ number_format($vehicle->pivot->percentual, 0, ',', '.') }}%</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.getElementById('cpfInput').addEventListener('input', function () {
    var v = this.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 9) v = v.slice(0,3)+'.'+v.slice(3,6)+'.'+v.slice(6,9)+'-'+v.slice(9);
    else if (v.length > 6) v = v.slice(0,3)+'.'+v.slice(3,6)+'.'+v.slice(6);
    else if (v.length > 3) v = v.slice(0,3)+'.'+v.slice(3);
    this.value = v;
});

document.getElementById('telefoneInput').addEventListener('input', function () {
    var v = this.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 10) v = '('+v.slice(0,2)+') '+v.slice(2,7)+'-'+v.slice(7);
    else if (v.length > 6) v = '('+v.slice(0,2)+') '+v.slice(2,6)+'-'+v.slice(6);
    else if (v.length > 2) v = '('+v.slice(0,2)+') '+v.slice(2);
    this.value = v;
});
</script>
@endsection
