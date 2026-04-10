<style>
    :root {
        --page-bg: #edf3f8;
        --nav-bg: linear-gradient(135deg, #18314f 0%, #244565 100%);
        --nav-gold: #d9b26a;
        --surface: rgba(255, 255, 255, 0.97);
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
            linear-gradient(180deg, rgba(24, 49, 79, 0.04), rgba(24, 49, 79, 0)),
            var(--page-bg);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .topbar {
        position: sticky;
        top: 0;
        z-index: 1200;
        background: var(--nav-bg);
        box-shadow: 0 16px 32px rgba(17, 24, 39, 0.24);
        transition: transform 0.28s ease, opacity 0.28s ease;
    }

    .topbar-hidden {
        transform: translateY(-100%);
        opacity: 0;
    }

    .topbar::after {
        content: "";
        display: block;
        height: 3px;
        background: linear-gradient(90deg, transparent, var(--nav-gold), transparent);
    }

    .topbar-inner {
        max-width: 100%;
        margin: 0 auto;
        padding: 0.95rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .brand-lockup {
        display: inline-flex;
        align-items: center;
        gap: 0.95rem;
        text-decoration: none;
        color: #fff;
    }

    .brand-lockup strong,
    .brand-lockup small {
        display: block;
    }

    .brand-lockup strong {
        font-size: 1.32rem;
        line-height: 1.1;
        letter-spacing: 0.01em;
    }

    .brand-lockup small {
        color: rgba(255, 255, 255, 0.76);
        font-size: 0.83rem;
    }

    .brand-emblem {
        width: 56px;
        height: 56px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 50%;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.14);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.18);
    }

    .brand-emblem-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
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
        padding: 0.6rem 0.6rem 1.25rem;
    }

    .page-title-block {
        padding: 0.4rem 0.5rem 0.9rem;
    }

    .page-title-kicker {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        background: rgba(24, 49, 79, 0.08);
        color: #244565;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 0.55rem;
    }

    .page-title-block h1 {
        margin: 0;
        font-size: clamp(1.55rem, 2.7vw, 2.45rem);
        line-height: 1.1;
        color: #18314f;
        letter-spacing: 0.03em;
        font-weight: 800;
    }

    .page-title-block p {
        margin: 0.45rem 0 0;
        color: var(--muted);
        max-width: 760px;
        font-size: 0.96rem;
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
        top: 12px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        width: min(1320px, calc(100% - 24px));
        pointer-events: none;
    }

    .map-overlay-shell {
        pointer-events: auto;
    }

    .map-filter-panel {
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid rgba(255, 255, 255, 0.75);
        border-radius: 22px;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.16);
        backdrop-filter: blur(16px);
        overflow: hidden;
        transition: box-shadow 0.24s ease;
    }

    .map-filter-panel.is-collapsed .map-filter-body {
        display: none;
    }

    .map-filter-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.8rem 1rem;
        background: linear-gradient(180deg, rgba(24, 49, 79, 0.05), rgba(24, 49, 79, 0));
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
    }

    .map-filter-title {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        min-width: 0;
    }

    .map-filter-title-badge {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(135deg, #1e5cad 0%, #0b8f7a 100%);
        box-shadow: 0 10px 18px rgba(30, 92, 173, 0.22);
    }

    .map-filter-title strong {
        display: block;
        font-size: 0.98rem;
        color: #18314f;
    }

    .map-filter-title small {
        display: block;
        color: var(--muted);
        font-size: 0.78rem;
    }

    .map-filter-tools {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .map-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #334155;
        background: rgba(15, 23, 42, 0.05);
    }

    .map-badge-primary {
        color: #1d4ed8;
        background: rgba(37, 99, 235, 0.12);
    }

    .map-panel-toggle,
    .map-filter-btn,
    .map-filter-export,
    .hero-button,
    .chart-export-button,
    .info-toggle-btn {
        border: 0;
        cursor: pointer;
        transition: 0.24s ease;
    }

    .map-panel-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.85rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #334155;
        background: rgba(15, 23, 42, 0.06);
    }

    .map-filter-body {
        padding: 0.85rem 1rem 1rem;
    }

    .map-filter-bar {
        display: grid;
        grid-template-columns: 1.15fr 0.9fr 1fr auto auto;
        gap: 0.75rem;
        align-items: end;
    }

    .map-filter-field {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        min-width: 0;
    }

    .map-filter-field label {
        font-size: 0.73rem;
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

    .map-filter-btn {
        min-width: 160px;
        padding: 0.8rem 1rem;
        color: #fff;
        font-weight: 700;
        background: linear-gradient(135deg, #12777a 0%, #2563eb 100%);
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.18);
    }

    .map-filter-export {
        min-width: 140px;
        padding: 0.8rem 1rem;
        color: var(--text);
        font-weight: 700;
        background: rgba(15, 23, 42, 0.05);
    }

    #peta {
        width: 100%;
        height: calc(100vh - 94px);
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
    .map-panel-toggle:hover,
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
        margin-top: 122px;
    }

    .leaflet-control-layers {
        border: 0 !important;
        border-radius: 16px !important;
        overflow: hidden;
        box-shadow: 0 16px 28px rgba(15, 23, 42, 0.16) !important;
    }

    .leaflet-control-layers-expanded {
        padding: 0.9rem 1rem !important;
        min-width: 170px;
        background: rgba(255, 255, 255, 0.97) !important;
    }

    .leaflet-control-layers-toggle {
        width: 42px !important;
        height: 42px !important;
        background-size: 20px 20px !important;
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
        background: linear-gradient(180deg, rgba(24, 49, 79, 0.04), rgba(24, 49, 79, 0));
    }

    .map-info-head h4 {
        margin: 0;
        font-size: 1rem;
        color: #18314f;
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
            width: calc(100% - 18px);
        }
    }

    @media (max-width: 768px) {
        .topbar-inner,
        .dashboard-heading,
        .chart-card-head,
        .detail-head,
        .map-filter-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-shell {
            padding: 0.45rem 0.45rem 1rem;
        }

        .map-filter-bar,
        .summary-grid,
        .charts-grid,
        .detail-grid {
            grid-template-columns: 1fr;
        }

        .map-filter-tools {
            width: 100%;
            justify-content: flex-start;
        }

        .brand-pulse {
            display: none;
        }

        .brand-lockup strong {
            font-size: 1.05rem;
        }

        .brand-lockup small {
            font-size: 0.75rem;
        }

        #peta {
            min-height: 620px;
            height: calc(100vh - 82px);
        }

        .page-title-block {
            padding: 0.35rem 0.25rem 0.8rem;
        }

        .map-top-overlay {
            top: 8px;
        }

        .map-floating-card {
            min-width: 270px;
            max-width: 315px;
        }

        .leaflet-top.leaflet-right {
            margin-top: 220px;
        }
    }
</style>
