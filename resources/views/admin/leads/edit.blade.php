@extends('adminlte::page')

@section('title', 'Lead: ' . $lead->nome . ' — ' . config('adminlte.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-phone-alt mr-2"></i>{{ $lead->nome }}</h1>
        <a href="{{ route('admin.leads.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Voltar
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">

    <form action="{{ route('admin.leads.update', $lead) }}" method="POST">
        @csrf @method('PUT')

        @include('admin.leads._form', ['lead' => $lead])

        <div class="d-flex justify-content-between align-items-center mb-4">
            <button type="button" class="btn btn-sm btn-outline-danger"
                title="Excluir este lead permanentemente"
                onclick="document.getElementById('formDeleteLead').requestSubmit()">
                <i class="fas fa-trash mr-1"></i>Excluir
            </button>
            <div style="display:flex;gap:8px">
                <a href="{{ route('admin.leads.index') }}" class="btn btn-default">Cancelar</a>
                <button type="submit" class="btn btn-primary" title="Salvar alterações do lead">
                    <i class="fas fa-save mr-1"></i>Atualizar Lead
                </button>
            </div>
        </div>
    </form>

    <form id="formDeleteLead" action="{{ route('admin.leads.destroy', $lead) }}" method="POST"
          data-confirm="Excluir este lead permanentemente?">
        @csrf @method('DELETE')
    </form>

</div>
@endsection
