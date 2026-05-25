<section class="dashboard-shell">
    <div class="summary-grid">
        <article class="summary-card accent-blue">
            <div class="summary-card-head">
                <span class="summary-label">Total Anggaran</span>
                <button type="button" class="card-detail-button icon-only dashboard-drawer-trigger" data-drawer-section="ringkasan" aria-label="Detail ringkasan"><i class="bi bi-info-circle"></i></button>
            </div>
            <strong id="summary_anggaran">Rp 0</strong>
            <div class="summary-compare" id="summary_anggaran_compare">Vs tahun sebelumnya: data belum tersedia.</div>
        </article>
        <article class="summary-card accent-teal">
            <div class="summary-card-head">
                <span class="summary-label">Total Realisasi</span>
                <button type="button" class="card-detail-button icon-only dashboard-drawer-trigger" data-drawer-section="ringkasan" aria-label="Detail ringkasan"><i class="bi bi-info-circle"></i></button>
            </div>
            <strong id="summary_realisasi">Rp 0</strong>
            <div class="summary-compare" id="summary_realisasi_compare">Vs tahun sebelumnya: data belum tersedia.</div>
        </article>
        <article class="summary-card accent-orange">
            <div class="summary-card-head">
                <span class="summary-label">Selisih</span>
                <button type="button" class="card-detail-button icon-only dashboard-drawer-trigger" data-drawer-section="ringkasan" aria-label="Detail ringkasan"><i class="bi bi-info-circle"></i></button>
            </div>
            <strong id="summary_selisih">Rp 0</strong>
            <div class="summary-compare" id="summary_selisih_compare">Vs tahun sebelumnya: data belum tersedia.</div>
        </article>
        <article class="summary-card accent-red">
            <div class="summary-card-head">
                <span class="summary-label">Persentase Capaian</span>
                <button type="button" class="card-detail-button icon-only dashboard-drawer-trigger" data-drawer-section="ringkasan" aria-label="Detail ringkasan"><i class="bi bi-info-circle"></i></button>
            </div>
            <strong id="summary_persentase">0%</strong>
            <div class="summary-compare" id="summary_persentase_compare">Vs tahun sebelumnya: data belum tersedia.</div>
        </article>
    </div>

    <div class="charts-grid">
        <article class="chart-card" data-drawer-section="perbandingan_akun">
            <div class="chart-card-head">
                <div>
                    <h3 id="title_perbandingan_akun">Perbandingan Anggaran dan Realisasi</h3>
                    <p id="desc_perbandingan_akun"></p>
                </div>
                <div class="card-actions">
                    <button type="button" class="card-detail-button icon-only dashboard-drawer-trigger" data-drawer-section="perbandingan_akun" aria-label="Detail perbandingan akun"><i class="bi bi-info-circle"></i></button>
                    <button type="button" class="chart-export-button icon-only export-trigger" data-export-section="perbandingan_akun" aria-label="Export perbandingan akun"><i class="bi bi-download"></i></button>
                </div>
            </div>
            <div class="chart-canvas"><canvas id="chart_perbandingan_akun"></canvas></div>
        </article>

        <article class="chart-card" data-drawer-section="tren_tahunan">
            <div class="chart-card-head">
                <div>
                    <h3 id="title_tren_tahunan">Tren Tahunan</h3>
                    <p id="desc_tren_tahunan"></p>
                </div>
                <div class="card-actions">
                    <button type="button" class="card-detail-button icon-only dashboard-drawer-trigger" data-drawer-section="tren_tahunan" aria-label="Detail tren tahunan"><i class="bi bi-info-circle"></i></button>
                    <button type="button" class="chart-export-button icon-only export-trigger" data-export-section="tren_tahunan" aria-label="Export tren tahunan"><i class="bi bi-download"></i></button>
                </div>
            </div>
            <div class="chart-canvas"><canvas id="chart_tren_tahunan"></canvas></div>
        </article>

        <article class="chart-card" data-drawer-section="peringkat">
            <div class="chart-card-head">
                <div>
                    <h3 id="title_peringkat">Peringkat</h3>
                    <p id="desc_peringkat"></p>
                </div>
                <div class="card-actions">
                    <button type="button" class="card-detail-button icon-only dashboard-drawer-trigger" data-drawer-section="peringkat" aria-label="Detail peringkat"><i class="bi bi-info-circle"></i></button>
                    <button type="button" class="chart-export-button icon-only export-trigger" data-export-section="peringkat" aria-label="Export peringkat"><i class="bi bi-download"></i></button>
                </div>
            </div>
            <div class="chart-canvas"><canvas id="chart_peringkat"></canvas></div>
        </article>

        <article class="chart-card" data-drawer-section="komposisi">
            <div class="chart-card-head">
                <div>
                    <h3 id="title_komposisi">Komposisi Realisasi</h3>
                    <p id="desc_komposisi"></p>
                </div>
                <div class="card-actions">
                    <button type="button" class="card-detail-button icon-only dashboard-drawer-trigger" data-drawer-section="komposisi" aria-label="Detail komposisi"><i class="bi bi-info-circle"></i></button>
                    <button type="button" class="chart-export-button icon-only export-trigger" data-export-section="komposisi" aria-label="Export komposisi"><i class="bi bi-download"></i></button>
                </div>
            </div>
            <div class="chart-canvas"><canvas id="chart_komposisi"></canvas></div>
        </article>

        <article class="chart-card" data-drawer-section="kontribusi">
            <div class="chart-card-head">
                <div>
                    <h3 id="title_kontribusi">Kontribusi</h3>
                    <p id="desc_kontribusi"></p>
                </div>
                <div class="card-actions">
                    <button type="button" class="card-detail-button icon-only dashboard-drawer-trigger" data-drawer-section="kontribusi" aria-label="Detail kontribusi"><i class="bi bi-info-circle"></i></button>
                    <button type="button" class="chart-export-button icon-only export-trigger" data-export-section="kontribusi" aria-label="Export kontribusi"><i class="bi bi-download"></i></button>
                </div>
            </div>
            <div class="chart-canvas"><canvas id="chart_kontribusi"></canvas></div>
        </article>

        <article class="chart-card" data-drawer-section="pertumbuhan">
            <div class="chart-card-head">
                <div>
                    <h3 id="title_pertumbuhan">Pertumbuhan YoY</h3>
                    <p id="desc_pertumbuhan"></p>
                </div>
                <div class="card-actions">
                    <button type="button" class="card-detail-button icon-only dashboard-drawer-trigger" data-drawer-section="pertumbuhan" aria-label="Detail pertumbuhan"><i class="bi bi-info-circle"></i></button>
                    <button type="button" class="chart-export-button icon-only export-trigger" data-export-section="pertumbuhan" aria-label="Export pertumbuhan"><i class="bi bi-download"></i></button>
                </div>
            </div>
            <div class="chart-canvas"><canvas id="chart_pertumbuhan"></canvas></div>
        </article>
    </div>

    <section id="karisidenan_trend_detail" class="karisidenan-detail-shell" hidden>
        <div class="dashboard-heading">
            <div>
                <span class="section-badge">Detail Karisidenan</span>
                <h2 id="karisidenan_detail_title">Tren Tahunan per Wilayah</h2>
                <p id="karisidenan_detail_description">Detail tren tahunan untuk tiap wilayah dalam karisidenan aktif.</p>
            </div>
            <button type="button" class="hero-button hero-button-light" id="close_karisidenan_detail">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </button>
        </div>
        <div id="karisidenan_trend_grid" class="charts-grid charts-grid-detail"></div>
    </section>

    <div class="detail-grid">
        <article class="detail-card" data-drawer-section="detail_akun">
            <div class="detail-head">
                <div>
                    <h3 id="detail_akun_title">Detail Akun PAD</h3>
                    <p>Rincian setiap jenis PAD agar analisis chart lebih mudah ditelusuri.</p>
                </div>
                <div class="card-actions">
                    <button type="button" class="card-detail-button icon-only dashboard-drawer-trigger" data-drawer-section="detail_akun" aria-label="Detail akun"><i class="bi bi-info-circle"></i></button>
                    <button type="button" class="chart-export-button icon-only export-trigger" data-export-section="detail_akun" aria-label="Export detail akun"><i class="bi bi-download"></i></button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-dashboard" id="table_detail_akun">
                    <thead></thead>
                    <tbody></tbody>
                </table>
            </div>
        </article>

        <article class="detail-card" data-drawer-section="detail_wilayah">
            <div class="detail-head">
                <div>
                    <h3 id="detail_wilayah_title">Detail Wilayah</h3>
                    <p>Rangkuman wilayah aktif untuk pendalaman analisis dan bahan export.</p>
                </div>
                <div class="card-actions">
                    <button type="button" class="card-detail-button icon-only dashboard-drawer-trigger" data-drawer-section="detail_wilayah" aria-label="Detail wilayah"><i class="bi bi-info-circle"></i></button>
                    <button type="button" class="chart-export-button icon-only export-trigger" data-export-section="detail_wilayah" aria-label="Export detail wilayah"><i class="bi bi-download"></i></button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-dashboard" id="table_detail_wilayah">
                    <thead></thead>
                    <tbody></tbody>
                </table>
            </div>
        </article>
    </div>
</section>

<div id="dashboard_drawer_backdrop" class="dashboard-drawer-backdrop"></div>
<aside id="dashboard_drawer" class="dashboard-drawer" aria-hidden="true">
    <div class="dashboard-drawer-head">
        <div>
            <span class="section-badge drawer-badge">Detail Analitik</span>
            <h3 id="drawer_title">Detail Dashboard</h3>
            <p id="drawer_description">Rincian chart dan data mentah.</p>
        </div>
        <button type="button" class="drawer-close-button" id="dashboard_drawer_close" aria-label="Tutup detail">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="dashboard-drawer-body">
        <div class="drawer-summary-grid" id="drawer_summary_grid"></div>
        <div class="drawer-actions">
            <button type="button" class="hero-button hero-button-light export-trigger" id="drawer_export_button" data-export-section="ringkasan">
                <i class="bi bi-download"></i>
                Download Data Mentah
            </button>
        </div>
        <div class="table-responsive drawer-table-wrap">
            <table class="table table-dashboard" id="drawer_table">
                <thead></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</aside>
