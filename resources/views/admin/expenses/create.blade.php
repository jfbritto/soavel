@extends('adminlte::page')
@section('title', 'Nova Despesa — Soavel')
@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-plus mr-2"></i>Nova Despesa</h1>
        <a href="{{ route('admin.expenses.index') }}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Voltar</a>
    </div>
@endsection
@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.expenses.store') }}" method="POST">
        @csrf
        @include('admin.expenses._form', ['expense' => null])
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Salvar</button>
            <a href="{{ route('admin.expenses.index') }}" class="btn btn-default ml-2">Cancelar</a>
        </div>
    </form>
</div>
@endsection
