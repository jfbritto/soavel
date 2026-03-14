<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configuracao Concluida!</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f0f2f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; }

        .complete-container { max-width: 580px; width: 100%; padding: 16px; }

        .complete-card {
            background: #fff; border-radius: 16px; padding: 40px 32px; text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,.07); border: 1px solid #e9ecef;
        }

        .complete-icon {
            width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
        }
        .complete-icon.success { background: #d4edda; color: #2ecc71; }
        .complete-icon.warning { background: #fff3cd; color: #f39c12; }

        .complete-title { font-size: 1.4rem; font-weight: 800; color: #1a1a2e; margin-bottom: 8px; }
        .complete-text { color: #6c757d; font-size: .92rem; margin-bottom: 24px; line-height: 1.6; }

        .pending-list { text-align: left; background: #fffbf0; border: 1px solid #ffeeba; border-radius: 10px; padding: 16px 20px; margin-bottom: 24px; }
        .pending-list h6 { font-weight: 700; color: #856404; font-size: .85rem; margin-bottom: 10px; }
        .pending-item { display: flex; align-items: center; gap: 8px; padding: 4px 0; font-size: .84rem; color: #666; }
        .pending-item i { color: #f39c12; font-size: .7rem; }

        .filled-list { text-align: left; background: #f0fdf4; border: 1px solid #c3e6cb; border-radius: 10px; padding: 16px 20px; margin-bottom: 24px; }
        .filled-list h6 { font-weight: 700; color: #155724; font-size: .85rem; margin-bottom: 10px; }
        .filled-item { display: flex; align-items: center; gap: 8px; padding: 4px 0; font-size: .84rem; color: #666; }
        .filled-item i { color: #2ecc71; }

        .btn-dashboard {
            display: inline-block; background: #4361ee; color: #fff; border: none;
            padding: 12px 36px; border-radius: 10px; font-weight: 700; font-size: .95rem;
            text-decoration: none; transition: all .2s;
        }
        .btn-dashboard:hover { background: #3451d1; color: #fff; text-decoration: none; }

        .btn-settings {
            display: inline-block; background: none; color: #4361ee; border: 1px solid #4361ee;
            padding: 10px 24px; border-radius: 10px; font-weight: 600; font-size: .88rem;
            text-decoration: none; transition: all .2s; margin-left: 10px;
        }
        .btn-settings:hover { background: #f0f4ff; text-decoration: none; color: #3451d1; }

        .btn-back-step {
            display: inline-block; color: #adb5bd; font-size: .82rem;
            text-decoration: underline; margin-top: 16px;
        }
        .btn-back-step:hover { color: #666; }
    </style>
</head>
<body>

<div class="complete-container">
    <div class="complete-card">

        @if(count($pending) === 0)
            <div class="complete-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="complete-title">Tudo pronto!</h2>
            <p class="complete-text">
                Todos os dados foram preenchidos. Seu site esta configurado e pronto para receber clientes.
            </p>
        @else
            <div class="complete-icon warning">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h2 class="complete-title">Quase la!</h2>
            <p class="complete-text">
                A configuracao basica foi salva, mas alguns dados recomendados ainda estao pendentes.
                Voce pode completa-los agora ou depois em <strong>Configuracoes</strong>.
            </p>

            <div class="pending-list">
                <h6><i class="fas fa-exclamation-triangle mr-1"></i> Itens pendentes</h6>
                @foreach($pending as $key => $label)
                    <div class="pending-item">
                        <i class="fas fa-circle"></i> {{ $label }}
                    </div>
                @endforeach
            </div>
        @endif

        @php
            $filled = [
                'nome_sistema'      => 'Nome do Sistema',
                'whatsapp_number'   => 'WhatsApp',
                'logo_path'         => 'Logo',
                'favicon_path'      => 'Favicon',
                'descricao_empresa' => 'Descricao',
                'cidade_estado'     => 'Cidade/Estado',
                'telefone_comercial'=> 'Telefone',
                'cor_primaria'      => 'Cor da Marca',
            ];
        @endphp
        @php
            $filledItems = [];
            foreach ($filled as $k => $l) {
                if (!empty($settings[$k]->value ?? null)) {
                    $filledItems[$k] = $l;
                }
            }
        @endphp
        @if(count($filledItems) > 0)
            <div class="filled-list">
                <h6><i class="fas fa-check-circle mr-1"></i> Dados preenchidos</h6>
                @foreach($filledItems as $key => $label)
                    <div class="filled-item">
                        <i class="fas fa-check"></i> {{ $label }}
                    </div>
                @endforeach
            </div>
        @endif

        <div>
            <a href="{{ route('admin.onboarding.finish') }}" class="btn-dashboard">
                <i class="fas fa-tachometer-alt mr-1"></i> Ir para o Painel
            </a>
            @if(count($pending) > 0)
                <a href="{{ route('admin.settings.index') }}" class="btn-settings">
                    <i class="fas fa-cog mr-1"></i> Completar Dados
                </a>
            @endif
        </div>

        <div>
            <a href="{{ route('admin.onboarding.step', count($allSteps)) }}" class="btn-back-step">
                <i class="fas fa-arrow-left mr-1"></i> Voltar para a ultima etapa
            </a>
        </div>

    </div>
</div>

</body>
</html>
