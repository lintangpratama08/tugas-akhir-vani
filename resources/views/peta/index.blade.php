@extends('peta.layout')

@section('css')
    @include('peta.style')
@endsection

@section('content')
    <div class="page-shell page-shell-map">
        <section class="hero-carousel-shell">
            <div class="hero-carousel-backdrop"></div>
            <div id="petaHeroCarousel" class="carousel slide carousel-fade hero-carousel-slider" data-bs-ride="carousel"
                data-bs-interval="5200">
                <div class="carousel-indicators hero-carousel-indicators">
                    <button type="button" data-bs-target="#petaHeroCarousel" data-bs-slide-to="0" class="active"
                        aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#petaHeroCarousel" data-bs-slide-to="1"
                        aria-label="Slide 2"></button>
                </div>
                <div class="carousel-inner">
                    <div class="carousel-item active hero-slide hero-slide-province">
                        <div class="hero-carousel-grid">
                            <div class="hero-carousel-copy">
                                <span class="hero-kicker"><i class="bi bi-stars"></i> Dashboard unggulan Jawa Timur</span>
                                <h1>Memetakan Kinerja PAD Jawa Timur dengan Visual yang Lebih Tajam dan Modern.</h1>
                                <div class="hero-guidebook-row">
                                    <a href="{{ asset('guidbook.pdf') }}" class="hero-guidebook-link" download>
                                        <i class="bi bi-file-earmark-arrow-down"></i>
                                        Download Guidebook
                                    </a>
                                </div>
                                <div class="hero-meta-row">
                                    <span><i class="bi bi-map"></i> 38 kabupaten/kota</span>
                                    <span><i class="bi bi-bar-chart-line"></i> Insight anggaran dan realisasi</span>
                                    <span><i class="bi bi-building"></i> Bappeda Provinsi Jawa Timur</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item hero-slide hero-slide-city">
                        <div class="hero-carousel-grid">
                            <div class="hero-carousel-copy">
                                <span class="hero-kicker"><i class="bi bi-compass"></i> Fokus wilayah aktif</span>
                                <h1>Telusuri Karisidenan, Kabupaten, dan Kecamatan dalam Satu Pengalaman Visual.</h1>
                                <p>Navigasi dibuat bertahap agar pembacaan data tetap nyaman, mulai dari lingkup provinsi
                                    sampai rincian wilayah yang sedang dipilih.</p>
                                <div class="hero-meta-row">
                                    <span><i class="bi bi-diagram-3"></i> Mode karisidenan dan kabupaten</span>
                                    <span><i class="bi bi-geo-alt"></i> Drilldown wilayah aktif</span>
                                    <span><i class="bi bi-lightning-charge"></i> Ringkas dan cepat dibaca</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev hero-carousel-control" type="button"
                    data-bs-target="#petaHeroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next hero-carousel-control" type="button"
                    data-bs-target="#petaHeroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </section>

        @if (!empty($backendUnavailable))
            <section class="page-warning-banner" role="alert">
                <strong>Data server belum tersedia.</strong>
                <span>{{ $backendErrorMessage }}</span>
            </section>
        @endif

        <section class="map-floating-dock-shell" id="filter_section">
            <div id="map_filter_dock" class="map-floating-dock"></div>
        </section>

        <section class="map-wrapper map-wrapper-full">
            <div id="map_top_overlay" class="map-top-overlay"></div>
            <div id="peta"></div>
        </section>

        <div id="peta_dashboard_section"></div>
        @include('peta.dashboard')

    </div>
@endsection

@section('js')
    <script>
        window.petaDashboardConfig = {
            defaultTahun: @json($defaultTahun),
            exportUrl: @json(route('peta.export')),
            chartInsightUrl: @json(route('peta.chart_insight')),
            dataUrl: @json(route('peta.data')),
            dashboardUrl: @json(route('peta.dashboard')),
            backendUnavailable: @json((bool) ($backendUnavailable ?? false)),
            backendErrorMessage: @json($backendErrorMessage ?? null)
        };

        window.petaFilterOptions = {
            tahunList: @json($tahunList),
            jenisAkun: @json($jenisAkun),
            karisidenanList: @json($karisidenanList),
            wilayahList: @json($wilayahList)
        };
    </script>
    @include('peta.script_peta')
    @include('peta.script_dashboard')
@endsection
