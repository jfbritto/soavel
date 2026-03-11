<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="@yield('meta_description', 'Soavel Veículos - Carros Seminovos em Santa Maria de Jetibá, ES. Confira nosso estoque e encontre o veículo ideal para você.')">
    <title>@yield('meta_title', 'Soavel Veículos | Seminovos em Santa Maria de Jetibá - ES')</title>

    <link rel="canonical" href="@yield('canonical', url()->current())">

    @php
        $siteTheme   = \App\Models\Setting::get('site_layout', 'clean-modern');
        $logoPath    = \App\Models\Setting::get('logo_path');
        $faviconPath = \App\Models\Setting::get('favicon_path');
        $nomeSistema = \App\Models\Setting::get('nome_sistema', 'Soavel Veículos');
        $whatsappNum = \App\Models\Setting::get('whatsapp_number', '5527998490472');
        $instagramUrl = \App\Models\Setting::get('instagram_url', '');
        $facebookUrl  = \App\Models\Setting::get('facebook_url', '');
        $telefone     = \App\Models\Setting::get('telefone_comercial', '(27) 99849-0472');
        $cidadeEstado     = \App\Models\Setting::get('cidade_estado', 'Santa Maria de Jetibá – ES');
        $enderecoCompleto = \App\Models\Setting::get('endereco_completo', '');
        $horarioAten      = \App\Models\Setting::get('horario_atendimento', 'Seg–Sex 8h–18h | Sáb 8h–12h');
        $descricaoEmpresa = \App\Models\Setting::get('descricao_empresa', 'Sua loja de confiança para encontrar o carro seminovo ideal. Qualidade e transparência em cada negociação.');
        $corPrimaria      = \App\Models\Setting::get('cor_primaria', '');
        $logoSrc          = $logoPath ? asset('storage/' . $logoPath) : asset('img/logo/soavel-fundo.png');
        $faviconSrc       = $faviconPath ? asset('storage/' . $faviconPath) : asset('img/default-favicon.svg');
    @endphp

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ $faviconSrc }}">

    <!-- Open Graph -->
    <meta property="og:title"       content="@yield('meta_title', $nomeSistema)">
    <meta property="og:description" content="@yield('meta_description', 'Carros Seminovos em Santa Maria de Jetibá, ES')">
    <meta property="og:image"       content="@yield('og_image', asset('img/logo/soavel-fundo.png'))">
    <meta property="og:url"         content="{{ url()->current() }}">
    <meta property="og:type"        content="website">

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- Google Fonts: All themes -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@400;500;600;700&family=Lato:wght@400;700&family=Playfair+Display:ital,wght@0,700;0,800;1,700;1,800&display=swap" rel="stylesheet">
    <!-- GLightbox -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
    <!-- Site CSS -->
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">

    @if($corPrimaria)
    {{-- Cor da marca configurada no admin -- sobrescreve a cor de destaque do tema --}}
    <style>
        :root, [data-theme], [data-theme="clean-modern"], [data-theme="dark-sport"], [data-theme="premium"] {
            --accent:          {{ $corPrimaria }};
            --accent-hover:    color-mix(in srgb, {{ $corPrimaria }} 82%, #000);
            --accent-subtle:   color-mix(in srgb, {{ $corPrimaria }} 10%, transparent);
            --accent-glow:     color-mix(in srgb, {{ $corPrimaria }} 28%, transparent);
            --price:           {{ $corPrimaria }};
            --navbar-link-h:   {{ $corPrimaria }};
            --hero-text-acc:   {{ $corPrimaria }};
            --footer-link-h:   {{ $corPrimaria }};
            --eyebrow-color:   {{ $corPrimaria }};
        }
    </style>
    @endif

    @yield('extra_css')

    @yield('schema')
</head>
<body data-theme="{{ $siteTheme }}">

<!-- ════════════════ NAVBAR ════════════════ -->
<nav class="navbar navbar-expand-lg navbar-site" id="mainNav">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('site.home') }}" style="gap:10px">
            <img src="{{ $logoSrc }}"
                 alt="{{ $nomeSistema }}"
                 onerror="this.style.display='none';document.getElementById('navBrandText').style.display='block'">
            <span id="navBrandText" class="navbar-brand-text" style="display:none">{{ $nomeSistema }}</span>
        </a>

        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Abrir menu">
            <i class="fas fa-bars navbar-toggler-icon-custom"></i>
        </button>

        <!-- Nav links -->
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('site.home') ? 'active' : '' }}" href="{{ route('site.home') }}">Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('site.vehicles.index') ? 'active' : '' }}" href="{{ route('site.vehicles.index') }}">Estoque</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('site.home') }}#sobre">Sobre</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('site.home') }}#contato">Contato</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link btn-nav-cta ml-lg-2" href="https://wa.me/{{ $whatsappNum }}" target="_blank" rel="noopener">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ════════════════ CONTENT ════════════════ -->
<div style="padding-top:var(--navbar-h)">
    @yield('content')
