<div class="col-md-6 col-lg-4 vehicle-card-wrap">
    <div class="vehicle-card">
        <div class="vehicle-card-img">
            @if($vehicle->photos->count())
                @php $photos = $vehicle->photos; @endphp
                <div class="card-slider" data-current="0">
                    @foreach($photos as $i => $photo)
                        <img src="{{ $photo->url }}"
                             alt="{{ $vehicle->titulo }} - Foto {{ $i + 1 }}"
                             class="card-slide{{ $i === 0 ? ' active' : '' }}"
                             {{ $i > 0 ? 'loading=lazy' : '' }}>
                    @endforeach

                    @if($photos->count() > 1)
                        <button type="button" class="card-slider-btn prev" aria-label="Foto anterior">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="card-slider-btn next" aria-label="Pr&oacute;xima foto">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        @if($photos->count() <= 8)
                        <div class="card-slider-dots">
                            @foreach($photos as $i => $photo)
                                <span class="card-slider-dot{{ $i === 0 ? ' active' : '' }}"></span>
                            @endforeach
                        </div>
                        @endif
                        <span class="card-slider-counter">{{ $photos->count() }} <i class="fas fa-camera"></i></span>
                    @endif
                </div>
            @else
                <div class="card-no-photo"><i class="fas fa-car"></i></div>
            @endif
            @if($vehicle->destaque)
                <span class="badge-destaque"><i class="fas fa-star mr-1"></i>Destaque</span>
            @endif
        </div>
        <div class="vehicle-card-body">
            <div class="vehicle-card-name">{{ $vehicle->titulo }}</div>
            <div class="vehicle-card-version">&middot; {{ $vehicle->ano_fabricacao }}/{{ $vehicle->ano_modelo }}</div>
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
