<script>
    window.PetaDashboardApp = {
        state: {
            tahun: window.petaDashboardConfig.defaultTahun ? String(window.petaDashboardConfig.defaultTahun) : '',
            jenis: '',
            wilayah: '',
            mapData: [],
            dashboard: null,
            infoCollapsed: false,
            filterCollapsed: true
        },
        map: null,
        mapLayer: null,
        infoControlContent: null,
        hasInitialFit: false,
        chartInstances: {},
        baseLayers: {},
        palette: ['#2563eb', '#14b8a6', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#f97316'],

        init: function() {
            this.renderTopOverlay();
            this.initMap();
            this.bindFilterEvents();
            this.refreshAll();
        },

        renderTopOverlay: function() {
            const target = document.getElementById('map_top_overlay');
            if (!target) {
                return;
            }

            target.innerHTML = this.buildOverlayHtml();
            this.syncFilterMeta();
        },

        buildOverlayHtml: function() {
            const tahunOptions = (window.petaFilterOptions.tahunList || []).map((tahun) => {
                const selected = String(tahun) === String(this.state.tahun) ? ' selected' : '';
                return '<option value="' + this.escapeHtml(tahun) + '"' + selected + '>' + this.escapeHtml(tahun) + '</option>';
            }).join('');

            const jenisOptions = ['<option value="">Semua Jenis</option>'].concat((window.petaFilterOptions.jenisAkun || []).map((jenis) => {
                const selected = jenis === this.state.jenis ? ' selected' : '';
                return '<option value="' + this.escapeHtml(jenis) + '"' + selected + '>' + this.escapeHtml(jenis) + '</option>';
            })).join('');

            const wilayahOptions = ['<option value="">Semua Pemda (Jatim)</option>'].concat((window.petaFilterOptions.wilayahList || []).map((wilayah) => {
                const selected = wilayah === this.state.wilayah ? ' selected' : '';
                return '<option value="' + this.escapeHtml(wilayah) + '"' + selected + '>' + this.escapeHtml(wilayah) + '</option>';
            })).join('');

            const collapsedClass = this.state.filterCollapsed ? ' is-collapsed' : '';
            const toggleText = this.state.filterCollapsed ? 'Tampilkan Filter' : 'Sembunyikan Filter';

            return '' +
                '<div class="map-overlay-shell">' +
                '<div class="map-filter-panel' + collapsedClass + '">' +
                '<div class="map-filter-head">' +
                '<div class="map-filter-title">' +
                '<span class="map-filter-title-badge"><i class="bi bi-building"></i></span>' +
                '<span><strong>Filter Peta PAD Bapenda Jatim</strong><small>Kontrol analisis wilayah dan ekspor data</small></span>' +
                '</div>' +
                '<div class="map-filter-tools">' +
                '<span class="map-badge" id="active_scope_badge">Semua Pemda (Jatim)</span>' +
                '<span class="map-badge map-badge-primary" id="active_filter_badge">Tahun ' + this.escapeHtml(this.state.tahun || 'Semua Tahun') + '</span>' +
                '<button id="toggle_filter_panel" type="button" class="map-panel-toggle"><i class="bi bi-layout-sidebar-inset"></i> ' + toggleText + '</button>' +
                '</div>' +
                '</div>' +
                '<div class="map-filter-body">' +
                '<div class="map-filter-bar">' +
                '<div class="map-filter-field">' +
                '<label for="filter_jenis">Jenis Akun</label>' +
                '<select id="filter_jenis" class="map-filter-select">' + jenisOptions + '</select>' +
                '</div>' +
                '<div class="map-filter-field">' +
                '<label for="filter_tahun">Tahun</label>' +
                '<select id="filter_tahun" class="map-filter-select"><option value="">Semua Tahun</option>' + tahunOptions + '</select>' +
                '</div>' +
                '<div class="map-filter-field">' +
                '<label for="filter_wilayah">Wilayah</label>' +
                '<select id="filter_wilayah" class="map-filter-select">' + wilayahOptions + '</select>' +
                '</div>' +
                '<button id="btn_filter" type="button" class="map-filter-btn"><i class="bi bi-sliders"></i> Terapkan Filter</button>' +
                '<button type="button" class="map-filter-export export-trigger" data-export-section="ringkasan"><i class="bi bi-file-earmark-excel"></i> Export</button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
        },

        bindFilterEvents: function() {
            const app = this;

            $(document).off('click', '#btn_filter').on('click', '#btn_filter', function() {
                app.readFilterState();
                app.refreshAll();
            });

            $(document).off('change', '#filter_tahun, #filter_jenis, #filter_wilayah').on('change', '#filter_tahun, #filter_jenis, #filter_wilayah', function() {
                app.readFilterState();
                app.syncFilterMeta();
            });

            $(document).off('click', '#toggle_info_panel').on('click', '#toggle_info_panel', function() {
                app.state.infoCollapsed = !app.state.infoCollapsed;
                app.refreshInfoPanel();
            });

            $(document).off('click', '#toggle_filter_panel').on('click', '#toggle_filter_panel', function() {
                app.state.filterCollapsed = !app.state.filterCollapsed;
                app.renderTopOverlay();
            });
        },

        initMap: function() {
            const app = this;

            this.map = L.map('peta', {
                zoomControl: false
            }).setView([-7.536, 112.238], 8);

            L.control.zoom({
                position: 'topleft'
            }).addTo(this.map);

            const street = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            });

            const satellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            });

            const hybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            });

            this.baseLayers = {
                Street: street,
                Satellite: satellite,
                Hybrid: hybrid
            };

            satellite.addTo(this.map);

            L.control.layers(this.baseLayers, null, {
                position: 'topright',
                collapsed: true
            }).addTo(this.map);

            const infoControl = L.control({
                position: 'bottomright'
            });

            infoControl.onAdd = function() {
                const div = L.DomUtil.create('div', 'map-floating-card');
                app.infoControlContent = div;
                app.renderDefaultInfo();
                return div;
            };

            infoControl.addTo(this.map);
        },

        readFilterState: function() {
            this.state.tahun = $('#filter_tahun').val();
            this.state.jenis = $('#filter_jenis').val();
            this.state.wilayah = $('#filter_wilayah').val();
            this.syncFilterMeta();
        },

        syncFilterMeta: function() {
            $('#active_scope_badge').text(this.state.wilayah || 'Semua Pemda (Jatim)');
            $('#active_filter_badge').text('Tahun ' + (this.state.tahun || 'Semua Tahun') + ' | ' + (this.state.jenis || 'Semua Jenis'));
        },

        refreshAll: function() {
            this.toggleLoading(true);
            this.refreshMap();
            this.refreshDashboard();
        },

        refreshMap: function() {
            const app = this;

            $.ajax({
                url: window.petaDashboardConfig.dataUrl,
                method: 'GET',
                data: {
                    tahun: this.state.tahun,
                    jenis: this.state.jenis
                }
            }).done(function(response) {
                app.state.mapData = response.data || [];
                app.renderMap(response);
                app.renderLegend(response.legend || []);
                app.refreshInfoPanel();
            }).fail(function() {
                alert('Gagal memuat data peta.');
            }).always(function() {
                app.toggleLoading(false);
            });
        },

        refreshDashboard: function() {
            const app = this;

            $.ajax({
                url: window.petaDashboardConfig.dashboardUrl,
                method: 'GET',
                data: {
                    tahun: this.state.tahun,
                    jenis: this.state.jenis,
                    wilayah: this.state.wilayah
                }
            }).done(function(response) {
                app.state.dashboard = response;
                if (typeof app.renderDashboard === 'function') {
                    app.renderDashboard(response);
                }
            }).fail(function() {
                alert('Gagal memuat dashboard.');
            });
        },

        renderMap: function(response) {
            const app = this;

            if (this.mapLayer) {
                this.map.removeLayer(this.mapLayer);
            }

            this.mapLayer = L.geoJSON([], {
                style: function(feature) {
                    return app.getFeatureStyle(feature.properties, feature.properties.kabupaten === app.state.wilayah);
                },
                onEachFeature: function(feature, layer) {
                    const props = feature.properties;

                    layer.on({
                        mouseover: function() {
                            layer.setStyle({
                                weight: 2.8,
                                color: '#0f172a',
                                fillOpacity: 0.92
                            });
                        },
                        mouseout: function() {
                            app.syncSelectedLayer();
                        },
                        click: function() {
                            app.state.wilayah = props.kabupaten || '';
                            $('#filter_wilayah').val(app.state.wilayah);
                            app.syncFilterMeta();
                            app.syncSelectedLayer();
                            app.focusSelectedWilayah();
                            app.renderInfo(props);
                            app.refreshDashboard();
                        }
                    });

                    layer.bindPopup(app.buildPopupHtml(props), {
                        maxWidth: 280
                    });
                }
            }).addTo(this.map);

            (response.data || []).forEach(function(item) {
                if (!item.geojson) {
                    return;
                }

                try {
                    const geojson = JSON.parse(item.geojson);
                    app.mapLayer.addData({
                        type: 'Feature',
                        geometry: geojson,
                        properties: item
                    });
                } catch (error) {
                    console.error('Geometri peta gagal diparsing.', error);
                }
            });

            this.syncSelectedLayer();

            if (!this.hasInitialFit) {
                this.fitAllBounds();
                this.hasInitialFit = true;
            } else {
                this.focusSelectedWilayah();
            }
        },

        renderLegend: function(legendItems) {
            const app = this;

            if (this.legendControl) {
                this.map.removeControl(this.legendControl);
            }

            this.legendControl = L.control({
                position: 'bottomleft'
            });

            this.legendControl.onAdd = function() {
                const div = L.DomUtil.create('div', 'map-floating-card');
                let html = '<div class="map-info-head"><h4>Legenda Capaian</h4></div><div class="map-info-body"><p>Warna peta menunjukkan tingkat capaian PAD.</p>';

                legendItems.forEach(function(item) {
                    html += '<div class="legend-row"><span class="legend-swatch" style="background:' + item.color + ';"></span><span>' + item.label + '</span></div>';
                });

                html += '</div>';
                div.innerHTML = html;
                return div;
            };

            this.legendControl.addTo(this.map);
        },

        refreshInfoPanel: function() {
            if (this.state.wilayah) {
                const selected = this.findMapDataByWilayah(this.state.wilayah);
                if (selected) {
                    this.renderInfo(selected);
                    return;
                }
            }

            this.renderDefaultInfo();
        },

        renderDefaultInfo: function() {
            if (!this.infoControlContent) {
                return;
            }

            const summary = this.aggregateProvinceSummary();
            const collapsedClass = this.state.infoCollapsed ? ' is-collapsed' : '';
            const toggleText = this.state.infoCollapsed ? 'Show' : 'Hide';

            this.infoControlContent.innerHTML =
                '<div class="map-info-head">' +
                '<h4>Ringkasan Jawa Timur</h4>' +
                '<button id="toggle_info_panel" type="button" class="info-toggle-btn">' + toggleText + '</button>' +
                '</div>' +
                '<div class="map-info-body' + collapsedClass + '">' +
                '<p>Pilih wilayah di peta untuk memperbarui rincian secara otomatis.</p>' +
                this.buildMapStat('Anggaran', this.formatCurrency(summary.total_anggaran)) +
                this.buildMapStat('Realisasi', this.formatCurrency(summary.total_realisasi)) +
                this.buildMapStat('Capaian', this.formatPercent(summary.persentase)) +
                '<div class="map-info-section">' +
                '<div class="map-info-section-title">Filter Aktif</div>' +
                this.buildMapStat('Tahun', this.state.tahun || 'Semua Tahun') +
                this.buildMapStat('Jenis', this.state.jenis || 'Semua Jenis') +
                this.buildMapStat('Wilayah', this.state.wilayah || 'Semua Pemda (Jatim)') +
                '</div>' +
                '</div>';
        },

        renderInfo: function(props) {
            if (!this.infoControlContent) {
                return;
            }

            const collapsedClass = this.state.infoCollapsed ? ' is-collapsed' : '';
            const toggleText = this.state.infoCollapsed ? 'Show' : 'Hide';
            let detailHtml = '';

            (props.detail_per_akun || []).forEach((item) => {
                detailHtml +=
                    '<div class="map-account-item">' +
                    '<div class="map-account-name">' + this.escapeHtml(this.shortAkunLabel(item.akun)) + '</div>' +
                    this.buildMapStat('Anggaran', this.formatCurrency(item.anggaran)) +
                    this.buildMapStat('Realisasi', this.formatCurrency(item.realisasi)) +
                    this.buildMapStat('Capaian', this.formatPercent(item.persentase)) +
                    '</div>';
            });

            this.infoControlContent.innerHTML =
                '<div class="map-info-head">' +
                '<h4>' + this.escapeHtml(props.kabupaten || 'Wilayah') + '</h4>' +
                '<button id="toggle_info_panel" type="button" class="info-toggle-btn">' + toggleText + '</button>' +
                '</div>' +
                '<div class="map-info-body' + collapsedClass + '">' +
                '<p>Detail ini mengikuti wilayah yang sedang dipilih pada peta.</p>' +
                this.buildMapStat('Anggaran', this.formatCurrency(props.total_anggaran)) +
                this.buildMapStat('Realisasi', this.formatCurrency(props.total_realisasi)) +
                this.buildMapStat('Selisih', this.formatCurrency(parseFloat(props.total_realisasi || 0) - parseFloat(props.total_anggaran || 0))) +
                this.buildMapStat('Capaian', this.formatPercent(props.persentase)) +
                '<div class="map-info-section">' +
                '<div class="map-info-section-title">Rincian Jenis PAD</div>' +
                detailHtml +
                '</div>' +
                '</div>';
        },

        buildPopupHtml: function(props) {
            return '' +
                '<div class="popup-title">' + this.escapeHtml(props.kabupaten || 'Wilayah') + '</div>' +
                '<div class="popup-row"><span>Anggaran</span><strong>' + this.formatCurrency(props.total_anggaran) + '</strong></div>' +
                '<div class="popup-row"><span>Realisasi</span><strong>' + this.formatCurrency(props.total_realisasi) + '</strong></div>' +
                '<div class="popup-row"><span>Capaian</span><strong>' + this.formatPercent(props.persentase) + '</strong></div>';
        },

        syncSelectedLayer: function() {
            const app = this;

            if (!this.mapLayer) {
                return;
            }

            this.mapLayer.eachLayer(function(layer) {
                const props = layer.feature.properties;
                const isActive = props.kabupaten === app.state.wilayah;
                layer.setStyle(app.getFeatureStyle(props, isActive));
            });
        },

        focusSelectedWilayah: function() {
            const app = this;

            if (!this.mapLayer) {
                return;
            }

            if (!this.state.wilayah) {
                this.fitAllBounds();
                return;
            }

            this.mapLayer.eachLayer(function(layer) {
                const props = layer.feature.properties;
                if (props.kabupaten === app.state.wilayah) {
                    app.map.fitBounds(layer.getBounds(), {
                        padding: [32, 32]
                    });
                }
            });
        },

        fitAllBounds: function() {
            if (this.mapLayer && this.mapLayer.getLayers().length > 0) {
                this.map.fitBounds(this.mapLayer.getBounds(), {
                    padding: [24, 24]
                });
            }
        },

        aggregateProvinceSummary: function() {
            const result = (this.state.mapData || []).reduce(function(summary, item) {
                summary.total_anggaran += parseFloat(item.total_anggaran || 0);
                summary.total_realisasi += parseFloat(item.total_realisasi || 0);
                return summary;
            }, {
                total_anggaran: 0,
                total_realisasi: 0
            });

            result.persentase = result.total_anggaran > 0 ? (result.total_realisasi / result.total_anggaran) * 100 : 0;

            return result;
        },

        findMapDataByWilayah: function(wilayah) {
            return (this.state.mapData || []).find(function(item) {
                return item.kabupaten === wilayah;
            });
        },

        getFeatureStyle: function(props, isActive) {
            return {
                fillColor: this.getColorByPercent(props.persentase),
                weight: isActive ? 3 : 1.15,
                opacity: 1,
                color: isActive ? '#0f172a' : '#ffffff',
                fillOpacity: isActive ? 0.92 : 0.78
            };
        },

        getColorByPercent: function(percent) {
            const value = parseFloat(percent || 0);
            if (value >= 100) return '#166534';
            if (value >= 90) return '#22c55e';
            if (value >= 80) return '#f59e0b';
            if (value >= 60) return '#f97316';
            return '#dc2626';
        },

        shortAkunLabel: function(label) {
            return String(label || '').replace('Pendapatan Asli Daerah - ', '');
        },

        formatCurrency: function(value) {
            const number = parseFloat(value || 0);
            return 'Rp ' + number.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' M';
        },

        formatPercent: function(value) {
            const number = parseFloat(value || 0);
            return number.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + '%';
        },

        buildMapStat: function(label, value) {
            return '<div class="map-stat"><span>' + this.escapeHtml(label) + '</span><span>' + this.escapeHtml(value) + '</span></div>';
        },

        toggleLoading: function(isLoading) {
            $('.map-wrapper-full, .dashboard-shell').toggleClass('loading-state', isLoading);
        },

        escapeHtml: function(value) {
            return String(value === null || value === undefined ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    };

    $(document).ready(function() {
        window.PetaDashboardApp.init();
    });
</script>
