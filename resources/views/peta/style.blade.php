<style>
    :root {
        --page-bg: #eef3f8;
        --nav-bg: linear-gradient(135deg, #17324f 0%, #1f3f63 50%, #2a4f77 100%);
        --nav-bg-strong: linear-gradient(135deg, #142d49 0%, #1d3d60 48%, #284c73 100%);
        --nav-gold: #d8ae62;
        --surface: rgba(255, 255, 255, 0.97);
        --surface-strong: #ffffff;
        --stroke: rgba(23, 58, 100, 0.10);
        --text: #142033;
        --muted: #617287;
        --primary: #204d7a;
        --secondary: #2b628d;
        --accent: #c69236;
        --danger: #dc2626;
        --shadow: 0 20px 48px rgba(15, 35, 62, 0.08);
        --shadow-soft: 0 12px 30px rgba(15, 35, 62, 0.06);
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        color: var(--text);
        background:
            linear-gradient(180deg, #f7fafc 0%, #eef3f8 38%, #e8eef5 100%);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .topbar {
        position: absolute;
        top: 1rem;
        left: 0;
        right: 0;
        z-index: 1200;
        background: transparent;
        transition: none;
    }

    .topbar::before {
        content: none;
    }

    .topbar-hidden {
        transform: translateY(-100%);
        opacity: 0;
        pointer-events: none;
    }

    .topbar::after {
        content: none;
    }

    .topbar-inner {
        max-width: 1180px;
        margin: 0 auto;
        padding: 0 1.2rem;
    }

    .topbar-surface {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.45rem;
        border-radius: 999px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.78), rgba(255, 255, 255, 0.68));
        border: 1px solid rgba(255, 255, 255, 0.22);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.18);
        backdrop-filter: blur(18px);
    }

    .brand-lockup {
        display: inline-flex;
        align-items: center;
        gap: 0.95rem;
        text-decoration: none;
        color: #1f1a14;
    }

    .brand-copy {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .brand-lockup strong,
    .brand-lockup small,
    .brand-lockup em {
        display: block;
    }

    .brand-lockup strong {
        font-size: 1.28rem;
        line-height: 1.1;
        letter-spacing: 0.03em;
        text-transform: none;
        font-family: Georgia, "Times New Roman", serif;
    }

    .brand-lockup strong span {
        color: #c58d2f;
    }

    .brand-lockup small {
        color: rgba(61, 47, 34, 0.78);
        font-size: 0.84rem;
    }

    .brand-emblem-image {
        width: 48px;
        height: 58px;
        object-fit: contain;
        display: block;
        flex-shrink: 0;
        filter: drop-shadow(0 10px 16px rgba(15, 35, 62, 0.24));
    }

    .topbar-actions {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .topbar-menu-toggle {
        display: none;
        width: 48px;
        height: 48px;
        border: 0;
        border-radius: 16px;
        background: rgba(23, 58, 100, 0.08);
        color: #173a64;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 0.28rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55);
    }

    .topbar-menu-toggle span {
        width: 20px;
        height: 2px;
        border-radius: 999px;
        background: currentColor;
        transition: transform 0.24s ease, opacity 0.24s ease;
    }

    .topbar-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.72rem 1rem;
        border-radius: 999px;
        color: rgba(59, 45, 31, 0.88);
        text-decoration: none;
        font-size: 0.92rem;
        font-weight: 700;
        transition: 0.24s ease;
    }

    .topbar-link:hover {
        color: #b88329;
        background: rgba(184, 131, 41, 0.08);
    }

    .page-shell {
        width: 100%;
        margin: 0 auto;
        padding: 0 0.85rem 1.25rem;
    }

    .hero-carousel-shell {
        position: relative;
        min-height: 100vh;
        margin: -9rem -0.85rem 0.3rem;
        padding: 0;
        border-radius: 0 0 34px 34px;
        overflow: hidden;
        background: #15110d;
        color: #fff7ee;
        box-shadow: 0 26px 60px rgba(15, 23, 42, 0.16);
    }

    .hero-carousel-shell::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 18% 36%, rgba(208, 144, 47, 0.22), transparent 28%),
            linear-gradient(135deg, rgba(255, 255, 255, 0.05), transparent 32%, rgba(208, 144, 47, 0.10) 100%);
        pointer-events: none;
    }

    .hero-carousel-backdrop {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(180deg, rgba(10, 14, 18, 0.02), rgba(10, 14, 18, 0.14)),
            radial-gradient(circle at 78% 26%, rgba(255, 221, 168, 0.16), transparent 44%);
        pointer-events: none;
    }

    .hero-carousel-grid {
        position: relative;
        z-index: 1;
        max-width: 1180px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1.1rem;
        align-items: end;
        min-height: 100%;
    }

    .hero-carousel-slider,
    .hero-carousel-slider .carousel-inner,
    .hero-carousel-slider .carousel-item {
        position: relative;
        height: 100%;
    }

    .hero-carousel-slider .carousel-inner {
        min-height: 130vh;
        overflow: hidden;
        border-radius: 0 0 34px 34px;
    }

    .hero-carousel-slider .carousel-item {
        min-height: 130vh;
        height: 130vh;
        padding: 15.8rem 1.6rem 8.8rem;
        background-position: center center;
        background-size: 108% auto;
        background-repeat: no-repeat;
        overflow: hidden;
    }

    .hero-slide::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(90deg, rgba(18, 22, 28, 0.84) 0%, rgba(26, 34, 42, 0.68) 34%, rgba(43, 53, 64, 0.34) 68%, rgba(18, 24, 30, 0.52) 100%),
            linear-gradient(180deg, rgba(255, 246, 233, 0.04), rgba(18, 16, 14, 0.54));
        pointer-events: none;
    }

    .hero-slide-province {
        background-image: url('{{ asset('provinsijawatimur.jpg') }}');
        background-position: center 42%;
    }

    .hero-slide-city {
        background-image: url('{{ asset('jatim.jpg') }}');
        background-position: center 48%;
    }

    .hero-slide-city .hero-carousel-copy {
        margin-top: 1.15rem;
    }

    .hero-carousel-indicators {
        margin: 0;
        bottom: 1.8rem;
        justify-content: flex-start;
        max-width: 1180px;
        left: 50%;
        right: auto;
        transform: translateX(-50%);
        width: calc(100% - 3.2rem);
    }

    .hero-carousel-indicators [data-bs-target] {
        width: 38px;
        height: 6px;
        border: 0;
        border-radius: 999px;
        margin: 0 0.32rem 0 0;
        background-color: rgba(255, 239, 214, 0.34);
        opacity: 1;
    }

    .hero-carousel-indicators .active {
        background-color: #d6a14c;
    }

    .hero-carousel-control {
        width: 54px;
        height: 54px;
        top: auto;
        bottom: 1.1rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(10px);
        opacity: 1;
    }

    .hero-carousel-control.carousel-control-prev {
        left: auto;
        right: calc(50% - 590px);
    }

    .hero-carousel-control.carousel-control-next {
        right: calc(50% - 656px);
    }

    .hero-carousel-copy {
        max-width: 760px;
        padding-right: 2rem;
        margin-top: -1.25rem;
    }

    .hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.58rem 1rem;
        border-radius: 999px;
        border: 1px solid rgba(212, 159, 64, 0.34);
        background: rgba(130, 88, 25, 0.18);
        color: #efc16d;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .hero-carousel-copy h1 {
        margin: 1.15rem 0 0;
        max-width: 780px;
        color: #fff7ef;
        font-size: clamp(1.9rem, 3.7vw, 3rem);
        line-height: 1.12;
        font-weight: 900;
        letter-spacing: -0.03em;
        font-family: Georgia, "Times New Roman", serif;
    }

    .hero-carousel-copy p {
        margin: 1rem 0 0;
        max-width: 650px;
        color: rgba(255, 248, 238, 0.92);
        font-size: 1rem;
        line-height: 1.72;
    }

    .hero-guidebook-row {
        margin-top: 1.2rem;
    }

    .hero-guidebook-link {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.8rem 1.15rem;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.18), rgba(214, 161, 76, 0.22));
        border: 1px solid rgba(255, 233, 201, 0.34);
        color: #fff7ef;
        font-size: 0.9rem;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 14px 26px rgba(15, 23, 42, 0.16);
    }

    .hero-guidebook-link:hover {
        color: #ffffff;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.24), rgba(214, 161, 76, 0.30));
        transform: translateY(-1px);
    }

    .hero-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 1.25rem;
        margin-top: 1.5rem;
    }

    .hero-meta-row span {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: rgba(247, 222, 187, 0.88);
        font-size: 0.9rem;
    }

    .page-title-block {
        padding: 0.4rem 0.35rem 0.75rem;
        margin-bottom: 0.35rem;
    }

    .page-title-kicker {
        display: inline-flex;
        align-items: center;
        padding: 0.42rem 0.78rem;
        border-radius: 999px;
        background: rgba(29, 78, 216, 0.10);
        color: #1f4b87;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 0.8rem;
    }

    .page-title-block h1 {
        margin: 0;
        font-size: clamp(1.95rem, 3.2vw, 3rem);
        line-height: 1.1;
        color: #14335b;
        letter-spacing: 0.03em;
        font-weight: 900;
    }

    .page-title-block p {
        margin: 0.55rem 0 0;
        color: var(--muted);
        max-width: 760px;
        font-size: 0.96rem;
        line-height: 1.6;
    }

    .section-banner {
        position: relative;
        margin: -1px -0.85rem 0;
        padding: 1.2rem 1.7rem 1rem;
        border-radius: 0;
        overflow: hidden;
        border: 0;
        box-shadow: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .section-banner::after {
        content: none;
    }

    .section-banner-map {
        background:
            linear-gradient(180deg, rgba(23, 50, 79, 0.22) 0%, rgba(23, 50, 79, 0.14) 14%, #d6e0eb 14%, #cbd8e6 52%, #dce5ee 100%);
        color: var(--text);
        border-top: 0;
        border-bottom: 1px solid rgba(23, 58, 100, 0.08);
    }

    .section-banner-map::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(90deg, rgba(255, 255, 255, 0.26), transparent 24%, rgba(32, 77, 122, 0.06) 72%, rgba(216, 174, 98, 0.10) 100%),
            linear-gradient(180deg, rgba(255, 255, 255, 0.08), transparent 42%);
        pointer-events: none;
    }

    .section-banner-copy {
        max-width: 980px;
        position: relative;
        z-index: 1;
    }

    .section-banner-dashboard {
        background: linear-gradient(135deg, rgba(20, 51, 91, 0.96), rgba(37, 99, 235, 0.92));
        color: #fff;
    }

    .section-banner-kicker {
        display: inline-flex;
        padding: 0.35rem 0.72rem;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(32, 77, 122, 0.07), rgba(200, 155, 67, 0.12));
        color: var(--primary);
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        margin-bottom: 0.65rem;
    }

    .section-banner-highlights {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.7rem;
    }

    .section-banner-highlights span {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.68rem 0.9rem;
        border-radius: 999px;
        background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
        border: 1px solid rgba(27, 77, 142, 0.08);
        color: #355677;
        font-size: 0.82rem;
        font-weight: 700;
        white-space: nowrap;
        box-shadow: 0 8px 18px rgba(15, 35, 62, 0.05);
    }

    .section-banner h2 {
        margin: 0;
        font-size: clamp(1.5rem, 2.75vw, 2.35rem);
        font-weight: 900;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        background: linear-gradient(135deg, #0f1720 0%, #1b3f66 58%, #274f79 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        white-space: nowrap;
        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.18);
    }

    .section-banner p {
        margin: 0.65rem 0 0;
        max-width: 860px;
        color: #46607c;
        font-size: 0.88rem;
        line-height: 1.6;
        text-transform: none;
    }

    .map-wrapper-full {
        position: relative;
        background: transparent;
        border: 0;
        box-shadow: none;
        margin-bottom: 1rem;
    }

    .map-floating-dock-shell {
        position: sticky;
        top: 0.85rem;
        z-index: 1105;
        margin: 0rem 0.25rem 1.1rem;
        pointer-events: none;
    }

    .map-floating-dock {
        display: flex;
        justify-content: center;
        pointer-events: auto;
    }

    .page-warning-banner {
        margin: 0 0.5rem 1rem;
        padding: 0.95rem 1rem;
        border-radius: 16px;
        border: 1px solid rgba(217, 178, 106, 0.38);
        background: linear-gradient(135deg, rgba(217, 178, 106, 0.18), rgba(255, 255, 255, 0.96));
        color: #6a4b0f;
        box-shadow: 0 12px 24px rgba(106, 75, 15, 0.08);
    }

    .page-warning-banner strong,
    .page-warning-banner span {
        display: block;
    }

    .page-warning-banner span {
        margin-top: 0.3rem;
        color: #7c5a17;
    }

    .map-top-overlay {
        position: absolute;
        top: 18px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        width: min(1180px, calc(100% - 32px));
        pointer-events: none;
    }

    .map-overlay-shell {
        pointer-events: auto;
    }

    .map-dock-panel {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.45rem;
        border-radius: 999px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(244, 248, 252, 0.97));
        border: 1px solid rgba(23, 58, 100, 0.08);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
        backdrop-filter: none;
        flex-wrap: wrap;
        justify-content: center;
    }

    .map-filter-badges {
        position: relative;
        display: flex;
        flex-wrap: nowrap;
        justify-content: flex-start;
        gap: 0.55rem;
        width: fit-content;
        margin-right: auto;
    }

    .map-overlay-title-card {
        width: fit-content;
        max-width: 420px;
        margin: 0 auto 0.75rem;
        padding: 0.75rem 1rem;
        border-radius: 20px;
        background: rgba(15, 35, 62, 0.72);
        border: 1px solid rgba(255, 255, 255, 0.16);
        color: #fff;
        backdrop-filter: blur(12px);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.18);
        text-align: center;
    }

    .map-overlay-title-kicker {
        display: block;
        color: rgba(255, 255, 255, 0.76);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        margin-bottom: 0.25rem;
    }

    .map-overlay-title-card strong {
        display: block;
        font-size: 1rem;
        line-height: 1.4;
    }

    .map-filter-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1006;
        background: rgba(15, 23, 42, 0.34);
        border: 0;
        backdrop-filter: blur(4px);
    }

    .map-filter-modal {
        position: fixed;
        top: 96px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1007;
        width: min(1120px, calc(100vw - 24px));
    }

    .map-filter-panel {
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(245, 249, 253, 0.95));
        border: 1px solid rgba(23, 58, 100, 0.08);
        border-radius: 30px;
        box-shadow: 0 22px 46px rgba(15, 23, 42, 0.10);
        backdrop-filter: none;
        overflow: hidden;
        transition: box-shadow 0.24s ease;
        padding: 1.25rem;
    }

    .map-filter-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .map-filter-head h3 {
        margin: 0.2rem 0 0;
        font-size: 1.08rem;
        color: #173a64;
    }

    .map-filter-modal-kicker {
        margin: 0;
        color: #2555d9;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.2em;
        text-transform: uppercase;
    }

    .map-filter-tools {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        flex-wrap: wrap;
    }

    .map-filter-btn,
    .map-filter-export,
    .map-filter-modal-close,
    .hero-button,
    .chart-export-button,
    .info-toggle-btn,
    .card-detail-button,
    .legend-toggle-button,
    .drawer-close-button {
        border: 0;
        cursor: pointer;
        transition: 0.24s ease;
    }

    .map-filter-bar {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .map-filter-field {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        min-width: 0;
    }

    .map-filter-field label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #4c637c;
        text-transform: uppercase;
        letter-spacing: 0.18em;
    }

    .map-filter-select,
    .map-filter-btn,
    .map-filter-export,
    .map-filter-chip,
    .map-filter-modal-close {
        height: 44px;
        border-radius: 999px;
    }

    .map-filter-select {
        min-width: 170px;
        padding: 0.62rem 1rem;
        border: 1px solid rgba(27, 77, 142, 0.10);
        background: linear-gradient(180deg, rgba(255, 255, 255, 1), rgba(247, 250, 253, 0.96));
        color: var(--text);
        outline: 0;
        font-size: 0.92rem;
        font-weight: 600;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9), 0 10px 18px rgba(15, 23, 42, 0.04);
    }

    .map-filter-select:focus {
        border-color: rgba(37, 99, 235, 0.5);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
    }

    .map-filter-btn {
        min-width: 112px;
        padding: 0.7rem 0.95rem;
        color: #fff;
        font-weight: 700;
        background: linear-gradient(135deg, #173a64 0%, #255789 100%);
        box-shadow: 0 14px 26px rgba(23, 58, 100, 0.18);
    }

    .map-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.65rem 1rem;
        border: 1px solid rgba(27, 77, 142, 0.08);
        background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
        color: #27415d;
        font-size: 0.86rem;
        font-weight: 700;
        white-space: nowrap;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    }

    .map-filter-chip-badge {
        cursor: default;
        gap: 0.55rem;
    }

    .map-filter-chip-year {
        cursor: default;
        padding-left: 1.1rem;
        padding-right: 1.1rem;
    }

    .map-filter-chip .chip-label {
        color: #6b7b90;
        font-size: 0.72rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .map-mode-chip.is-active {
        border-color: rgba(37, 99, 235, 0.65);
        background: linear-gradient(180deg, rgba(27, 77, 142, 0.10), rgba(212, 169, 79, 0.10));
        color: #153d78;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.07);
    }

    .map-filter-modal-close {
        min-width: 96px;
        padding: 0.7rem 0.95rem;
        border: 1px solid rgba(27, 77, 142, 0.10);
        background: linear-gradient(180deg, rgba(255, 255, 255, 1), rgba(244, 247, 251, 0.96));
        color: #4b5d75;
        font-weight: 700;
    }

    .map-filter-export {
        min-width: 118px;
        padding: 0.7rem 0.95rem;
        color: var(--text);
        font-weight: 700;
        background: rgba(15, 23, 42, 0.05);
    }

    .map-filter-footer {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 1rem;
    }

    #peta {
        width: 100%;
        height: calc(100vh - 178px);
        min-height: 720px;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: var(--shadow);
    }

    .dashboard-shell {
        padding-top: 0.25rem;
    }

    .dashboard-heading,
    .chart-card-head,
    .detail-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .dashboard-heading {
        margin-bottom: 0.85rem;
    }

    .section-badge {
        display: inline-flex;
        padding: 0.35rem 0.72rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #1f4b87;
        background: linear-gradient(135deg, rgba(32, 77, 122, 0.08), rgba(200, 155, 67, 0.14));
        margin-bottom: 0.75rem;
        border: 1px solid rgba(29, 78, 216, 0.10);
    }

    .dashboard-heading h2,
    .chart-card-head h3,
    .detail-head h3 {
        margin: 0;
    }

    .dashboard-heading p,
    .chart-card-head p,
    .detail-head p {
        margin: 0.35rem 0 0;
        color: var(--muted);
        font-size: 0.92rem;
    }

    .dashboard-heading p {
        display: none;
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

    .hero-button-light,
    .chart-export-button {
        color: var(--text);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(243, 247, 251, 0.94));
        border: 1px solid rgba(23, 58, 100, 0.08);
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.06);
    }

    .chart-export-button.icon-only {
        width: 42px;
        min-width: 42px;
        height: 42px;
        padding: 0;
        border-radius: 14px;
    }

    .hero-button:hover,
    .chart-export-button:hover,
    .map-filter-btn:hover,
    .map-filter-export:hover,
    .info-toggle-btn:hover,
    .card-detail-button:hover,
    .legend-toggle-button:hover,
    .drawer-close-button:hover {
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
        border: 1px solid rgba(23, 58, 100, 0.08);
        box-shadow: var(--shadow);
        backdrop-filter: none;
    }

    .summary-card {
        border-radius: 24px;
        padding: 0.95rem;
        position: relative;
        overflow: hidden;
        color: #fff;
        border: 0;
    }

    .summary-card-head,
    .card-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.6rem;
    }

    .card-actions {
        justify-content: flex-end;
        flex-wrap: wrap;
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
        margin-bottom: 0.55rem;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: rgba(255, 255, 255, 0.86);
    }

    .summary-card strong {
        display: block;
        font-size: clamp(1.2rem, 1.55vw, 1.65rem);
        margin-bottom: 0.55rem;
        color: #fff;
        line-height: 1.25;
    }

    .summary-compare {
        padding: 0.62rem 0.72rem;
        border-radius: 14px;
        border-top: 0;
        color: rgba(255, 255, 255, 0.94);
        font-size: 0.7rem;
        line-height: 1.55;
        background: rgba(255, 255, 255, 0.16);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
    }

    .summary-compare-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        margin-bottom: 0.3rem;
    }

    .summary-compare-kicker {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.92);
    }

    .summary-compare-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.22rem 0.48rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
    }

    .summary-compare-badge.is-up {
        background: rgba(52, 211, 153, 0.22);
    }

    .summary-compare-badge.is-down {
        background: rgba(191, 219, 254, 0.18);
    }

    .summary-compare-badge.is-flat {
        background: rgba(255, 255, 255, 0.16);
    }

    .summary-compare-main {
        font-size: 1.02rem;
        font-weight: 900;
        line-height: 1.2;
        color: #fff;
    }

    .summary-compare-sub {
        margin-top: 0.2rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.88);
    }

    .accent-blue {
        background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
        box-shadow: 0 18px 34px rgba(29, 78, 216, 0.24);
    }

    .accent-teal {
        background: linear-gradient(135deg, #db2777 0%, #ec4899 100%);
        box-shadow: 0 18px 34px rgba(219, 39, 119, 0.24);
    }

    .accent-orange {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        box-shadow: 0 18px 34px rgba(15, 118, 110, 0.24);
    }

    .accent-red {
        background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
        box-shadow: 0 18px 34px rgba(249, 115, 22, 0.24);
    }

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
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.99), rgba(244, 248, 252, 0.96));
        position: relative;
        overflow: hidden;
    }

    .chart-card::before,
    .detail-card::before {
        content: "";
        position: absolute;
        inset: 0 auto auto 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #204d7a 0%, #5d85ab 55%, #c89b43 100%);
    }

    .chart-card::after,
    .detail-card::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.22), transparent 30%);
        pointer-events: none;
    }

    .chart-card-head {
        margin-bottom: 0.9rem;
    }

    .chart-card-head h3 {
        font-size: 1.05rem;
        line-height: 1.3;
        max-width: 78%;
        color: #204d7a;
        letter-spacing: 0.01em;
    }

    .card-detail-button {
        padding: 0.7rem 0.95rem;
        border-radius: 14px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 1), rgba(243, 247, 251, 0.95));
        color: #1b4d8e;
        font-weight: 700;
        font-size: 0.85rem;
        border: 1px solid rgba(23, 58, 100, 0.08);
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.07);
    }

    .card-detail-button.icon-only {
        width: 42px;
        min-width: 42px;
        height: 42px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .chart-canvas {
        position: relative;
        min-height: 320px;
    }

    .chart-card[data-drawer-section="komposisi"] .chart-canvas {
        min-height: 380px;
    }

    .charts-grid-detail {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .karisidenan-detail-shell[hidden] {
        display: none !important;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .table-dashboard {
        margin-bottom: 0;
        font-size: 0.92rem;
    }

    .table-responsive {
        border-radius: 18px;
        overflow-x: auto;
        overflow-y: hidden;
        border: 1px solid rgba(23, 58, 100, 0.08);
        background: rgba(255, 255, 255, 0.78);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.84);
    }

    .table-responsive.is-scrollable::after {
        content: "Geser kiri/kanan untuk melihat semua kolom";
        display: block;
        padding: 0.45rem 0.9rem 0.7rem;
        color: #617287;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.03em;
    }

    .table-dashboard thead th {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--muted);
        border-bottom-width: 1px;
        white-space: nowrap;
        background: rgba(247, 250, 252, 0.96);
    }

    .table-dashboard tbody td {
        vertical-align: middle;
        white-space: nowrap;
    }

    .table-dashboard .table-number-col {
        min-width: 72px;
        text-align: center;
        font-weight: 800;
        color: #173a64;
    }

    .table-pagination-shell {
        margin-top: 0.9rem;
    }

    .table-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.9rem;
        flex-wrap: wrap;
        padding: 0.85rem 0.95rem;
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(32, 77, 122, 0.06), rgba(200, 155, 67, 0.08));
        border: 1px solid rgba(23, 58, 100, 0.08);
    }

    .table-pagination-meta {
        color: #4b6078;
        font-size: 0.84rem;
        font-weight: 700;
    }

    .table-pagination-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        flex-wrap: wrap;
    }

    .table-pagination-button {
        min-width: 40px;
        height: 40px;
        padding: 0 0.8rem;
        border: 1px solid rgba(23, 58, 100, 0.10);
        border-radius: 12px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(243, 247, 251, 0.96));
        color: #1d4b7c;
        font-size: 0.88rem;
        font-weight: 800;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.05);
        transition: 0.22s ease;
    }

    .table-pagination-button:hover:not(:disabled) {
        transform: translateY(-1px);
        border-color: rgba(37, 99, 235, 0.28);
    }

    .table-pagination-button.is-active {
        border-color: transparent;
        background: linear-gradient(135deg, #1e5cad 0%, #0b8f7a 100%);
        color: #fff;
        box-shadow: 0 12px 22px rgba(30, 92, 173, 0.22);
    }

    .table-pagination-button.is-disabled,
    .table-pagination-button:disabled {
        opacity: 0.45;
        cursor: not-allowed;
        box-shadow: none;
    }

    .chart-card[data-drawer-section="kontribusi"] .chart-canvas {
        min-height: 360px;
    }

    .leaflet-container {
        background: #dbeafe;
    }

    .leaflet-top.leaflet-right {
        margin-top: 18px;
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        gap: 0.65rem;
    }

    .leaflet-top.leaflet-right .leaflet-control {
        margin: 0;
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

    .map-back-control.leaflet-bar {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.14);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 250, 253, 0.96));
    }

    .map-back-button {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        min-height: 44px;
        padding: 0.72rem 0.95rem;
        border: 0;
        background: transparent;
        color: #18314f;
        font-size: 0.84rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .map-back-button:hover:not(:disabled) {
        background: rgba(37, 99, 235, 0.08);
    }

    .map-back-button:disabled {
        color: #94a3b8;
        cursor: not-allowed;
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

    .map-info-tabs {
        display: flex;
        gap: 0.45rem;
        flex-wrap: wrap;
        margin-top: 0.9rem;
    }

    .map-info-tab {
        padding: 0.45rem 0.72rem;
        border-radius: 999px;
        border: 0;
        background: rgba(15, 23, 42, 0.06);
        color: #334155;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
    }

    .map-info-tab.is-active {
        background: linear-gradient(135deg, #1e5cad 0%, #0b8f7a 100%);
        color: #fff;
    }

    .map-tab-panel {
        display: none;
        margin-top: 0.8rem;
    }

    .map-tab-panel.is-active {
        display: block;
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

    .legend-toggle-button {
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #334155;
        background: rgba(15, 23, 42, 0.06);
    }

    .legend-body.is-collapsed {
        display: none;
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

    .popup-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.85rem;
        flex-wrap: wrap;
    }

    .popup-action-button {
        border: 0;
        border-radius: 12px;
        padding: 0.55rem 0.8rem;
        font-weight: 700;
        font-size: 0.82rem;
        cursor: pointer;
        color: #fff;
        background: linear-gradient(135deg, #1e5cad 0%, #0b8f7a 100%);
    }

    .popup-action-button.secondary {
        color: #1d4ed8;
        background: rgba(37, 99, 235, 0.12);
    }

    .leaflet-tooltip.region-label-tooltip {
        background: rgba(24, 49, 79, 0.9);
        border: 0;
        border-radius: 999px;
        color: #fff;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.18);
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.24rem 0.5rem;
        white-space: nowrap;
    }

    .leaflet-tooltip.region-label-tooltip::before {
        display: none;
    }

    .loading-state {
        opacity: 0.75;
        pointer-events: none;
    }

    .dashboard-drawer-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.36);
        opacity: 0;
        visibility: hidden;
        transition: 0.24s ease;
        z-index: 1290;
    }

    .dashboard-drawer-backdrop.is-open {
        opacity: 1;
        visibility: visible;
    }

    .dashboard-drawer {
        position: fixed;
        top: 0;
        right: 0;
        width: min(560px, 100%);
        height: 100vh;
        background: rgba(255, 255, 255, 0.98);
        box-shadow: -22px 0 44px rgba(15, 23, 42, 0.18);
        transform: translateX(100%);
        transition: transform 0.28s ease;
        z-index: 1300;
        display: flex;
        flex-direction: column;
    }

    .dashboard-drawer.is-open {
        transform: translateX(0);
    }

    .dashboard-drawer-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.2rem 1.2rem 1rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.18);
    }

    .dashboard-drawer-head h3 {
        margin: 0;
    }

    .dashboard-drawer-head p {
        margin: 0.35rem 0 0;
        color: var(--muted);
    }

    .drawer-badge {
        margin-bottom: 0.6rem;
    }

    .drawer-close-button {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(15, 23, 42, 0.05);
        color: #0f172a;
    }

    .dashboard-drawer-body {
        padding: 1rem 1.2rem 1.2rem;
        overflow: auto;
    }

    .drawer-summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .drawer-stat-card {
        padding: 0.9rem 1rem;
        border-radius: 16px;
        background: rgba(37, 99, 235, 0.06);
    }

    .drawer-stat-card span {
        display: block;
        font-size: 0.78rem;
        color: var(--muted);
        margin-bottom: 0.3rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .drawer-stat-card strong {
        font-size: 1rem;
        color: #18314f;
    }

    .drawer-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 0.9rem;
    }

    .drawer-table-wrap {
        border-radius: 18px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        overflow: hidden;
        background: #fff;
    }

    .drawer-ai-insight {
        margin-bottom: 1rem;
        padding: 1rem 1rem 0.95rem;
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(248, 250, 252, 0.98), rgba(255, 255, 255, 1));
        border: 1px solid rgba(148, 163, 184, 0.18);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.08);
    }

    .drawer-ai-insight-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.7rem;
    }

    .drawer-ai-insight-head strong {
        color: #18314f;
        font-size: 0.98rem;
    }

    .drawer-ai-insight-body {
        color: #24364f;
        font-size: 0.96rem;
        line-height: 1.72;
        text-align: left;
        white-space: normal;
    }

    .drawer-ai-insight-body p {
        margin: 0 0 0.95rem;
    }

    .drawer-ai-insight-body p:last-child {
        margin-bottom: 0;
    }

    .dashboard-export-modal {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 28px 80px rgba(15, 23, 42, 0.24);
    }

    .dashboard-export-modal-head {
        align-items: flex-start;
        padding: 1.25rem 1.25rem 0.9rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.18);
    }

    .dashboard-export-modal-head h5 {
        margin: 0;
        color: #18314f;
        font-weight: 800;
    }

    .dashboard-export-modal-head p {
        margin: 0.4rem 0 0;
        color: var(--muted);
        font-size: 0.94rem;
    }

    .dashboard-export-modal-body {
        display: grid;
        gap: 0.9rem;
        padding: 1.1rem 1.25rem 1.25rem;
    }

    .dashboard-export-option {
        display: flex;
        align-items: center;
        gap: 0.95rem;
        width: 100%;
        text-align: left;
        padding: 1rem 1.05rem;
        border-radius: 18px;
        border: 1px solid rgba(148, 163, 184, 0.22);
        background: linear-gradient(135deg, rgba(248, 250, 252, 0.96), rgba(255, 255, 255, 1));
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .dashboard-export-option:hover {
        transform: translateY(-2px);
        border-color: rgba(37, 99, 235, 0.28);
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.12);
    }

    .dashboard-export-option-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .dashboard-export-option-copy {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .dashboard-export-option-copy strong {
        color: #18314f;
        font-size: 1rem;
    }

    .dashboard-export-option-copy small {
        color: var(--muted);
        font-size: 0.9rem;
    }

    .page-footer {
        margin: 1.5rem 0 0;
        padding: 1.25rem 1.35rem 1rem;
        border-radius: 0;
        background:
            linear-gradient(90deg, rgba(17, 13, 10, 0.96) 0%, rgba(25, 17, 13, 0.9) 32%, rgba(47, 28, 17, 0.74) 58%, rgba(24, 18, 14, 0.84) 100%),
            linear-gradient(180deg, rgba(18, 16, 14, 0.26), rgba(18, 16, 14, 0.92));
        color: #ffffff;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 -10px 30px rgba(15, 35, 62, 0.10);
        overflow: hidden;
        position: relative;
    }

    .page-footer::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 18% 36%, rgba(208, 144, 47, 0.18), transparent 28%),
            linear-gradient(135deg, rgba(255, 255, 255, 0.04), transparent 32%, rgba(208, 144, 47, 0.08) 100%);
        pointer-events: none;
    }

    .page-footer::after {
        content: none;
    }

    .page-footer-main {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 0.95rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        position: relative;
        z-index: 1;
        max-width: 1680px;
        margin: 0 auto;
    }

    .page-footer-brand {
        max-width: 760px;
    }

    .page-footer-kicker {
        display: inline-flex;
        padding: 0.32rem 0.68rem;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.12), rgba(216, 174, 98, 0.14));
        color: rgba(255, 255, 255, 0.96);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        margin-bottom: 0.7rem;
    }

    .page-footer h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 900;
        letter-spacing: 0.03em;
        color: #ffffff;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.12);
    }

    .page-footer p {
        margin: 0.45rem 0 0;
        max-width: 720px;
        color: rgba(255, 255, 255, 0.94);
        font-size: 0.9rem;
        line-height: 1.65;
    }

    .page-footer-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 0.95rem;
    }

    .page-footer-tags span {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.62rem 0.88rem;
        border-radius: 999px;
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.22);
        color: rgba(255, 244, 231, 0.94);
        font-size: 0.82rem;
        font-weight: 700;
        box-shadow: none;
    }

    .page-footer-meta {
        display: grid;
        gap: 0.55rem;
        min-width: 280px;
        position: relative;
        z-index: 1;
    }

    .page-footer-meta span,
    .page-footer-bottom span {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .page-footer-meta span {
        padding: 0.55rem 0.8rem;
        border-radius: 16px;
        background: transparent;
        color: rgba(255, 244, 231, 0.94);
        font-size: 0.84rem;
        border: 1px solid rgba(255, 255, 255, 0.22);
        box-shadow: none;
    }

    .page-footer-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-top: 0.85rem;
        color: rgba(255, 255, 255, 0.90);
        font-size: 0.82rem;
        position: relative;
        z-index: 1;
        max-width: 1680px;
        margin: 0 auto;
    }

    @media (max-width: 1400px) {
        .map-filter-modal {
            width: min(980px, calc(100vw - 24px));
        }
    }

    @media (max-width: 1200px) {

        .summary-grid,
        .charts-grid,
        .detail-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .hero-carousel-control {
            display: none;
        }

        .map-top-overlay {
            width: 100%;
        }

        .map-filter-modal {
            width: min(860px, calc(100vw - 20px));
        }

        .drawer-summary-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {

        .topbar-surface,
        .section-banner,
        .dashboard-heading,
        .chart-card-head,
        .detail-head,
        .map-filter-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .topbar-actions,
        .section-banner-highlights {
            width: 100%;
            justify-content: flex-start;
        }

        .topbar-menu-toggle {
            display: inline-flex;
            margin-left: auto;
        }

        .page-shell {
            padding: 0 0.45rem 1rem;
        }

        .topbar {
            top: 0.65rem;
        }

        .topbar-inner {
            padding: 0 0.5rem;
        }

        .topbar-surface {
            padding: 0.95rem 1rem;
            border-radius: 28px;
            position: relative;
        }

        .topbar-link {
            padding: 0.62rem 0.82rem;
            font-size: 0.84rem;
        }

        .topbar-actions {
            display: none;
            gap: 0.55rem;
            flex-direction: column;
            align-items: stretch;
            padding-top: 0.2rem;
        }

        .topbar-actions.is-open {
            display: flex;
        }

        .topbar-link {
            width: 100%;
            justify-content: flex-start;
            background: rgba(255, 255, 255, 0.72);
        }

        .hero-carousel-shell {
            min-height: 92vh;
            margin: -7.3rem -0.45rem 0.4rem;
            padding: 0;
            border-radius: 0 0 28px 28px;
        }

        .hero-carousel-slider .carousel-inner {
            min-height: 92vh;
            border-radius: 0 0 28px 28px;
        }

        .hero-carousel-slider .carousel-item {
            min-height: 92vh;
            height: 92vh;
            padding: 11.6rem 1rem 7rem;
            background-size: cover;
        }

        .hero-carousel-grid {
            min-height: 100%;
        }

        .hero-carousel-copy h1 {
            font-size: clamp(1.7rem, 7vw, 2.45rem);
        }

        .hero-carousel-copy p {
            font-size: 0.94rem;
            line-height: 1.72;
        }

        .hero-carousel-copy {
            margin-top: -0.25rem;
            padding-right: 0;
        }

        .hero-slide-city .hero-carousel-copy {
            margin-top: 0.55rem;
        }

        .hero-meta-row {
            gap: 0.8rem;
        }

        .hero-carousel-indicators {
            bottom: 1.2rem;
            width: calc(100% - 2rem);
        }

        .map-floating-dock-shell {
            top: 0.45rem;
            margin-top: -3.4rem;
        }

        .summary-grid,
        .charts-grid,
        .detail-grid,
        .charts-grid-detail {
            grid-template-columns: 1fr;
        }

        .map-filter-modal {
            top: 84px;
            width: calc(100vw - 16px);
        }

        .map-filter-badges {
            justify-content: flex-start;
            flex-wrap: wrap;
        }

        .section-banner-highlights {
            justify-content: flex-start;
        }

        .map-filter-bar {
            grid-template-columns: 1fr;
            gap: 0.8rem;
        }

        .map-filter-tools {
            width: 100%;
            justify-content: flex-start;
        }

        .brand-lockup strong {
            font-size: 1.05rem;
        }

        .brand-lockup small {
            font-size: 0.75rem;
        }

        .chart-card-head h3 {
            max-width: 100%;
        }

        #peta {
            min-height: 620px;
            height: calc(100vh - 146px);
        }

        .map-top-overlay {
            top: 14px;
            width: calc(100% - 16px);
        }

        .page-footer-main,
        .page-footer-bottom {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-footer {
            margin: 1.5rem 0 0;
        }

        .section-banner {
            margin: -1px -0.45rem 0.95rem;
            padding: 1.15rem 1rem 1rem;
        }

        .section-banner h2 {
            white-space: normal;
        }

        .page-footer-meta {
            min-width: 0;
            width: 100%;
        }

        .map-floating-card {
            min-width: 270px;
            max-width: 315px;
        }

        .leaflet-top.leaflet-right {
            margin-top: 12px;
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            gap: 0.5rem;
            flex-wrap: nowrap;
        }

        .dashboard-drawer {
            width: 100%;
        }

        .table-pagination {
            align-items: stretch;
        }

        .table-pagination-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>





