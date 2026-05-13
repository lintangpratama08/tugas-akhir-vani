@extends('peta.layout')

@section('css')
    @include('peta.style')
@endsection

@section('content')
    <div class="page-shell page-shell-map">
        <section class="page-title-block">
            <span class="page-title-kicker">Portal Informasi Bapenda Jatim</span>
            <h1>DASHBOARD PAD JAWA TIMUR</h1>
            <p>Peta interaktif pendapatan asli daerah untuk monitoring capaian kabupaten/kota di Jawa Timur.</p>
        </section>

        @if (!empty($backendUnavailable))
            <section class="page-warning-banner" role="alert">
                <strong>Data server belum tersedia.</strong>
                <span>{{ $backendErrorMessage }}</span>
            </section>
        @endif

        <section class="map-wrapper map-wrapper-full">
            <div id="map_top_overlay" class="map-top-overlay"></div>
            <div id="peta"></div>
        </section>

        @include('peta.dashboard')
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
