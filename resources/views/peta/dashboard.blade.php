<section class="dashboard-shell">
    <div class="dashboard-heading">
        <div>
            <span class="section-badge">Dashboard Analitik</span>
            <h2 id="dashboard_scope_title">Ringkasan Jawa Timur</h2>
            <p id="dashboard_scope_description">Memuat data wilayah dan performa PAD berdasarkan filter aktif.</p>
        </div>
        <button type="button" class="hero-button hero-button-light export-trigger" data-export-section="detail_wilayah">
            <i class="bi bi-download"></i>
            Export Detail Wilayah
        </button>
    </div>

    <div class="summary-grid">
        <article class="summary-card accent-blue">
            <span class="summary-label">Total Anggaran</span>
            <strong id="summary_anggaran">Rp 0 M</strong>
            <small>Target PAD pada cakupan wilayah aktif.</small>
        </article>
        <article class="summary-card accent-teal">
            <span class="summary-label">Total Realisasi</span>
            <strong id="summary_realisasi">Rp 0 M</strong>
            <small>Akumulasi realisasi PAD sesuai filter.</small>
        </article>
        <article class="summary-card accent-orange">
            <span class="summary-label">Selisih</span>
            <strong id="summary_selisih">Rp 0 M</strong>
            <small>Gap antara realisasi dan anggaran.</small>
        </article>
        <article class="summary-card accent-red">
            <span class="summary-label">Persentase Capaian</span>
            <strong id="summary_persentase">0%</strong>
            <small id="summary_status">Status kinerja akan tampil di sini.</small>
        </article>
    </div>

    <div class="charts-grid">
        <article class="chart-card">
            <div class="chart-card-head">
                <div>
                    <h3 id="title_perbandingan_akun">Perbandingan Anggaran dan Realisasi</h3>
                    <p id="desc_perbandingan_akun"></p>
                </div>
                <button type="button" class="chart-export-button export-trigger" data-export-section="perbandingan_akun">
                    Export
                </button>
            </div>
            <div class="chart-canvas"><canvas id="chart_perbandingan_akun"></canvas></div>
        </article>

        <article class="chart-card">
            <div class="chart-card-head">
                <div>
                    <h3 id="title_tren_tahunan">Tren Tahunan</h3>
                    <p id="desc_tren_tahunan"></p>
                </div>
                <button type="button" class="chart-export-button export-trigger" data-export-section="tren_tahunan">
                    Export
                </button>
            </div>
            <div class="chart-canvas"><canvas id="chart_tren_tahunan"></canvas></div>
        </article>

        <article class="chart-card">
            <div class="chart-card-head">
                <div>
                    <h3 id="title_peringkat">Peringkat</h3>
                    <p id="desc_peringkat"></p>
                </div>
                <button type="button" class="chart-export-button export-trigger" data-export-section="peringkat">
                    Export
                </button>
            </div>
            <div class="chart-canvas"><canvas id="chart_peringkat"></canvas></div>
        </article>

        <article class="chart-card">
            <div class="chart-card-head">
                <div>
                    <h3 id="title_komposisi">Komposisi Realisasi</h3>
                    <p id="desc_komposisi"></p>
                </div>
                <button type="button" class="chart-export-button export-trigger" data-export-section="komposisi">
                    Export
                </button>
            </div>
            <div class="chart-canvas"><canvas id="chart_komposisi"></canvas></div>
        </article>

        <article class="chart-card">
            <div class="chart-card-head">
                <div>
                    <h3 id="title_kontribusi">Kontribusi</h3>
                    <p id="desc_kontribusi"></p>
                </div>
                <button type="button" class="chart-export-button export-trigger" data-export-section="kontribusi">
                    Export
                </button>
            </div>
            <div class="chart-canvas"><canvas id="chart_kontribusi"></canvas></div>
        </article>

        <article class="chart-card">
            <div class="chart-card-head">
                <div>
                    <h3 id="title_pertumbuhan">Pertumbuhan YoY</h3>
                    <p id="desc_pertumbuhan"></p>
                </div>
                <button type="button" class="chart-export-button export-trigger" data-export-section="pertumbuhan">
                    Export
                </button>
            </div>
            <div class="chart-canvas"><canvas id="chart_pertumbuhan"></canvas></div>
        </article>
    </div>

    <div class="detail-grid">
        <article class="detail-card">
            <div class="detail-head">
                <div>
                    <h3 id="detail_akun_title">Detail Akun PAD</h3>
                    <p>Rincian setiap jenis PAD agar analisis chart lebih mudah ditelusuri.</p>
                </div>
                <button type="button" class="chart-export-button export-trigger" data-export-section="detail_akun">
                    Export
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-dashboard" id="table_detail_akun">
                    <thead></thead>
                    <tbody></tbody>
                </table>
            </div>
        </article>

        <article class="detail-card">
            <div class="detail-head">
                <div>
                    <h3 id="detail_wilayah_title">Detail Wilayah</h3>
                    <p>Rangkuman wilayah aktif untuk pendalaman analisis dan bahan export.</p>
                </div>
                <button type="button" class="chart-export-button export-trigger" data-export-section="detail_wilayah">
                    Export
                </button>
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
