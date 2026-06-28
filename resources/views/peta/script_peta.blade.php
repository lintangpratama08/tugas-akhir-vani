<script>
    window.PetaDashboardApp = {
        state: {
            tahun: window.petaDashboardConfig.defaultTahun ? String(window.petaDashboardConfig.defaultTahun) : '',
            jenis: '',
            karisidenan: '',
            wilayah: '',
            kecamatan: '',
            mapMode: 'kabupaten',
            filterOpen: false,
            filterDraft: null,
            mapData: [],
            mapScope: {
                mode: 'province',
                label: 'Semua Pemda (Jawa Timur)',
                parent: 'Jawa Timur'
            },
            mapSummary: null,
            dashboard: null,
            infoCollapsed: false,
            legendCollapsed: false,
            infoTab: 'pad'
        },
        map: null,
        mapLayer: null,
        infoControlContent: null,
        mapBackControlButton: null,
        hasInitialFit: false,
        chartInstances: {},
        baseLayers: {},
        palette: ['#2563eb', '#14b8a6', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#f97316'],

        init: function() {
            this.renderTopOverlay();
            this.initMap();
            this.bindFilterEvents();

            if (window.petaDashboardConfig.backendUnavailable) {
                this.renderDefaultInfo();
                return;
            }

            this.refreshAll();
        },

        renderTopOverlay: function() {
            const mapTarget = document.getElementById('map_top_overlay');
            const dockTarget = document.getElementById('map_filter_dock');

            if (mapTarget) {
                mapTarget.innerHTML = this.buildMapOverlayHtml();
            }

            if (dockTarget) {
                dockTarget.innerHTML = this.buildDockHtml();
            }

            this.syncFilterMeta();
            this.updateMapBackControl();
        },

        buildMapOverlayHtml: function() {
            const kabupatenClass = this.state.mapMode === 'kabupaten' ? ' is-active' : '';
            const karisidenanClass = this.state.mapMode === 'karisidenan' ? ' is-active' : '';

            return '' +
                '<div class="map-overlay-shell">' +
                '<div class="map-filter-badges">' +
                '<button id="set_map_mode_kabupaten" type="button" class="map-filter-chip map-mode-chip' +
                kabupatenClass + '"><i class="bi bi-map"></i><span>Kabupaten</span></button>' +
                '<button id="set_map_mode_karisidenan" type="button" class="map-filter-chip map-mode-chip' +
                karisidenanClass + '"><i class="bi bi-grid-3x3-gap"></i><span>Karisidenan</span></button>' +
                '</div>' +
                '</div>';
        },

        buildDockHtml: function() {
            const draft = this.getFilterDraft();
            const tahunOptions = (window.petaFilterOptions.tahunList || []).map((tahun) => {
                const selected = String(tahun) === String(draft.tahun) ? ' selected' : '';
                return '<option value="' + this.escapeHtml(tahun) + '"' + selected + '>' + this
                    .escapeHtml(tahun) + '</option>';
            }).join('');

            const jenisOptions = ['<option value="">Semua Jenis</option>'].concat((window.petaFilterOptions
                .jenisAkun || []).map((jenis) => {
                const selected = jenis === draft.jenis ? ' selected' : '';
                return '<option value="' + this.escapeHtml(jenis) + '"' + selected + '>' + this
                    .escapeHtml(jenis) + '</option>';
            })).join('');

            const karisidenanOptions = ['<option value="">Semua Karisidenan</option>'].concat((window
                .petaFilterOptions.karisidenanList || []).map((item) => {
                const selected = String(item.id) === String(draft.karisidenan) ? ' selected' : '';
                return '<option value="' + this.escapeHtml(item.id) + '"' + selected + '>' + this
                    .escapeHtml(item.nama_karisidenan) + '</option>';
            })).join('');

            const wilayahOptions = ['<option value="">Semua Pemda (Jatim)</option>'].concat(this
                .getFilteredWilayahOptions(draft.karisidenan).map((item) => {
                    const selected = item.kabupaten === draft.wilayah ? ' selected' : '';
                    const label = item.nama_karisidenan && item.nama_karisidenan !== '-' ?
                        item.kabupaten + ' (' + item.nama_karisidenan + ')' :
                        item.kabupaten;
                    return '<option value="' + this.escapeHtml(item.kabupaten) + '"' + selected + '>' +
                        this
                        .escapeHtml(label) + '</option>';
                })).join('');

            const badges = this.buildFilterBadgeHtml();
            const yearChip = this.renderYearChip();

            return '' +
                '<div class="map-dock-panel">' +
                '<button id="toggle_filter_modal" type="button" class="map-filter-chip"><i class="bi bi-funnel"></i><span>Filter</span></button>' +
                yearChip +
                badges +
                '</div>' +
                (this.state.filterOpen ?
                    '<button id="map_filter_backdrop" type="button" class="map-filter-backdrop" aria-label="Tutup filter"></button>' +
                    '<div class="map-filter-modal">' +
                    '<div class="map-filter-panel">' +
                    '<div class="map-filter-head">' +
                    '<div><p class="map-filter-modal-kicker">Filter Dashboard</p><h3>Atur wilayah dan tahun untuk peta serta dashboard.</h3></div>' +
                    '<div class="map-filter-tools">' +
                    '<button id="close_filter_modal" type="button" class="map-filter-modal-close">Tutup</button>' +
                    '<button id="btn_filter" type="button" class="map-filter-btn">Cari</button>' +
                    '</div>' +
                    '</div>' +
                    '<div class="map-filter-bar">' +
                    '<div class="map-filter-field"><label for="filter_tahun">Tahun</label><select id="filter_tahun" class="map-filter-select"><option value="">Semua Tahun</option>' +
                    tahunOptions + '</select></div>' +
                    '<div class="map-filter-field"><label for="filter_jenis">Jenis Akun</label><select id="filter_jenis" class="map-filter-select" aria-label="Jenis Akun">' +
                    jenisOptions + '</select></div>' +
                    '<div class="map-filter-field"><label for="filter_karisidenan">Karisidenan</label><select id="filter_karisidenan" class="map-filter-select" aria-label="Karisidenan">' +
                    karisidenanOptions + '</select></div>' +
                    '<div class="map-filter-field"><label for="filter_wilayah">Wilayah</label><select id="filter_wilayah" class="map-filter-select" aria-label="Wilayah">' +
                    wilayahOptions + '</select></div>' +
                    '</div>' +
                    '<div class="map-filter-footer">' + this.buildDrilldownActions() + '</div>' +
                    '</div>' +
                    '</div>' : '') +
                '';
        },

        getFilterDraft: function() {
            return this.state.filterDraft || {
                tahun: this.state.tahun,
                jenis: this.state.jenis,
                karisidenan: this.state.karisidenan,
                wilayah: this.state.wilayah
            };
        },

        buildFilterBadgeHtml: function() {
            const badges = [];

            if (this.state.karisidenan) {
                badges.push(this.renderBadgeChip('Kar', this.resolveKarisidenanLabel()));
            }

            if (this.state.wilayah) {
                badges.push(this.renderBadgeChip('Wil', this.state.wilayah));
            }

            return badges.join('');
        },

        renderBadgeChip: function(label, value) {
            return '<span class="map-filter-chip map-filter-chip-badge"><span class="chip-label">' + this
                .escapeHtml(label) + '</span><span>' + this.escapeHtml(value) + '</span></span>';
        },

        renderYearChip: function() {
            return '<span class="map-filter-chip map-filter-chip-year"><span class="chip-label">Tahun</span><span>' +
                this.escapeHtml(this.state.tahun || 'Semua') + '</span></span>';
        },

        buildDrilldownActions: function() {
            let html = '';

            if (this.state.kecamatan) {
                html +=
                    '<button id="btn_back_to_kecamatan" type="button" class="map-filter-btn"><i class="bi bi-arrow-left"></i> Kembali ke Kecamatan</button>';
            }

            if (this.state.wilayah) {
                if (this.state.karisidenan) {
                    html +=
                        '<button id="btn_back_to_karisidenan" type="button" class="map-filter-btn"><i class="bi bi-diagram-3"></i> Lihat Karisidenan</button>';
                }
                html +=
                    '<button id="btn_back_to_province" type="button" class="map-filter-btn"><i class="bi bi-globe2"></i> Lihat Jawa Timur</button>';
            } else if (this.state.karisidenan) {
                html +=
                    '<button id="btn_clear_karisidenan" type="button" class="map-filter-btn"><i class="bi bi-globe2"></i> Semua Jawa Timur</button>';
            }

            return html;
        },

        getBackNavigationConfig: function() {
            if (this.state.kecamatan) {
                return {
                    label: 'Kembali',
                    nextState: {
                        kecamatan: ''
                    }
                };
            }

            if (this.state.wilayah) {
                return {
                    label: 'Kembali',
                    nextState: {
                        wilayah: '',
                        kecamatan: ''
                    }
                };
            }

            if (this.state.karisidenan) {
                return {
                    label: 'Kembali',
                    nextState: {
                        karisidenan: '',
                        wilayah: '',
                        kecamatan: ''
                    }
                };
            }

            if (this.state.mapMode === 'karisidenan') {
                return {
                    label: 'Kembali',
                    nextState: {
                        mapMode: 'kabupaten',
                        karisidenan: '',
                        wilayah: '',
                        kecamatan: ''
                    }
                };
            }

            return {
                label: 'Kembali',
                disabled: true
            };
        },

        navigateBackMapView: function() {
            const config = this.getBackNavigationConfig();

            if (!config || config.disabled || !config.nextState) {
                return;
            }

            Object.assign(this.state, config.nextState, {
                infoTab: 'pad'
            });
            this.syncFilterMeta();
            this.renderTopOverlay();
            this.refreshAll();
        },

        updateMapBackControl: function() {
            if (!this.mapBackControlButton) {
                return;
            }

            const config = this.getBackNavigationConfig();
            this.mapBackControlButton.disabled = !!config.disabled;
            this.mapBackControlButton.setAttribute('aria-disabled', config.disabled ? 'true' : 'false');
            this.mapBackControlButton.setAttribute('title', config.label || 'Kembali');
            this.mapBackControlButton.innerHTML =
                '<i class="bi bi-arrow-left"></i><span>' + this.escapeHtml(config.label || 'Kembali') + '</span>';
        },

        addMapBackControl: function() {
            const app = this;

            const backControl = L.control({
                position: 'topright'
            });

            backControl.onAdd = function() {
                const container = L.DomUtil.create('div', 'leaflet-bar map-back-control');
                const button = L.DomUtil.create('button', 'map-back-button', container);
                button.type = 'button';
                button.setAttribute('aria-label', 'Kembali ke tampilan sebelumnya');
                L.DomEvent.disableClickPropagation(container);
                L.DomEvent.disableScrollPropagation(container);
                L.DomEvent.on(button, 'click', function(event) {
                    L.DomEvent.preventDefault(event);
                    app.navigateBackMapView();
                });
                app.mapBackControlButton = button;
                app.updateMapBackControl();
                return container;
            };

            backControl.addTo(this.map);
        },

        bindFilterEvents: function() {
            const app = this;

            $(document).off('click', '#toggle_filter_modal').on('click', '#toggle_filter_modal', function() {
                app.state.filterDraft = {
                    tahun: app.state.tahun,
                    jenis: app.state.jenis,
                    karisidenan: app.state.karisidenan,
                    wilayah: app.state.wilayah
                };
                app.state.filterOpen = true;
                app.renderTopOverlay();
            });

            $(document).off('click', '#close_filter_modal, #map_filter_backdrop').on('click',
                '#close_filter_modal, #map_filter_backdrop',
                function() {
                    app.state.filterOpen = false;
                    app.state.filterDraft = null;
                    app.renderTopOverlay();
                });

            $(document).off('click', '#btn_filter').on('click', '#btn_filter', function() {
                app.readFilterState();
                app.state.infoTab = 'pad';
                app.state.filterOpen = false;
                app.state.filterDraft = null;
                app.renderTopOverlay();
                app.refreshAll();
            });

            $(document).off('change', '#filter_tahun, #filter_jenis, #filter_karisidenan, #filter_wilayah').on(
                'change',
                '#filter_tahun, #filter_jenis, #filter_karisidenan, #filter_wilayah',
                function() {
                    app.readFilterDraftState();
                    app.renderTopOverlay();
                });

            $(document).off('click', '#set_map_mode_kabupaten').on('click', '#set_map_mode_kabupaten',
                function() {
                    app.state.mapMode = 'kabupaten';
                    app.state.karisidenan = '';
                    app.state.wilayah = '';
                    app.state.kecamatan = '';
                    app.state.infoTab = 'pad';
                    app.renderTopOverlay();
                    app.refreshAll();
                });

            $(document).off('click', '#set_map_mode_karisidenan').on('click', '#set_map_mode_karisidenan',
                function() {
                    app.state.mapMode = 'karisidenan';
                    app.state.karisidenan = '';
                    app.state.wilayah = '';
                    app.state.kecamatan = '';
                    app.state.infoTab = 'pad';
                    app.renderTopOverlay();
                    app.refreshAll();
                });

            $(document).off('click', '#btn_back_to_kecamatan').on('click', '#btn_back_to_kecamatan',
                function() {
                    app.state.kecamatan = '';
                    app.state.infoTab = 'pad';
                    app.syncFilterMeta();
                    app.renderTopOverlay();
                    app.refreshAll();
                });

            $(document).off('click', '#btn_back_to_province').on('click', '#btn_back_to_province', function() {
                app.state.wilayah = '';
                app.state.kecamatan = '';
                app.state.karisidenan = '';
                app.state.infoTab = 'pad';
                app.syncFilterMeta();
                app.renderTopOverlay();
                app.refreshAll();
            });

            $(document).off('click', '#btn_back_to_karisidenan').on('click', '#btn_back_to_karisidenan',
                function() {
                    app.state.wilayah = '';
                    app.state.kecamatan = '';
                    app.state.infoTab = 'pad';
                    app.renderTopOverlay();
                    app.refreshAll();
                });

            $(document).off('click', '#btn_clear_karisidenan').on('click', '#btn_clear_karisidenan',
                function() {
                    app.state.karisidenan = '';
                    app.state.wilayah = '';
                    app.state.kecamatan = '';
                    app.state.infoTab = 'pad';
                    app.state.filterDraft = null;
                    app.renderTopOverlay();
                    app.refreshAll();
                });

            $(document).off('click', '#toggle_info_panel').on('click', '#toggle_info_panel', function() {
                app.state.infoCollapsed = !app.state.infoCollapsed;
                app.refreshInfoPanel();
            });

            $(document).off('click', '.map-info-tab').on('click', '.map-info-tab', function() {
                app.state.infoTab = $(this).data('tab') || 'pad';
                app.refreshInfoPanel();
            });

            $(document).off('click', '#toggle_legend_panel').on('click', '#toggle_legend_panel', function() {
                app.state.legendCollapsed = !app.state.legendCollapsed;
                app.refreshLegendState();
            });

            $(document).off('click', '.popup-action-kecamatan').on('click', '.popup-action-kecamatan',
                function() {
                    app.state.mapMode = 'kabupaten';
                    app.state.wilayah = $(this).data('wilayah') || '';
                    app.state.karisidenan = $(this).data('karisidenan-id') || app.state.karisidenan;
                    app.state.kecamatan = '';
                    app.state.infoTab = 'pad';
                    app.renderTopOverlay();
                    app.refreshAll();
                });

            $(document).off('click', '.popup-action-karisidenan').on('click', '.popup-action-karisidenan',
                function() {
                    app.state.mapMode = 'karisidenan';
                    app.state.karisidenan = $(this).data('karisidenan-id') || '';
                    app.state.wilayah = '';
                    app.state.kecamatan = '';
                    app.state.infoTab = 'pad';
                    app.renderTopOverlay();
                    app.refreshAll();
                });
        },

        initMap: function() {
            const app = this;

            this.map = L.map('peta', {
                zoomControl: false
            }).setView([-7.536, 112.238], 10);

            L.control.zoom({
                position: 'topleft'
            }).addTo(this.map);

            const street = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 16,
                attribution: '&copy; OpenStreetMap'
            });

            const satellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                maxZoom: 16,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            });

            const hybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                maxZoom: 16,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            });

            this.baseLayers = {
                Street: street,
                Satellite: satellite,
                Hybrid: hybrid
            };

            street.addTo(this.map);

            L.control.layers(this.baseLayers, null, {
                position: 'topright',
                collapsed: true
            }).addTo(this.map);
            this.addMapBackControl();

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
            const draft = this.getFilterDraft();
            const previousKarisidenan = this.state.karisidenan;
            const previousWilayah = this.state.wilayah;
            this.state.tahun = draft.tahun || '';
            this.state.jenis = draft.jenis || '';
            this.state.karisidenan = draft.karisidenan || '';
            this.state.wilayah = draft.wilayah || '';

            if (previousKarisidenan !== this.state.karisidenan) {
                this.state.wilayah = '';
                this.state.kecamatan = '';
            }

            if (previousWilayah !== this.state.wilayah) {
                this.state.kecamatan = '';
            }

            this.syncFilterMeta();
            this.updateMapBackControl();
        },

        readFilterDraftState: function() {
            const draft = this.getFilterDraft();
            const previousKarisidenan = draft.karisidenan || '';
            const nextKarisidenan = $('#filter_karisidenan').val() || '';
            const nextWilayah = $('#filter_wilayah').val() || '';

            draft.tahun = $('#filter_tahun').val() || '';
            draft.jenis = $('#filter_jenis').val() || '';
            draft.karisidenan = nextKarisidenan;
            draft.wilayah = nextWilayah;

            if (String(previousKarisidenan) !== String(nextKarisidenan)) {
                draft.wilayah = '';
            }

            this.state.filterDraft = draft;
        },

        getFilteredWilayahOptions: function(selectedKarisidenan) {
            const karisidenan = String(selectedKarisidenan || '');
            const items = window.petaFilterOptions.wilayahList || [];

            if (!karisidenan) {
                return items;
            }

            return items.filter(function(item) {
                return String(item.karisidenan_id || '') === karisidenan;
            });
        },

        syncFilterMeta: function() {
            const scopeLabel = this.state.kecamatan || this.state.wilayah || this.resolveKarisidenanLabel() || (
                this.state.mapMode === 'karisidenan' ? 'Semua Karisidenan (Jatim)' : 'Semua Pemda (Jatim)');
            this.state.mapScope.label = scopeLabel;
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
                    jenis: this.state.jenis,
                    karisidenan: this.state.karisidenan,
                    wilayah: this.state.wilayah,
                    kecamatan: this.state.kecamatan,
                    map_mode: this.state.mapMode
                }
            }).done(function(response) {
                app.state.mapData = response.data || [];
                app.state.mapScope = response.scope || app.state.mapScope;
                app.state.mapSummary = response.summary || null;
                app.renderTopOverlay();
                app.renderMap(response);
                app.renderLegend(response.legend || [], response.legend_meta || {});
                app.refreshInfoPanel();
            }).fail(function(xhr) {
                const message = xhr && xhr.responseJSON && xhr.responseJSON.message ?
                    xhr.responseJSON.message :
                    'Gagal memuat data peta.';
                alert(message);
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
                    karisidenan: this.state.karisidenan,
                    wilayah: this.state.wilayah,
                    kecamatan: this.state.kecamatan,
                    map_mode: this.state.mapMode
                }
            }).done(function(response) {
                app.state.dashboard = response;
                if (typeof app.renderDashboard === 'function') {
                    app.renderDashboard(response);
                }
                app.refreshInfoPanel();
            }).fail(function(xhr) {
                const message = xhr && xhr.responseJSON && xhr.responseJSON.message ?
                    xhr.responseJSON.message :
                    'Gagal memuat dashboard.';
                alert(message);
            });
        },

        renderMap: function(response) {
            const app = this;

            if (this.mapLayer) {
                this.map.removeLayer(this.mapLayer);
            }

            this.mapLayer = L.geoJSON([], {
                style: function(feature) {
                    return app.getFeatureStyle(feature.properties, app.isActiveFeature(feature
                        .properties));
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
                            app.handleFeatureClick(props, layer);
                        }
                    });

                    layer.bindPopup(app.buildPopupHtml(props), {
                        maxWidth: 280
                    });

                    layer.bindTooltip(app.getRegionLabel(props), {
                        permanent: true,
                        direction: 'center',
                        className: 'region-label-tooltip'
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
                this.fitAllBounds(1);
                this.hasInitialFit = true;
            } else {
                this.focusSelectedWilayah();
            }
        },

        renderLegend: function(legendItems, legendMeta) {
            const app = this;

            if (this.legendControl) {
                this.map.removeControl(this.legendControl);
            }

            this.legendControl = L.control({
                position: 'bottomleft'
            });

            this.legendControl.onAdd = function() {
                const div = L.DomUtil.create('div', 'map-floating-card');
                const collapsedClass = app.state.legendCollapsed ? ' is-collapsed' : '';
                const toggleText = app.state.legendCollapsed ? 'Show' : 'Hide';
                const title = legendMeta && legendMeta.title ? legendMeta.title : 'Legenda Capaian';
                const description = legendMeta && legendMeta.description ? legendMeta.description :
                    'Warna peta menunjukkan tingkat capaian PAD.';
                let html =
                    '<div class="map-info-head"><h4>' + app.escapeHtml(title) +
                    '</h4><button id="toggle_legend_panel" type="button" class="legend-toggle-button">' +
                    toggleText + '</button></div><div class="map-info-body legend-body' + collapsedClass +
                    '"><p>' + app.escapeHtml(description) + '</p>';

                legendItems.forEach(function(item) {
                    html +=
                        '<div class="legend-row"><span class="legend-swatch" style="background:' +
                        item.color + ';"></span><span>' + item.label + '</span></div>';
                });

                html += '</div>';
                div.innerHTML = html;
                return div;
            };

            this.legendControl.addTo(this.map);
        },

        refreshLegendState: function() {
            const body = document.querySelector('.legend-body');
            const button = document.getElementById('toggle_legend_panel');

            if (body) {
                body.classList.toggle('is-collapsed', this.state.legendCollapsed);
            }

            if (button) {
                button.textContent = this.state.legendCollapsed ? 'Show' : 'Hide';
            }
        },

        refreshInfoPanel: function() {
            if (this.state.kecamatan || this.state.wilayah) {
                const selected = this.findSelectedMapData();
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

            const scope = this.state.mapScope || {};
            const summary = this.state.mapSummary || this.aggregateProvinceSummary();
            const collapsedClass = this.state.infoCollapsed ? ' is-collapsed' : '';
            const toggleText = this.state.infoCollapsed ? 'Show' : 'Hide';
            const title = scope.mode === 'kabupaten' ?
                (scope.label || 'Kabupaten/Kota') :
                (scope.mode === 'kecamatan' ?
                    (scope.label || 'Kecamatan') :
                    (scope.mode === 'karisidenan' ? (scope.label || 'Karisidenan') : (this.state.mapMode ===
                        'karisidenan' ? 'Ringkasan Karisidenan' : 'Ringkasan Jawa Timur')));
            const message = scope.mode === 'kabupaten' ?
                'Peta menampilkan kecamatan pada kabupaten/kota terpilih. Klik kecamatan untuk melihat detailnya.' :
                (scope.mode === 'kecamatan' ?
                    'Peta menampilkan kecamatan yang sedang dipilih beserta ringkasannya.' :
                    (scope.mode === 'karisidenan' ?
                        'Peta menampilkan seluruh wilayah dalam karisidenan aktif. Klik polygon untuk memilih detail wilayah.' :
                        (this.state.mapMode === 'karisidenan' ?
                            'Peta menampilkan pembagian karisidenan. Klik karisidenan untuk membuka wilayah di dalamnya.' :
                            'Pilih wilayah di peta untuk memperbarui rincian secara otomatis.')));
            const wilayahLabel = this.state.kecamatan ?
                this.state.kecamatan + ' | ' + (this.state.wilayah || '-') :
                (this.state.wilayah || this.resolveKarisidenanLabel() || 'Semua Pemda (Jatim)');
            const dashboardDetailRows = (((this.state.dashboard || {}).tables || {}).detail_akun || {}).rows ||
                [];
            const scopeTabs = (scope.mode === 'kabupaten' || scope.mode === 'karisidenan') &&
                dashboardDetailRows.length ?
                this.buildInfoTabsFromDashboard(dashboardDetailRows) :
                '';
            const scopeTabPanels = (scope.mode === 'kabupaten' || scope.mode === 'karisidenan') &&
                dashboardDetailRows.length ?
                this.buildInfoPanelsFromDashboard(dashboardDetailRows) :
                '';

            this.infoControlContent.innerHTML =
                '<div class="map-info-head">' +
                '<h4>' + this.escapeHtml(title) + '</h4>' +
                '<button id="toggle_info_panel" type="button" class="info-toggle-btn">' + toggleText +
                '</button>' +
                '</div>' +
                '<div class="map-info-body' + collapsedClass + '">' +
                '<p>' + this.escapeHtml(message) + '</p>' +
                this.buildMapStat('Anggaran', this.formatCurrency(summary.total_anggaran)) +
                this.buildMapStat('Realisasi', this.formatCurrency(summary.total_realisasi)) +
                this.buildMapStat('Capaian', this.formatPercent(summary.persentase)) +
                this.buildMapStat('Tahun', this.state.tahun || 'Semua Tahun') +
                this.buildMapStat('Jenis', this.state.jenis || 'Semua Jenis') +
                this.buildMapStat('Wilayah', wilayahLabel) +
                (scopeTabs ?
                    '<div class="map-info-section"><div class="map-info-section-title">Ringkasan Jenis PAD</div>' +
                    scopeTabs + scopeTabPanels + '</div>' : '') +
                '</div>' +
                '</div>';

            if (scopeTabs) {
                this.syncInfoTabPanels();
            }
        },

        renderInfo: function(props) {
            if (!this.infoControlContent) {
                return;
            }

            const collapsedClass = this.state.infoCollapsed ? ' is-collapsed' : '';
            const toggleText = this.state.infoCollapsed ? 'Show' : 'Hide';
            let detailHtml = '';
            const detailItems = props.detail_per_akun || [];
            const hasTabs = detailItems.length > 0;

            detailItems.forEach((item) => {
                detailHtml +=
                    '<div class="map-account-item map-tab-panel" data-tab-panel="' + this.escapeHtml(
                        this.buildInfoTabKey(item.akun)) + '">' +
                    '<div class="map-account-name">' + this.escapeHtml(this.shortAkunLabel(item.akun)) +
                    '</div>' +
                    this.buildMapStat('Anggaran', this.formatCurrency(item.anggaran)) +
                    this.buildMapStat('Realisasi', this.formatCurrency(item.realisasi)) +
                    this.buildMapStat('Capaian', this.formatPercent(item.persentase)) +
                    '</div>';
            });

            this.infoControlContent.innerHTML =
                '<div class="map-info-head">' +
                '<h4>' + this.escapeHtml(props.kecamatan || props.kabupaten || 'Wilayah') + '</h4>' +
                '<button id="toggle_info_panel" type="button" class="info-toggle-btn">' + toggleText +
                '</button>' +
                '</div>' +
                '<div class="map-info-body' + collapsedClass + '">' +
                '<p>Detail ini mengikuti wilayah yang sedang dipilih pada peta.</p>' +
                (hasTabs ? this.buildInfoTabs(detailItems) : '') +
                '<div class="' + (hasTabs ? 'map-tab-panel' : '') + '" data-tab-panel="pad">' +
                this.buildMapStat('Anggaran', this.formatCurrency(props.total_anggaran)) +
                this.buildMapStat('Realisasi', this.formatCurrency(props.total_realisasi)) +
                this.buildMapStat('Selisih', this.formatCurrency(parseFloat(props.total_realisasi || 0) -
                    parseFloat(props.total_anggaran || 0))) +
                this.buildMapStat('Capaian', this.formatPercent(props.persentase)) +
                (props.kecamatan ? this.buildMapStat('Kabupaten/Kota', props.kabupaten || '-') : '') +
                '</div>' +
                (hasTabs ?
                    '<div class="map-info-section"><div class="map-info-section-title">Rincian Jenis PAD</div>' +
                    detailHtml + '</div>' :
                    '') +
                '</div>';

            if (hasTabs) {
                this.syncInfoTabPanels();
            }
        },

        buildPopupHtml: function(props) {
            if (props.feature_type === 'karisidenan') {
                return '' +
                    '<div class="popup-title">' + this.escapeHtml(props.nama_karisidenan || props.karisidenan ||
                        'Karisidenan') + '</div>' +
                    '<div class="popup-row"><span>Anggaran</span><strong>' + this.formatCurrency(props
                        .total_anggaran) + '</strong></div>' +
                    '<div class="popup-row"><span>Realisasi</span><strong>' + this.formatCurrency(props
                        .total_realisasi) + '</strong></div>' +
                    '<div class="popup-row"><span>Capaian</span><strong>' + this.formatPercent(props
                        .persentase) + '</strong></div>' +
                    '<div class="popup-actions"><button type="button" class="popup-action-button secondary popup-action-karisidenan" data-karisidenan-id="' +
                    this.escapeHtml(props.id || '') + '">Lihat Wilayah</button></div>';
            }

            let actionHtml = '';

            if (!props.kecamatan) {
                actionHtml =
                    '<div class="popup-actions">' +
                    '<button type="button" class="popup-action-button popup-action-kecamatan" data-wilayah="' +
                    this.escapeHtml(props.kabupaten || '') + '" data-karisidenan-id="' + this.escapeHtml(props
                        .karisidenan_id || '') + '">Detail Kecamatan</button>' +
                    '<button type="button" class="popup-action-button secondary popup-action-karisidenan" data-karisidenan-id="' +
                    this.escapeHtml(props.karisidenan_id || '') + '">Karisidenan</button>' +
                    '</div>';
            }

            return '' +
                '<div class="popup-title">' + this.escapeHtml(props.kecamatan || props.kabupaten || 'Wilayah') +
                '</div>' +
                (!props.kecamatan ? '<div class="popup-row"><span>Karisidenan</span><strong>' + this.escapeHtml(
                    props.karisidenan || '-') + '</strong></div>' : '') +
                (props.kecamatan ? '<div class="popup-row"><span>Kabupaten/Kota</span><strong>' + this
                    .escapeHtml(props.kabupaten || '-') + '</strong></div>' : '') +
                '<div class="popup-row"><span>Anggaran</span><strong>' + this.formatCurrency(props
                    .total_anggaran) + '</strong></div>' +
                '<div class="popup-row"><span>Realisasi</span><strong>' + this.formatCurrency(props
                    .total_realisasi) + '</strong></div>' +
                '<div class="popup-row"><span>Capaian</span><strong>' + this.formatPercent(props.persentase) +
                '</strong></div>' +
                actionHtml;
        },

        syncSelectedLayer: function() {
            const app = this;

            if (!this.mapLayer) {
                return;
            }

            this.mapLayer.eachLayer(function(layer) {
                const props = layer.feature.properties;
                const isActive = app.isActiveFeature(props);
                layer.setStyle(app.getFeatureStyle(props, isActive));
            });
        },

        focusSelectedWilayah: function() {
            const app = this;

            if (!this.mapLayer) {
                return;
            }

            if (this.state.mapScope && this.state.mapScope.mode === 'kabupaten' && !this.state.kecamatan) {
                this.fitAllBounds();
                return;
            }

            if (!this.state.wilayah && !this.state.kecamatan) {
                this.fitAllBounds();
                return;
            }

            this.mapLayer.eachLayer(function(layer) {
                const props = layer.feature.properties;
                if (app.isActiveFeature(props)) {
                    app.map.fitBounds(layer.getBounds(), {
                        padding: [32, 32]
                    });
                }
            });
        },

        fitAllBounds: function(extraZoom) {
            const zoomBoost = parseInt(extraZoom || 0, 10);

            if (this.mapLayer && this.mapLayer.getLayers().length > 0) {
                const viewportWidth = window.innerWidth || 1365;
                const leftPanelPadding = viewportWidth >= 1200 ? 42 : 20;
                const rightPanelPadding = viewportWidth >= 1200 ? 190 : (viewportWidth >= 768 ? 120 : 20);
                const topPadding = viewportWidth >= 1200 ? 6 : 10;
                const bottomPadding = viewportWidth >= 1200 ? 30 : 20;

                this.map.invalidateSize(false);
                this.map.fitBounds(this.mapLayer.getBounds(), {
                    paddingTopLeft: [leftPanelPadding, topPadding],
                    paddingBottomRight: [rightPanelPadding, bottomPadding]
                });

                if (zoomBoost > 0) {
                    this.map.setZoom(Math.min(this.map.getZoom() + zoomBoost, 10));
                }
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

            result.persentase = result.total_anggaran > 0 ? (result.total_realisasi / result.total_anggaran) *
                100 : 0;

            return result;
        },

        findSelectedMapData: function() {
            if (this.state.kecamatan) {
                return (this.state.mapData || []).find((item) => item.kecamatan === this.state.kecamatan);
            }

            if (this.state.mapMode === 'karisidenan' && this.state.karisidenan && !this.state.wilayah) {
                return (this.state.mapData || []).find((item) => String(item.id || '') === String(this.state
                    .karisidenan));
            }

            if (this.state.wilayah && this.state.mapScope && this.state.mapScope.mode === 'province') {
                return (this.state.mapData || []).find((item) => item.kabupaten === this.state.wilayah);
            }

            return null;
        },

        isActiveFeature: function(props) {
            if (this.state.kecamatan) {
                return props.kecamatan === this.state.kecamatan;
            }

            if (props.feature_type === 'karisidenan') {
                return String(props.id || '') === String(this.state.karisidenan || '');
            }

            if (this.state.mapScope && this.state.mapScope.mode === 'province') {
                return props.kabupaten === this.state.wilayah;
            }

            return false;
        },

        handleFeatureClick: function(props, layer) {
            this.state.infoTab = 'pad';

            if (props.feature_type === 'karisidenan') {
                this.state.karisidenan = String(props.id || '');
                this.state.wilayah = '';
                this.state.kecamatan = '';
                this.state.filterDraft = null;
                this.syncFilterMeta();
                this.renderTopOverlay();
                this.refreshAll();
                return;
            }

            if (!props.kecamatan && layer && layer.openPopup) {
                layer.openPopup();
                return;
            }

            if (props.kecamatan) {
                this.state.kecamatan = props.kecamatan || '';
                this.syncFilterMeta();
                this.renderTopOverlay();
                this.refreshAll();
            }
        },

        findMapDataByWilayah: function(wilayah) {
            return (this.state.mapData || []).find(function(item) {
                return item.kabupaten === wilayah;
            });
        },

        getFeatureStyle: function(props, isActive) {
            if (props.feature_type === 'karisidenan') {
                return {
                    fillColor: props.region_color || '#2563eb',
                    weight: isActive ? 3.2 : 1.3,
                    opacity: 1,
                    color: isActive ? '#0f172a' : '#ffffff',
                    fillOpacity: isActive ? 0.88 : 0.7
                };
            }

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

        getRegionLabel: function(props) {
            return props.kecamatan || props.kabupaten || props.nama_karisidenan || props.karisidenan ||
                'Wilayah';
        },

        resolveKarisidenanLabel: function() {
            const current = String(this.state.karisidenan || '');
            const items = window.petaFilterOptions.karisidenanList || [];
            const match = items.find(function(item) {
                return String(item.id) === current;
            });

            return match ? match.nama_karisidenan : '';
        },

        buildInfoTabs: function(detailItems) {
            const tabs = [{
                key: 'pad',
                label: 'PAD'
            }];

            detailItems.forEach((item) => {
                tabs.push({
                    key: this.buildInfoTabKey(item.akun),
                    label: this.abbreviateAkunLabel(item.akun)
                });
            });

            if (!detailItems.length) {
                tabs.push({
                    key: 'filter',
                    label: 'Filter'
                });
            }

            if (!tabs.some((tab) => tab.key === this.state.infoTab)) {
                this.state.infoTab = 'pad';
            }

            return '<div class="map-info-tabs">' + tabs.map((tab) => {
                const activeClass = this.state.infoTab === tab.key ? ' is-active' : '';
                return '<button type="button" class="map-info-tab' + activeClass + '" data-tab="' + this
                    .escapeHtml(tab.key) + '">' + this.escapeHtml(tab.label) + '</button>';
            }).join('') + '</div>';
        },

        buildInfoTabKey: function(label) {
            return 'akun_' + String(label || '').toLowerCase().replace(/[^a-z0-9]+/g, '_');
        },

        buildInfoTabsFromDashboard: function(rows) {
            const tabs = [{
                key: 'pad',
                label: 'PAD'
            }];

            rows.forEach((row) => {
                tabs.push({
                    key: this.buildInfoTabKey(row.Kategori),
                    label: this.abbreviateAkunLabel(row.Kategori)
                });
            });

            if (!tabs.some((tab) => tab.key === this.state.infoTab)) {
                this.state.infoTab = 'pad';
            }

            return '<div class="map-info-tabs">' + tabs.map((tab) => {
                const activeClass = this.state.infoTab === tab.key ? ' is-active' : '';
                return '<button type="button" class="map-info-tab' + activeClass + '" data-tab="' + this
                    .escapeHtml(tab.key) + '">' + this.escapeHtml(tab.label) + '</button>';
            }).join('') + '</div>';
        },

        buildInfoPanelsFromDashboard: function(rows) {
            let html = '<div class="map-account-item map-tab-panel" data-tab-panel="pad">' +
                this.buildMapStat('Jenis Aktif', this.state.jenis || 'Semua Jenis') +
                this.buildMapStat('Jumlah Kategori', String(rows.length)) +
                '</div>';

            rows.forEach((row) => {
                html += '<div class="map-account-item map-tab-panel" data-tab-panel="' + this
                    .escapeHtml(this.buildInfoTabKey(row.Kategori)) + '">' +
                    this.buildMapStat('Anggaran', this.formatCurrency(row.Anggaran)) +
                    this.buildMapStat('Realisasi', this.formatCurrency(row.Realisasi)) +
                    this.buildMapStat('Selisih', this.formatCurrency(row.Selisih)) +
                    this.buildMapStat('Capaian', this.formatPercent(row['Persentase (%)'])) +
                    '</div>';
            });

            return html;
        },

        abbreviateAkunLabel: function(label) {
            const short = this.shortAkunLabel(label);

            if (/Pajak Daerah/i.test(short)) return 'Pjk';
            if (/Retribusi Daerah/i.test(short)) return 'Retr';
            if (/Hasil Pengelolaan Kekayaan Daerah yang Dipisahkan/i.test(short)) return 'HPKD';
            if (/Lain-Lain PAD yang Sah/i.test(short)) return 'PAD Lain';

            return short.length > 10 ? short.slice(0, 10) : short;
        },

        syncInfoTabPanels: function() {
            const activeKey = this.state.infoTab || 'pad';

            $('.map-info-tab').each(function() {
                const $tab = $(this);
                $tab.toggleClass('is-active', $tab.data('tab') === activeKey);
            });

            $('[data-tab-panel]').each(function() {
                const $panel = $(this);
                $panel.toggleClass('is-active', $panel.data('tab-panel') === activeKey);
            });
        },

        formatCurrency: function(value) {
            const number = parseFloat(value || 0);
            return 'Rp ' + number.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        },

        formatPercent: function(value) {
            const number = parseFloat(value || 0);
            return number.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + '%';
        },

        buildMapStat: function(label, value) {
            return '<div class="map-stat"><span>' + this.escapeHtml(label) + '</span><span>' + this.escapeHtml(
                value) + '</span></div>';
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



