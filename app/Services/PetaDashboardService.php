<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        $tahunList = DB::table('tabel_pad')
            ->select('tahun')
            ->whereNotNull('tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $wilayahList = DB::table('peta')
            ->select('kabupaten')
            ->whereNotNull('kabupaten')
            ->distinct()
            ->orderBy('kabupaten')
            ->pluck('kabupaten');

        return [
            'tahunList' => $tahunList,
            'defaultTahun' => $tahunList->first(),
            'jenisAkun' => $this->akunUtama,
            'wilayahList' => $wilayahList,
        ];
    }

    public function normalizeFilters(Request $request)
    {
        $filters = $this->getFilterOptions();
        $defaultTahun = $filters['defaultTahun'];

        return [
            'tahun' => $request->filled('tahun') ? (int) $request->input('tahun') : $defaultTahun,
            'jenis' => $request->filled('jenis') ? $request->input('jenis') : null,
            'wilayah' => $request->filled('wilayah') ? $request->input('wilayah') : null,
        ];
    }

    public function getMapPayload(array $filters)
    {
        $aggregateSubquery = $this->aggregateByKotaSubquery($filters);

        $detailPerKota = $this->detailPerAkunByKota($filters);

        $rows = DB::table('peta as p')
            ->leftJoinSub($aggregateSubquery, 'agg', function ($join) {
                $join->on('p.ogc_fid', '=', 'agg.kota');
            })
            ->select(
                'p.ogc_fid',
                'p.province',
                'p.kabupaten',
                DB::raw('ST_AsGeoJSON(p.wkb_geometry) as geojson'),
                DB::raw('COALESCE(agg.total_anggaran, 0) as total_anggaran'),
                DB::raw('COALESCE(agg.total_realisasi, 0) as total_realisasi'),
                DB::raw('COALESCE(agg.persentase, 0) as persentase')
            )
            ->orderBy('p.kabupaten')
            ->get();

        $data = $rows->map(function ($row) use ($detailPerKota) {
            $row->detail_per_akun = $detailPerKota->get($row->ogc_fid, collect())->values();
            return $row;
        });

        return [
            'scope' => $this->buildScope($filters),
            'legend' => $this->getLegend(),
            'data' => $data,
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

        return [
            'scope' => $scope,
            'summary' => $summary,
            'charts' => [
                $this->makeChart(
                    'perbandingan_akun',
                    'Perbandingan Anggaran dan Realisasi',
                    $scope['mode'] === 'province'
                        ? 'Per jenis PAD untuk seluruh pemerintah daerah di Jawa Timur.'
                        : 'Per jenis PAD untuk wilayah yang sedang dipilih.',
                    'doughnut',
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
                    $scope['mode'] === 'province' ? 'Peringkat Kinerja Wilayah' : 'Peringkat Kinerja Jenis PAD',
                    $scope['mode'] === 'province'
                        ? 'Daftar wilayah dengan persentase realisasi tertinggi pada filter aktif.'
                        : 'Perbandingan persentase tiap jenis PAD di wilayah yang dipilih.',
                    'bar',
                    $rankingWilayah->pluck('label')->all(),
                    [
                        $this->dataset('Persentase Realisasi', $rankingWilayah->pluck('value')->all(), 'percent', '#ef4444'),
                    ],
                    [
                        'indexAxis' => 'y',
                        'rows' => $rankingWilayah->map(function ($row) {
                            return [
                                'Kategori' => $row['label'],
                                'Persentase (%)' => $row['value'],
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
                    $scope['mode'] === 'province' ? 'Kontribusi Wilayah' : 'Kontribusi Jenis PAD',
                    $scope['mode'] === 'province'
                        ? 'Nilai realisasi terbesar per kabupaten/kota.'
                        : 'Nilai realisasi terbesar per jenis PAD di wilayah aktif.',
                    'bar',
                    $kontribusi->pluck('label')->all(),
                    [
                        $this->dataset('Realisasi', $kontribusi->pluck('value')->all(), 'currency', '#0f766e'),
                    ],
                    [
                        'rows' => $kontribusi->map(function ($row) {
                            return [
                                'Kategori' => $row['label'],
                                'Realisasi' => $row['value'],
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
                    'title' => $scope['mode'] === 'province' ? 'Detail Wilayah Jawa Timur' : 'Detail Wilayah Aktif',
                    'rows' => $scope['mode'] === 'province'
                        ? $this->queryDetailWilayah($filters)->all()
                        : $this->queryDetailWilayahTerpilih($filters)->all(),
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

        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<table border="1">';
        $html .= '<tr><th colspan="2">Dashboard PAD Jawa Timur</th></tr>';
        $html .= '<tr><td>Lingkup</td><td>' . e($dashboard['scope']['label']) . '</td></tr>';
        $html .= '<tr><td>Tahun</td><td>' . e((string) $filterTahun) . '</td></tr>';
        $html .= '<tr><td>Jenis Akun</td><td>' . e($filterJenis) . '</td></tr>';
        $html .= '<tr><td>Wilayah</td><td>' . e($filterWilayah) . '</td></tr>';
        $html .= '<tr><td colspan="2"></td></tr>';
        $html .= '<tr><th colspan="' . max(1, count($headers)) . '">' . e($export['title']) . '</th></tr>';

        if (!empty($headers)) {
            $html .= '<tr>';
            foreach ($headers as $header) {
                $html .= '<th>' . e($header) . '</th>';
            }
            $html .= '</tr>';

            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach ($headers as $header) {
                    $html .= '<td>' . e((string) $row[$header]) . '</td>';
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
        $wilayah = $filters['wilayah'] ?: 'jatim';
        $tahun = $filters['tahun'] ?: 'semua-tahun';

        $safeWilayah = preg_replace('/[^a-z0-9]+/i', '-', strtolower($wilayah));
        $safeSection = preg_replace('/[^a-z0-9]+/i', '-', strtolower($section));

        return 'dashboard-pad-' . $safeSection . '-' . $safeWilayah . '-' . $tahun . '.xls';
    }

    protected function buildScope(array $filters)
    {
        if (!empty($filters['wilayah'])) {
            return [
                'mode' => 'kabupaten',
                'label' => $filters['wilayah'],
                'parent' => 'Jawa Timur',
                'description' => 'Dashboard menampilkan rincian wilayah yang dipilih pada peta.',
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

    protected function padQuery(array $filters, $ignoreTahun)
    {
        $query = DB::table('tabel_pad as tp')
            ->join('peta as p', 'p.ogc_fid', '=', 'tp.kota');

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

    protected function detailPerAkunByKota(array $filters)
    {
        return $this->padQuery($filters, false)
            ->select(
                'tp.kota',
                'tp.akun',
                DB::raw('SUM(tp.anggaran) as anggaran'),
                DB::raw('SUM(tp.realisasi) as realisasi'),
                DB::raw('CASE WHEN SUM(tp.anggaran) > 0 THEN (SUM(tp.realisasi) / SUM(tp.anggaran)) * 100 ELSE 0 END as persentase')
            )
            ->groupBy('tp.kota', 'tp.akun')
            ->orderBy('tp.akun')
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

    protected function queryPerJenis(Builder $query)
    {
        return $query
            ->select(
                'tp.akun',
                DB::raw('SUM(tp.anggaran) as total_anggaran'),
                DB::raw('SUM(tp.realisasi) as total_realisasi')
            )
            ->groupBy('tp.akun')
            ->orderBy('tp.akun')
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
        if ($mode === 'kabupaten') {
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

        return $this->padQuery($filters, false)
            ->select(
                'p.kabupaten',
                DB::raw('SUM(tp.anggaran) as total_anggaran'),
                DB::raw('SUM(tp.realisasi) as total_realisasi')
            )
            ->groupBy('p.kabupaten')
            ->get()
            ->map(function ($row) {
                $anggaran = (float) $row->total_anggaran;
                $realisasi = (float) $row->total_realisasi;

                return [
                    'label' => $row->kabupaten,
                    'value' => $anggaran > 0 ? round(($realisasi / $anggaran) * 100, 2) : 0,
                ];
            })
            ->sortByDesc('value')
            ->take(10)
            ->values();
    }

    protected function queryKontribusi(array $filters, $mode)
    {
        if ($mode === 'kabupaten') {
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

        return $this->padQuery($filters, false)
            ->select(
                'p.kabupaten',
                DB::raw('SUM(tp.realisasi) as total_realisasi')
            )
            ->groupBy('p.kabupaten')
            ->orderByDesc(DB::raw('SUM(tp.realisasi)'))
            ->limit(10)
            ->get()
            ->map(function ($row) {
                return [
                    'label' => $row->kabupaten,
                    'value' => round((float) $row->total_realisasi, 2),
                ];
            })
            ->values();
    }

    protected function queryDetailWilayah(array $filters)
    {
        return $this->padQuery($filters, false)
            ->select(
                'p.kabupaten',
                DB::raw('SUM(tp.anggaran) as total_anggaran'),
                DB::raw('SUM(tp.realisasi) as total_realisasi')
            )
            ->groupBy('p.kabupaten')
            ->orderBy('p.kabupaten')
            ->get()
            ->map(function ($row) {
                $anggaran = (float) $row->total_anggaran;
                $realisasi = (float) $row->total_realisasi;

                return [
                    'Wilayah' => $row->kabupaten,
                    'Anggaran' => round($anggaran, 2),
                    'Realisasi' => round($realisasi, 2),
                    'Selisih' => round($realisasi - $anggaran, 2),
                    'Persentase (%)' => $anggaran > 0 ? round(($realisasi / $anggaran) * 100, 2) : 0,
                ];
            })
            ->values();
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

        if (!empty($filters['wilayah'])) {
            $query->where('p.kabupaten', $filters['wilayah']);
        }

        return $query;
    }

    protected function shortAkunLabel($akun)
    {
        return str_replace('Pendapatan Asli Daerah - ', '', $akun);
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
}
