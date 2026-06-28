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
    @php
        $isUploadPage = request()->routeIs('pad.import.*');
        $navLinks = $isUploadPage
            ? [
                ['label' => 'Beranda', 'href' => route('peta.index')],
                ['label' => 'Upload Data', 'href' => route('pad.import.index')],
                ['label' => 'Informasi', 'href' => asset('guidbook.pdf'), 'download' => true],
            ]
            : [
                ['label' => 'Beranda', 'href' => route('peta.index')],
                ['label' => 'Peta', 'href' => '#peta'],
                ['label' => 'Dashboard', 'href' => '#peta_dashboard_section'],
                ['label' => 'Upload Data', 'href' => route('pad.import.index')],
                ['label' => 'Informasi', 'href' => asset('guidbook.pdf'), 'download' => true],
            ];
    @endphp
    <nav class="topbar" id="governmentTopbar">
        <div class="topbar-inner">
            <div class="topbar-surface">
                <a class="brand-lockup" href="{{ route('peta.index') }}">
                    <img src="{{ asset('logojatim.png') }}" alt="Logo Jawa Timur" class="brand-emblem-image">
                    <span class="brand-copy">
                        <strong>Bappeda <span>Jawa Timur</span></strong>
                        <small>Dashboard Peta Pendapatan Asli Daerah terintegrasi</small>
                    </span>
                </a>
                <button class="topbar-menu-toggle" type="button" id="topbarMenuToggle" aria-expanded="false"
                    aria-controls="topbarMenuPanel" aria-label="Buka menu navigasi">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <div class="topbar-actions" id="topbarMenuPanel">
                    @foreach ($navLinks as $navLink)
                        <a href="{{ $navLink['href'] }}" class="topbar-link"
                            @if (!empty($navLink['download'])) download @endif>{{ $navLink['label'] }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer class="page-footer" id="page_footer">
        <div class="page-footer-main">
            <div class="page-footer-brand">
                <span class="page-footer-kicker">Dashboard PAD Jawa Timur</span>
                <h3>BAPPEDA JAWA TIMUR</h3>
                <p>Portal visual untuk monitoring Pendapatan Asli Daerah, evaluasi anggaran PAD, dan pembacaan capaian
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
            <span>&copy; {{ date('Y') }} Vani Febianti</span>
            <span>Dashboard Peta Pendapatan Asli Daerah</span>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script>
        (function() {
            const toggle = document.getElementById('topbarMenuToggle');
            const panel = document.getElementById('topbarMenuPanel');

            if (!toggle || !panel) {
                return;
            }

            const setMenuState = (isOpen) => {
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                panel.classList.toggle('is-open', isOpen);
                document.body.classList.toggle('topbar-menu-open', isOpen);
            };

            toggle.addEventListener('click', function() {
                setMenuState(toggle.getAttribute('aria-expanded') !== 'true');
            });

            panel.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function() {
                    setMenuState(false);
                });
            });

            document.addEventListener('click', function(event) {
                if (window.innerWidth > 768) {
                    return;
                }

                if (!panel.contains(event.target) && !toggle.contains(event.target)) {
                    setMenuState(false);
                }
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    setMenuState(false);
                }
            });
        })();
    </script>
    @yield('js')
</body>

</html>

