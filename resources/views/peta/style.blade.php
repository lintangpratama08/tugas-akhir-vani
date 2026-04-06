<style>
    :root {
        --page-bg: #e8f1fb;
        --nav-bg: linear-gradient(135deg, #20293c 0%, #303951 100%);
        --surface: rgba(255, 255, 255, 0.96);
        --stroke: rgba(148, 163, 184, 0.22);
        --text: #172033;
        --muted: #56637a;
        --primary: #2563eb;
        --teal: #14b8a6;
        --shadow: 0 18px 50px rgba(22, 32, 51, 0.12);
    }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        color: var(--text);
        background:
            radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 20%),
            radial-gradient(circle at top right, rgba(20, 184, 166, 0.08), transparent 22%),
            var(--page-bg);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .topbar {
        position: sticky;
        top: 0;
        z-index: 1200;
        background: var(--nav-bg);
        box-shadow: 0 16px 32px rgba(17, 24, 39, 0.24);
    }

    .topbar-inner {
        max-width: 100%;
        margin: 0 auto;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .brand-lockup {
        display: inline-flex;
        align-items: center;
        gap: 0.85rem;
        text-decoration: none;
        color: #fff;
    }

    .brand-lockup strong, .brand-lockup small { display: block; }
    .brand-lockup strong { font-size: 1.5rem; line-height: 1.1; }
    .brand-lockup small { color: rgba(255, 255, 255, 0.76); font-size: 0.84rem; }

    .brand-badge {
        width: 50px;
        height: 50px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        font-weight: 800;
        letter-spacing: 0.08em;
        background: linear-gradient(135deg, #3ec6ff 0%, #16c4a6 100%);
        color: #0a2741;
        box-shadow: 0 10px 24px rgba(62, 198, 255, 0.35);
    }

    .brand-pulse {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.7rem 1rem;
        border-radius: 999px;
        color: rgba(255, 255, 255, 0.92);
        background: rgba(255, 255, 255, 0.10);
        font-weight: 700;
    }

    .brand-pulse-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #22c55e;
        box-shadow: 0 0 0 8px rgba(34, 197, 94, 0.18);
    }

    .page-shell {
        width: 100%;
        margin: 0 auto;
        padding: 0.75rem 0.75rem 1.5rem;
    }

    .map-wrapper-full {
        position: relative;
        background: transparent;
        border: 0;
        box-shadow: none;
        margin-bottom: 1rem;
    }

    .map-top-overlay {
        position: absolute;
        top: 14px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        width: min(1280px, calc(100% - 28px));
        pointer-events: none;
    }

    .map-filter-bar {
        pointer-events: auto;
        display: grid;
        grid-template-columns: 1.1fr 1.25fr 1.15fr auto auto;
        gap: 0.75rem;
        align-items: end;
        padding: 0.85rem;
        background: rgba(255, 255, 255, 0.97);
        border: 1px solid rgba(255, 255, 255, 0.7);
        border-radius: 22px;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.16);
        backdrop-filter: blur(16px);
    }

    .map-filter-field {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        min-width: 0;
    }

    .map-filter-field label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #465366;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .map-filter-select,
    .map-filter-btn,
    .map-filter-export {
        height: 46px;
        border-radius: 14px;
    }

    .map-filter-select {
        width: 100%;
        padding: 0.7rem 0.9rem;
        border: 1px solid rgba(148, 163, 184, 0.26);
        background: #fff;
        color: var(--text);
        outline: 0;
    }

    .map-filter-select:focus {
        border-color: rgba(37, 99, 235, 0.5);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
    }

    .map-filter-btn,
    .map-filter-export,
    .hero-button,
    .chart-export-button,
    .map-action-chip,
    .info-toggle-btn {
        border: 0;
        cursor: pointer;
        transition: 0.24s ease;
    }

    .map-filter-btn {
        min-width: 160px;
        padding: 0.8rem 1rem;
        color: #fff;
        font-weight: 700;
        background: linear-gradient(135deg, #12777a 0%, #2563eb 100%);
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.18);
    }

    .map-filter-export {
        min-width: 160px;
        padding: 0.8rem 1rem;
        color: var(--text);
        font-weight: 700;
        background: rgba(15, 23, 42, 0.05);
    }

    #peta {
        width: 100%;
        height: calc(100vh - 104px);
        min-height: 780px;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: var(--shadow);
    }

    .dashboard-shell { padding-top: 0.25rem; }

    .dashboard-heading,
    .chart-card-head,
    .detail-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .dashboard-heading { margin-bottom: 1rem; }

    .section-badge {
        display: inline-flex;
        padding: 0.4rem 0.8rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #2251ca;
        background: rgba(37, 99, 235, 0.10);
        margin-bottom: 0.8rem;
    }

    .dashboard-heading h2, .chart-card-head h3, .detail-head h3 { margin: 0; }

    .dashboard-heading p, .chart-card-head p, .detail-head p {
        margin: 0.35rem 0 0;
        color: var(--muted);
        font-size: 0.92rem;
    }

    .hero-button,
    .chart-export-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        padding: 0.8rem 1rem;
        border-radius: 16px;
        text-decoration: none;
        font-weight: 700;
    }

    .hero-button-light, .chart-export-button {
        color: var(--text);
        background: rgba(15, 23, 42, 0.04);
    }

    .hero-button:hover,
    .chart-export-button:hover,
    .map-filter-btn:hover,
    .map-filter-export:hover,
    .map-action-chip:hover,
    .info-toggle-btn:hover {
        transform: translateY(-2px);
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .summary-card,
    .chart-card,
    .detail-card {
        background: var(--surface);
        border: 1px solid rgba(255, 255, 255, 0.45);
        box-shadow: var(--shadow);
        backdrop-filter: blur(14px);
    }

    .summary-card {
        border-radius: 22px;
        padding: 1.2rem;
        position: relative;
        overflow: hidden;
    }

    .summary-card::before {
        content: "";
        position: absolute;
        inset: auto -28px -28px auto;
        width: 110px;
        height: 110px;
        border-radius: 999px;
        opacity: 0.1;
        background: currentColor;
    }

    .summary-label {
        display: block;
        margin-bottom: 0.7rem;
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--muted);
    }

    .summary-card strong {
        display: block;
        font-size: clamp(1.4rem, 2vw, 2rem);
        margin-bottom: 0.35rem;
    }

    .summary-card small {
        color: var(--muted);
        font-size: 0.85rem;
    }

    .accent-blue { color: #2563eb; }
    .accent-teal { color: #0f766e; }
    .accent-orange { color: #f59e0b; }
    .accent-red { color: #dc2626; }

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .chart-card,
    .detail-card {
        border-radius: 24px;
        padding: 1.1rem;
    }

    .chart-card-head { margin-bottom: 0.9rem; }

    .chart-card-head h3 {
        font-size: 1.05rem;
        line-height: 1.3;
        max-width: 78%;
    }

    .chart-canvas {
        position: relative;
        min-height: 320px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .table-dashboard { margin-bottom: 0; font-size: 0.92rem; }

    .table-dashboard thead th {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--muted);
        border-bottom-width: 1px;
    }

    .table-dashboard tbody td { vertical-align: middle; }

    .leaflet-container { background: #dbeafe; }

    .leaflet-top.leaflet-right {
        margin-top: 92px;
    }

    .leaflet-control-layers {
        border: 0 !important;
        border-radius: 16px !important;
        overflow: hidden;
        box-shadow: 0 16px 28px rgba(15, 23, 42, 0.16) !important;
    }

    .leaflet-control-layers-expanded {
        padding: 0.9rem 1rem !important;
        min-width: 168px;
        background: rgba(255, 255, 255, 0.96) !important;
    }

    .leaflet-control-layers-base label {
        margin-bottom: 0.45rem;
        font-weight: 600;
        color: #334155;
    }

    .map-floating-card {
        background: rgba(255, 255, 255, 0.97);
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 18px;
        box-shadow: 0 18px 30px rgba(15, 23, 42, 0.12);
        min-width: 320px;
        max-width: 360px;
        overflow: hidden;
    }

    .map-info-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.9rem 1rem 0.75rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.16);
    }

    .map-info-head h4 {
        margin: 0;
        font-size: 1rem;
    }

    .info-toggle-btn {
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #334155;
        background: rgba(15, 23, 42, 0.06);
    }

    .map-info-body {
        padding: 0.9rem 1rem 1rem;
    }

    .map-info-body.is-collapsed {
        display: none;
    }

    .map-floating-card p {
        margin: 0;
        color: var(--muted);
        font-size: 0.83rem;
    }

    .map-stat {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        margin-top: 0.48rem;
        font-size: 0.85rem;
    }

    .map-stat span:last-child {
        font-weight: 700;
        color: var(--text);
    }

    .map-info-section {
        margin-top: 0.85rem;
        padding-top: 0.8rem;
        border-top: 1px dashed rgba(148, 163, 184, 0.4);
    }

    .map-info-section-title {
        margin-bottom: 0.5rem;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #475569;
    }

    .map-account-item {
        padding: 0.65rem 0.7rem;
        border-radius: 12px;
        background: rgba(15, 23, 42, 0.035);
        margin-top: 0.55rem;
    }

    .map-account-name {
        font-size: 0.84rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }

    .legend-row {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-top: 0.45rem;
        font-size: 0.84rem;
    }

    .legend-swatch {
        width: 18px;
        height: 12px;
        border-radius: 999px;
    }

    .leaflet-popup-content-wrapper {
        border-radius: 18px;
    }

    .leaflet-popup-content {
        margin: 0.9rem 1rem;
        min-width: 220px;
    }

    .popup-title {
        margin-bottom: 0.65rem;
        font-weight: 700;
        font-size: 1rem;
    }

    .popup-row {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        margin-top: 0.35rem;
        font-size: 0.88rem;
    }

    .popup-row strong {
        color: var(--text);
    }

    .loading-state {
        opacity: 0.75;
        pointer-events: none;
    }

    @media (max-width: 1400px) {
        .map-filter-bar {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 1200px) {
        .summary-grid,
        .charts-grid,
        .detail-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .map-top-overlay {
            width: calc(100% - 20px);
        }
    }

    @media (max-width: 768px) {
        .topbar-inner,
        .dashboard-heading,
        .chart-card-head,
        .detail-head {
            flex-direction: column;
        }

        .page-shell {
            padding: 0.55rem 0.55rem 1rem;
        }

        .map-filter-bar,
        .summary-grid,
        .charts-grid,
        .detail-grid {
            grid-template-columns: 1fr;
        }

        .brand-pulse {
            display: none;
        }

        #peta {
            min-height: 620px;
            height: calc(100vh - 96px);
        }

        .map-top-overlay {
            top: 10px;
        }

        .map-floating-card {
            min-width: 280px;
            max-width: 320px;
        }

        .leaflet-top.leaflet-right {
            margin-top: 250px;
        }
    }
</style>