</div>

<!-- ════════════════ FOOTER ════════════════ -->
<footer class="site-footer" id="contato">
    <div class="container">
        <div class="row">

            <!-- Col 1: Brand + Social -->
            <div class="col-md-4 mb-5">
                <img src="{{ $logoSrc }}"
                     alt="{{ $nomeSistema }}"
                     style="max-height:50px;margin-bottom:14px;display:block"
                     onerror="this.style.display='none'">
                <h5 class="footer-head">{{ $nomeSistema }}</h5>
                <p>{{ $descricaoEmpresa }}</p>
                <div class="social-links">
                    @if($instagramUrl)
                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener" title="Instagram"><i class="fab fa-instagram"></i></a>
                    @else
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    @endif
                    @if($facebookUrl)
                        <a href="{{ $facebookUrl }}" target="_blank" rel="noopener" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    @else
                        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    @endif
                    <a href="https://wa.me/{{ $whatsappNum }}" target="_blank" rel="noopener" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <!-- Col 2: Contact Info -->
            <div class="col-md-4 mb-5">
                <h5 class="footer-head">Contato</h5>
                @if($telefone)
                    <p><i class="fas fa-phone mr-2" style="color:var(--accent);width:16px;text-align:center"></i>{{ $telefone }}</p>
                @endif
                <p><i class="fab fa-whatsapp mr-2" style="color:var(--whatsapp);width:16px;text-align:center"></i>
                    <a href="https://wa.me/{{ $whatsappNum }}" target="_blank" rel="noopener">
                        (27) {{ substr($whatsappNum, -9, 5) }}-{{ substr($whatsappNum, -4) }}
                    </a>
                </p>
                @if($enderecoCompleto)
                    <p><i class="fas fa-map-marker-alt mr-2" style="color:var(--accent);width:16px;text-align:center"></i>{{ $enderecoCompleto }}</p>
                @elseif($cidadeEstado)
                    <p><i class="fas fa-map-marker-alt mr-2" style="color:var(--accent);width:16px;text-align:center"></i>{{ $cidadeEstado }}</p>
                @endif
                @if($horarioAten)
                    <p><i class="fas fa-clock mr-2" style="color:var(--accent);width:16px;text-align:center"></i>{{ $horarioAten }}</p>
                @endif
            </div>

            <!-- Col 3: Contact Form -->
            <div class="col-md-4 mb-5">
                <h5 class="footer-head">Fale Conosco</h5>
                @if(session('contact_success'))
                    <div class="alert alert-success p-2" style="font-size:.875rem">{{ session('contact_success') }}</div>
                @endif
                <form action="{{ route('site.contact.store') }}" method="POST" class="footer-form">
                    @csrf
                    <input type="text" name="nome" class="form-control" placeholder="Seu nome" required>
                    <input type="text" name="telefone" class="form-control" placeholder="Seu WhatsApp" required>
                    <textarea name="mensagem" class="form-control" rows="3" placeholder="Mensagem (opcional)"></textarea>
                    <button type="submit" class="btn-footer-send mt-1">
                        <i class="fas fa-paper-plane mr-1"></i> Enviar Mensagem
                    </button>
                </form>
            </div>

        </div>
    </div>

    <hr class="footer-divider m-0">
    <div class="footer-bottom">
        <div class="container">
            <p class="mb-0">© {{ date('Y') }} {{ $nomeSistema }}. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>

<!-- ════════════════ WhatsApp Float ════════════════ -->
<div class="whatsapp-float">
    <a href="https://wa.me/{{ $whatsappNum }}" target="_blank" rel="noopener" title="Fale conosco no WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>
</div>

<!-- ════════════════ SCRIPTS ════════════════ -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>

<script>
// Navbar scroll effect
window.addEventListener('scroll', function () {
    document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 60);
});

// GLightbox init (used on detail pages)
if (typeof GLightbox !== 'undefined') {
    var lightbox = GLightbox({ selector: '.glightbox' });
}
</script>

@yield('extra_js')
</body>
</html>
