@extends('adminlte::page')

@section('title', 'Novo Lead — Soavel')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-phone-alt mr-2"></i>Novo Lead</h1>
        <a href="{{ route('admin.leads.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Voltar
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.leads.store') }}" method="POST">
        @csrf

        @include('admin.leads._form', ['lead' => null])

        <div class="d-flex justify-content-end mb-4" style="gap:8px">
            <a href="{{ route('admin.leads.index') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i>Salvar Lead
            </button>
        </div>
    </form>
</div>
@endsection
