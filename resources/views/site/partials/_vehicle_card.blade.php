<div class="col-md-6 col-lg-4 vehicle-card-wrap">
    <div class="vehicle-card">
        <div class="vehicle-card-img">
            @if($vehicle->principalPhoto)
                <img src="{{ $vehicle->principalPhoto->url }}" alt="{{ $vehicle->titulo }}" loading="lazy">
            @else
                <div class="card-no-photo"><i class="fas fa-car"></i></div>
            @endif
            @if($vehicle->destaque)
                <span class="badge-destaque"><i class="fas fa-star mr-1"></i>Destaque</span>
            @endif
        </div>
        <div class="vehicle-card-body">
            <div class="vehicle-card-name">{{ $vehicle->marca }} {{ $vehicle->modelo }}</div>
            <div class="vehicle-card-version">{{ $vehicle->versao }} · {{ $vehicle->ano_fabricacao }}/{{ $vehicle->ano_modelo }}</div>
            <div class="vehicle-specs">
                <span class="vehicle-spec-pill"><i class="fas fa-tachometer-alt"></i> {{ $vehicle->km_formatado }}</span>
                <span class="vehicle-spec-pill"><i class="fas fa-gas-pump"></i> {{ ucfirst($vehicle->combustivel) }}</span>
                <span class="vehicle-spec-pill"><i class="fas fa-cog"></i> {{ ucfirst($vehicle->transmissao) }}</span>
            </div>
            <div class="vehicle-card-price">{{ $vehicle->preco_formatado }}</div>
            <div class="vehicle-card-actions">
                <a href="{{ route('site.vehicles.show', $vehicle->slug) }}" class="btn-ver-detalhes">Ver detalhes</a>
                <a href="https://wa.me/{{ \App\Models\Setting::get('whatsapp_number', '5527998490472') }}?text={{ urlencode('Olá! Tenho interesse no ' . $vehicle->titulo . '. Vi no site.') }}"
                   target="_blank"
                   rel="noopener"
                   class="btn-whatsapp-card"
                   title="Falar no WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </div>
        </div>
    </div>
</div>
