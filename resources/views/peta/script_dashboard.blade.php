<script>
    (function(app) {
        app.tableStates = app.tableStates || {};
        app.chartDefinitions = {
            perbandingan_akun: {
                canvasId: 'chart_perbandingan_akun',
                titleId: 'title_perbandingan_akun',
                descId: 'desc_perbandingan_akun'
            },
            tren_tahunan: {
                canvasId: 'chart_tren_tahunan',
                titleId: 'title_tren_tahunan',
                descId: 'desc_tren_tahunan'
            },
            peringkat: {
                canvasId: 'chart_peringkat',
                titleId: 'title_peringkat',
                descId: 'desc_peringkat'
            },
            komposisi: {
                canvasId: 'chart_komposisi',
                titleId: 'title_komposisi',
                descId: 'desc_komposisi'
            },
            kontribusi: {
                canvasId: 'chart_kontribusi',
                titleId: 'title_kontribusi',
                descId: 'desc_kontribusi'
            },
            pertumbuhan: {
                canvasId: 'chart_pertumbuhan',
                titleId: 'title_pertumbuhan',
                descId: 'desc_pertumbuhan'
            }
        };

        app.renderDashboard = function(payload) {
            this.closeKarisidenanTrendDetail(false);
            this.renderSummary(payload.summary, payload.scope);
            this.renderDashboardHeader(payload.scope);
            this.renderCharts(payload.charts || []);
            this.renderTable('table_detail_akun', payload.tables.detail_akun.rows || []);
            this.renderTable('table_detail_wilayah', payload.tables.detail_wilayah.rows || []);
            $('#detail_akun_title').text(payload.tables.detail_akun.title || 'Detail Akun PAD');
            $('#detail_wilayah_title').text(payload.tables.detail_wilayah.title || 'Detail Wilayah');
            this.bindDrawerEvents();
        };

        app.renderDashboardHeader = function(scope) {
            $('#dashboard_scope_title').text(scope.label || 'Dashboard PAD');
            $('#dashboard_scope_description').text(scope.description || '');
        };

        app.renderSummary = function(summary, scope) {
            $('#summary_anggaran').text(this.formatCurrency(summary.total_anggaran));
            $('#summary_realisasi').text(this.formatCurrency(summary.total_realisasi));
            $('#summary_selisih').text(this.formatCurrency(summary.selisih));
            $('#summary_persentase').text(this.formatPercent(summary.persentase));
            $('#summary_anggaran_compare').html(this.formatComparisonHtml(summary.comparison, 'total_anggaran', 'currency'));
            $('#summary_realisasi_compare').html(this.formatComparisonHtml(summary.comparison, 'total_realisasi', 'currency'));
            $('#summary_selisih_compare').html(this.formatComparisonHtml(summary.comparison, 'selisih', 'currency'));
            $('#summary_persentase_compare').html(this.formatComparisonHtml(summary.comparison, 'persentase', 'percent'));
        };

        app.formatComparisonHtml = function(comparison, key, format) {
            if (!comparison || !comparison.available || !comparison.previous_summary) {
                return '<div class="summary-compare-head"><span class="summary-compare-kicker">VS THN LALU</span></div><div class="summary-compare-main">Data belum tersedia</div>';
            }

            const previousYear = comparison.previous_year || 'tahun sebelumnya';
            const previousSummary = comparison.previous_summary || {};
            const diff = comparison.differences || {};
            const previousValue = previousSummary[key] || 0;
            const delta = diff[key] || 0;
            const direction = delta > 0 ? 'naik' : (delta < 0 ? 'turun' : 'stabil');
            const icon = delta > 0 ? 'bi-arrow-up-right' : (delta < 0 ? 'bi-arrow-down-right' : 'bi-dash-lg');
            const badgeClass = delta > 0 ? 'is-up' : (delta < 0 ? 'is-down' : 'is-flat');
            const deltaText = this.formatValue(Math.abs(delta), format);
            const prevText = this.formatValue(previousValue, format);

            return '' +
                '<div class="summary-compare-head">' +
                '<span class="summary-compare-kicker">VS ' + this.escapeHtml(previousYear) + '</span>' +
                '<span class="summary-compare-badge ' + badgeClass + '"><i class="bi ' + icon + '"></i>' + this.escapeHtml(direction) + '</span>' +
                '</div>' +
                '<div class="summary-compare-main">' + this.escapeHtml(deltaText) + '</div>' +
                '<div class="summary-compare-sub">vs ' + this.escapeHtml(prevText) + '</div>';
        };

        app.renderCharts = function(charts) {
            const appInstance = this;

            charts.forEach(function(chart) {
                const definition = appInstance.chartDefinitions[chart.key];

                if (!definition) {
                    return;
                }

                $('#' + definition.titleId).text(chart.title || '-');
                $('#' + definition.descId).text(chart.description || '');
                appInstance.renderManagedChart(chart, definition);
            });
        };

        app.renderManagedChart = function(chart, definition) {
            const canvas = document.getElementById(definition.canvasId);

            if (!canvas) {
                return;
            }

            if (this.chartInstances[chart.key]) {
                this.chartInstances[chart.key].destroy();
            }

            this.chartInstances[chart.key] = new Chart(canvas.getContext('2d'), this.buildChartConfig(chart));
        };

        app.buildChartConfig = function(chart) {
            const appInstance = this;
            const isLine = chart.type === 'line';
            const isPie = chart.type === 'doughnut' || chart.type === 'pie';
            const isPopulationPerThousand = chart.key === 'kontribusi' && /1\.000 Penduduk/i.test(chart.title || '');
            const isHorizontal = isPopulationPerThousand || (chart.options && chart.options.indexAxis === 'y');
            const primaryFormat = chart.datasets[0] ? chart.datasets[0].format : 'currency';
            const formattedLabels = (chart.labels || []).map(function(label) {
                if (isPie) {
                    return appInstance.limitLabel(label, 22);
                }

                return isHorizontal ? appInstance.limitLabel(label, 24) : appInstance.wrapLabel(label, 16);
            });

            const datasets = chart.datasets.map((dataset, index) => {
                const baseColor = dataset.color || this.palette[index % this.palette.length];

                return {
                    label: dataset.label,
                    data: dataset.data,
                    borderWidth: isPie ? 2 : 2,
                    borderRadius: isLine || isPie ? 0 : 10,
                    borderColor: isPie ? '#ffffff' : baseColor,
                    backgroundColor: isPie ? this.buildPalette(chart.labels.length) : this.hexToRgba(baseColor, isLine ? 0.16 : 0.78),
                    fill: isLine,
                    tension: isLine ? 0.35 : 0,
                    pointRadius: isLine ? 4 : 0,
                    pointHoverRadius: isLine ? 6 : 0
                };
            });

            const scales = isPie ? {} : (isHorizontal ? {
                x: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.16)'
                    },
                    ticks: {
                        callback: (value) => this.formatNumericTick(value, primaryFormat)
                    }
                },
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        autoSkip: false,
                        font: {
                            size: 11
                        }
                    }
                }
            } : {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        autoSkip: chart.labels.length > 8,
                        maxRotation: chart.labels.length > 8 ? 34 : 0,
                        minRotation: chart.labels.length > 8 ? 34 : 0,
                        padding: 10,
                        font: {
                            size: 11
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.18)'
                    },
                    ticks: {
                        callback: (value) => this.formatNumericTick(value, primaryFormat)
                    }
                }
            });

            return {
                type: chart.type,
                data: {
                    labels: formattedLabels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: isHorizontal ? 'y' : 'x',
                    layout: {
                        padding: {
                            bottom: isPopulationPerThousand ? 8 : 0
                        }
                    },
                    scales: scales,
                    plugins: {
                        legend: {
                            display: isPie || chart.datasets.length > 1,
                            position: isPie ? 'bottom' : 'top',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                padding: isPie ? 14 : 12,
                                usePointStyle: isPie,
                                pointStyle: 'rectRounded',
                                font: {
                                    size: isPie ? 11 : 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                title: (items) => {
                                    const item = items && items[0];
                                    return item ? chart.labels[item.dataIndex] : '';
                                },
                                label: (context) => {
                                    const currentDataset = chart.datasets[context.datasetIndex] || {};
                                    const format = currentDataset.format || primaryFormat;
                                    const value = isPie ? context.parsed : (isHorizontal ? context.parsed.x : context.parsed.y);
                                    return currentDataset.label + ': ' + this.formatValue(value, format);
                                }
                            }
                        }
                    }
                }
            };
        };

        app.renderTable = function(tableId, rows) {
            this.tableStates[tableId] = {
                rows: Array.isArray(rows) ? rows : [],
                currentPage: 1,
                pageSize: 10
            };

            this.renderTablePage(tableId);
        };

        app.renderTablePage = function(tableId) {
            const table = $('#' + tableId);
            const thead = table.find('thead');
            const tbody = table.find('tbody');
            const state = this.tableStates[tableId] || {
                rows: [],
                currentPage: 1,
                pageSize: 10
            };
            const rows = state.rows || [];
            const paginationHost = this.ensurePaginationHost(tableId);

            if (!rows.length) {
                thead.html('');
                tbody.html('<tr><td colspan="10" class="text-center text-muted">Tidak ada data.</td></tr>');
                paginationHost.html('');
                return;
            }

            const headers = Object.keys(rows[0]);
            const totalPages = Math.max(1, Math.ceil(rows.length / state.pageSize));
            const currentPage = Math.min(state.currentPage, totalPages);
            const startIndex = (currentPage - 1) * state.pageSize;
            const pageRows = rows.slice(startIndex, startIndex + state.pageSize);

            state.currentPage = currentPage;
            this.tableStates[tableId] = state;

            thead.html('<tr>' + headers.map(function(header) {
                return '<th>' + app.escapeHtml(header) + '</th>';
            }).join('') + '</tr>');

            tbody.html(pageRows.map(function(row) {
                return '<tr>' + headers.map(function(header) {
                    const value = row[header];
                    return '<td>' + app.escapeHtml(app.formatCellValue(header, value)) + '</td>';
                }).join('') + '</tr>';
            }).join(''));

            paginationHost.html(this.buildPaginationHtml(tableId, rows.length, currentPage, state.pageSize));
        };

        app.ensurePaginationHost = function(tableId) {
            const table = $('#' + tableId);
            const wrapper = table.closest('.table-responsive');
            let host = wrapper.next('.table-pagination-shell[data-table-pagination="' + tableId + '"]');

            if (!host.length) {
                host = $('<div class="table-pagination-shell" data-table-pagination="' + tableId + '"></div>');
                wrapper.after(host);
            }

            return host;
        };

        app.buildPaginationHtml = function(tableId, totalRows, currentPage, pageSize) {
            const totalPages = Math.max(1, Math.ceil(totalRows / pageSize));
            const startRow = totalRows ? ((currentPage - 1) * pageSize) + 1 : 0;
            const endRow = Math.min(currentPage * pageSize, totalRows);

            if (totalPages <= 1) {
                return '<div class="table-pagination-meta">Menampilkan ' + startRow + '-' + endRow + ' dari ' + totalRows + ' data</div>';
            }

            return '' +
                '<div class="table-pagination">' +
                '<div class="table-pagination-meta">Menampilkan ' + startRow + '-' + endRow + ' dari ' + totalRows + ' data</div>' +
                '<div class="table-pagination-actions">' +
                this.buildPaginationButton(tableId, currentPage - 1, currentPage === 1, '<i class="bi bi-chevron-left"></i>') +
                this.buildPaginationNumberButtons(tableId, currentPage, totalPages) +
                this.buildPaginationButton(tableId, currentPage + 1, currentPage === totalPages, '<i class="bi bi-chevron-right"></i>') +
                '</div>' +
                '</div>';
        };

        app.buildPaginationNumberButtons = function(tableId, currentPage, totalPages) {
            const buttons = [];
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, startPage + 4);
            const normalizedStart = Math.max(1, endPage - 4);

            for (let page = normalizedStart; page <= endPage; page += 1) {
                buttons.push(this.buildPaginationButton(tableId, page, false, String(page), page === currentPage));
            }

            return buttons.join('');
        };

        app.buildPaginationButton = function(tableId, page, disabled, label, isActive = false) {
            const disabledClass = disabled ? ' is-disabled' : '';
            const activeClass = isActive ? ' is-active' : '';

            return '<button type="button" class="table-pagination-button' + disabledClass + activeClass + '" data-table-id="' + this.escapeHtml(tableId) + '" data-page="' + page + '"' + (disabled ? ' disabled' : '') + '>' + label + '</button>';
        };

        app.formatCellValue = function(header, value) {
            if (/persentase|kontribusi|growth/i.test(header)) {
                return this.formatPercent(value);
            }

            if (/penduduk/i.test(header)) {
                return this.formatNumber(value);
            }

            if (/pad per/i.test(header)) {
                return this.formatCurrency(value);
            }

            if (/anggaran|realisasi|selisih/i.test(header)) {
                return this.formatCurrency(value);
            }

            return value;
        };

        app.formatNumber = function(value) {
            const number = parseFloat(value || 0);
            return number.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        };

        app.resolvePerformanceLabel = function(percent) {
            const value = parseFloat(percent || 0);

            if (value >= 100) return 'Sangat baik';
            if (value >= 90) return 'Baik';
            if (value >= 80) return 'Perlu akselerasi';
            if (value >= 60) return 'Perlu perhatian';
            return 'Prioritas pembenahan';
        };

        app.formatNumericTick = function(value, format) {
            if (format === 'percent') {
                return value + '%';
            }

            if (format === 'currency') {
                return this.formatShortCurrency(value);
            }

            return value;
        };

        app.formatValue = function(value, format) {
            if (format === 'percent') {
                return this.formatPercent(value);
            }

            if (format === 'currency') {
                return this.formatCurrency(value);
            }

            return value;
        };

        app.formatShortCurrency = function(value) {
            const number = parseFloat(value || 0);
            const absolute = Math.abs(number);

            if (absolute >= 1000000000) {
                return 'Rp ' + (number / 1000000000).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 1
                }) + ' M';
            }

            if (absolute >= 1000000) {
                return 'Rp ' + (number / 1000000).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 1
                }) + ' Jt';
            }

            return 'Rp ' + number.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        };

        app.wrapLabel = function(label, maxChars) {
            const text = String(label || '');
            const words = text.split(' ');
            const lines = [];
            let current = '';

            words.forEach(function(word) {
                const next = current ? current + ' ' + word : word;
                if (next.length > maxChars && current) {
                    lines.push(current);
                    current = word;
                } else {
                    current = next;
                }
            });

            if (current) {
                lines.push(current);
            }

            return lines;
        };

        app.limitLabel = function(label, maxChars) {
            const text = String(label || '');
            return text.length > maxChars ? text.slice(0, maxChars - 3) + '...' : text;
        };

        app.buildPalette = function(length) {
            const palette = [];

            for (let index = 0; index < length; index += 1) {
                palette.push(this.hexToRgba(this.palette[index % this.palette.length], 0.82));
            }

            return palette;
        };

        app.hexToRgba = function(hex, alpha) {
            const normalized = String(hex || '#2563eb').replace('#', '');
            const full = normalized.length === 3 ? normalized.split('').map(function(char) {
                return char + char;
            }).join('') : normalized;
            const bigint = parseInt(full, 16);
            const r = (bigint >> 16) & 255;
            const g = (bigint >> 8) & 255;
            const b = bigint & 255;

            return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
        };

        app.bindExportEvents = function() {
            const appInstance = this;

            $(document).on('click', '.export-trigger', function() {
                const section = $(this).data('export-section');
                const query = $.param({
                    section: section,
                    tahun: appInstance.state.tahun,
                    jenis: appInstance.state.jenis,
                    karisidenan: appInstance.state.karisidenan,
                    wilayah: appInstance.state.wilayah,
                    kecamatan: appInstance.state.kecamatan
                });

                window.open(window.petaDashboardConfig.exportUrl + '?' + query, '_blank');
            });
        };

        app.bindDrawerEvents = function() {
            const appInstance = this;

            $(document).off('click', '.dashboard-drawer-trigger').on('click', '.dashboard-drawer-trigger', function() {
                const section = $(this).data('drawer-section');
                const dashboard = appInstance.state.dashboard || {};

                if (dashboard.scope && dashboard.scope.mode === 'karisidenan' && dashboard.karisidenan_detail && dashboard.karisidenan_detail[section]) {
                    appInstance.openKarisidenanChartDetail(section);
                    return;
                }

                appInstance.openDrawer(section);
            });

            $(document).off('click', '#dashboard_drawer_close, #dashboard_drawer_backdrop').on('click', '#dashboard_drawer_close, #dashboard_drawer_backdrop', function() {
                appInstance.closeDrawer();
            });

            $(document).off('click', '#close_karisidenan_detail').on('click', '#close_karisidenan_detail', function() {
                appInstance.closeKarisidenanTrendDetail();
            });

            $(document).off('click', '.table-pagination-button').on('click', '.table-pagination-button', function() {
                const tableId = $(this).data('table-id');
                const page = parseInt($(this).data('page'), 10);
                const tableState = appInstance.tableStates[tableId];

                if (!tableState || Number.isNaN(page) || page < 1) {
                    return;
                }

                tableState.currentPage = page;
                appInstance.renderTablePage(tableId);
            });
        };

        app.openDrawer = function(section) {
            const payload = this.resolveDrawerPayload(section);

            $('#drawer_title').text(payload.title);
            $('#drawer_description').text(payload.description);
            $('#drawer_export_button').attr('data-export-section', payload.exportSection);
            $('#drawer_summary_grid').html(payload.summaryCards.map((card) => {
                return '<div class="drawer-stat-card"><span>' + this.escapeHtml(card.label) + '</span><strong>' + this.escapeHtml(card.value) + '</strong></div>';
            }).join(''));

            this.renderTable('drawer_table', payload.rows);

            $('#dashboard_drawer, #dashboard_drawer_backdrop').addClass('is-open');
            $('#dashboard_drawer').attr('aria-hidden', 'false');
        };

        app.closeDrawer = function() {
            $('#dashboard_drawer, #dashboard_drawer_backdrop').removeClass('is-open');
            $('#dashboard_drawer').attr('aria-hidden', 'true');
        };

        app.resolveDrawerPayload = function(section) {
            const dashboard = this.state.dashboard || {};
            const scope = dashboard.scope || {};
            const summary = dashboard.summary || {};
            const charts = dashboard.charts || [];
            const tables = dashboard.tables || {};
            let payload = {
                title: 'Detail Dashboard',
                description: 'Rincian analitik dan data mentah yang bisa diunduh.',
                exportSection: section || 'ringkasan',
                rows: [],
                summaryCards: [
                    { label: 'Lingkup', value: scope.label || 'Jawa Timur' },
                    { label: 'Anggaran', value: this.formatCurrency(summary.total_anggaran || 0) },
                    { label: 'Realisasi', value: this.formatCurrency(summary.total_realisasi || 0) },
                    { label: 'Capaian', value: this.formatPercent(summary.persentase || 0) }
                ]
            };

            if (section === 'ringkasan') {
                payload.title = 'Ringkasan Dashboard PAD';
                payload.description = 'Ikhtisar capaian untuk filter aktif beserta angka dasar analisis.';
                payload.rows = [{
                    'Lingkup': scope.label || 'Jawa Timur',
                    'Induk': scope.parent || 'Jawa Timur',
                    'Anggaran': summary.total_anggaran || 0,
                    'Realisasi': summary.total_realisasi || 0,
                    'Selisih': summary.selisih || 0,
                    'Persentase (%)': summary.persentase || 0
                }];
                return payload;
            }

            const chart = charts.find((item) => item.key === section);
            if (chart) {
                payload.title = chart.title || payload.title;
                payload.description = chart.description || payload.description;
                payload.rows = chart.export && chart.export.rows ? chart.export.rows : [];
                payload.summaryCards = [
                    { label: 'Lingkup', value: scope.label || 'Jawa Timur' },
                    { label: 'Jumlah Label', value: String((chart.labels || []).length) },
                    { label: 'Dataset', value: String((chart.datasets || []).length) },
                    { label: 'Tahun', value: this.state.tahun || 'Semua Tahun' }
                ];
                return payload;
            }

            if (tables[section]) {
                payload.title = tables[section].title || payload.title;
                payload.description = 'Tabel detail untuk pendalaman analisis dan unduhan data mentah.';
                payload.rows = tables[section].rows || [];
                payload.summaryCards = [
                    { label: 'Lingkup', value: scope.label || 'Jawa Timur' },
                    { label: 'Baris Data', value: String(payload.rows.length) },
                    { label: 'Filter Jenis', value: this.state.jenis || 'Semua Jenis' },
                    { label: 'Tahun', value: this.state.tahun || 'Semua Tahun' }
                ];
                return payload;
            }

            return payload;
        };

        app.openKarisidenanChartDetail = function(section) {
            const detailMap = (this.state.dashboard || {}).karisidenan_detail || {};
            const detailConfig = detailMap[section] || {};
            const detail = detailConfig.items || [];
            const sourceChart = ((this.state.dashboard || {}).charts || []).find(function(item) {
                return item.key === section;
            }) || {};
            const target = $('#karisidenan_trend_grid');
            const appInstance = this;

            target.html('');
            $('#karisidenan_detail_title').text(detailConfig.title || ((sourceChart.title || 'Detail Chart') + ' per Wilayah'));
            $('#karisidenan_detail_description').text(detailConfig.description || 'Mode fokus untuk seluruh wilayah dalam karisidenan aktif.');

            detail.forEach(function(item, index) {
                const canvasId = 'karisidenan_detail_' + section + '_' + index;
                target.append(
                    '<article class="chart-card">' +
                    '<div class="chart-card-head"><div><h3>' + appInstance.escapeHtml(item.label || 'Wilayah') + '</h3><p>' + appInstance.escapeHtml(item.description || detailConfig.description || sourceChart.description || 'Detail wilayah dalam karisidenan aktif.') + '</p></div></div>' +
                    '<div class="chart-canvas"><canvas id="' + canvasId + '"></canvas></div>' +
                    '</article>'
                );

                const chart = {
                    key: canvasId,
                    type: item.type || sourceChart.type || 'bar',
                    labels: item.labels || [],
                    datasets: item.datasets || [],
                    options: item.options || {
                        indexAxis: 'x'
                    }
                };

                appInstance.renderManagedChart(chart, {
                    canvasId: canvasId
                });
            });

            $('#karisidenan_trend_detail').prop('hidden', false);
            $('.charts-grid, .detail-grid').not('#karisidenan_trend_grid').hide();
        };

        app.closeKarisidenanTrendDetail = function(restore = true) {
            $('#karisidenan_trend_detail').prop('hidden', true);
            if (restore) {
                $('.charts-grid, .detail-grid').show();
            } else {
                $('.charts-grid, .detail-grid').show();
            }
        };

        app.bindExportEvents();
        app.bindDrawerEvents();
    })(window.PetaDashboardApp);
</script>
