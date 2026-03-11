@if($errors->any())
<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card">
    <div class="card-header"><h3 class="card-title">Dados do Cliente</h3></div>
    <div class="card-body">

        {{-- Nome + CPF + Telefone --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nome Completo <span class="text-danger">*</span></label>
                    <input type="text" id="nome" name="nome"
                        class="form-control form-control-lg @error('nome') is-invalid @enderror"
                        value="{{ old('nome', $customer?->nome) }}"
                        placeholder="Ex: João da Silva"
                        autocomplete="off" required>
                    @error('nome')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>CPF</label>
                    <input type="text" id="cpf" name="cpf"
                        class="form-control form-control-lg @error('cpf') is-invalid @enderror"
                        value="{{ old('cpf', $customer?->cpf) }}"
                        placeholder="000.000.000-00"
                        maxlength="14">
                    @error('cpf')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Telefone <span class="text-danger">*</span></label>
                    <input type="text" id="telefone" name="telefone"
                        class="form-control form-control-lg @error('telefone') is-invalid @enderror"
                        value="{{ old('telefone', $customer?->telefone) }}"
                        placeholder="(27) 99999-9999"
                        maxlength="15" required>
                    @error('telefone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        {{-- E-mail + CEP --}}
        <div class="row">
            <div class="col-md-7">
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email"
                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                        value="{{ old('email', $customer?->email) }}"
                        placeholder="email@exemplo.com">
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>CEP</label>
                    <div class="input-group">
                        <input type="text" id="cep" name="cep"
                            class="form-control form-control-lg"
                            value="{{ old('cep', $customer?->cep) }}"
                            placeholder="00000-000"
                            maxlength="9">
                        <div class="input-group-append">
                            <span class="input-group-text" id="cep-spinner" style="display:none;">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Endereço</label>
                    <input type="text" id="endereco" name="endereco"
                        class="form-control form-control-lg"
                        value="{{ old('endereco', $customer?->endereco) }}"
                        placeholder="Rua, Avenida...">
                </div>
            </div>
        </div>

        {{-- Número + Bairro + Cidade + Estado --}}
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label>Número</label>
                    <input type="text" id="numero" name="numero"
                        class="form-control form-control-lg"
                        value="{{ old('numero', $customer?->numero) }}"
                        placeholder="123">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Bairro</label>
                    <input type="text" id="bairro" name="bairro"
                        class="form-control form-control-lg"
                        value="{{ old('bairro', $customer?->bairro) }}">
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label>Cidade</label>
                    <input type="text" id="cidade" name="cidade"
                        class="form-control form-control-lg"
                        value="{{ old('cidade', $customer?->cidade) }}">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Estado</label>
                    <input type="text" id="estado" name="estado"
                        class="form-control form-control-lg text-uppercase"
                        maxlength="2"
                        value="{{ old('estado', $customer?->estado) }}"
                        placeholder="ES">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Observações</label>
            <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes', $customer?->observacoes) }}</textarea>
        </div>

    </div>
</div>

@push('js')
<script>
(function () {

    /* ── Capitalizar nome (Title Case) ──────────────────────────── */
    const nomeInput  = document.getElementById('nome');
    const smallWords = ['de','da','do','das','dos','e','em','com','por','para','a','o','as','os'];

    function toTitleCase(str) {
        return str.toLowerCase().split(' ').map(function (word, i) {
            if (!word) return word;
            if (i > 0 && smallWords.includes(word)) return word;
            return word.charAt(0).toUpperCase() + word.slice(1);
        }).join(' ');
    }

    nomeInput.addEventListener('blur', function () {
        if (this.value.trim()) {
            this.value = toTitleCase(this.value.trim());
        }
    });

    /* ── Máscara CPF ─────────────────────────────────────────────── */
    document.getElementById('cpf').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 11);
        if (v.length > 9)      v = v.replace(/^(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
        else if (v.length > 6) v = v.replace(/^(\d{3})(\d{3})(\d{1,3})/,        '$1.$2.$3');
        else if (v.length > 3) v = v.replace(/^(\d{3})(\d{1,3})/,               '$1.$2');
        this.value = v;
    });

    /* ── Máscara Telefone ────────────────────────────────────────── */
    document.getElementById('telefone').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 11);
        if (v.length > 10)     v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
        else if (v.length > 6) v = v.replace(/^(\d{2})(\d{4,5})(\d{0,4})/, '($1) $2-$3');
        else if (v.length > 2) v = v.replace(/^(\d{2})(\d+)/,              '($1) $2');
        this.value = v;
    });

    /* ── Máscara CEP + busca ViaCEP ──────────────────────────────── */
    const cepInput   = document.getElementById('cep');
    const cepSpinner = document.getElementById('cep-spinner');
    let lastCepBuscado = '';

    cepInput.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 8);
        if (v.length > 5) v = v.replace(/^(\d{5})(\d{1,3})/, '$1-$2');
        this.value = v;

        const digits = v.replace(/\D/g, '');
        if (digits.length === 8 && digits !== lastCepBuscado) {
            buscarCep(digits);
        }
    });

    function buscarCep(digits) {
        lastCepBuscado = digits;

        const endereco = document.getElementById('endereco');
        const bairro   = document.getElementById('bairro');
        const cidade   = document.getElementById('cidade');
        const estado   = document.getElementById('estado');
        const numero   = document.getElementById('numero');

        cepSpinner.style.display = '';
        cepInput.disabled = true;

        fetch('https://viacep.com.br/ws/' + digits + '/json/')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.erro) {
                    cepInput.classList.add('is-invalid');
                    showCepError('CEP não encontrado.');
                    return;
                }
                cepInput.classList.remove('is-invalid');
                removeCepError();

                if (data.logradouro) endereco.value = data.logradouro;
                if (data.bairro)     bairro.value   = data.bairro;
                if (data.localidade) cidade.value   = data.localidade;
                if (data.uf)         estado.value   = data.uf.toUpperCase();

                if (data.logradouro) numero.focus();
            })
            .catch(function () {
                showCepError('Erro ao buscar CEP. Verifique sua conexão.');
            })
            .finally(function () {
                cepSpinner.style.display = 'none';
                cepInput.disabled = false;
            });
    }

    function showCepError(msg) {
        removeCepError();
        var span = document.createElement('span');
        span.className = 'invalid-feedback d-block';
        span.id = 'cep-error';
        span.textContent = msg;
        cepInput.closest('.form-group').appendChild(span);
    }

    function removeCepError() {
        var el = document.getElementById('cep-error');
        if (el) el.remove();
    }

    /* ── Estado: forçar maiúsculas ───────────────────────────────── */
    document.getElementById('estado').addEventListener('input', function () {
        this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');
    });

})();
</script>
@endpush
