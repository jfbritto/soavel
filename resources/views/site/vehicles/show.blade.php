@extends('layouts.site')

@section('meta_title', $vehicle->titulo . ' ' . $vehicle->ano_modelo . ' | ' . \App\Models\Setting::get('nome_sistema', config('app.name')))
@section('meta_description', 'Compre ' . $vehicle->titulo . ' ' . $vehicle->ano_modelo . ' por ' . $vehicle->preco_formatado . '. ' . $vehicle->km_formatado . ', ' . ucfirst($vehicle->combustivel) . '. ' . \App\Models\Setting::get('nome_sistema', config('app.name')) . (\App\Models\Setting::get('cidade_estado') ? ', ' . \App\Models\Setting::get('cidade_estado') : '') . '.')
@if($vehicle->photos->isNotEmpty())
@section('og_image', ($vehicle->photos->firstWhere('principal', true) ?? $vehicle->photos->first())->url)
@endif

@section('schema')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Car",
    "name": "{{ $vehicle->titulo }} {{ $vehicle->ano_modelo }}",
    "description": "{{ Str::limit($vehicle->descricao ?: ($vehicle->titulo . ' ' . $vehicle->ano_fabricacao . '/' . $vehicle->ano_modelo . ', ' . $vehicle->km_formatado . ', ' . ucfirst($vehicle->combustivel)), 200) }}",
    "brand": { "@type": "Brand", "name": "{{ $vehicle->marca }}" },
    "model": "{{ $vehicle->modelo }}",
    "vehicleModelDate": "{{ $vehicle->ano_modelo }}",
    "modelDate": "{{ $vehicle->ano_modelo }}",
    "vehicleTransmission": "{{ $vehicle->transmissao }}",
    "driveWheelConfiguration": "FWD",
    "numberOfDoors": "{{ $vehicle->portas }}",
    "bodyType": "{{ $vehicle->categoria }}",
    "mileageFromOdometer": { "@type": "QuantitativeValue", "value": {{ $vehicle->km }}, "unitCode": "KMT" },
    "color": "{{ $vehicle->cor }}",
    "fuelType": "{{ $vehicle->combustivel }}",
    "vehicleEngine": {
        "@type": "EngineSpecification",
        "name": "{{ $vehicle->motorizacao }}"
    },
    @if($vehicle->photos->isNotEmpty())
    "image": [
        @foreach($vehicle->photos->take(5) as $i => $photo)
        "{{ $photo->url }}"{{ $i < min($vehicle->photos->count(), 5) - 1 ? ',' : '' }}
        @endforeach
    ],
    @endif
    "url": "{{ route('site.vehicles.show', $vehicle->slug) }}",
    "offers": {
        "@type": "Offer",
        "priceCurrency": "BRL",
        "price": "{{ $vehicle->preco }}",
        "priceValidUntil": "{{ now()->addMonths(3)->format('Y-m-d') }}",
        "itemCondition": "https://schema.org/UsedCondition",
        "availability": "{{ $vehicle->status === 'disponivel' ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
        "url": "{{ route('site.vehicles.show', $vehicle->slug) }}"
    },
    "seller": {
        "@type": "AutoDealer",
        "name": "{{ \App\Models\Setting::get('nome_sistema', config('app.name')) }}",
        "telephone": "+{{ \App\Models\Setting::get('whatsapp_number', '') }}",
        "url": "{{ url('/') }}",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "{{ \App\Models\Setting::get('endereco_completo', '') }}",
            "addressLocality": "{{ \App\Models\Setting::get('cidade_estado', '') }}",
            "addressCountry": "BR"
        }
    }
}
</script>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Início", "item": "{{ route('site.home') }}" },
        { "@type": "ListItem", "position": 2, "name": "Estoque", "item": "{{ route('site.vehicles.index') }}" },
        { "@type": "ListItem", "position": 3, "name": "{{ $vehicle->titulo }}" }
    ]
}
</script>
@endsection

