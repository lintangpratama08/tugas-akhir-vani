@extends('peta.layout')

@section('css')
    @include('peta.style')
@endsection

@section('content')
    <div class="page-shell page-shell-map">
        @if (!empty($backendUnavailable))
            <section class="page-warning-banner" role="alert">
                <strong>Data server belum tersedia.</strong>
                <span>{{ $backendErrorMessage }}</span>
            </section>
        @endif

        <section class="map-floating-dock-shell">
            <div id="map_filter_dock" class="map-floating-dock"></div>
        </section>

        <section class="map-wrapper map-wrapper-full">
            <div id="map_top_overlay" class="map-top-overlay"></div>
            <div id="peta"></div>
        </section>

        @include('peta.dashboard')

        <footer class="page-footer">
            <div class="page-footer-main">
                <div class="page-footer-brand">
                    <span class="page-footer-kicker">Dashboard PAD Jawa Timur</span>
                    <h3>BAPPEDA JAWA TIMUR</h3>
                    <p>Portal visual untuk monitoring pendapatan daerah, evaluasi anggaran PAD, dan pembacaan capaian
                        wilayah kabupaten/kota di Jawa Timur.</p>
                    <div class="page-footer-tags">
                        <span><i class="bi bi-shield-check"></i> Informasi terkurasi</span>
                        <span><i class="bi bi-lightning-charge"></i> Insight cepat</span>
                    </div>
                </div>
                <div class="page-footer-meta">
                    <span><i class="bi bi-geo-alt"></i> Jl. Pahlawan 102-108, Surabaya</span>
                    <span><i class="bi bi-bar-chart-line"></i> Monitoring PAD Terintegrasi</span>
                    <span><i class="bi bi-map"></i> Peta dan dashboard dalam satu layar</span>
                </div>
            </div>
            <div class="page-footer-bottom">
                <span>&copy; {{ date('Y') }} BAPPEDA Jawa Timur</span>
                <span>Dashboard Peta Pendapatan Daerah</span>
            </div>
        </footer>
    </div>
@endsection

@section('js')
    <script>
        window.petaDashboardConfig = {
            defaultTahun: @json($defaultTahun),
            exportUrl: @json(route('peta.export')),
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
