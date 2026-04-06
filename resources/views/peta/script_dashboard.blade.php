<script>
    (function(app) {
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
            this.renderSummary(payload.summary, payload.scope);
            this.renderDashboardHeader(payload.scope);
            this.renderCharts(payload.charts || []);
            this.renderTable('table_detail_akun', payload.tables.detail_akun.rows || []);
            this.renderTable('table_detail_wilayah', payload.tables.detail_wilayah.rows || []);
            $('#detail_akun_title').text(payload.tables.detail_akun.title || 'Detail Akun PAD');
            $('#detail_wilayah_title').text(payload.tables.detail_wilayah.title || 'Detail Wilayah');
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
            $('#summary_status').text(this.resolvePerformanceLabel(summary.persentase) + ' | ' + (scope.label || 'Jawa Timur'));
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
            const isHorizontal = chart.options && chart.options.indexAxis === 'y';
            const primaryFormat = chart.datasets[0] ? chart.datasets[0].format : 'currency';
            const formattedLabels = (chart.labels || []).map(function(label) {
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
                        autoSkip: false,
                        maxRotation: 0,
                        minRotation: 0,
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
                    scales: scales,
                    plugins: {
                        legend: {
                            display: isPie || chart.datasets.length > 1,
                            position: isPie ? 'bottom' : 'top'
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
            const table = $('#' + tableId);
            const thead = table.find('thead');
            const tbody = table.find('tbody');

            if (!rows.length) {
                thead.html('');
                tbody.html('<tr><td colspan="10" class="text-center text-muted">Tidak ada data.</td></tr>');
                return;
            }

            const headers = Object.keys(rows[0]);
            thead.html('<tr>' + headers.map(function(header) {
                return '<th>' + app.escapeHtml(header) + '</th>';
            }).join('') + '</tr>');

            tbody.html(rows.map(function(row) {
                return '<tr>' + headers.map(function(header) {
                    const value = row[header];
                    return '<td>' + app.escapeHtml(app.formatCellValue(header, value)) + '</td>';
                }).join('') + '</tr>';
            }).join(''));
        };

        app.formatCellValue = function(header, value) {
            if (/persentase|kontribusi|growth/i.test(header)) {
                return this.formatPercent(value);
            }

            if (/anggaran|realisasi|selisih/i.test(header)) {
                return this.formatCurrency(value);
            }

            return value;
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
                return 'Rp ' + value;
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
                    wilayah: appInstance.state.wilayah
                });

                window.open(window.petaDashboardConfig.exportUrl + '?' + query, '_blank');
            });
        };

        app.bindExportEvents();
    })(window.PetaDashboardApp);
</script>
