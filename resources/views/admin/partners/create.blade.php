@extends('adminlte::page')

@section('title', 'Novo Sócio — Soavel')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-user-tie mr-2"></i>Novo Sócio</h1>
        <a href="{{ route('admin.partners.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Voltar
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm" style="max-width:600px">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.partners.store') }}">
                @csrf

                <div class="form-group">
                    <label class="font-weight-bold">Nome <span class="text-danger">*</span></label>
                    <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                        value="{{ old('nome') }}" maxlength="150" required autofocus>
                    @error('nome')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">CPF</label>
                            <input type="text" name="cpf" id="cpfInput"
                                class="form-control @error('cpf') is-invalid @enderror"
                                value="{{ old('cpf') }}" maxlength="14" placeholder="000.000.000-00">
                            @error('cpf')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Telefone</label>
                            <input type="text" name="telefone" id="telefoneInput"
                                class="form-control @error('telefone') is-invalid @enderror"
                                value="{{ old('telefone') }}" maxlength="20" placeholder="(00) 00000-0000">
                            @error('telefone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">E-mail</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" maxlength="150">
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes') }}</textarea>
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
@endsection

@section('js')
<script>
// Máscara CPF
document.getElementById('cpfInput').addEventListener('input', function () {
    var v = this.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 9) v = v.slice(0,3)+'.'+v.slice(3,6)+'.'+v.slice(6,9)+'-'+v.slice(9);
    else if (v.length > 6) v = v.slice(0,3)+'.'+v.slice(3,6)+'.'+v.slice(6);
    else if (v.length > 3) v = v.slice(0,3)+'.'+v.slice(3);
    this.value = v;
});

// Máscara telefone
document.getElementById('telefoneInput').addEventListener('input', function () {
    var v = this.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 10) v = '('+v.slice(0,2)+') '+v.slice(2,7)+'-'+v.slice(7);
    else if (v.length > 6) v = '('+v.slice(0,2)+') '+v.slice(2,6)+'-'+v.slice(6);
    else if (v.length > 2) v = '('+v.slice(0,2)+') '+v.slice(2);
    this.value = v;
});
</script>
@endsection
