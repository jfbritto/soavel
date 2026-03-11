<script>
$(function () {
    var urlMarcas  = '{{ route('admin.fipe.marcas') }}';
    var urlModelos = '{{ route('admin.fipe.modelos', '__marca__') }}';
    var urlAnos    = '{{ route('admin.fipe.anos', ['__marca__', '__modelo__']) }}';
    var urlPreco   = '{{ route('admin.fipe.preco', ['__marca__', '__modelo__', '__ano__']) }}';

    var fipeDados = {};

    function s2init(id, placeholder) {
        $(id).select2({
            placeholder: placeholder,
            allowClear: true,
            width: '100%',
            language: { noResults: function () { return 'Nenhum resultado'; },
                        searching: function () { return 'Buscando...'; } }
        });
    }

    function s2reset(id, placeholder) {
        $(id).empty().append('<option value=""></option>').prop('disabled', true);
        try { $(id).select2('destroy'); } catch(e) {}
        s2init(id, placeholder);
    }

    function setLoading(on) {
        $('#fipeLoading').toggle(on);
    }

    function fetchJson(url, cb) {
        setLoading(true);
        $.getJSON(url).done(function (d) { cb(d); }).always(function () { setLoading(false); });
    }

    // ── Inicializar os três selects com Select2 ───────────────
    s2init('#fipeMarca',  '— selecione —');
    s2init('#fipeModelo', '— selecione a marca —');
    s2init('#fipeAno',    '— selecione o modelo —');

    // ── Carregar marcas ao abrir a página ─────────────────────
    fetchJson(urlMarcas, function (marcas) {
        marcas.forEach(function (m) {
            $('#fipeMarca').append(new Option(m.nome, m.codigo));
        });
        try { $('#fipeMarca').select2('destroy'); } catch(e) {}
        s2init('#fipeMarca', '— selecione —');
    });

    // ── Marca → Modelos ───────────────────────────────────────
    $('#fipeMarca').on('change', function () {
        s2reset('#fipeModelo', '— selecione o modelo —');
        s2reset('#fipeAno',    '— selecione o modelo —');
        $('#fipePreco, #fipeAplicar').addClass('d-none');
        fipeDados = {};

        var marca = $(this).val();
        if (!marca) return;

        fetchJson(urlModelos.replace('__marca__', marca), function (data) {
            var modelos = data.modelos || [];
            modelos.forEach(function (m) {
                $('#fipeModelo').append(new Option(m.nome, m.codigo));
            });
            $('#fipeModelo').prop('disabled', false);
            try { $('#fipeModelo').select2('destroy'); } catch(e) {}
            s2init('#fipeModelo', '— pesquise o modelo —');
        });
    });

    // ── Modelo → Anos ─────────────────────────────────────────
    $('#fipeModelo').on('change', function () {
        s2reset('#fipeAno', '— selecione o ano —');
        $('#fipePreco, #fipeAplicar').addClass('d-none');
        fipeDados = {};

        var modelo = $(this).val();
        if (!modelo) return;

        fetchJson(urlAnos.replace('__marca__', $('#fipeMarca').val()).replace('__modelo__', modelo), function (anos) {
            anos.forEach(function (a) {
                $('#fipeAno').append(new Option(a.nome, a.codigo));
            });
            $('#fipeAno').prop('disabled', false);
            try { $('#fipeAno').select2('destroy'); } catch(e) {}
            s2init('#fipeAno', '— selecione o ano —');
        });
    });

    // ── Ano → Preço FIPE ──────────────────────────────────────
    $('#fipeAno').on('change', function () {
        $('#fipePreco, #fipeAplicar').addClass('d-none');
        fipeDados = {};

        var ano = $(this).val();
        if (!ano) return;

        fetchJson(
            urlPreco
                .replace('__marca__',  $('#fipeMarca').val())
                .replace('__modelo__', $('#fipeModelo').val())
                .replace('__ano__',    ano),
            function (data) {
                if (!data || !data.Valor) return;
                fipeDados = data;
                $('#fipePrecoValor').text(data.Valor);
                $('#fipeMesRef').text('Ref.: ' + (data.MesReferencia || ''));
                $('#fipePreco, #fipeAplicar').removeClass('d-none');
            }
        );
    });

    // ── Preencher formulário ──────────────────────────────────
    $('#btnFipeAplicar').on('click', function () {
        if (!fipeDados || !fipeDados.Modelo) return;

        var nomeMarca = $('#fipeMarca option:selected').text();
        $('[name="marca"]').val(nomeMarca);
        $('[name="modelo"]').val(fipeDados.Modelo || '');

        var anoModelo = fipeDados.AnoModelo ? String(fipeDados.AnoModelo) : '';
        if (anoModelo) {
            $('[name="ano_modelo"]').val(anoModelo);
            $('[name="ano_fabricacao"]').val(anoModelo);
        }

        var combMap = { 'G': 'gasolina', 'A': 'etanol', 'D': 'diesel', 'F': 'flex', 'E': 'eletrico', 'H': 'hibrido' };
        var combVal = combMap[($( fipeDados.SiglaCombustivel || '' ).text() || fipeDados.SiglaCombustivel || '').toUpperCase()]
                   || combMap[(fipeDados.SiglaCombustivel || '').toUpperCase()]
                   || 'flex';
        $('[name="combustivel"]').val(combVal);

        var valorFipe = (fipeDados.Valor || '').replace(/[R$\s.]/g, '').replace(',', '.');
        var num = parseFloat(valorFipe);
        if (!isNaN(num)) {
            $('#precoHidden').val(num.toFixed(2));
            $('#precoDisplay').val(num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'success', title: 'Dados aplicados!', text: 'Revise e ajuste antes de salvar.',
                timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
        }
    });
});
</script>
