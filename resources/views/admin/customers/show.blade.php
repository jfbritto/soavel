@extends('adminlte::page')
@section('title', $customer->nome . ' — ' . config('adminlte.title'))
@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-user mr-2"></i>{{ $customer->nome }}</h1>
        <div>
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-warning" title="Editar dados do cliente"><i class="fas fa-edit mr-1"></i>Editar</a>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-default ml-1" title="Voltar para a lista de clientes"><i class="fas fa-arrow-left mr-1"></i>Voltar</a>
        </div>
    </div>
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body text-center pt-4">
                    <i class="fas fa-user-circle fa-4x text-muted mb-3"></i>
                    <h3>{{ $customer->nome }}</h3>
                    <p class="text-muted">{{ $customer->cidade ? $customer->cidade.'/'.$customer->estado : '' }}</p>
                    <a href="https://wa.me/55{{ preg_replace('/\D/', '', $customer->telefone) }}" target="_blank" class="btn btn-success btn-sm"
                       title="Abrir conversa no WhatsApp">
                        <i class="fab fa-whatsapp mr-1"></i>{{ $customer->telefone }}
                    </a>
                </div>
                <div class="card-footer">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th>CPF</th><td>{{ $customer->cpf ?? '—' }}</td></tr>
                        <tr><th>E-mail</th><td>{{ $customer->email ?? '—' }}</td></tr>
                        <tr><th>Endereço</th><td>{{ $customer->endereco_completo ?: '—' }}</td></tr>
                        @if($customer->observacoes)
                        <tr><th>Obs.</th><td>{{ $customer->observacoes }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            {{-- Histórico de Compras --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-history mr-1"></i>Histórico de Compras ({{ $customer->sales->count() }})</h3></div>
                <div class="card-body p-0">
                    <table class="table table-hover">
                        <thead><tr><th>Foto</th><th>Veículo</th><th>Data</th><th>Valor</th><th>Pagamento</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($customer->sales as $sale)
                            <tr>
                                <td>
                                    @if($sale->vehicle?->principalPhoto)
                                        <img src="{{ $sale->vehicle->principalPhoto->url }}" width="50" height="40" style="object-fit:cover;border-radius:4px">
                                    @endif
                                </td>
                                <td><a href="{{ route('admin.sales.show', $sale) }}">{{ $sale->vehicle?->titulo ?? '—' }}</a></td>
                                <td>{{ $sale->data_venda->format('d/m/Y') }}</td>
                                <td>{{ $sale->preco_venda_formatado }}</td>
                                <td>{{ $sale->tipo_pagamento_label }}</td>
                                <td><span class="badge badge-{{ $sale->status_color }}">{{ $sale->status_label }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Nenhuma compra registrada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Documentos --}}
            <div class="card shadow-sm" id="documentos">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title text-muted text-uppercase mb-0" style="font-size:.72rem;letter-spacing:.08em;font-weight:700">
                            Documentos <span class="text-secondary">({{ $customer->documents->count() }})</span>
                        </h3>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#modalDocumento">
                            <i class="fas fa-plus mr-1"></i>Adicionar
                        </button>
                    </div>
                </div>
                <div class="card-body pt-3">
                    @if($customer->documents->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" style="font-size:.88rem">
                            <thead>
                                <tr>
                                    <th style="width:40px"></th>
                                    <th>Nome</th>
                                    <th>Categoria</th>
                                    <th>Tamanho</th>
                                    <th>Data</th>
                                    <th style="width:100px" class="text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->documents as $doc)
                                <tr>
                                    <td class="text-center"><i class="{{ $doc->icon_class }} fa-lg"></i></td>
                                    <td>
                                        <a href="{{ route('admin.customers.documents.download', [$customer, $doc]) }}" class="text-dark font-weight-600" title="{{ $doc->original_name }}">
                                            {{ Str::limit($doc->name, 40) }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary px-2 py-1">{{ $doc->categoria_label }}</span>
                                    </td>
                                    <td class="text-muted">{{ $doc->size_formatado }}</td>
                                    <td class="text-muted">{{ $doc->created_at->format('d/m/Y') }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.customers.documents.download', [$customer, $doc]) }}" class="btn btn-xs btn-outline-secondary mr-1" title="Baixar">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <form action="{{ route('admin.customers.documents.destroy', [$customer, $doc]) }}" method="POST" class="d-inline"
                                              data-confirm="Remover o documento '{{ $doc->name }}'?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Remover">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center mb-0" style="font-size:.88rem">Nenhum documento anexado.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Upload Documento --}}
<div class="modal fade" id="modalDocumento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.customers.documents.store', $customer) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-upload mr-1"></i>Adicionar Documento</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold" style="font-size:.85rem">Arquivo(s) *</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="docInput" name="documents[]" multiple required>
                            <label class="custom-file-label" for="docInput">Selecionar arquivos...</label>
                        </div>
                        <small class="text-muted">PDF, imagens, documentos — máx. 10 MB cada</small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold" style="font-size:.85rem">Nome / Descrição</label>
                        <input type="text" name="name" class="form-control" placeholder="Ex: CNH do cliente (opcional, usa o nome do arquivo se vazio)">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold" style="font-size:.85rem">Categoria *</label>
                        <select name="categoria" class="form-control" required>
                            @foreach(\App\Models\CustomerDocument::CATEGORIAS as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload mr-1"></i>Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.getElementById('docInput').addEventListener('change', function() {
    var label = this.nextElementSibling;
    label.textContent = this.files.length > 1
        ? this.files.length + ' arquivos selecionados'
        : (this.files[0] ? this.files[0].name : 'Selecionar arquivos...');
});
</script>
@endsection