@section('content')
<div class="container py-4 py-md-5 vehicle-detail-pb">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('site.home') }}">Início</a></li>
            <li class="breadcrumb-item"><a href="{{ route('site.vehicles.index') }}">Estoque</a></li>
            <li class="breadcrumb-item active">{{ $vehicle->titulo }}</li>
        </ol>
    </nav>

    @if(session('contact_success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('contact_success') }}
    </div>
    @endif

    <div class="row">
        <!-- Galeria + Info -->
        <div class="col-lg-8">

            <!-- Galeria de fotos -->
            <div class="mb-4">
                @if($vehicle->photos->isNotEmpty())
                    <!-- Foto principal -->
                    <a href="{{ $vehicle->photos->first()->url }}" class="glightbox" data-gallery="vehicle-{{ $vehicle->id }}">
                        <img src="{{ $vehicle->photos->first()->url }}" alt="{{ $vehicle->titulo }} {{ $vehicle->ano_modelo }} - Foto principal"
                             class="vehicle-gallery-main rounded shadow" width="800" height="500">
                    </a>
                    <!-- Thumbnails -->
                    @if($vehicle->photos->count() > 1)
                    <div class="row mt-2 mx-0">
                        @foreach($vehicle->photos->skip(1) as $pi => $photo)
                        <div class="col-4 col-sm-3 p-1">
                            <a href="{{ $photo->url }}" class="glightbox" data-gallery="vehicle-{{ $vehicle->id }}">
                                <img src="{{ $photo->url }}" alt="{{ $vehicle->titulo }} - Foto {{ $pi + 2 }}" class="vehicle-thumb-img rounded" loading="lazy" width="200" height="150">
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @endif
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center vehicle-gallery-nophoto">
                        <i class="fas fa-car fa-5x text-muted"></i>
                    </div>
                @endif
            </div>

            <!-- Compartilhar -->
            <div class="d-flex align-items-center mb-4" style="gap:10px">
                <span class="text-muted font-weight-600" style="font-size:.85rem"><i class="fas fa-share-alt mr-1"></i> Compartilhar:</span>
                <a href="https://wa.me/?text={{ urlencode($vehicle->titulo . ' ' . $vehicle->ano_modelo . ' por ' . $vehicle->preco_formatado . ' - Veja no site: ' . request()->url()) }}"
                   target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" title="Compartilhar no WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                   target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" title="Compartilhar no Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary" style="border-color:#E1306C;color:#E1306C" onclick="generateStoryImage()" title="Gerar imagem para Instagram Stories">
                    <i class="fab fa-instagram"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyVehicleLink()" title="Copiar link">
                    <i class="fas fa-link"></i>
                </button>
                <span id="copy-feedback" class="text-success font-weight-600" style="font-size:.8rem;display:none">Copiado!</span>
            </div>

            <!-- Especificações -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h1 class="font-weight-800 h2" id="vehicleTitle" style="color:var(--azul)">{{ $vehicle->titulo }} {{ $vehicle->ano_modelo }}</h1>
                    <div class="d-flex flex-wrap mb-3" style="gap:8px">
                        <span class="badge badge-primary px-3 py-2">{{ ucfirst($vehicle->categoria) }}</span>
                        <span class="badge badge-{{ $vehicle->status_color }} px-3 py-2">{{ $vehicle->status_label }}</span>
                        @if($vehicle->destaque)<span class="badge badge-warning px-3 py-2">⭐ Destaque</span>@endif
                    </div>

                    <div class="row">
                        <div class="col-6 col-md-3 mb-3 text-center">
                            <i class="fas fa-calendar-alt fa-lg mb-1" style="color:var(--azul-claro)"></i>
                            <div class="font-weight-700">{{ $vehicle->ano_fabricacao }}/{{ $vehicle->ano_modelo }}</div>
                            <small class="text-muted">Ano Fab/Mod</small>
                        </div>
                        <div class="col-6 col-md-3 mb-3 text-center">
                            <i class="fas fa-tachometer-alt fa-lg mb-1" style="color:var(--azul-claro)"></i>
                            <div class="font-weight-700">{{ $vehicle->km_formatado }}</div>
                            <small class="text-muted">Quilometragem</small>
                        </div>
                        <div class="col-6 col-md-3 mb-3 text-center">
                            <i class="fas fa-gas-pump fa-lg mb-1" style="color:var(--azul-claro)"></i>
                            <div class="font-weight-700">{{ ucfirst($vehicle->combustivel) }}</div>
                            <small class="text-muted">Combustível</small>
                        </div>
                        <div class="col-6 col-md-3 mb-3 text-center">
                            <i class="fas fa-cog fa-lg mb-1" style="color:var(--azul-claro)"></i>
                            <div class="font-weight-700">{{ ucfirst($vehicle->transmissao) }}</div>
                            <small class="text-muted">Transmissão</small>
                        </div>
                        @if($vehicle->motorizacao)
                        <div class="col-6 col-md-3 mb-3 text-center">
                            <i class="fas fa-engine fa-lg mb-1" style="color:var(--azul-claro)"></i>
                            <div class="font-weight-700">{{ $vehicle->motorizacao }}</div>
                            <small class="text-muted">Motor</small>
                        </div>
                        @endif
                        <div class="col-6 col-md-3 mb-3 text-center">
                            <i class="fas fa-palette fa-lg mb-1" style="color:var(--azul-claro)"></i>
                            <div class="font-weight-700">{{ $vehicle->cor }}</div>
                            <small class="text-muted">Cor</small>
                        </div>
                        @if($vehicle->portas > 0)
                        <div class="col-6 col-md-3 mb-3 text-center">
                            <i class="fas fa-door-closed fa-lg mb-1" style="color:var(--azul-claro)"></i>
                            <div class="font-weight-700">{{ $vehicle->portas }}</div>
                            <small class="text-muted">Portas</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Descrição -->
            @if($vehicle->descricao)
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="font-weight-700 mb-3">Descrição</h5>
                    <p class="mb-0">{!! nl2br(e($vehicle->descricao)) !!}</p>
                </div>
            </div>
            @endif

            <!-- Opcionais -->
            @if($vehicle->features->isNotEmpty())
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="font-weight-700 mb-3">Opcionais e Equipamentos</h5>
                    <div class="row">
                        @foreach($vehicle->features as $feat)
                        <div class="col-md-4 col-6 mb-2">
                            <i class="fas fa-check-circle text-success mr-1"></i>{{ $feat->feature }}
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar CTA -->
        <div class="col-lg-4">
            <div style="position:sticky;top:90px">
                {{-- Preço + Status --}}
                <div class="card shadow-sm mb-3 border-0" style="border-radius:12px;overflow:hidden">
                    <div style="background:#1e3a5f;color:#ffffff;padding:24px 20px;text-align:center">
                        <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;opacity:.8;margin-bottom:4px">Preço</div>
                        <div id="vehiclePrice" style="font-size:clamp(1.5rem,6vw,2.2rem);font-weight:800;color:#ffffff;line-height:1.1">{{ $vehicle->preco_formatado }}</div>
                        @if($vehicle->status !== 'disponivel')
                        <span class="badge badge-{{ $vehicle->status_color }} mt-2" style="font-size:.85rem">{{ $vehicle->status_label }}</span>
                        @endif
                    </div>

                    <div class="card-body" style="padding:20px">
                        {{-- Resumo rápido --}}
                        <div class="d-flex justify-content-between mb-3 pb-2 border-bottom" style="font-size:.85rem">
                            <span class="text-muted"><i class="fas fa-calendar-alt mr-1"></i>Ano</span>
                            <strong>{{ $vehicle->ano_fabricacao }}/{{ $vehicle->ano_modelo }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-2 border-bottom" style="font-size:.85rem">
                            <span class="text-muted"><i class="fas fa-tachometer-alt mr-1"></i>KM</span>
                            <strong>{{ $vehicle->km_formatado }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-2 border-bottom" style="font-size:.85rem">
                            <span class="text-muted"><i class="fas fa-cog mr-1"></i>Câmbio</span>
                            <strong>{{ ucfirst($vehicle->transmissao) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3" style="font-size:.85rem">
                            <span class="text-muted"><i class="fas fa-gas-pump mr-1"></i>Combustível</span>
                            <strong>{{ ucfirst($vehicle->combustivel) }}</strong>
                        </div>

                        {{-- Botão WhatsApp --}}
                        @php $wpNum = \App\Models\Setting::get('whatsapp_number', ''); @endphp
                        <a href="https://wa.me/{{ $wpNum }}?text={{ urlencode('Olá! Tenho interesse no ' . $vehicle->titulo . ' ' . $vehicle->ano_modelo . ' por ' . $vehicle->preco_formatado . '. Vi no site. Podemos conversar?') }}"
                           target="_blank" class="btn btn-whatsapp btn-block btn-lg mb-2" style="font-size:1rem;padding:12px">
                            <i class="fab fa-whatsapp mr-2"></i>Quero este veículo!
                        </a>

                        {{-- Ligar --}}
                        <a href="tel:+{{ $wpNum }}" class="btn btn-outline-secondary btn-block mb-0" style="font-size:.9rem">
                            <i class="fas fa-phone-alt mr-1"></i>Ligar agora
                        </a>
                    </div>
                </div>

                {{-- Formulário de contato --}}
                <div class="card shadow-sm mb-4 border-0" style="border-radius:12px">
                    <div class="card-body" style="padding:20px">
                        <h6 class="font-weight-700 mb-1"><i class="fas fa-envelope mr-1" style="color:var(--azul)"></i>Solicitar Informações</h6>
                        <p class="text-muted mb-3" style="font-size:.8rem">Preencha e entraremos em contato</p>

                        @if(session('contact_success'))
                        <div class="alert alert-success p-2">{{ session('contact_success') }}</div>
                        @else
                        <form action="{{ route('site.interesse.store', $vehicle) }}" method="POST">
                            @csrf
                            <div class="form-group mb-2">
                                <input type="text" name="nome" class="form-control form-control-sm" placeholder="Seu nome *" required value="{{ old('nome') }}">
                            </div>
                            <div class="form-group mb-2">
                                <input type="text" name="telefone" class="form-control form-control-sm" placeholder="Seu WhatsApp *" required value="{{ old('telefone') }}">
                            </div>
                            <div class="form-group mb-2">
                                <input type="email" name="email" class="form-control form-control-sm" placeholder="E-mail (opcional)" value="{{ old('email') }}">
                            </div>
                            <div class="form-group mb-2">
                                <textarea name="mensagem" class="form-control form-control-sm" rows="2" placeholder="Mensagem (opcional)">{{ old('mensagem') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary-site btn-block text-white" style="font-size:.9rem">
                                <i class="fas fa-paper-plane mr-1"></i>Enviar Interesse
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Veículos similares -->
    @if($similares->isNotEmpty())
    <div class="mt-5">
        <h4 class="font-weight-800 mb-4" style="color:var(--azul)">Veículos Similares</h4>
        <div class="row">
            @foreach($similares as $vehicle)
                @include('site.partials._vehicle_card', ['vehicle' => $vehicle])
            @endforeach
        </div>
    </div>
    @endif

</div>

{{-- Sticky CTA bar — mobile only --}}
@if($vehicle->status === 'disponivel')
<div class="mobile-cta-bar d-lg-none">
    <div class="mobile-cta-bar__price">{{ $vehicle->preco_formatado }}</div>
    <a href="https://wa.me/{{ \App\Models\Setting::get('whatsapp_number', '') }}?text={{ urlencode('Olá! Tenho interesse no ' . $vehicle->titulo . ' ' . $vehicle->ano_modelo . ' por ' . $vehicle->preco_formatado . '. Vi no site. Podemos conversar?') }}"
       target="_blank" rel="noopener" class="mobile-cta-bar__btn">
        <i class="fab fa-whatsapp"></i> Tenho interesse!
    </a>
</div>
@endif

<script>
// Esconde o botão flutuante do WhatsApp quando a barra CTA mobile está presente
if (document.querySelector('.mobile-cta-bar')) {
    document.body.classList.add('has-mobile-cta');
}

function copyVehicleLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        var fb = document.getElementById('copy-feedback');
        fb.style.display = 'inline';
        setTimeout(function() { fb.style.display = 'none'; }, 2000);
    });
}

function generateStoryImage() {
    var btn = event.currentTarget;
    var origHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando...';
    btn.disabled = true;

    var W = 1080, H = 1920;
    var canvas = document.createElement('canvas');
    canvas.width = W;
    canvas.height = H;
    var ctx = canvas.getContext('2d');

    // Dados do veículo via DOM — garante que é o veículo da página atual
    var vehicleTitle = (document.getElementById('vehicleTitle') || {}).textContent || 'Veículo';
    var vehiclePrice = (document.getElementById('vehiclePrice') || {}).textContent || '';

    // Specs: lê do DOM via labels "Ano Fab/Mod", "Quilometragem", etc.
    function getSpecByLabel(label) {
        var smalls = document.querySelectorAll('.card-body small.text-muted');
        for (var i = 0; i < smalls.length; i++) {
            if (smalls[i].textContent.trim() === label) {
                var prev = smalls[i].previousElementSibling;
                if (prev) return prev.textContent.trim();
            }
        }
        return '';
    }
    var vehicleYear  = getSpecByLabel('Ano Fab/Mod');
    var vehicleKm    = getSpecByLabel('Quilometragem');
    var vehicleFuel  = getSpecByLabel('Combustível');
    var vehicleTrans = getSpecByLabel('Transmissão');
    var vehicleMotor = getSpecByLabel('Motor');

    var storeName   = (document.querySelector('.navbar-brand-text') || document.querySelector('.navbar-brand img') || {}).textContent || document.title.split('|').pop().trim();
    var accentColor = getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#FF4500';
    var siteUrl     = window.location.hostname;

    // Fotos do DOM — principal + thumbnails
    var logoImgEl = document.querySelector('.navbar-brand img');

    // Coleta até 3 fotos: principal + 2 primeiras thumbnails
    var allPhotoEls = [];
    var mainImg = document.querySelector('.vehicle-gallery-main');
    if (mainImg) allPhotoEls.push(mainImg);
    document.querySelectorAll('.vehicle-thumb-img').forEach(function(el, i) {
        if (allPhotoEls.length < 3) allPhotoEls.push(el);
    });

    // Helper: desenhar imagem cover em rounded rect
    function drawCoverRounded(img, x, y, w, h, r) {
        ctx.save();
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.lineTo(x + w - r, y);
        ctx.quadraticCurveTo(x + w, y, x + w, y + r);
        ctx.lineTo(x + w, y + h - r);
        ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
        ctx.lineTo(x + r, y + h);
        ctx.quadraticCurveTo(x, y + h, x, y + h - r);
        ctx.lineTo(x, y + r);
        ctx.quadraticCurveTo(x, y, x + r, y);
        ctx.closePath();
        ctx.clip();

        var imgR = img.naturalWidth / img.naturalHeight;
        var areaR = w / h;
        var sx, sy, sw, sh;
        if (imgR > areaR) {
            sh = img.naturalHeight; sw = sh * areaR;
            sx = (img.naturalWidth - sw) / 2; sy = 0;
        } else {
            sw = img.naturalWidth; sh = sw / areaR;
            sx = 0; sy = (img.naturalHeight - sh) / 2;
        }
        ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
        ctx.restore();
    }

    function drawStory(photos, logoImg) {
        // Background
        var bgGrad = ctx.createLinearGradient(0, 0, 0, H);
        bgGrad.addColorStop(0, '#0D0D0D');
        bgGrad.addColorStop(0.4, '#1A1A1A');
        bgGrad.addColorStop(1, '#0D0D0D');
        ctx.fillStyle = bgGrad;
        ctx.fillRect(0, 0, W, H);

        // Accent line top
        ctx.fillStyle = accentColor;
        ctx.fillRect(0, 0, W, 8);

        var y = 60;
        var pad = 40;
        var gap = 12;

        // Logo ou nome da loja
        if (logoImg) {
            var logoH = 70;
            var logoW = logoImg.width * (logoH / logoImg.height);
            if (logoW > 400) { logoW = 400; logoH = logoImg.height * (400 / logoImg.width); }
            ctx.drawImage(logoImg, (W - logoW) / 2, y, logoW, logoH);
            y += logoH + 35;
        } else {
            ctx.font = 'bold 42px "Inter", "Helvetica Neue", sans-serif';
            ctx.fillStyle = '#FFFFFF';
            ctx.textAlign = 'center';
            ctx.fillText(storeName, W / 2, y + 42);
            y += 80;
        }

        // Fotos
        var contentW = W - pad * 2;
        if (photos.length >= 3) {
            // 1 grande + 2 menores lado a lado
            var mainH = 540;
            drawCoverRounded(photos[0], pad, y, contentW, mainH, 16);
            y += mainH + gap;

            var thumbW = (contentW - gap) / 2;
            var thumbH = 320;
            drawCoverRounded(photos[1], pad, y, thumbW, thumbH, 12);
            drawCoverRounded(photos[2], pad + thumbW + gap, y, thumbW, thumbH, 12);
            y += thumbH + 45;
        } else if (photos.length === 2) {
            // 1 grande + 1 menor
            var mainH = 560;
            drawCoverRounded(photos[0], pad, y, contentW, mainH, 16);
            y += mainH + gap;

            var thumbH = 300;
            drawCoverRounded(photos[1], pad, y, contentW, thumbH, 12);
            y += thumbH + 45;
        } else if (photos.length === 1) {
            // Só 1 foto grande
            var mainH = 700;
            drawCoverRounded(photos[0], pad, y, contentW, mainH, 16);
            y += mainH + 45;
        } else {
            y += 40;
        }

        // Accent line separadora
        ctx.fillStyle = accentColor;
        ctx.fillRect(pad, y, contentW, 4);
        y += 75;

        // Título do veículo
        ctx.textAlign = 'center';
        ctx.fillStyle = '#FFFFFF';
        ctx.font = 'bold 60px "Inter", "Helvetica Neue", sans-serif';

        var words = vehicleTitle.split(' ');
        var lines = [];
        var line = '';
        var maxTitleW = W - 120;
        for (var i = 0; i < words.length; i++) {
            var test = line + (line ? ' ' : '') + words[i];
            if (ctx.measureText(test).width > maxTitleW && line) {
                lines.push(line);
                line = words[i];
            } else {
                line = test;
            }
        }
        lines.push(line);

        for (var j = 0; j < lines.length; j++) {
            ctx.fillText(lines[j], W / 2, y + j * 72);
        }
        y += lines.length * 72 + 20;

        // Preço
        ctx.font = 'bold 84px "Inter", "Helvetica Neue", sans-serif';
        ctx.fillStyle = accentColor;
        ctx.fillText(vehiclePrice, W / 2, y + 65);
        y += 115;

        // Specs em linha
        ctx.font = '600 34px "Inter", "Helvetica Neue", sans-serif';
        ctx.fillStyle = 'rgba(255,255,255,0.6)';
        var specLine = vehicleYear + '  ·  ' + vehicleKm + '  ·  ' + vehicleFuel + '  ·  ' + vehicleTrans;
        if (vehicleMotor) specLine += '  ·  ' + vehicleMotor;
        ctx.fillText(specLine, W / 2, y + 10);

        // Rodapé fixo no bottom
        ctx.fillStyle = 'rgba(255,255,255,0.15)';
        ctx.fillRect(80, H - 110, W - 160, 1);
        ctx.font = '500 30px "Inter", "Helvetica Neue", sans-serif';
        ctx.fillStyle = 'rgba(255,255,255,0.5)';
        ctx.textAlign = 'center';
        ctx.fillText(siteUrl, W / 2, H - 55);

        // Accent line bottom
        ctx.fillStyle = accentColor;
        ctx.fillRect(0, H - 8, W, 8);

        // Download or share
        canvas.toBlob(function (blob) {
            if (navigator.share && navigator.canShare) {
                var file = new File([blob], 'story-' + vehicleTitle.replace(/\s+/g, '-').toLowerCase() + '.jpg', { type: 'image/jpeg' });
                if (navigator.canShare({ files: [file] })) {
                    navigator.share({ files: [file] }).catch(function() {});
                    btn.innerHTML = origHTML;
                    btn.disabled = false;
                    return;
                }
            }
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'story-' + vehicleTitle.replace(/\s+/g, '-').toLowerCase() + '.jpg';
            a.click();
            URL.revokeObjectURL(url);
            btn.innerHTML = origHTML;
            btn.disabled = false;
        }, 'image/jpeg', 0.92);
    }

    // Usa imagens já carregadas no DOM
    var readyPhotos = allPhotoEls.filter(function(el) {
        return el.complete && el.naturalWidth > 0;
    });
    var logoImgObj = (logoImgEl && logoImgEl.complete && logoImgEl.naturalWidth > 0) ? logoImgEl : null;

    drawStory(readyPhotos, logoImgObj);
}
</script>
@endsection
