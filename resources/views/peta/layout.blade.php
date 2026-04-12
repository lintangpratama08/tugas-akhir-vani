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
            <a class="brand-lockup" href="{{ route('peta.index') }}">
                <img src="{{ asset('logojatim.png') }}" alt="Logo Jawa Timur" class="brand-emblem-image">
                <span class="brand-copy">
                    <strong>Bapenda Provinsi Jawa Timur</strong>
                    <small>Jl. Manyar Kertoarjo No.1, Airlangga, Gubeng, Surabaya</small>
                    <em>Dashboard Peta Pendapatan Asli Daerah</em>
                </span>
            </a>
            <div class="brand-pulse">
                <span class="brand-pulse-dot"></span>
                <span>Layanan Informasi PAD</span>
            </div>
        </div>
    </nav>

    @yield('content')

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function() {
            const topbar = document.getElementById('governmentTopbar');
            let lastY = window.scrollY;

            window.addEventListener('scroll', function() {
                const currentY = window.scrollY;

                if (currentY > lastY && currentY > 80) {
                    topbar.classList.add('topbar-hidden');
                } else {
                    topbar.classList.remove('topbar-hidden');
                }

                lastY = currentY;
            }, {
                passive: true
            });
        })();
    </script>
    @yield('js')
</body>

</html>
