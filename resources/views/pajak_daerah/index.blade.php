<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pajak Daerah Bapenda Lamongan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .dashboard-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .card-custom {
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            margin-bottom: 30px;
            border: none;
        }

        .card-custom:hover {
            transform: translateY(-5px);
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .stats-card h3 {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }

        .stats-card p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .chart-container {
            position: relative;
            height: 400px;
            padding: 20px;
        }

        h1,
        h2,
        h3,
        h4 {
            color: #2d3748;
        }

        .section-title {
            font-weight: 700;
            margin: 30px 0 20px 0;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .loading {
            text-align: center;
            padding: 50px;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="dashboard-header">
            <h1 class="text-center mb-3">📊 Dashboard Visualisasi & Analisis Pajak Daerah</h1>
            <h4 class="text-center text-muted">Badan Pendapatan Daerah (Bapenda) Kabupaten Lamongan</h4>
            <p class="text-center text-muted">Periode 2021-2025</p>
        </div>

        <div class="filter-section">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tahun:</label>
                    <select class="form-select" id="filterTahun">
                        <option value="">Semua Tahun</option>
                        <option value="2021">2021</option>
                        <option value="2022">2022</option>
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Bulan:</label>
                    <select class="form-select" id="filterBulan">
                        <option value="">Semua Bulan</option>
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Jenis Pajak:</label>
                    <select class="form-select" id="filterKode">
                        <option value="">Semua Jenis Pajak</option>
                        <option value="4.1.01">PAJAK DAERAH</option>
                        <option value="4.1.01.09">Pajak Reklame</option>
                        <option value="4.1.01.12">Pajak Air Tanah</option>
                        <option value="4.1.01.13">Pajak Sarang Burung Walet</option>
                        <option value="4.1.01.14">Pajak Mineral Bukan Logam dan Batuan</option>
                        <option value="4.1.01.15">PBB-P2</option>
                        <option value="4.1.01.16">BPHTB</option>
                        <option value="4.1.01.19">PBJT</option>
                        <option value="4.1.01.20">Opsen PKB</option>
                        <option value="4.1.01.21">Opsen BBNKB</option>
                    </select>
                </div>
            </div>
        </div>

        <h2 class="section-title">📈 Overview Dashboard - KPI Utama</h2>
        <div class="row" id="statsContainer">
            <div class="col-md-3">
                <div class="stats-card">
                    <p>Total Target</p>
                    <h3 id="totalTarget">-</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <p>Total Realisasi</p>
                    <h3 id="totalRealisasi">-</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <p>Persentase Pencapaian</p>
                    <h3 id="persentasePencapaian">-</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <p>Selisih</p>
                    <h3 id="selisih">-</h3>
                </div>
            </div>
        </div>

        <h2 class="section-title">📊 Analisis Tahunan (2021-2025)</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Trend Realisasi vs Target per Tahun</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartTrendTahunan"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Persentase Pencapaian per Tahun</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartPersentaseTahunan"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Growth Rate Year-over-Year</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartGrowthRate"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="section-title">📅 Analisis Bulanan</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Pola Penerimaan Bulanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartTrendBulanan"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Realisasi Kumulatif Bulanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartKumulatifBulanan"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Perbandingan Bulanan vs Target</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartBulananVsTarget"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="section-title">🎯 Analisis per Jenis Pajak</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Kontribusi per Jenis Pajak</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartKontribusiPajak"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header" style="background: #667eea; color: white;">
                        <h5 class="mb-0">Top 5 Pajak Tertinggi</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartTop5Pajak"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header" style="background: #f093fb; color: white;">
                        <h5 class="mb-0">Pencapaian per Jenis Pajak</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartPencapaianPajak"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="section-title">📉 Analisis Variance & Gap</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header" style="background: #4facfe; color: white;">
                        <h5 class="mb-0">Gap Analysis Target vs Realisasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartGapAnalysis"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header" style="background: #43e97b; color: white;">
                        <h5 class="mb-0">Performa Pajak (Over/Under)</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartPerformaPajak"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header" style="background: #fa709a; color: white;">
                        <h5 class="mb-0">Trend Selisih per Tahun</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartTrendSelisih"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        let charts = {};

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(angka);
        }

        function formatPersen(angka) {
            return (angka * 100).toFixed(2) + '%';
        }

        function loadDataPertahun() {
            const tahun = $('#filterTahun').val();
            const kode = $('#filterKode').val();

            $.ajax({
                url: '/pajak-daerah/data-pertahun',
                method: 'GET',
                data: {
                    tahun: tahun,
                    kode: kode
                },
                success: function(data) {
                    updateDashboardTahunan(data);
                }
            });
        }

        function loadDataPerbulan() {
            const tahun = $('#filterTahun').val();
            const bulan = $('#filterBulan').val();
            const kode = $('#filterKode').val();

            $.ajax({
                url: '/pajak-daerah/data-perbulan',
                method: 'GET',
                data: {
                    tahun: tahun,
                    bulan: bulan,
                    kode: kode
                },
                success: function(data) {
                    updateDashboardBulanan(data);
                }
            });
        }

        function updateDashboardTahunan(data) {
            updateStats(data);
            createChartTrendTahunan(data);
            createChartPersentaseTahunan(data);
            createChartGrowthRate(data);
            createChartKontribusiPajak(data);
            createChartTop5Pajak(data);
            createChartPencapaianPajak(data);
            createChartGapAnalysis(data);
            createChartPerformaPajak(data);
            createChartTrendSelisih(data);
        }

        function updateDashboardBulanan(data) {
            createChartTrendBulanan(data);
            createChartKumulatifBulanan(data);
            createChartBulananVsTarget(data);
        }

        function updateStats(data) {
            let totalTarget = 0;
            let totalRealisasi = 0;

            data.forEach(item => {
                totalTarget += parseFloat(item.total_target || 0);
                totalRealisasi += parseFloat(item.total_realisasi || 0);
            });

            const persentase = totalTarget > 0 ? (totalRealisasi / totalTarget) : 0;
            const selisih = totalRealisasi - totalTarget;

            $('#totalTarget').text(formatRupiah(totalTarget));
            $('#totalRealisasi').text(formatRupiah(totalRealisasi));
            $('#persentasePencapaian').text(formatPersen(persentase));
            $('#selisih').text(formatRupiah(selisih));
        }

        function createChartTrendTahunan(data) {
            const groupedData = {};
            data.forEach(item => {
                if (!groupedData[item.tahun]) {
                    groupedData[item.tahun] = {
                        target: 0,
                        realisasi: 0
                    };
                }
                groupedData[item.tahun].target += parseFloat(item.total_target || 0);
                groupedData[item.tahun].realisasi += parseFloat(item.total_realisasi || 0);
            });

            const labels = Object.keys(groupedData).sort();
            const targetData = labels.map(year => groupedData[year].target);
            const realisasiData = labels.map(year => groupedData[year].realisasi);

            destroyChart('chartTrendTahunan');
            const ctx = document.getElementById('chartTrendTahunan').getContext('2d');
            charts['chartTrendTahunan'] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Target',
                        data: targetData,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Realisasi',
                        data: realisasiData,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function createChartPersentaseTahunan(data) {
            const groupedData = {};
            data.forEach(item => {
                if (!groupedData[item.tahun]) {
                    groupedData[item.tahun] = {
                        target: 0,
                        realisasi: 0
                    };
                }
                groupedData[item.tahun].target += parseFloat(item.total_target || 0);
                groupedData[item.tahun].realisasi += parseFloat(item.total_realisasi || 0);
            });

            const labels = Object.keys(groupedData).sort();
            const persentaseData = labels.map(year => {
                const target = groupedData[year].target;
                const realisasi = groupedData[year].realisasi;
                return target > 0 ? (realisasi / target * 100) : 0;
            });

            destroyChart('chartPersentaseTahunan');
            const ctx = document.getElementById('chartPersentaseTahunan').getContext('2d');
            charts['chartPersentaseTahunan'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Persentase Pencapaian (%)',
                        data: persentaseData,
                        backgroundColor: 'rgba(75, 192, 192, 0.7)',
                        borderColor: 'rgb(75, 192, 192)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        function createChartGrowthRate(data) {
            const groupedData = {};
            data.forEach(item => {
                if (!groupedData[item.tahun]) {
                    groupedData[item.tahun] = 0;
                }
                groupedData[item.tahun] += parseFloat(item.total_realisasi || 0);
            });

            const labels = Object.keys(groupedData).sort();
            const growthData = [];

            for (let i = 1; i < labels.length; i++) {
                const prevYear = groupedData[labels[i - 1]];
                const currentYear = groupedData[labels[i]];
                const growth = prevYear > 0 ? ((currentYear - prevYear) / prevYear * 100) : 0;
                growthData.push(growth);
            }

            destroyChart('chartGrowthRate');
            const ctx = document.getElementById('chartGrowthRate').getContext('2d');
            charts['chartGrowthRate'] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels.slice(1),
                    datasets: [{
                        label: 'Growth Rate YoY (%)',
                        data: growthData,
                        borderColor: 'rgb(153, 102, 255)',
                        backgroundColor: 'rgba(153, 102, 255, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function createChartTrendBulanan(data) {
            const bulanNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
            const groupedData = {};

            data.forEach(item => {
                if (!groupedData[item.bulan]) {
                    groupedData[item.bulan] = 0;
                }
                groupedData[item.bulan] += parseFloat(item.total_realisasi || 0);
            });

            const labels = Object.keys(groupedData).sort((a, b) => a - b).map(b => bulanNames[b - 1]);
            const values = Object.keys(groupedData).sort((a, b) => a - b).map(b => groupedData[b]);

            destroyChart('chartTrendBulanan');
            const ctx = document.getElementById('chartTrendBulanan').getContext('2d');
            charts['chartTrendBulanan'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Realisasi Bulanan',
                        data: values,
                        backgroundColor: 'rgba(255, 206, 86, 0.7)',
                        borderColor: 'rgb(255, 206, 86)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function createChartKumulatifBulanan(data) {
            const bulanNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
            const groupedData = {};

            data.forEach(item => {
                if (!groupedData[item.bulan]) {
                    groupedData[item.bulan] = 0;
                }
                groupedData[item.bulan] += parseFloat(item.total_realisasi_smp || 0);
            });

            const labels = Object.keys(groupedData).sort((a, b) => a - b).map(b => bulanNames[b - 1]);
            const values = Object.keys(groupedData).sort((a, b) => a - b).map(b => groupedData[b]);

            destroyChart('chartKumulatifBulanan');
            const ctx = document.getElementById('chartKumulatifBulanan').getContext('2d');
            charts['chartKumulatifBulanan'] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Realisasi Kumulatif',
                        data: values,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function createChartBulananVsTarget(data) {
            const bulanNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
            const groupedData = {};

            data.forEach(item => {
                if (!groupedData[item.bulan]) {
                    groupedData[item.bulan] = {
                        target: 0,
                        realisasi: 0
                    };
                }
                groupedData[item.bulan].target += parseFloat(item.total_target || 0);
                groupedData[item.bulan].realisasi += parseFloat(item.total_realisasi || 0);
            });

            const labels = Object.keys(groupedData).sort((a, b) => a - b).map(b => bulanNames[b - 1]);
            const targetData = Object.keys(groupedData).sort((a, b) => a - b).map(b => groupedData[b].target);
            const realisasiData = Object.keys(groupedData).sort((a, b) => a - b).map(b => groupedData[b].realisasi);

            destroyChart('chartBulananVsTarget');
            const ctx = document.getElementById('chartBulananVsTarget').getContext('2d');
            charts['chartBulananVsTarget'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Target',
                        data: targetData,
                        backgroundColor: 'rgba(201, 203, 207, 0.7)'
                    }, {
                        label: 'Realisasi',
                        data: realisasiData,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function createChartKontribusiPajak(data) {
            const groupedData = {};

            data.forEach(item => {
                if (item.kode !== '4.1.01') {
                    if (!groupedData[item.nama_pajak]) {
                        groupedData[item.nama_pajak] = 0;
                    }
                    groupedData[item.nama_pajak] += parseFloat(item.total_realisasi || 0);
                }
            });

            const labels = Object.keys(groupedData);
            const values = Object.values(groupedData);

            destroyChart('chartKontribusiPajak');
            const ctx = document.getElementById('chartKontribusiPajak').getContext('2d');
            charts['chartKontribusiPajak'] = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(255, 159, 64, 0.8)',
                            'rgba(201, 203, 207, 0.8)',
                            'rgba(255, 99, 71, 0.8)',
                            'rgba(144, 238, 144, 0.8)',
                            'rgba(173, 216, 230, 0.8)',
                            'rgba(240, 128, 128, 0.8)',
                            'rgba(221, 160, 221, 0.8)',
                            'rgba(255, 218, 185, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        }

        function createChartTop5Pajak(data) {
            const groupedData = {};

            data.forEach(item => {
                if (item.kode !== '4.1.01') {
                    if (!groupedData[item.nama_pajak]) {
                        groupedData[item.nama_pajak] = 0;
                    }
                    groupedData[item.nama_pajak] += parseFloat(item.total_realisasi || 0);
                }
            });

            const sorted = Object.entries(groupedData).sort((a, b) => b[1] - a[1]).slice(0, 5);
            const labels = sorted.map(item => item[0]);
            const values = sorted.map(item => item[1]);

            destroyChart('chartTop5Pajak');
            const ctx = document.getElementById('chartTop5Pajak').getContext('2d');
            charts['chartTop5Pajak'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Realisasi (Top 5)',
                        data: values,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)'
                        ],
                        borderColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 206, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(153, 102, 255)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        function createChartPencapaianPajak(data) {
            const groupedData = {};

            data.forEach(item => {
                if (item.kode !== '4.1.01') {
                    if (!groupedData[item.nama_pajak]) {
                        groupedData[item.nama_pajak] = {
                            target: 0,
                            realisasi: 0
                        };
                    }
                    groupedData[item.nama_pajak].target += parseFloat(item.total_target || 0);
                    groupedData[item.nama_pajak].realisasi += parseFloat(item.total_realisasi || 0);
                }
            });

            const labels = Object.keys(groupedData);
            const persentaseData = labels.map(pajak => {
                const target = groupedData[pajak].target;
                const realisasi = groupedData[pajak].realisasi;
                return target > 0 ? (realisasi / target * 100) : 0;
            });

            destroyChart('chartPencapaianPajak');
            const ctx = document.getElementById('chartPencapaianPajak').getContext('2d');
            charts['chartPencapaianPajak'] = new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pencapaian (%)',
                        data: persentaseData,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgb(255, 99, 132)',
                        pointBackgroundColor: 'rgb(255, 99, 132)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(255, 99, 132)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        r: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        function createChartGapAnalysis(data) {
            const groupedData = {};

            data.forEach(item => {
                if (item.kode !== '4.1.01') {
                    if (!groupedData[item.nama_pajak]) {
                        groupedData[item.nama_pajak] = {
                            target: 0,
                            realisasi: 0
                        };
                    }
                    groupedData[item.nama_pajak].target += parseFloat(item.total_target || 0);
                    groupedData[item.nama_pajak].realisasi += parseFloat(item.total_realisasi || 0);
                }
            });

            const labels = Object.keys(groupedData);
            const gapData = labels.map(pajak => {
                return groupedData[pajak].realisasi - groupedData[pajak].target;
            });

            destroyChart('chartGapAnalysis');
            const ctx = document.getElementById('chartGapAnalysis').getContext('2d');
            charts['chartGapAnalysis'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Selisih (Realisasi - Target)',
                        data: gapData,
                        backgroundColor: gapData.map(v => v >= 0 ? 'rgba(75, 192, 192, 0.7)' :
                            'rgba(255, 99, 132, 0.7)'),
                        borderColor: gapData.map(v => v >= 0 ? 'rgb(75, 192, 192)' : 'rgb(255, 99, 132)'),
                        borderWidth: 2
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function createChartPerformaPajak(data) {
            const groupedData = {};

            data.forEach(item => {
                if (item.kode !== '4.1.01') {
                    if (!groupedData[item.nama_pajak]) {
                        groupedData[item.nama_pajak] = {
                            target: 0,
                            realisasi: 0
                        };
                    }
                    groupedData[item.nama_pajak].target += parseFloat(item.total_target || 0);
                    groupedData[item.nama_pajak].realisasi += parseFloat(item.total_realisasi || 0);
                }
            });

            const labels = Object.keys(groupedData);
            const performaData = labels.map(pajak => {
                const target = groupedData[pajak].target;
                const realisasi = groupedData[pajak].realisasi;
                return target > 0 ? ((realisasi - target) / target * 100) : 0;
            });

            destroyChart('chartPerformaPajak');
            const ctx = document.getElementById('chartPerformaPajak').getContext('2d');
            charts['chartPerformaPajak'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Performa (%) Over/Under Target',
                        data: performaData,
                        backgroundColor: performaData.map(v => v >= 0 ? 'rgba(75, 192, 192, 0.7)' :
                            'rgba(255, 99, 132, 0.7)'),
                        borderColor: performaData.map(v => v >= 0 ? 'rgb(75, 192, 192)' :
                            'rgb(255, 99, 132)'),
                        borderWidth: 2
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        function createChartTrendSelisih(data) {
            const groupedData = {};

            data.forEach(item => {
                if (!groupedData[item.tahun]) {
                    groupedData[item.tahun] = {
                        target: 0,
                        realisasi: 0
                    };
                }
                groupedData[item.tahun].target += parseFloat(item.total_target || 0);
                groupedData[item.tahun].realisasi += parseFloat(item.total_realisasi || 0);
            });

            const labels = Object.keys(groupedData).sort();
            const selisihData = labels.map(year => {
                return groupedData[year].realisasi - groupedData[year].target;
            });

            destroyChart('chartTrendSelisih');
            const ctx = document.getElementById('chartTrendSelisih').getContext('2d');
            charts['chartTrendSelisih'] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Selisih per Tahun',
                        data: selisihData,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function destroyChart(chartId) {
            if (charts[chartId]) {
                charts[chartId].destroy();
            }
        }

        $(document).ready(function() {
            loadDataPertahun();
            loadDataPerbulan();

            $('#filterTahun, #filterKode').on('change', function() {
                loadDataPertahun();
                loadDataPerbulan();
            });

            $('#filterBulan').on('change', function() {
                loadDataPerbulan();
            });
        });
    </script>
</body>

</html>
