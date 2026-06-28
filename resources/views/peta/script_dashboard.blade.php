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

        app.buildChartValueLabelPlugin = function(sourceChart, options) {
            const appInstance = this;
            const isPie = !!options.isPie;
            const isHorizontal = !!options.isHorizontal;
            const isLine = !!options.isLine;
            const primaryFormat = options.primaryFormat || 'currency';

            return {
                id: 'valueLabels_' + sourceChart.key,
                afterDatasetsDraw(chartInstance) {
                    if (isLine) {
                        return;
                    }

                    if (isPie) {
                        appInstance.drawPieValueLabels(chartInstance, sourceChart, primaryFormat);
                        return;
                    }

                    appInstance.drawBarValueLabels(chartInstance, sourceChart, primaryFormat, isHorizontal);
                }
            };
        };

        app.drawBarValueLabels = function(chartInstance, sourceChart, primaryFormat, isHorizontal) {
            const ctx = chartInstance.ctx;

            chartInstance.data.datasets.forEach((dataset, datasetIndex) => {
                const meta = chartInstance.getDatasetMeta(datasetIndex);
                const sourceDataset = sourceChart.datasets[datasetIndex] || {};
                const format = sourceDataset.format || primaryFormat;

                meta.data.forEach((element, dataIndex) => {
                    const rawValue = sourceDataset.data[dataIndex];

                    if (rawValue === null || rawValue === undefined || Number(rawValue) === 0) {
                        return;
                    }

                    const label = appInstance.formatCompactValue(rawValue, format);
                    const point = element.getProps(['x', 'y', 'base'], true);
                    const x = isHorizontal ? point.x + 10 : point.x;
                    const y = isHorizontal ? point.y : Math.min(point.y - 12, point.base - 12);

                    ctx.save();
                    ctx.font = '700 11px "Segoe UI", sans-serif';
                    ctx.textAlign = isHorizontal ? 'left' : 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#18314f';
                    ctx.strokeStyle = 'rgba(255,255,255,0.92)';
                    ctx.lineWidth = 4;
                    ctx.strokeText(label, x, y);
                    ctx.fillText(label, x, y);
                    ctx.restore();
                });
            });
        };

        app.drawPieValueLabels = function(chartInstance, sourceChart, primaryFormat) {
            const ctx = chartInstance.ctx;
            const meta = chartInstance.getDatasetMeta(0);
            const sourceDataset = sourceChart.datasets[0] || {};
            const values = sourceDataset.data || [];
            const format = sourceDataset.format || primaryFormat;

            meta.data.forEach((arc, index) => {
                const rawValue = values[index];

                if (rawValue === null || rawValue === undefined || Number(rawValue) === 0) {
                    return;
                }

                const angle = (arc.startAngle + arc.endAngle) / 2;
                const radius = arc.outerRadius || 0;
                const startX = arc.x + Math.cos(angle) * (radius - 4);
                const startY = arc.y + Math.sin(angle) * (radius - 4);
                const middleX = arc.x + Math.cos(angle) * (radius + 14);
                const middleY = arc.y + Math.sin(angle) * (radius + 14);
                const isRightSide = Math.cos(angle) >= 0;
                const endX = middleX + (isRightSide ? 20 : -20);
                const labelX = endX + (isRightSide ? 6 : -6);
                const labelY = middleY;
                const label = appInstance.limitLabel(sourceChart.labels[index], 16);
                const valueText = appInstance.formatCompactValue(rawValue, format);

                ctx.save();
                ctx.strokeStyle = 'rgba(24, 49, 79, 0.46)';
                ctx.lineWidth = 1.25;
                ctx.beginPath();
                ctx.moveTo(startX, startY);
                ctx.lineTo(middleX, middleY);
                ctx.lineTo(endX, middleY);
                ctx.stroke();

                ctx.fillStyle = '#18314f';
                ctx.font = '700 11px "Segoe UI", sans-serif';
                ctx.textAlign = isRightSide ? 'left' : 'right';
                ctx.textBaseline = 'bottom';
                ctx.fillText(label, labelX, labelY - 1);

                ctx.fillStyle = '#64748b';
                ctx.font = '600 10px "Segoe UI", sans-serif';
                ctx.textBaseline = 'top';
                ctx.fillText(valueText, labelX, labelY + 1);
                ctx.restore();
            });
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
                plugins: [this.buildChartValueLabelPlugin(chart, {
                    isPie: isPie,
                    isHorizontal: isHorizontal,
                    isLine: isLine,
                    primaryFormat: primaryFormat
                })],
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
                            top: isLine ? 10 : 22,
                            right: isPie ? 86 : (isHorizontal ? 72 : 14),
                            bottom: isPie ? 24 : (isPopulationPerThousand ? 8 : 0),
                            left: isPie ? 86 : 14
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
            const wrapper = table.closest('.table-responsive');
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
                wrapper.removeClass('is-scrollable');
                return;
            }

            const headers = Object.keys(rows[0]);
            const totalPages = Math.max(1, Math.ceil(rows.length / state.pageSize));
            const currentPage = Math.min(state.currentPage, totalPages);
            const startIndex = (currentPage - 1) * state.pageSize;
            const pageRows = rows.slice(startIndex, startIndex + state.pageSize);
            const numberedHeaders = ['No'].concat(headers);

            state.currentPage = currentPage;
            this.tableStates[tableId] = state;

            thead.html('<tr>' + numberedHeaders.map(function(header, index) {
                const numberClass = index === 0 ? ' class="table-number-col"' : '';
                return '<th' + numberClass + '>' + app.escapeHtml(header) + '</th>';
            }).join('') + '</tr>');

            tbody.html(pageRows.map(function(row, rowIndex) {
                const rowNumber = startIndex + rowIndex + 1;
                return '<tr><td class="table-number-col">' + rowNumber + '</td>' + headers.map(function(header) {
                    const value = row[header];
                    return '<td>' + app.escapeHtml(app.formatCellValue(header, value)) + '</td>';
                }).join('') + '</tr>';
            }).join(''));

            wrapper.toggleClass('is-scrollable', numberedHeaders.length > 5);
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

        app.formatCompactValue = function(value, format) {
            if (format === 'percent') {
                const number = parseFloat(value || 0);
                return number.toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 1
                }) + '%';
            }

            if (format === 'currency') {
                return this.formatShortCurrency(value);
            }

            const number = parseFloat(value || 0);
            if (Math.abs(number) >= 1000000) {
                return (number / 1000000).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 1
                }) + ' Jt';
            }

            return number.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
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

            $(document).off('click', '.export-trigger').on('click', '.export-trigger', function() {
                const section = $(this).data('export-section');
                appInstance.openExportModal(section, this);
            });

            $(document).off('click', '#dashboard_export_excel').on('click', '#dashboard_export_excel', function() {
                if (!appInstance.exportContext) {
                    return;
                }

                appInstance.performRawExport(appInstance.exportContext.section);
                appInstance.closeExportModal();
            });

            $(document).off('click', '#dashboard_export_pdf').on('click', '#dashboard_export_pdf', async function() {
                if (!appInstance.exportContext) {
                    return;
                }

                const context = appInstance.exportContext;
                appInstance.closeExportModal();
                await appInstance.exportSectionPdf(context.section, context.button);
            });
        };

        app.openExportModal = function(section, button) {
            const modalElement = document.getElementById('dashboardExportModal');
            const sectionMeta = this.resolveSectionMeta(section);

            if (!modalElement || !window.bootstrap || !window.bootstrap.Modal) {
                this.exportSectionPdf(section, button);
                return;
            }

            this.exportContext = {
                section: section,
                button: button || null
            };

            $('#dashboardExportModalLabel').text('Download ' + (sectionMeta.title || 'Dashboard'));
            $('#dashboard_export_modal_desc').text('Pilih Excel untuk data mentah atau PDF untuk visual rapi beserta penjelasan otomatis AI.');

            if (!this.exportModalInstance) {
                this.exportModalInstance = new window.bootstrap.Modal(modalElement);
            }

            this.exportModalInstance.show();
        };

        app.closeExportModal = function() {
            if (this.exportModalInstance) {
                this.exportModalInstance.hide();
            }
        };

        app.performRawExport = function(section) {
            const query = $.param({
                section: section,
                tahun: this.state.tahun,
                jenis: this.state.jenis,
                karisidenan: this.state.karisidenan,
                wilayah: this.state.wilayah,
                kecamatan: this.state.kecamatan
            });

            window.open(window.petaDashboardConfig.exportUrl + '?' + query, '_blank');
        };

        app.exportSectionPdf = async function(section, button) {
            const target = this.resolveExportVisualElement(section);

            if (!target || !window.html2canvas || !window.jspdf || !window.jspdf.jsPDF) {
                return;
            }

            const originalHtml = button ? button.innerHTML : '';

            this.setExportButtonLoading(button, true);

            try {
                const insightPayload = this.resolveInsightPayload(section);
                const insightResponse = await this.requestSectionInsight(section, insightPayload);
                const canvas = await window.html2canvas(target, {
                    backgroundColor: '#ffffff',
                    scale: 2,
                    useCORS: true,
                    logging: false
                });

                await this.downloadPdfDocument(section, insightPayload, insightResponse.insight, canvas);
            } catch (error) {
                console.error('PDF export gagal:', error);
                alert('PDF gagal dibuat. Silakan coba lagi.');
            } finally {
                this.setExportButtonLoading(button, false, originalHtml);
            }
        };

        app.setExportButtonLoading = function(button, isLoading, originalHtml) {
            if (!button) {
                return;
            }

            if (isLoading) {
                button.dataset.originalHtml = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="bi bi-hourglass-split"></i>';
                return;
            }

            button.disabled = false;
            button.innerHTML = originalHtml || button.dataset.originalHtml || button.innerHTML;
        };

        app.resolveExportVisualElement = function(section) {
            if (section === 'ringkasan') {
                return document.querySelector('.summary-grid');
            }

            const chartCard = document.querySelector('.chart-card[data-drawer-section="' + section + '"]');

            if (chartCard) {
                return chartCard;
            }

            const detailCard = document.querySelector('.detail-card[data-drawer-section="' + section + '"]');

            if (detailCard) {
                return detailCard;
            }

            return null;
        };

        app.resolveSectionMeta = function(section) {
            const dashboard = this.state.dashboard || {};
            const charts = dashboard.charts || [];
            const tables = dashboard.tables || {};
            const chart = charts.find(function(item) {
                return item.key === section;
            });

            if (section === 'ringkasan') {
                return {
                    title: 'Ringkasan Dashboard PAD'
                };
            }

            if (chart) {
                return {
                    title: chart.title || 'Chart Dashboard'
                };
            }

            if (tables[section]) {
                return {
                    title: tables[section].title || 'Tabel Dashboard'
                };
            }

            return {
                title: 'Dashboard PAD'
            };
        };

        app.resolveInsightPayload = function(section) {
            const dashboard = this.state.dashboard || {};
            const scope = dashboard.scope || {};
            const charts = dashboard.charts || [];
            const tables = dashboard.tables || {};
            const chart = charts.find(function(item) {
                return item.key === section;
            });
            const table = tables[section] || null;

            if (chart) {
                return {
                    section: section,
                    title: chart.title || 'Chart Dashboard',
                    description: chart.description || '',
                    scope_label: scope.label || 'Jawa Timur',
                    filters: this.buildInsightFilters(),
                    labels: chart.labels || [],
                    datasets: chart.datasets || [],
                    rows: (chart.export && chart.export.rows) ? chart.export.rows : []
                };
            }

            if (table) {
                return {
                    section: section,
                    title: table.title || 'Tabel Dashboard',
                    description: 'Ringkasan data tabel sesuai filter aktif.',
                    scope_label: scope.label || 'Jawa Timur',
                    filters: this.buildInsightFilters(),
                    labels: [],
                    datasets: [],
                    rows: table.rows || []
                };
            }

            return {
                section: section,
                title: 'Dashboard PAD',
                description: 'Visual dashboard sesuai filter aktif.',
                scope_label: scope.label || 'Jawa Timur',
                filters: this.buildInsightFilters(),
                labels: [],
                datasets: [],
                rows: []
            };
        };

        app.buildInsightFilters = function() {
            return {
                tahun: this.state.tahun || 'Semua Tahun',
                jenis: this.state.jenis || 'Semua Jenis',
                karisidenan: this.resolveKarisidenanLabel ? (this.resolveKarisidenanLabel() || 'Semua Karisidenan') : 'Semua Karisidenan',
                wilayah: this.state.wilayah || 'Semua Wilayah',
                kecamatan: this.state.kecamatan || 'Semua Kecamatan'
            };
        };

        app.requestSectionInsight = async function(section, payload) {
            try {
                const response = await $.ajax({
                    url: window.petaDashboardConfig.chartInsightUrl,
                    method: 'POST',
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: JSON.stringify(payload)
                });

                if (response && response.insight) {
                    return response;
                }
            } catch (error) {
                console.warn('Insight AI fallback dipakai untuk section:', section, error);
            }

            return {
                insight: this.buildLocalInsight(payload),
                source: 'fallback',
                used_fallback: true,
                error_message: 'Request AJAX ke endpoint insight gagal atau tidak merespons.'
            };
        };

        app.buildLocalInsight = function(payload) {
            const title = payload.title || 'Visual dashboard';
            const scopeLabel = payload.scope_label || 'wilayah aktif';
            const rows = payload.rows || [];
            const datasets = payload.datasets || [];
            const labels = payload.labels || [];

            if (rows.length) {
                return title + ' pada ' + scopeLabel + ' memuat ' + rows.length +
                    ' baris data utama yang dapat dipakai untuk membaca distribusi capaian dan membandingkan prioritas tindak lanjut antar kategori atau wilayah. Dokumen ini memakai ringkasan lokal karena layanan AI eksternal sedang tidak merespons.';
            }

            if (datasets.length) {
                const summary = datasets.map(function(dataset) {
                    const values = (dataset.data || []).map(function(value) {
                        return parseFloat(value || 0);
                    });

                    if (!values.length) {
                        return '';
                    }

                    const maxValue = Math.max.apply(null, values);
                    const minValue = Math.min.apply(null, values);
                    const maxIndex = values.indexOf(maxValue);
                    const minIndex = values.indexOf(minValue);
                    const maxLabel = labels[maxIndex] || 'kategori tertinggi';
                    const minLabel = labels[minIndex] || 'kategori terendah';

                    return (dataset.label || 'Nilai') + ' tertinggi berada pada ' + maxLabel +
                        ', sedangkan nilai terendah berada pada ' + minLabel + '.';
                }).filter(Boolean).join(' ');

                return title + ' pada ' + scopeLabel +
                    ' memperlihatkan pola perbandingan antar kategori pada filter aktif. ' + summary +
                    ' Ringkasan ini dibuat otomatis dari data dashboard saat layanan AI gratis belum tersedia.';
            }

            return title + ' pada ' + scopeLabel +
                ' merangkum kondisi dashboard sesuai filter aktif. Ringkasan otomatis AI belum tersedia, sehingga sistem menggunakan penjelasan cadangan berbasis data yang sedang tampil.';
        };

        app.normalizeInsightText = function(text) {
            let normalized = String(text || '');

            normalized = normalized.replace(/\r\n/g, '\n');
            normalized = normalized.replace(/\b(?:[A-Za-z]\s){3,}[A-Za-z]\b/g, function(match) {
                return match.replace(/\s+/g, '');
            });
            normalized = normalized.replace(/[ \t]+/g, ' ');
            normalized = normalized.replace(/\s+([,.;:!?])/g, '$1');
            normalized = normalized.replace(/\n{3,}/g, '\n\n');

            return normalized.trim();
        };

        app.formatInsightHtml = function(text) {
            const normalized = this.normalizeInsightText(text || '');
            const paragraphs = normalized.split(/\n{2,}/).filter(Boolean);

            return paragraphs.map(function(paragraph) {
                return '<p>' + app.escapeHtml(paragraph) + '</p>';
            }).join('');
        };

        app.setDrawerInsightState = function(isVisible, html) {
            const shell = $('#drawer_ai_insight');
            const body = $('#drawer_ai_insight_body');

            if (!isVisible) {
                shell.prop('hidden', true);
                body.html('');
                return;
            }

            body.html(html || '');
            shell.prop('hidden', false);
        };

        app.downloadPdfDocument = async function(section, payload, insight, canvas) {
            const pdfLib = window.jspdf;
            const jsPDF = pdfLib.jsPDF;
            const pdf = new jsPDF('p', 'mm', 'a4');
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            const margin = 16;
            const contentWidth = pageWidth - (margin * 2);
            const title = payload.title || 'Dashboard PAD';
            const scopeLabel = payload.scope_label || 'Jawa Timur';
            const filterLines = [
                'Lingkup: ' + scopeLabel,
                'Tahun: ' + (payload.filters.tahun || 'Semua Tahun'),
                'Jenis: ' + (payload.filters.jenis || 'Semua Jenis'),
                'Wilayah: ' + (payload.filters.wilayah || 'Semua Wilayah')
            ];
            const imageData = canvas.toDataURL('image/png');
            const ratio = (canvas.width > 0 && canvas.height > 0) ? (canvas.height / canvas.width) : 1;
            let imageWidth = contentWidth;
            let imageHeight = imageWidth * ratio;
            let cursorY = margin;
            const safeInsight = this.normalizeInsightText(insight || this.buildLocalInsight(payload));

            pdf.setFont('helvetica', 'bold');
            pdf.setFontSize(17);
            const titleLines = pdf.splitTextToSize(title, contentWidth);
            pdf.text(titleLines, margin, cursorY);
            cursorY += (titleLines.length * 7) + 1;

            pdf.setFont('times', 'normal');
            pdf.setFontSize(10.5);
            filterLines.forEach(function(line) {
                pdf.text(line, margin, cursorY, {
                    align: 'left'
                });
                cursorY += 5.1;
            });
            cursorY += 3;

            const maxImageHeight = pageHeight - cursorY - 82;

            if (imageHeight > maxImageHeight) {
                imageHeight = maxImageHeight;
                imageWidth = imageHeight / ratio;
            }

            const centeredImageX = margin + ((contentWidth - imageWidth) / 2);
            pdf.addImage(imageData, 'PNG', centeredImageX, cursorY, imageWidth, imageHeight);
            cursorY += imageHeight + 8;

            pdf.setFont('helvetica', 'bold');
            pdf.setFontSize(12);
            pdf.text('Penjelasan Otomatis', margin, cursorY);
            cursorY += 6;

            pdf.setFont('times', 'normal');
            pdf.setFontSize(11);
            const paragraphs = safeInsight.split(/\n{2,}/).filter(Boolean);
            const paragraphGap = 2.5;
            const insightLineHeight = 5.3;

            paragraphs.forEach(function(paragraph, paragraphIndex) {
                const paragraphLines = pdf.splitTextToSize(paragraph, contentWidth);

                if ((cursorY + (paragraphLines.length * insightLineHeight)) > (pageHeight - margin)) {
                    pdf.addPage();
                    cursorY = margin;
                    pdf.setFont('helvetica', 'bold');
                    pdf.setFontSize(12);
                    pdf.text('Penjelasan Otomatis', margin, cursorY);
                    cursorY += 6;
                    pdf.setFont('times', 'normal');
                    pdf.setFontSize(11);
                }

                paragraphLines.forEach(function(line) {
                    pdf.text(line, margin, cursorY, {
                        align: 'left',
                        maxWidth: contentWidth
                    });
                    cursorY += insightLineHeight;
                });

                if (paragraphIndex < (paragraphs.length - 1)) {
                    cursorY += paragraphGap;
                }
            });

            pdf.save(this.buildPdfFilename(section, title));
        };

        app.buildPdfFilename = function(section, title) {
            const cleanTitle = String(title || section || 'dashboard')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');

            return 'dashboard-pad-' + cleanTitle + '.pdf';
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

            $(document).off('click', '#drawer_ai_button').on('click', '#drawer_ai_button', function() {
                const section = $(this).data('drawer-section') || 'ringkasan';
                appInstance.generateDrawerInsight(section, this);
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
            $('#drawer_ai_button').attr('data-drawer-section', payload.exportSection);
            $('#drawer_summary_grid').html(payload.summaryCards.map((card) => {
                return '<div class="drawer-stat-card"><span>' + this.escapeHtml(card.label) + '</span><strong>' + this.escapeHtml(card.value) + '</strong></div>';
            }).join(''));
            this.drawerContext = payload;
            this.setDrawerInsightState(false);

            this.renderTable('drawer_table', payload.rows);

            $('#dashboard_drawer, #dashboard_drawer_backdrop').addClass('is-open');
            $('#dashboard_drawer').attr('aria-hidden', 'false');
        };

        app.closeDrawer = function() {
            $('#dashboard_drawer, #dashboard_drawer_backdrop').removeClass('is-open');
            $('#dashboard_drawer').attr('aria-hidden', 'true');
            this.drawerContext = null;
            this.setDrawerInsightState(false);
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

        app.resolveDrawerInsightPayload = function(section) {
            const drawerPayload = this.drawerContext || this.resolveDrawerPayload(section);

            return {
                section: section,
                title: drawerPayload.title || 'Detail Dashboard',
                description: drawerPayload.description || 'Rincian analitik dan data pendukung.',
                scope_label: (this.state.dashboard || {}).scope ? ((this.state.dashboard || {}).scope.label || 'Jawa Timur') : 'Jawa Timur',
                filters: this.buildInsightFilters(),
                labels: [],
                datasets: [],
                rows: drawerPayload.rows || []
            };
        };

        app.generateDrawerInsight = async function(section, button) {
            const payload = this.resolveDrawerInsightPayload(section);
            const originalHtml = button ? button.innerHTML : '';

            this.setExportButtonLoading(button, true);
            this.setDrawerInsightState(true, '<p>Sedang menyiapkan penjelasan otomatis dari AI...</p>');

            try {
                const insightResponse = await this.requestSectionInsight(section, payload);
                let html = this.formatInsightHtml(insightResponse.insight);

                if (insightResponse.used_fallback && insightResponse.error_message) {
                    html = '<p><strong>Gemini belum berhasil dipakai.</strong></p><p>' + app.escapeHtml(insightResponse.error_message) + '</p>' + html;
                }

                this.setDrawerInsightState(true, html);
            } catch (error) {
                console.error('Drawer insight gagal:', error);
                this.setDrawerInsightState(true, this.formatInsightHtml(this.buildLocalInsight(payload)));
            } finally {
                this.setExportButtonLoading(button, false, originalHtml);
            }
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



