<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PetaDashboardService
{
    protected $akunUtama = [
        'Pajak Daerah',
        'Retribusi Daerah',
        'Hasil Pengelolaan Kekayaan Daerah yang Dipisahkan',
        'Lain-Lain PAD yang Sah',
    ];

    public function getFilterOptions()
    {
        $cacheMinutes = (int) env('PETA_FILTER_CACHE_MINUTES', 60);

        return Cache::remember('peta.filter_options', now()->addMinutes(max($cacheMinutes, 1)), function () {
            $tahunList = DB::table('tabel_pad')
                ->select('tahun')
                ->whereNotNull('tahun')
                ->distinct()
                ->orderBy('tahun', 'desc')
                ->pluck('tahun');

            $karisidenanList = DB::table('master_karisidenan')
                ->select('id', 'nama_karisidenan')
                ->orderBy('nama_karisidenan')
                ->get();

            $wilayahList = DB::table('peta as p')
                ->leftJoin('master_karisidenan as mk', 'mk.id', '=', 'p.karisidenan_id')
                ->select(
                    'p.kabupaten',
                    'p.karisidenan_id',
                    DB::raw('COALESCE(mk.nama_karisidenan, \'-\') as nama_karisidenan')
                )
                ->whereNotNull('p.kabupaten')
                ->distinct()
                ->orderBy('nama_karisidenan')
                ->orderBy('p.kabupaten')
                ->get();

            return [
                'tahunList' => $tahunList,
                'defaultTahun' => $tahunList->first(),
                'jenisAkun' => $this->akunUtama,
                'karisidenanList' => $karisidenanList,
                'wilayahList' => $wilayahList,
            ];
        });
    }

    public function getFallbackFilterOptions()
    {
        return [
            'tahunList' => collect(),
            'defaultTahun' => null,
            'jenisAkun' => $this->akunUtama,
            'karisidenanList' => collect(),
            'wilayahList' => collect(),
        ];
    }

    public function normalizeFilters(Request $request)
    {
        $defaultTahun = $request->filled('tahun')
            ? null
            : $this->getFilterOptions()['defaultTahun'];

        return [
            'tahun' => $request->filled('tahun') ? (int) $request->input('tahun') : $defaultTahun,
            'jenis' => $request->filled('jenis') ? $request->input('jenis') : null,
            'karisidenan' => $request->filled('karisidenan') ? (int) $request->input('karisidenan') : null,
            'wilayah' => $request->filled('wilayah') ? $request->input('wilayah') : null,
            'kecamatan' => $request->filled('kecamatan') ? $request->input('kecamatan') : null,
            'map_mode' => $request->input('map_mode') === 'karisidenan' ? 'karisidenan' : 'kabupaten',
        ];
    }

    public function getMapPayload(array $filters)
    {
        if (($filters['map_mode'] ?? 'kabupaten') === 'karisidenan' && empty($filters['karisidenan']) && empty($filters['wilayah'])) {
            return $this->getKarisidenanOverviewPayload($filters);
        }

        if (!empty($filters['wilayah'])) {
            return $this->getKecamatanMapPayload($filters);
        }

        $aggregateSubquery = $this->aggregateByKotaSubquery($filters);
        $detailPerKota = $this->detailPerAkunByKota($filters);

        $rows = DB::table('peta as p')
            ->leftJoinSub($aggregateSubquery, 'agg', function ($join) {
                $join->on('p.ogc_fid', '=', 'agg.kota');
            })
            ->leftJoin('master_karisidenan as mk', 'mk.id', '=', 'p.karisidenan_id')
            ->select(
                'p.ogc_fid',
                'p.province',
                'p.kabupaten',
                'p.karisidenan_id',
                DB::raw('COALESCE(mk.nama_karisidenan, \'-\') as karisidenan'),
                DB::raw('ST_AsGeoJSON(p.wkb_geometry) as geojson'),
                DB::raw('COALESCE(agg.total_anggaran, 0) as total_anggaran'),
                DB::raw('COALESCE(agg.total_realisasi, 0) as total_realisasi'),
                DB::raw('COALESCE(agg.persentase, 0) as persentase')
            )
            ->when(!empty($filters['karisidenan']), function ($query) use ($filters) {
                $query->where('p.karisidenan_id', $filters['karisidenan']);
            })
            ->orderBy('p.kabupaten')
            ->get();

        $data = $rows->map(function ($row) use ($detailPerKota) {
            $row->detail_per_akun = $detailPerKota->get($row->ogc_fid, collect())->values();
            return $row;
        });

        return [
            'scope' => $this->buildScope($filters),
            'legend' => $this->getLegend(),
            'legend_meta' => [
                'title' => 'Legenda Capaian',
                'description' => 'Warna peta menunjukkan tingkat capaian PAD.',
            ],
            'summary' => $this->buildMapSummaryFromRows($data),
            'data' => $data,
        ];
    }

    protected function getKarisidenanOverviewPayload(array $filters)
    {
        $aggregateSubquery = $this->aggregateByKarisidenanSubquery($filters);
        $colorMap = $this->getKarisidenanColorMap();

        $rows = DB::table('master_karisidenan as mk')
            ->leftJoinSub($aggregateSubquery, 'agg', function ($join) {
                $join->on('mk.id', '=', 'agg.karisidenan_id');
            })
            ->select(
                'mk.id',
                'mk.nama_karisidenan',
                DB::raw('ST_AsGeoJSON(mk.wkb_geometry) as geojson'),
                DB::raw('COALESCE(agg.total_anggaran, 0) as total_anggaran'),
                DB::raw('COALESCE(agg.total_realisasi, 0) as total_realisasi'),
                DB::raw('COALESCE(agg.persentase, 0) as persentase')
            )
            ->orderBy('mk.nama_karisidenan')
            ->get()
            ->map(function ($row) use ($colorMap) {
                $row->feature_type = 'karisidenan';
                $row->karisidenan = $row->nama_karisidenan;
                $row->region_color = $colorMap[(int) $row->id] ?? '#2563eb';
                return $row;
            });

        $legend = $rows->map(function ($row) {
            return [
                'label' => $row->nama_karisidenan,
                'color' => $row->region_color,
            ];
        })->values()->all();

        return [
            'scope' => [
                'mode' => 'province',
                'label' => 'Semua Karisidenan Jawa Timur',
                'parent' => 'Jawa Timur',
                'description' => 'Peta menampilkan pembagian karisidenan. Klik karisidenan untuk melihat wilayah di dalamnya.',
            ],
            'legend' => $legend,
            'legend_meta' => [
                'title' => 'Legenda Karisidenan',
                'description' => 'Warna membedakan masing-masing karisidenan di Jawa Timur.',
            ],
            'summary' => $this->buildMapSummaryFromRows($rows),
            'data' => $rows,
        ];
    }

    public function getDashboardPayload(array $filters)
    {
        $baseQuery = $this->padQuery($filters, false);
        $allYearsQuery = $this->padQuery($filters, true);
        $scope = $this->buildScope($filters);

        $summary = $this->querySummary(clone $baseQuery);
        $perJenis = $this->queryPerJenis(clone $baseQuery);
        $trendTahunan = $this->queryTrendTahunan(clone $allYearsQuery);
        $rankingWilayah = $this->queryRankingWilayah($filters, $scope['mode']);
        $kontribusi = $this->queryKontribusi($filters, $scope['mode']);
        $pertumbuhan = $this->buildPertumbuhanSeries($trendTahunan);
        $isRegionalScope = in_array($scope['mode'], ['province', 'karisidenan'], true);

        return [
            'scope' => $scope,
            'summary' => array_merge($summary, [
                'comparison' => $this->buildSummaryComparison($filters, $summary),
            ]),
            'karisidenan_detail' => $scope['mode'] === 'karisidenan'
                ? $this->buildKarisidenanChartDetails($filters)
                : null,
            'charts' => [
                $this->makeChart(
                    'perbandingan_akun',
                    'Perbandingan Anggaran dan Realisasi',
                    $scope['mode'] === 'province'
                        ? 'Per jenis PAD untuk seluruh pemerintah daerah di Jawa Timur.'
                        : 'Per jenis PAD untuk wilayah yang sedang dipilih.',
                    'bar',
                    $perJenis->pluck('label')->all(),
                    [
                        $this->dataset('Anggaran', $perJenis->pluck('anggaran')->all(), 'currency', '#2563eb'),
                        $this->dataset('Realisasi', $perJenis->pluck('realisasi')->all(), 'currency', '#14b8a6'),
                    ],
                    [
                        'rows' => $perJenis->map(function ($row) {
                            return [
                                'Kategori' => $row['label'],
                                'Anggaran' => $row['anggaran'],
                                'Realisasi' => $row['realisasi'],
                                'Persentase (%)' => $row['persentase'],
                            ];
                        })->all(),
                    ]
                ),
                $this->makeChart(
                    'tren_tahunan',
                    'Tren Tahunan',
                    'Pergerakan anggaran dan realisasi lintas tahun untuk melihat konsistensi kinerja.',
                    'line',
                    $trendTahunan->pluck('tahun')->all(),
                    [
                        $this->dataset('Anggaran', $trendTahunan->pluck('anggaran')->all(), 'currency', '#f59e0b'),
                        $this->dataset('Realisasi', $trendTahunan->pluck('realisasi')->all(), 'currency', '#8b5cf6'),
                    ],
                    [
                        'rows' => $trendTahunan->map(function ($row) {
                            return [
                                'Tahun' => $row['tahun'],
                                'Anggaran' => $row['anggaran'],
                                'Realisasi' => $row['realisasi'],
                                'Persentase (%)' => $row['persentase'],
                            ];
                        })->all(),
                    ]
                ),
                $this->makeChart(
                    'peringkat',
                    $isRegionalScope ? 'Wilayah PAD per Penduduk Tertinggi' : 'Peringkat Kinerja Jenis PAD',
                    $isRegionalScope
                        ? '10 wilayah dengan PAD per penduduk tertinggi pada filter aktif.'
                        : 'Perbandingan persentase tiap jenis PAD di wilayah yang dipilih.',
                    'bar',
                    $rankingWilayah->pluck('label')->all(),
                    [
                        $this->dataset($isRegionalScope ? 'PAD per Penduduk' : 'Persentase Realisasi', $rankingWilayah->pluck('value')->all(), $isRegionalScope ? 'currency' : 'percent', '#ef4444'),
                    ],
                    [
                        'indexAxis' => 'y',
                        'rows' => $rankingWilayah->map(function ($row) {
                            return [
                                'Kategori' => $row['label'],
                                $row['raw_label'] ?? 'Nilai' => $row['value'],
                                'Penduduk' => $row['population'] ?? null,
                                'Realisasi' => $row['realisasi'] ?? null,
                            ];
                        })->all(),
                    ]
                ),
                $this->makeChart(
                    'komposisi',
                    'Komposisi Realisasi',
                    'Komposisi realisasi membantu melihat kontributor PAD terbesar secara cepat.',
                    'doughnut',
                    $perJenis->pluck('label')->all(),
                    [
                        $this->dataset('Realisasi', $perJenis->pluck('realisasi')->all(), 'currency', null),
                    ],
                    [
                        'rows' => $perJenis->map(function ($row) use ($summary) {
                            $kontribusi = $summary['total_realisasi'] > 0
                                ? ($row['realisasi'] / $summary['total_realisasi']) * 100
                                : 0;

                            return [
                                'Kategori' => $row['label'],
                                'Realisasi' => $row['realisasi'],
                                'Kontribusi (%)' => round($kontribusi, 2),
                            ];
                        })->all(),
                    ]
                ),
                $this->makeChart(
                    'kontribusi',
                    $isRegionalScope ? 'Wilayah Realisasi PAD Tertinggi' : 'Kontribusi Jenis PAD',
                    $isRegionalScope
                        ? '10 wilayah dengan realisasi PAD tertinggi pada filter aktif.'
                        : 'Nilai realisasi terbesar per jenis PAD di wilayah aktif.',
                    'bar',
                    $kontribusi->pluck('label')->all(),
                    [
                        $this->dataset('Realisasi', $kontribusi->pluck('value')->all(), 'currency', '#0f766e'),
                    ],
                    [
                        'indexAxis' => $isRegionalScope ? 'y' : 'x',
                        'rows' => $kontribusi->map(function ($row) {
                            return [
                                'Kategori' => $row['label'],
                                ($row['raw_label'] ?? 'Nilai') => $row['value'],
                                'Penduduk' => $row['population'] ?? null,
                                'Realisasi' => $row['realisasi'] ?? null,
                                'Anggaran' => $row['anggaran'] ?? null,
                                'Persentase (%)' => $row['persentase'] ?? null,
                            ];
                        })->all(),
                    ]
                ),
                $this->makeChart(
                    'pertumbuhan',
                    'Pertumbuhan YoY',
                    'Pertumbuhan tahun-ke-tahun memperlihatkan momentum akselerasi atau perlambatan PAD.',
                    'line',
                    $pertumbuhan->pluck('tahun')->all(),
                    [
                        $this->dataset('Pertumbuhan YoY', $pertumbuhan->pluck('growth')->all(), 'percent', '#06b6d4'),
                    ],
                    [
                        'rows' => $pertumbuhan->map(function ($row) {
                            return [
                                'Tahun' => $row['tahun'],
                                'Pertumbuhan YoY (%)' => $row['growth'],
                            ];
                        })->all(),
                    ]
                ),
            ],
            'tables' => [
                'detail_akun' => [
                    'title' => 'Detail Akun PAD',
                    'rows' => $perJenis->map(function ($row) {
                        return [
                            'Kategori' => $row['label'],
                            'Anggaran' => $row['anggaran'],
                            'Realisasi' => $row['realisasi'],
                            'Selisih' => $row['selisih'],
                            'Persentase (%)' => $row['persentase'],
                        ];
                    })->all(),
                ],
                'detail_wilayah' => [
                    'title' => $scope['mode'] === 'province'
                        ? 'Detail Wilayah Jawa Timur'
                        : ($scope['mode'] === 'karisidenan'
                            ? 'Detail Wilayah dalam Karisidenan'
                            : ($scope['mode'] === 'kecamatan' ? 'Detail Akun Kecamatan Aktif' : 'Detail Kecamatan pada Wilayah Aktif')),
                    'rows' => $scope['mode'] === 'province'
                        ? $this->queryDetailWilayah($filters)->all()
                        : ($scope['mode'] === 'karisidenan'
                            ? $this->queryDetailWilayah($filters)->all()
                            : ($scope['mode'] === 'kabupaten'
                                ? $this->queryDetailKecamatanDalamWilayah($filters)->all()
                                : $this->queryDetailWilayahTerpilih($filters)->all())),
                ],
            ],
        ];
    }

    public function exportSection(array $filters, $section)
    {
        $dashboard = $this->getDashboardPayload($filters);
        $export = $this->resolveExportData($dashboard, $section);
        $filename = $this->buildExportFilename($section, $filters);

        return response()->streamDownload(function () use ($dashboard, $export, $filters) {
            echo $this->renderExcelHtml($dashboard, $export, $filters);
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    protected function resolveExportData(array $dashboard, $section)
    {
        if ($section === 'ringkasan') {
            return [
                'title' => 'Ringkasan Dashboard PAD',
                'rows' => [
                    [
                        'Lingkup' => $dashboard['scope']['label'],
                        'Anggaran' => $dashboard['summary']['total_anggaran'],
                        'Realisasi' => $dashboard['summary']['total_realisasi'],
                        'Selisih' => $dashboard['summary']['selisih'],
                        'Persentase (%)' => $dashboard['summary']['persentase'],
                    ],
                ],
            ];
        }

        foreach ($dashboard['charts'] as $chart) {
            if ($chart['key'] === $section) {
                return [
                    'title' => $chart['title'],
                    'rows' => $chart['export']['rows'],
                ];
            }
        }

        foreach ($dashboard['tables'] as $key => $table) {
            if ($key === $section) {
                return $table;
            }
        }

        return [
            'title' => 'Ringkasan Dashboard PAD',
            'rows' => [
                [
                    'Lingkup' => $dashboard['scope']['label'],
                    'Anggaran' => $dashboard['summary']['total_anggaran'],
                    'Realisasi' => $dashboard['summary']['total_realisasi'],
                    'Selisih' => $dashboard['summary']['selisih'],
                    'Persentase (%)' => $dashboard['summary']['persentase'],
                ],
            ],
        ];
    }

    protected function renderExcelHtml(array $dashboard, array $export, array $filters)
    {
        $rows = $export['rows'];
        $headers = empty($rows) ? [] : array_keys($rows[0]);
        $filterWilayah = $filters['wilayah'] ?: 'Semua Pemda (Jawa Timur)';
        $filterJenis = $filters['jenis'] ?: 'Semua Jenis';
        $filterTahun = $filters['tahun'] ?: 'Semua Tahun';
        $filterKarisidenan = $filters['karisidenan'] ?: 'Semua Karisidenan';
        $filterKecamatan = $filters['kecamatan'] ?: 'Semua Kecamatan';
        $scopeLabel = $dashboard['scope']['label'] ?? 'Jawa Timur';
        $summary = $dashboard['summary'] ?? [];
        $generatedAt = now()->format('d-m-Y H:i:s');

        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<table border="1">';
        $html .= '<tr><th colspan="2">Dashboard PAD Jawa Timur</th></tr>';
        $html .= '<tr><td>Judul Data</td><td>' . e($export['title']) . '</td></tr>';
        $html .= '<tr><td>Lingkup Aktif</td><td>' . e($scopeLabel) . '</td></tr>';
        $html .= '<tr><td>Tahun</td><td>' . e((string) $filterTahun) . '</td></tr>';
        $html .= '<tr><td>Jenis Akun</td><td>' . e($filterJenis) . '</td></tr>';
        $html .= '<tr><td>Karisidenan</td><td>' . e($filterKarisidenan) . '</td></tr>';
        $html .= '<tr><td>Wilayah</td><td>' . e($filterWilayah) . '</td></tr>';
        $html .= '<tr><td>Kecamatan</td><td>' . e($filterKecamatan) . '</td></tr>';
        $html .= '<tr><td>Waktu Download</td><td>' . e($generatedAt) . '</td></tr>';
        $html .= '<tr><td>Total Baris</td><td>' . e((string) count($rows)) . '</td></tr>';
        $html .= '<tr><td colspan="2"></td></tr>';
        $html .= '<tr><th colspan="2">Ringkasan Angka</th></tr>';
        $html .= '<tr><td>Total Anggaran</td><td>' . e($this->formatExcelCellValue('anggaran', $summary['total_anggaran'] ?? 0)) . '</td></tr>';
        $html .= '<tr><td>Total Realisasi</td><td>' . e($this->formatExcelCellValue('realisasi', $summary['total_realisasi'] ?? 0)) . '</td></tr>';
        $html .= '<tr><td>Selisih</td><td>' . e($this->formatExcelCellValue('selisih', $summary['selisih'] ?? 0)) . '</td></tr>';
        $html .= '<tr><td>Persentase Capaian</td><td>' . e($this->formatExcelCellValue('persentase', $summary['persentase'] ?? 0)) . '</td></tr>';
        $html .= '<tr><td colspan="2"></td></tr>';
        $html .= '<tr><th colspan="' . max(1, count($headers) + 1) . '">' . e($export['title']) . '</th></tr>';

        if (!empty($headers)) {
            $html .= '<tr>';
            $html .= '<th>No</th>';
            foreach ($headers as $header) {
                $html .= '<th>' . e($header) . '</th>';
            }
            $html .= '</tr>';

            foreach ($rows as $index => $row) {
                $html .= '<tr>';
                $html .= '<td>' . e((string) ($index + 1)) . '</td>';
                foreach ($headers as $header) {
                    $html .= '<td>' . e($this->formatExcelCellValue($header, $row[$header] ?? null)) . '</td>';
                }
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td>Tidak ada data</td></tr>';
        }

        $html .= '</table></body></html>';

        return $html;
    }

    protected function buildExportFilename($section, array $filters)
    {
        $wilayah = $filters['kecamatan'] ?: ($filters['wilayah'] ?: 'jatim');
        $tahun = $filters['tahun'] ?: 'semua-tahun';

        $safeWilayah = preg_replace('/[^a-z0-9]+/i', '-', strtolower($wilayah));
        $safeSection = preg_replace('/[^a-z0-9]+/i', '-', strtolower($section));

        return 'dashboard-pad-' . $safeSection . '-' . $safeWilayah . '-' . $tahun . '.xls';
    }

    protected function formatExcelCellValue($header, $value)
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (preg_match('/persentase|kontribusi|growth/i', $header)) {
            return number_format((float) $value, 2, ',', '.') . '%';
        }

        if (preg_match('/penduduk/i', $header)) {
            return number_format((float) $value, 0, ',', '.');
        }

        if (preg_match('/pad per|anggaran|realisasi|selisih/i', $header)) {
            return 'Rp ' . number_format((float) $value, 0, ',', '.');
        }

        if (is_numeric($value)) {
            return number_format((float) $value, 0, ',', '.');
        }

        return (string) $value;
    }

    protected function buildScope(array $filters)
    {
        if (!empty($filters['kecamatan'])) {
            return [
                'mode' => 'kecamatan',
                'label' => $filters['kecamatan'],
                'parent' => $filters['wilayah'] ?: 'Jawa Timur',
                'description' => 'Dashboard menampilkan rincian kecamatan yang dipilih pada peta.',
            ];
        }

        if (!empty($filters['wilayah'])) {
            return [
                'mode' => 'kabupaten',
                'label' => $filters['wilayah'],
                'parent' => $this->resolveKarisidenanName($filters['karisidenan']),
                'description' => 'Dashboard menampilkan rincian kabupaten/kota yang dipilih pada peta.',
            ];
        }

        if (!empty($filters['karisidenan'])) {
            return [
                'mode' => 'karisidenan',
                'label' => $this->resolveKarisidenanName($filters['karisidenan']),
                'parent' => 'Jawa Timur',
                'description' => 'Dashboard menampilkan agregasi seluruh wilayah dalam karisidenan yang dipilih.',
            ];
        }

        return [
            'mode' => 'province',
            'label' => 'Semua Pemda (Jawa Timur)',
            'parent' => 'Jawa Timur',
            'description' => 'Dashboard menampilkan ringkasan seluruh kabupaten/kota di Jawa Timur.',
        ];
    }

    protected function getLegend()
    {
        return [
            ['label' => '>= 100%', 'color' => '#166534'],
            ['label' => '90 - 99%', 'color' => '#22c55e'],
            ['label' => '80 - 89%', 'color' => '#f59e0b'],
            ['label' => '60 - 79%', 'color' => '#f97316'],
            ['label' => '< 60%', 'color' => '#dc2626'],
        ];
    }

    protected function getKarisidenanColorMap()
    {
        $palette = ['#2563eb', '#14b8a6', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#0f766e', '#f97316'];

        return DB::table('master_karisidenan')
            ->select('id')
            ->orderBy('nama_karisidenan')
            ->get()
            ->values()
            ->mapWithKeys(function ($item, $index) use ($palette) {
                return [(int) $item->id => $palette[$index % count($palette)]];
            })
            ->all();
    }

    protected function padQuery(array $filters, $ignoreTahun)
    {
        if (!empty($filters['wilayah']) || !empty($filters['kecamatan'])) {
            $query = DB::table('tabel_pad_kecamatan as tp');
        } else {
            $query = DB::table('tabel_pad as tp')
                ->join('peta as p', 'p.ogc_fid', '=', 'tp.kota');
        }

        if (!$ignoreTahun && !empty($filters['tahun'])) {
            $query->where('tp.tahun', $filters['tahun']);
        }

        return $this->applyPadFilters($query, $filters);
    }

    protected function aggregateByKotaSubquery(array $filters)
    {
        return $this->padQuery($filters, false)
            ->select(
                'tp.kota',
                DB::raw('SUM(tp.anggaran) as total_anggaran'),
                DB::raw('SUM(tp.realisasi) as total_realisasi'),
                DB::raw('CASE WHEN SUM(tp.anggaran) > 0 THEN (SUM(tp.realisasi) / SUM(tp.anggaran)) * 100 ELSE 0 END as persentase')
            )
            ->groupBy('tp.kota');
    }

    protected function aggregateByKarisidenanSubquery(array $filters)
    {
        return $this->padQuery($filters, false)
            ->select(
                'p.karisidenan_id',
                DB::raw('SUM(tp.anggaran) as total_anggaran'),
                DB::raw('SUM(tp.realisasi) as total_realisasi'),
                DB::raw('CASE WHEN SUM(tp.anggaran) > 0 THEN (SUM(tp.realisasi) / SUM(tp.anggaran)) * 100 ELSE 0 END as persentase')
            )
            ->groupBy('p.karisidenan_id');
    }

    protected function detailPerAkunByKota(array $filters)
    {
        $akunExpression = $this->akunGroupingExpression('tp.akun');
        $akunOrderExpression = $this->akunOrderingExpression($akunExpression);

        return $this->padQuery($filters, false)
            ->select(
                'tp.kota',
                DB::raw($akunExpression . ' as akun'),
                DB::raw('SUM(tp.anggaran) as anggaran'),
                DB::raw('SUM(tp.realisasi) as realisasi'),
                DB::raw('CASE WHEN SUM(tp.anggaran) > 0 THEN (SUM(tp.realisasi) / SUM(tp.anggaran)) * 100 ELSE 0 END as persentase')
            )
            ->groupBy('tp.kota', DB::raw($akunExpression))
            ->orderByRaw($akunOrderExpression)
            ->get()
            ->groupBy('kota');
    }

    protected function querySummary(Builder $query)
    {
        $row = $query
            ->select(
                DB::raw('COALESCE(SUM(tp.anggaran), 0) as total_anggaran'),
                DB::raw('COALESCE(SUM(tp.realisasi), 0) as total_realisasi')
            )
            ->first();

        $anggaran = (float) ($row->total_anggaran ?: 0);
        $realisasi = (float) ($row->total_realisasi ?: 0);

        return [
            'total_anggaran' => round($anggaran, 2),
            'total_realisasi' => round($realisasi, 2),
            'selisih' => round($realisasi - $anggaran, 2),
            'persentase' => $anggaran > 0 ? round(($realisasi / $anggaran) * 100, 2) : 0,
        ];
    }

    protected function buildSummaryComparison(array $filters, array $currentSummary)
    {
        if (empty($filters['tahun'])) {
            return [
                'available' => false,
                'previous_year' => null,
                'previous_summary' => null,
                'differences' => null,
            ];
        }

        $previousYear = (int) $filters['tahun'] - 1;

        if ($previousYear <= 0) {
            return [
                'available' => false,
                'previous_year' => null,
                'previous_summary' => null,
                'differences' => null,
            ];
        }

        $previousFilters = $filters;
        $previousFilters['tahun'] = $previousYear;
        $previousSummary = $this->querySummary($this->padQuery($previousFilters, false));

        $hasPreviousData = ((float) $previousSummary['total_anggaran'] !== 0.0)
            || ((float) $previousSummary['total_realisasi'] !== 0.0)
            || ((float) $previousSummary['selisih'] !== 0.0)
            || ((float) $previousSummary['persentase'] !== 0.0);

        return [
            'available' => $hasPreviousData,
            'previous_year' => (string) $previousYear,
            'previous_summary' => $previousSummary,
            'differences' => [
                'total_anggaran' => round($currentSummary['total_anggaran'] - $previousSummary['total_anggaran'], 2),
                'total_realisasi' => round($currentSummary['total_realisasi'] - $previousSummary['total_realisasi'], 2),
                'selisih' => round($currentSummary['selisih'] - $previousSummary['selisih'], 2),
                'persentase' => round($currentSummary['persentase'] - $previousSummary['persentase'], 2),
            ],
        ];
    }

    protected function queryPerJenis(Builder $query)
    {
        $akunExpression = $this->akunGroupingExpression('tp.akun');
        $akunOrderExpression = $this->akunOrderingExpression($akunExpression);

        return $query
            ->select(
                DB::raw($akunExpression . ' as akun'),
                DB::raw('SUM(tp.anggaran) as total_anggaran'),
                DB::raw('SUM(tp.realisasi) as total_realisasi')
            )
            ->groupBy(DB::raw($akunExpression))
            ->orderByRaw($akunOrderExpression)
            ->get()
            ->map(function ($row) {
                $anggaran = (float) $row->total_anggaran;
                $realisasi = (float) $row->total_realisasi;

                return [
                    'label' => $this->shortAkunLabel($row->akun),
                    'akun' => $row->akun,
                    'anggaran' => round($anggaran, 2),
                    'realisasi' => round($realisasi, 2),
                    'selisih' => round($realisasi - $anggaran, 2),
                    'persentase' => $anggaran > 0 ? round(($realisasi / $anggaran) * 100, 2) : 0,
                ];
            })
            ->values();
    }

    protected function queryTrendTahunan(Builder $query)
    {
        return $query
            ->select(
                'tp.tahun',
                DB::raw('SUM(tp.anggaran) as total_anggaran'),
                DB::raw('SUM(tp.realisasi) as total_realisasi')
            )
            ->groupBy('tp.tahun')
            ->orderBy('tp.tahun')
            ->get()
            ->map(function ($row) {
                $anggaran = (float) $row->total_anggaran;
                $realisasi = (float) $row->total_realisasi;

                return [
                    'tahun' => (string) $row->tahun,
                    'anggaran' => round($anggaran, 2),
                    'realisasi' => round($realisasi, 2),
                    'persentase' => $anggaran > 0 ? round(($realisasi / $anggaran) * 100, 2) : 0,
                ];
            })
            ->values();
    }

    protected function queryRankingWilayah(array $filters, $mode)
    {
        if (in_array($mode, ['kabupaten', 'kecamatan'], true)) {
            return $this->queryPerJenis($this->padQuery($filters, false))
                ->map(function ($row) {
                    return [
                        'label' => $row['label'],
                        'value' => $row['persentase'],
                    ];
                })
                ->sortByDesc('value')
                ->values();
        }

        return $this->provincePadPopulationQuery($filters)
            ->select(
                'p.kabupaten as wilayah',
                DB::raw('COALESCE(pop.jumlah_penduduk, 0) as jumlah_penduduk'),
                DB::raw('SUM(tp.realisasi) as total_realisasi')
            )
            ->groupBy('p.kabupaten', 'pop.jumlah_penduduk')
            ->get()
            ->map(function ($row) {
                $realisasi = (float) $row->total_realisasi;
                $penduduk = (float) $row->jumlah_penduduk;
                $perPenduduk = $penduduk > 0 ? $realisasi / $penduduk : 0;

                return [
                    'label' => $row->wilayah,
                    'value' => round($perPenduduk, 2),
                    'raw_label' => 'PAD per Penduduk',
                    'population' => round($penduduk, 2),
                    'realisasi' => round($realisasi, 2),
                ];
            })
            ->sortByDesc('value')
            ->take(10)
            ->values();
    }

    protected function queryKontribusi(array $filters, $mode)
    {
        if (in_array($mode, ['kabupaten', 'kecamatan'], true)) {
            return $this->queryPerJenis($this->padQuery($filters, false))
                ->map(function ($row) {
                    return [
                        'label' => $row['label'],
                        'value' => $row['realisasi'],
                    ];
                })
                ->sortByDesc('value')
                ->values();
        }

        return $this->provincePadPopulationQuery($filters)
            ->select(
                'p.kabupaten as wilayah',
                DB::raw('COALESCE(pop.jumlah_penduduk, 0) as jumlah_penduduk'),
                DB::raw('SUM(tp.anggaran) as total_anggaran'),
                DB::raw('SUM(tp.realisasi) as total_realisasi')
            )
            ->groupBy('p.kabupaten', 'pop.jumlah_penduduk')
            ->get()
            ->map(function ($row) {
                $anggaran = (float) $row->total_anggaran;
                $realisasi = (float) $row->total_realisasi;
                $penduduk = (float) $row->jumlah_penduduk;

                return [
                    'label' => $row->wilayah,
                    'value' => round($realisasi, 2),
                    'raw_label' => 'Realisasi',
                    'population' => round($penduduk, 2),
                    'anggaran' => round($anggaran, 2),
                    'realisasi' => round($realisasi, 2),
                    'persentase' => $anggaran > 0 ? round(($realisasi / $anggaran) * 100, 2) : 0,
                ];
            })
            ->sortByDesc('value')
            ->take(10)
            ->values();
    }

    protected function queryDetailWilayah(array $filters)
    {
        return $this->provincePadPopulationQuery($filters)
            ->select(
                'p.kabupaten as wilayah',
                DB::raw('COALESCE(pop.jumlah_penduduk, 0) as jumlah_penduduk'),
                DB::raw('SUM(tp.anggaran) as total_anggaran'),
                DB::raw('SUM(tp.realisasi) as total_realisasi')
            )
            ->groupBy('p.kabupaten', 'pop.jumlah_penduduk')
            ->orderBy('p.kabupaten')
            ->get()
            ->map(function ($row) {
                $anggaran = (float) $row->total_anggaran;
                $realisasi = (float) $row->total_realisasi;
                $penduduk = (float) $row->jumlah_penduduk;

                return [
                    'Wilayah' => $row->wilayah,
                    'Penduduk' => round($penduduk, 2),
                    'Anggaran' => round($anggaran, 2),
                    'Realisasi' => round($realisasi, 2),
                    'PAD per Penduduk' => $penduduk > 0 ? round($realisasi / $penduduk, 2) : 0,
                    'PAD per 1.000 Penduduk' => $penduduk > 0 ? round(($realisasi / $penduduk) * 1000, 2) : 0,
                    'Selisih' => round($realisasi - $anggaran, 2),
                    'Persentase (%)' => $anggaran > 0 ? round(($realisasi / $anggaran) * 100, 2) : 0,
                ];
            })
            ->values();
    }

    protected function provincePadPopulationQuery(array $filters)
    {
        $populationSubquery = $this->populationSubquery($filters);

        return $this->padQuery($filters, false)
            ->leftJoinSub($populationSubquery, 'pop', function ($join) {
                $join->on(
                    DB::raw('TRIM(CAST(p.kode AS TEXT))'),
                    '=',
                    DB::raw('TRIM(CAST(pop.kode_wilayah AS TEXT))')
                );
            });
    }

    protected function populationSubquery(array $filters)
    {
        return DB::table('tb_penduduk as pend')
            ->select(
                DB::raw('TRIM(CAST(pend.kode AS TEXT)) as kode_wilayah'),
                DB::raw('SUM(pend.jumlah_penduduk) as jumlah_penduduk')
            )
            ->when(!empty($filters['tahun']), function ($query) use ($filters) {
                $query->where('pend.tahun', $filters['tahun']);
            })
            ->groupBy(DB::raw('TRIM(CAST(pend.kode AS TEXT))'));
    }

    protected function queryDetailWilayahTerpilih(array $filters)
    {
        return $this->queryPerJenis($this->padQuery($filters, false))
            ->map(function ($row) {
                return [
                    'Kategori' => $row['label'],
                    'Anggaran' => $row['anggaran'],
                    'Realisasi' => $row['realisasi'],
                    'Selisih' => $row['selisih'],
                    'Persentase (%)' => $row['persentase'],
                ];
            })
            ->values();
    }

    protected function queryDetailKecamatanDalamWilayah(array $filters)
    {
        return DB::table('tabel_pad_kecamatan as tp')
            ->when(!empty($filters['tahun']), function ($query) use ($filters) {
                $query->where('tp.tahun', $filters['tahun']);
            })
            ->where('tp.wadmkk', $filters['wilayah'])
            ->when(!empty($filters['jenis']), function ($query) use ($filters) {
                $query->where('tp.akun', 'ILIKE', '%' . $filters['jenis'] . '%');
            }, function ($query) {
                $query->where(function ($innerQuery) {
                    foreach ($this->akunUtama as $index => $akun) {
                        if ($index === 0) {
                            $innerQuery->where('tp.akun', 'ILIKE', '%' . $akun . '%');
                        } else {
                            $innerQuery->orWhere('tp.akun', 'ILIKE', '%' . $akun . '%');
                        }
                    }
                });
            })
            ->select(
                'tp.wadmkc as kecamatan',
                DB::raw('SUM(tp.anggaran) as total_anggaran'),
                DB::raw('SUM(tp.realisasi) as total_realisasi')
            )
            ->groupBy('tp.wadmkc')
            ->orderBy('tp.wadmkc')
            ->get()
            ->map(function ($row) {
                $anggaran = (float) $row->total_anggaran;
                $realisasi = (float) $row->total_realisasi;

                return [
                    'Kecamatan' => $row->kecamatan,
                    'Anggaran' => round($anggaran, 2),
                    'Realisasi' => round($realisasi, 2),
                    'Selisih' => round($realisasi - $anggaran, 2),
                    'Persentase (%)' => $anggaran > 0 ? round(($realisasi / $anggaran) * 100, 2) : 0,
                ];
            })
            ->values();
    }

    protected function buildPertumbuhanSeries(Collection $trendTahunan)
    {
        $previous = null;

        return $trendTahunan->map(function ($row) use (&$previous) {
            $growth = 0;

            if ($previous !== null && $previous > 0) {
                $growth = round((($row['realisasi'] - $previous) / $previous) * 100, 2);
            }

            $previous = $row['realisasi'];

            return [
                'tahun' => $row['tahun'],
                'growth' => $growth,
            ];
        })->values();
    }

    protected function applyPadFilters(Builder $query, array $filters)
    {
        // dd($filters);
        if (!empty($filters['jenis'])) {
            $query->where('tp.akun', 'ILIKE', '%' . $filters['jenis'] . '%');
        } else {
            $query->where(function ($innerQuery) {
                foreach ($this->akunUtama as $index => $akun) {
                    if ($index === 0) {
                        $innerQuery->where('tp.akun', 'ILIKE', '%' . $akun . '%');
                    } else {
                        $innerQuery->orWhere('tp.akun', 'ILIKE', '%' . $akun . '%');
                    }
                }
            });
        }

        if (!empty($filters['kecamatan'])) {
            $query->where('tp.wadmkc', $filters['kecamatan']);
        }

        if (!empty($filters['wilayah'])) {
            $query->where('tp.wadmkk', $filters['wilayah']);
        }

        if (!empty($filters['karisidenan']) && strpos($query->toSql(), '"peta"') !== false) {
            $query->where('p.karisidenan_id', $filters['karisidenan']);
        }

        return $query;
    }

    protected function getKecamatanMapPayload(array $filters)
    {
        $aggregateSubquery = $this->aggregateByKecamatanSubquery($filters);
        $detailPerKecamatan = $this->detailPerAkunByKecamatan($filters);

        $rows = DB::table('data_kec as dk')
            ->leftJoinSub($aggregateSubquery, 'agg', function ($join) {
                $join->on('dk.id', '=', 'agg.kec_id');
            })
            ->select(
                'dk.id',
                'dk.wadmkc as kecamatan',
                'dk.wadmkk as kabupaten',
                DB::raw('ST_AsGeoJSON(ST_Force2D(dk.wkb_geometry)) as geojson'),
                DB::raw('COALESCE(agg.total_anggaran, 0) as total_anggaran'),
                DB::raw('COALESCE(agg.total_realisasi, 0) as total_realisasi'),
                DB::raw('COALESCE(agg.persentase, 0) as persentase')
            )
            ->where('dk.wadmkk', $filters['wilayah'])
            ->when(!empty($filters['kecamatan']), function ($query) use ($filters) {
                $query->where('dk.wadmkc', $filters['kecamatan']);
            })
            ->orderBy('dk.wadmkc')
            ->get();

        $data = $rows->map(function ($row) use ($detailPerKecamatan) {
            $row->detail_per_akun = $detailPerKecamatan->get($row->id, collect())->values();
            return $row;
        });

        return [
            'scope' => $this->buildScope($filters),
            'legend' => $this->getLegend(),
            'summary' => $this->buildMapSummaryFromRows($data),
            'data' => $data,
        ];
    }

    protected function aggregateByKecamatanSubquery(array $filters)
    {
        return DB::table('tabel_pad_kecamatan as tp')
            ->join('data_kec as dk', function ($join) {
                $join->on('dk.kdcpum', '=', 'tp.kdcpum');
            })
            ->when(!empty($filters['tahun']), function ($query) use ($filters) {
                $query->where('tp.tahun', $filters['tahun']);
            })
            ->where('dk.wadmkk', $filters['wilayah'])
            ->when(!empty($filters['jenis']), function ($query) use ($filters) {
                $query->where('tp.akun', 'ILIKE', '%' . $filters['jenis'] . '%');
            }, function ($query) {
                $query->where(function ($innerQuery) {
                    foreach ($this->akunUtama as $index => $akun) {
                        if ($index === 0) {
                            $innerQuery->where('tp.akun', 'ILIKE', '%' . $akun . '%');
                        } else {
                            $innerQuery->orWhere('tp.akun', 'ILIKE', '%' . $akun . '%');
                        }
                    }
                });
            })
            ->when(!empty($filters['kecamatan']), function ($query) use ($filters) {
                $query->where('dk.wadmkc', $filters['kecamatan']);
            })
            ->select(
                'dk.id as kec_id',
                DB::raw('SUM(tp.anggaran) as total_anggaran'),
                DB::raw('SUM(tp.realisasi) as total_realisasi'),
                DB::raw('CASE WHEN SUM(tp.anggaran) > 0 THEN (SUM(tp.realisasi) / SUM(tp.anggaran)) * 100 ELSE 0 END as persentase')
            )
            ->groupBy('dk.id');
    }

    protected function detailPerAkunByKecamatan(array $filters)
    {
        $akunExpression = $this->akunGroupingExpression('tp.akun');
        $akunOrderExpression = $this->akunOrderingExpression($akunExpression);

        return DB::table('tabel_pad_kecamatan as tp')
            ->join('data_kec as dk', function ($join) {
                $join->on('dk.kdcpum', '=', 'tp.kdcpum');
            })
            ->when(!empty($filters['tahun']), function ($query) use ($filters) {
                $query->where('tp.tahun', $filters['tahun']);
            })
            ->where('dk.wadmkk', $filters['wilayah'])
            ->when(!empty($filters['jenis']), function ($query) use ($filters) {
                $query->where('tp.akun', 'ILIKE', '%' . $filters['jenis'] . '%');
            }, function ($query) {
                $query->where(function ($innerQuery) {
                    foreach ($this->akunUtama as $index => $akun) {
                        if ($index === 0) {
                            $innerQuery->where('tp.akun', 'ILIKE', '%' . $akun . '%');
                        } else {
                            $innerQuery->orWhere('tp.akun', 'ILIKE', '%' . $akun . '%');
                        }
                    }
                });
            })
            ->when(!empty($filters['kecamatan']), function ($query) use ($filters) {
                $query->where('dk.wadmkc', $filters['kecamatan']);
            })
            ->select(
                'dk.id as kec_id',
                DB::raw($akunExpression . ' as akun'),
                DB::raw('SUM(tp.anggaran) as anggaran'),
                DB::raw('SUM(tp.realisasi) as realisasi'),
                DB::raw('CASE WHEN SUM(tp.anggaran) > 0 THEN (SUM(tp.realisasi) / SUM(tp.anggaran)) * 100 ELSE 0 END as persentase')
            )
            ->groupBy('dk.id', DB::raw($akunExpression))
            ->orderByRaw($akunOrderExpression)
            ->get()
            ->groupBy('kec_id');
    }

    protected function buildMapSummaryFromRows(Collection $rows)
    {
        $totalAnggaran = (float) $rows->sum(function ($row) {
            return (float) ($row->total_anggaran ?: 0);
        });

        $totalRealisasi = (float) $rows->sum(function ($row) {
            return (float) ($row->total_realisasi ?: 0);
        });

        return [
            'total_anggaran' => round($totalAnggaran, 2),
            'total_realisasi' => round($totalRealisasi, 2),
            'selisih' => round($totalRealisasi - $totalAnggaran, 2),
            'persentase' => $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 2) : 0,
        ];
    }

    protected function queryTrendTahunanPerWilayahKarisidenan(array $filters)
    {
        return $this->padQuery($filters, true)
            ->select(
                'p.kabupaten as wilayah',
                'tp.tahun',
                DB::raw('SUM(tp.anggaran) as total_anggaran'),
                DB::raw('SUM(tp.realisasi) as total_realisasi')
            )
            ->groupBy('p.kabupaten', 'tp.tahun')
            ->orderBy('p.kabupaten')
            ->orderBy('tp.tahun')
            ->get()
            ->groupBy('wilayah')
            ->map(function ($rows, $wilayah) {
                return [
                    'label' => $wilayah,
                    'labels' => $rows->pluck('tahun')->map(function ($tahun) {
                        return (string) $tahun;
                    })->values()->all(),
                    'datasets' => [
                        $this->dataset('Anggaran', $rows->pluck('total_anggaran')->all(), 'currency', '#f59e0b'),
                        $this->dataset('Realisasi', $rows->pluck('total_realisasi')->all(), 'currency', '#8b5cf6'),
                    ],
                ];
            })
            ->values();
    }

    protected function buildKarisidenanChartDetails(array $filters)
    {
        $wilayahList = DB::table('peta')
            ->where('karisidenan_id', $filters['karisidenan'])
            ->whereNotNull('kabupaten')
            ->orderBy('kabupaten')
            ->pluck('kabupaten');

        $details = [
            'perbandingan_akun' => $this->karisidenanDetailBucket(
                'Perbandingan Anggaran dan Realisasi per Wilayah',
                'Setiap kartu menampilkan perbandingan anggaran dan realisasi untuk jenis PAD di wilayah dalam karisidenan aktif.'
            ),
            'tren_tahunan' => $this->karisidenanDetailBucket(
                'Tren Tahunan per Wilayah',
                'Setiap kartu menampilkan tren anggaran dan realisasi lintas tahun untuk satu wilayah.'
            ),
            'peringkat' => $this->karisidenanDetailBucket(
                'Peringkat Kinerja Jenis PAD per Wilayah',
                'Setiap kartu menampilkan persentase realisasi tiap jenis PAD pada wilayah dalam karisidenan aktif.'
            ),
            'komposisi' => $this->karisidenanDetailBucket(
                'Komposisi Realisasi per Wilayah',
                'Setiap kartu menampilkan komposisi realisasi PAD berdasarkan jenis akun pada satu wilayah.'
            ),
            'kontribusi' => $this->karisidenanDetailBucket(
                'Kontribusi Jenis PAD per Wilayah',
                'Setiap kartu menampilkan nilai realisasi tiap jenis PAD pada wilayah dalam karisidenan aktif.'
            ),
            'pertumbuhan' => $this->karisidenanDetailBucket(
                'Pertumbuhan YoY per Wilayah',
                'Setiap kartu menampilkan pertumbuhan tahun-ke-tahun untuk wilayah dalam karisidenan aktif.'
            ),
        ];

        foreach ($wilayahList as $wilayah) {
            $wilayahFilters = $filters;
            $wilayahFilters['wilayah'] = $wilayah;
            $wilayahFilters['kecamatan'] = null;

            $baseQuery = $this->padQuery($wilayahFilters, false);
            $allYearsQuery = $this->padQuery($wilayahFilters, true);
            $perJenis = $this->queryPerJenis(clone $baseQuery);
            $trendTahunan = $this->queryTrendTahunan(clone $allYearsQuery);
            $ranking = $this->queryRankingWilayah($wilayahFilters, 'kabupaten');
            $kontribusi = $this->queryKontribusi($wilayahFilters, 'kabupaten');
            $pertumbuhan = $this->buildPertumbuhanSeries($trendTahunan);

            $details['perbandingan_akun']['items'][] = [
                'label' => $wilayah,
                'type' => 'bar',
                'labels' => $perJenis->pluck('label')->all(),
                'datasets' => [
                    $this->dataset('Anggaran', $perJenis->pluck('anggaran')->all(), 'currency', '#2563eb'),
                    $this->dataset('Realisasi', $perJenis->pluck('realisasi')->all(), 'currency', '#14b8a6'),
                ],
            ];

            $details['tren_tahunan']['items'][] = [
                'label' => $wilayah,
                'type' => 'line',
                'labels' => $trendTahunan->pluck('tahun')->all(),
                'datasets' => [
                    $this->dataset('Anggaran', $trendTahunan->pluck('anggaran')->all(), 'currency', '#f59e0b'),
                    $this->dataset('Realisasi', $trendTahunan->pluck('realisasi')->all(), 'currency', '#8b5cf6'),
                ],
            ];

            $details['peringkat']['items'][] = [
                'label' => $wilayah,
                'type' => 'bar',
                'labels' => $ranking->pluck('label')->all(),
                'datasets' => [
                    $this->dataset('Persentase Realisasi', $ranking->pluck('value')->all(), 'percent', '#ef4444'),
                ],
                'options' => [
                    'indexAxis' => 'y',
                ],
            ];

            $details['komposisi']['items'][] = [
                'label' => $wilayah,
                'type' => 'doughnut',
                'labels' => $perJenis->pluck('label')->all(),
                'datasets' => [
                    $this->dataset('Realisasi', $perJenis->pluck('realisasi')->all(), 'currency', null),
                ],
            ];

            $details['kontribusi']['items'][] = [
                'label' => $wilayah,
                'type' => 'bar',
                'labels' => $kontribusi->pluck('label')->all(),
                'datasets' => [
                    $this->dataset('Realisasi', $kontribusi->pluck('value')->all(), 'currency', '#0f766e'),
                ],
            ];

            $details['pertumbuhan']['items'][] = [
                'label' => $wilayah,
                'type' => 'line',
                'labels' => $pertumbuhan->pluck('tahun')->all(),
                'datasets' => [
                    $this->dataset('Pertumbuhan YoY', $pertumbuhan->pluck('growth')->all(), 'percent', '#06b6d4'),
                ],
            ];
        }

        return $details;
    }

    protected function shortAkunLabel($akun)
    {
        return str_replace('Pendapatan Asli Daerah - ', '', $akun);
    }

    protected function karisidenanDetailBucket($title, $description)
    {
        return [
            'title' => $title,
            'description' => $description,
            'items' => [],
        ];
    }

    protected function akunGroupingExpression($column)
    {
        return "CASE
            WHEN {$column} ILIKE '%Pajak Daerah%' THEN 'Pajak Daerah'
            WHEN {$column} ILIKE '%Retribusi Daerah%' THEN 'Retribusi Daerah'
            WHEN {$column} ILIKE '%Hasil Pengelolaan Kekayaan Daerah yang Dipisahkan%' THEN 'Hasil Pengelolaan Kekayaan Daerah yang Dipisahkan'
            WHEN {$column} ILIKE '%Lain-Lain PAD yang Sah%' THEN 'Lain-Lain PAD yang Sah'
            ELSE TRIM(REPLACE({$column}, 'Pendapatan Asli Daerah - ', ''))
        END";
    }

    protected function akunOrderingExpression($akunExpression)
    {
        return "CASE
            WHEN {$akunExpression} = 'Pajak Daerah' THEN 1
            WHEN {$akunExpression} = 'Retribusi Daerah' THEN 2
            WHEN {$akunExpression} = 'Hasil Pengelolaan Kekayaan Daerah yang Dipisahkan' THEN 3
            WHEN {$akunExpression} = 'Lain-Lain PAD yang Sah' THEN 4
            ELSE 99
        END";
    }

    protected function makeChart($key, $title, $description, $type, array $labels, array $datasets, array $meta)
    {
        return [
            'key' => $key,
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'labels' => $labels,
            'datasets' => $datasets,
            'options' => [
                'indexAxis' => isset($meta['indexAxis']) ? $meta['indexAxis'] : 'x',
            ],
            'export' => [
                'rows' => isset($meta['rows']) ? $meta['rows'] : [],
            ],
        ];
    }

    protected function dataset($label, array $data, $format, $color)
    {
        return [
            'label' => $label,
            'data' => array_map(function ($value) {
                return round((float) $value, 2);
            }, $data),
            'format' => $format,
            'color' => $color,
        ];
    }

    protected function resolveKarisidenanName($karisidenanId)
    {
        if (empty($karisidenanId)) {
            return 'Jawa Timur';
        }

        return DB::table('master_karisidenan')
            ->where('id', $karisidenanId)
            ->value('nama_karisidenan') ?: 'Karisidenan';
    }
}


