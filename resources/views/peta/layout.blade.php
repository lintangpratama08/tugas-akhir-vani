<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Peta PAD Jawa Timur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @yield('css')
</head>

<body>
    <nav class="topbar" id="governmentTopbar">
        <div class="topbar-inner">
            <div class="topbar-surface">
                <a class="brand-lockup" href="{{ route('peta.index') }}">
                    <img src="{{ asset('logojatim.png') }}" alt="Logo Jawa Timur" class="brand-emblem-image">
                    <span class="brand-copy">
                        <strong>Bappeda <span>Jawa Timur</span></strong>
                        <small>Dashboard peta pendapatan daerah terintegrasi</small>
                    </span>
                </a>
                <div class="topbar-actions">
                    <a href="{{ route('peta.index') }}" class="topbar-link">Beranda</a>
                    <a href="#peta" class="topbar-link">Peta</a>
                    <a href="#peta_dashboard_section" class="topbar-link">Dashboard</a>
                    <a href="#page_footer" class="topbar-link">Informasi</a>
                </div>
            </div>
        </div>
    </nav>

    @yield('content')

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @yield('js')
</body>

</html>
