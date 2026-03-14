@extends('adminlte::page')
@section('title', 'Novo Cliente — ' . config('adminlte.title'))
@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-user-plus mr-2"></i>Novo Cliente</h1>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Voltar</a>
    </div>
@endsection
@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.customers.store') }}" method="POST">
        @csrf
        @include('admin.customers._form', ['customer' => null])
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Salvar</button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-default ml-2">Cancelar</a>
        </div>
    </form>
</div>
@endsection
