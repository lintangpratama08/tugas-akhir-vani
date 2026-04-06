<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Realisasi Pajak</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e22ce 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1800px;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 20px;
            padding: 35px;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .header h1 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .header p {
            color: #64748b;
            font-size: 16px;
            font-weight: 500;
        }

        .filters {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 28px;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .filter-row {
            display: flex;
            gap: 18px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .filter-row:last-child {
            margin-bottom: 0;
        }

        .filter-group {
            flex: 1;
            min-width: 220px;
        }

        .filter-group label {
            display: block;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .filter-group select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: white;
            cursor: pointer;
            color: #334155;
        }

        .filter-group select:hover {
            border-color: #cbd5e1;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .filter-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .filter-tab {
            padding: 12px 24px;
            background: #f1f5f9;
            border: 2px solid transparent;
            border-radius: 12px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            transition: all 0.3s ease;
            color: #64748b;
        }

        .filter-tab:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        .filter-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 28px 80px rgba(0, 0, 0, 0.4);
        }

        .stat-card h3 {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .stat-card .subtext {
            font-size: 13px;
            color: #94a3b8;
            font-weight: 500;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(550px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .chart-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 28px 80px rgba(0, 0, 0, 0.4);
        }

        .chart-card h2 {
            font-size: 18px;
            color: #1e293b;
            font-weight: 800;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 3px solid;
            border-image: linear-gradient(90deg, #667eea 0%, #764ba2 100%) 1;
        }

        .chart-container {
            position: relative;
            width: 100%;
            min-height: 400px;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .filter-row {
                flex-direction: column;
            }

            .header h1 {
                font-size: 28px;
            }

            .stat-card .value {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📊 Dashboard Realisasi Pajak Daerah</h1>
            <p>Monitoring dan Analisis Pendapatan Pajak Jawa Timur - Detail Per UPT dan Jenis Pajak Per Bulan</p>
        </div>

        <div class="filters">
            <div class="filter-tabs">
                <button class="filter-tab active" data-view="bulanan">Per Bulan</button>
                <button class="filter-tab" data-view="tahunan">Per Tahun</button>
            </div>

            <div class="filter-row">
                <div class="filter-group">
                    <label>🏢 Unit Pelayanan Teknis (UPT)</label>
                    <select id="filter-upt">
                        <option value="">Semua UPT</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>💰 Jenis Pajak (SKT)</label>
                    <select id="filter-skt">
                        <option value="">Semua Pajak</option>
                    </select>
                </div>
            </div>

            <div class="filter-row" id="filter-bulanan">
                <div class="filter-group">
                    <label>📅 Periode Bulan</label>
                    <select id="filter-periode">
                        <option value="">Semua Bulan</option>
                    </select>
                </div>
            </div>

            <div class="filter-row" id="filter-tahunan" style="display:none;">
                <div class="filter-group">
                    <label>📅 Tahun</label>
                    <select id="filter-tahun">
                        <option value="">Semua Tahun</option>
                        <option value="2022">2022</option>
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Target Total</h3>
                <div class="value" id="stat-target">Rp 0</div>
                <div class="subtext">Target keseluruhan</div>
            </div>
            <div class="stat-card">
                <h3>Realisasi Total</h3>
                <div class="value" id="stat-realisasi">Rp 0</div>
                <div class="subtext">Pendapatan terkumpul</div>
            </div>
            <div class="stat-card">
                <h3>Persentase Capaian</h3>
                <div class="value" id="stat-persentase">0%</div>
                <div class="subtext">Dari total target</div>
            </div>
            <div class="stat-card">
                <h3>Kekurangan</h3>
                <div class="value" id="stat-kurang">Rp 0</div>
                <div class="subtext">Sisa target belum tercapai</div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card full-width">
                <h2 id="trend-title">📈 Trend Realisasi vs Target per Bulan</h2>
                <div class="chart-container">
                    <div id="chart-trend"></div>
                </div>
            </div>

            <div class="chart-card full-width">
                <h2>📊 Detail Realisasi per Jenis Pajak per Bulan (UPT: <span id="upt-name">Semua</span>)</h2>
                <div class="chart-container">
                    <div id="chart-pajak-bulanan"></div>
                </div>
            </div>

            <div class="chart-card">
                <h2>🎯 Realisasi per Jenis Pajak</h2>
                <div class="chart-container">
                    <div id="chart-skt"></div>
                </div>
            </div>

            <div class="chart-card">
                <h2>🏆 Top 10 UPT Performer</h2>
                <div class="chart-container">
                    <div id="chart-top"></div>
                </div>
            </div>

            <div class="chart-card">
                <h2>📊 Perbandingan Realisasi per UPT</h2>
                <div class="chart-container">
                    <div id="chart-upt"></div>
                </div>
            </div>

            <div class="chart-card">
                <h2>🥧 Komposisi Pajak</h2>
                <div class="chart-container">
                    <div id="chart-pie"></div>
                </div>
            </div>

            <div class="chart-card full-width">
                <h2>📉 Persentase Capaian per Jenis Pajak</h2>
                <div class="chart-container">
                    <div id="chart-persentase"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let charts = {};
        let currentView = 'bulanan';

        function formatRupiah(value) {
            if (!value) return 'Rp 0';
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        }

        function formatBulan(periode) {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const [year, month] = periode.split('-');
            return months[parseInt(month) - 1] + ' ' + year;
        }

        function getFilters() {
            return {
                upt: $('#filter-upt').val(),
                skt: $('#filter-skt').val(),
                periode: currentView === 'bulanan' ? $('#filter-periode').val() : '',
                tahun: currentView === 'tahunan' ? $('#filter-tahun').val() : ''
            };
        }

        $('.filter-tab').on('click', function() {
            $('.filter-tab').removeClass('active');
            $(this).addClass('active');
            currentView = $(this).data('view');

            if (currentView === 'bulanan') {
                $('#filter-bulanan').show();
                $('#filter-tahunan').hide();
                $('#trend-title').text('📈 Trend Realisasi vs Target per Bulan');
            } else {
                $('#filter-bulanan').hide();
                $('#filter-tahunan').show();
                $('#trend-title').text('📈 Trend Realisasi vs Target per Tahun');
            }

            loadAllCharts();
        });

        function loadStats() {
            $.get('/api/total-realisasi-target', getFilters(), function(data) {
                const target = parseFloat(data.total_target) || 0;
                const realisasi = parseFloat(data.total_realisasi) || 0;
                const persentase = target > 0 ? (realisasi / target * 100).toFixed(2) : 0;
                const kurang = target - realisasi;

                $('#stat-target').text(formatRupiah(target));
                $('#stat-realisasi').text(formatRupiah(realisasi));
                $('#stat-persentase').text(persentase + '%');
                $('#stat-kurang').text(formatRupiah(kurang));
            });
        }

        function createTrendChart() {
            if (charts.trend) {
                charts.trend.destroy();
            }

            const endpoint = currentView === 'bulanan' ? '/api/trend-bulanan' : '/api/trend-tahunan';

            $.get(endpoint, getFilters(), function(data) {
                const labels = data.map(d => currentView === 'bulanan' ? formatBulan(d.periode_update) : d.tahun);
                const targetData = data.map(d => parseFloat(d.target));
                const realisasiData = data.map(d => parseFloat(d.realisasi));

                const options = {
                    series: [{
                        name: 'Target',
                        data: targetData
                    }, {
                        name: 'Realisasi',
                        data: realisasiData
                    }],
                    chart: {
                        type: 'area',
                        height: 400,
                        toolbar: {
                            show: true,
                            tools: {
                                download: true,
                                selection: true,
                                zoom: true,
                                zoomin: true,
                                zoomout: true,
                                pan: true,
                                reset: true
                            }
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.6,
                            opacityTo: 0.1,
                            stops: [0, 90, 100]
                        }
                    },
                    colors: ['#ff6b6b', '#4ecdc4'],
                    xaxis: {
                        categories: labels,
                        labels: {
                            style: {
                                fontSize: '12px',
                                fontWeight: 600
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function(value) {
                                return formatRupiah(value);
                            },
                            style: {
                                fontSize: '12px',
                                fontWeight: 600
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return formatRupiah(value);
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        fontSize: '14px',
                        fontWeight: 600
                    },
                    grid: {
                        borderColor: '#e7e7e7',
                        strokeDashArray: 5
                    }
                };

                charts.trend = new ApexCharts(document.querySelector("#chart-trend"), options);
                charts.trend.render();
            });
        }

        function createPajakBulananChart() {
            if (charts.pajakBulanan) {
                charts.pajakBulanan.destroy();
            }

            const upt = $('#filter-upt').val() || '';
            $('#upt-name').text(upt || 'Semua UPT');

            $.get('/api/trend-per-pajak', {
                upt: upt,
                skt: $('#filter-skt').val()
            }, function(data) {
                const grouped = {};
                data.forEach(item => {
                    if (!grouped[item.label]) {
                        grouped[item.label] = [];
                    }
                    grouped[item.label].push(item);
                });

                const allPeriods = [...new Set(data.map(d => d.periode_update))].sort();
                const labels = allPeriods.map(p => formatBulan(p));

                const series = Object.keys(grouped).map(label => {
                    const dataMap = {};
                    grouped[label].forEach(item => {
                        dataMap[item.periode_update] = parseFloat(item.realisasi);
                    });

                    return {
                        name: label,
                        data: allPeriods.map(p => dataMap[p] || 0)
                    };
                });

                const options = {
                    series: series,
                    chart: {
                        type: 'line',
                        height: 400,
                        toolbar: {
                            show: true
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    colors: ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#fee140',
                        '#30cfd0', '#a8edea', '#fed6e3', '#c471f5', '#12c2e9'
                    ],
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        fontSize: '16px',
                                        fontWeight: 700,
                                        formatter: function(w) {
                                            const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                            return formatRupiah(total);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return val.toFixed(1) + "%";
                        },
                        style: {
                            fontSize: '12px',
                            fontWeight: 700
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return formatRupiah(value);
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                        fontSize: '12px',
                        fontWeight: 600,
                        markers: {
                            width: 12,
                            height: 12
                        }
                    }
                };

                charts.pie = new ApexCharts(document.querySelector("#chart-pie"), options);
                charts.pie.render();
            });
        }

        function createPersentaseChart() {
            if (charts.persentase) {
                charts.persentase.destroy();
            }

            $.get('/api/persentase-realisasi', getFilters(), function(data) {
                const labels = data.map(d => d.label);
                const values = data.map(d => parseFloat(d.persentase));

                const options = {
                    series: [{
                        name: 'Persentase Capaian',
                        data: values
                    }],
                    chart: {
                        type: 'bar',
                        height: 400,
                        toolbar: {
                            show: true
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 10,
                            distributed: true,
                            dataLabels: {
                                position: 'top'
                            },
                            columnWidth: '60%'
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return val.toFixed(2) + "%";
                        },
                        offsetY: -25,
                        style: {
                            fontSize: '12px',
                            fontWeight: 700,
                            colors: ['#333']
                        }
                    },
                    colors: values.map(v => {
                        if (v >= 90) return '#00e676';
                        if (v >= 70) return '#ffd600';
                        return '#ff5252';
                    }),
                    xaxis: {
                        categories: labels,
                        labels: {
                            rotate: -45,
                            style: {
                                fontSize: '12px',
                                fontWeight: 600
                            }
                        }
                    },
                    yaxis: {
                        max: 100,
                        labels: {
                            formatter: function(value) {
                                return value.toFixed(0) + '%';
                            },
                            style: {
                                fontSize: '12px',
                                fontWeight: 600
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return value.toFixed(2) + '%';
                            }
                        }
                    },
                    legend: {
                        show: false
                    },
                    grid: {
                        borderColor: '#e7e7e7',
                        strokeDashArray: 5
                    }
                };

                charts.persentase = new ApexCharts(document.querySelector("#chart-persentase"), options);
                charts.persentase.render();
            });
        }

        function loadAllCharts() {
            loadStats();
            createTrendChart();
            createPajakBulananChart();
            createSKTChart();
            createTopChart();
            createUPTChart();
            createPieChart();
            createPersentaseChart();
        }

        $('#filter-upt, #filter-skt, #filter-periode, #filter-tahun').on('change', function() {
            loadAllCharts();
        });

        $(document).ready(function() {
            $.get('/api/total-realisasi-target', {}, function(response) {
                const upts = ['NGANJUK', 'SURABAYA', 'MALANG', 'KEDIRI', 'JEMBER', 'MADIUN'];
                upts.forEach(function(upt) {
                    $('#filter-upt').append('<option value="' + upt + '">' + upt + '</option>');
                });

                const skts = [{
                        value: 'SKT1',
                        label: 'Pajak Hotel'
                    },
                    {
                        value: 'SKT2',
                        label: 'Pajak Restoran'
                    },
                    {
                        value: 'SKT3',
                        label: 'Pajak Hiburan'
                    },
                    {
                        value: 'SKT4',
                        label: 'Pajak Reklame'
                    },
                    {
                        value: 'SKT5',
                        label: 'Pajak Parkir'
                    },
                    {
                        value: 'SKT6',
                        label: 'Pajak Air Tanah'
                    },
                    {
                        value: 'SKT7',
                        label: 'Pajak PBB-P2'
                    },
                    {
                        value: 'SKT8',
                        label: 'Pajak BPHTB'
                    }
                ];
                skts.forEach(function(skt) {
                    $('#filter-skt').append('<option value="' + skt.value + '">' + skt.label +
                        '</option>');
                });

                const periodes = ['2022-01', '2022-02', '2022-03', '2022-04', '2022-05', '2022-06',
                    '2023-01', '2023-02', '2023-03', '2023-04', '2023-05', '2023-06',
                    '2024-01', '2024-02', '2024-03', '2024-04', '2024-05', '2024-06'
                ];
                periodes.forEach(function(periode) {
                    $('#filter-periode').append('<option value="' + periode + '">' + formatBulan(
                        periode) + '</option>');
                });

                loadAllCharts();
            });
        });
    </script>
</body>
</html>