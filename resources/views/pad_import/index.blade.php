@extends('peta.layout')

@section('css')
    @include('peta.style')
    <style>
        .upload-page-shell {
            max-width: 1180px;
            margin: 0 auto;
            padding: 8.4rem 0.45rem 1.5rem;
        }

        .upload-hero-card,
        .upload-form-card,
        .upload-guide-card {
            border: 1px solid rgba(23, 58, 100, 0.08);
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(245, 249, 253, 0.96));
            box-shadow: 0 24px 52px rgba(15, 23, 42, 0.08);
        }

        .upload-hero-card {
            padding: 1.6rem;
            margin-bottom: 1.2rem;
        }

        .upload-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(280px, 0.8fr);
            gap: 1rem;
            align-items: stretch;
        }

        .upload-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.08);
            color: #1d4ed8;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .upload-hero-card h1 {
            margin: 0.9rem 0 0.7rem;
            font-size: clamp(2rem, 3vw, 2.8rem);
            line-height: 1.08;
            color: #14335b;
            font-weight: 900;
        }

        .upload-hero-card p {
            margin: 0;
            color: #5b6f86;
            line-height: 1.7;
        }

        .upload-hero-points,
        .upload-side-list {
            display: grid;
            gap: 0.75rem;
        }

        .upload-point,
        .upload-side-item {
            display: flex;
            gap: 0.8rem;
            align-items: flex-start;
            padding: 0.9rem 1rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(23, 58, 100, 0.07);
        }

        .upload-point i,
        .upload-side-item i {
            color: #c58d2f;
            font-size: 1.05rem;
            margin-top: 0.1rem;
        }

        .upload-point strong,
        .upload-side-item strong {
            display: block;
            color: #173a64;
            margin-bottom: 0.18rem;
        }

        .upload-point span,
        .upload-side-item span {
            color: #617287;
            font-size: 0.9rem;
            line-height: 1.55;
        }

        .upload-form-card {
            padding: 1.5rem;
            margin-bottom: 1.2rem;
        }

        .upload-form-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .upload-form-head h2 {
            margin: 0 0 0.35rem;
            color: #14335b;
            font-size: 1.4rem;
            font-weight: 800;
        }

        .upload-form-head p {
            margin: 0;
            color: #617287;
        }

        .upload-file-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .upload-file-card {
            padding: 1.1rem;
            border-radius: 22px;
            border: 1px solid rgba(23, 58, 100, 0.08);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(248, 250, 252, 0.94));
        }

        .upload-file-card.is-wide {
            grid-column: 1 / -1;
        }

        .upload-file-year {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.72rem;
            border-radius: 999px;
            background: rgba(197, 141, 47, 0.1);
            color: #9a6919;
            font-size: 0.8rem;
            font-weight: 800;
            margin-bottom: 0.8rem;
        }

        .upload-guide-card {
            padding: 1.3rem 1.4rem;
        }

        .upload-guide-card h3 {
            margin: 0 0 0.8rem;
            color: #173a64;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .upload-guide-list {
            display: grid;
            gap: 0.55rem;
            color: #5b6f86;
            font-size: 0.92rem;
        }

        @media (max-width: 992px) {

            .upload-hero-grid,
            .upload-file-grid {
                grid-template-columns: 1fr;
            }

            .upload-file-card.is-wide {
                grid-column: auto;
            }
        }

        @media (max-width: 768px) {
            .upload-page-shell {
                padding-top: 7.9rem;
            }

            .upload-hero-card,
            .upload-form-card,
            .upload-guide-card {
                border-radius: 24px;
            }

            .upload-form-head {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')
    <div class="upload-page-shell">
        <section class="upload-hero-card">
            <div class="upload-hero-grid">
                <div>
                    <span class="upload-badge"><i class="bi bi-cloud-arrow-up"></i> Upload Data PAD</span>
                    <h1>Kelola import data PAD dengan tampilan yang lebih rapi dan fokus.</h1>
                    <p>Navbar dan footer tetap memakai identitas dashboard utama. Di halaman ini, konten difokuskan untuk
                        upload file Excel per tahun, memilih kabupaten/kota tujuan, dan memastikan data masuk konsisten ke
                        <code>tabel_pad</code>.
                    </p>
                </div>
                <div class="upload-side-list">
                    <div class="upload-side-item">
                        <i class="bi bi-collection"></i>
                        <div>
                            <strong>5 file sekaligus</strong>
                            <span>Upload untuk tahun 2021 sampai 2025 dalam satu alur.</span>
                        </div>
                    </div>
                    <div class="upload-side-item">
                        <i class="bi bi-shield-check"></i>
                        <div>
                            <strong>Validasi otomatis</strong>
                            <span>Kolom penting dicek, angka dinormalisasi, dan proses memakai transaksi penuh.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
                @if (session('import_summary'))
                    <div class="small mt-2">Tahun terimport: {{ implode(', ', session('import_summary')['tahun']) }}</div>
                    <div class="small mt-1">
                        @foreach (session('import_summary')['files'] as $fileSummary)
                            <div>Upload {{ $fileSummary['slot'] }}: {{ $fileSummary['filename'] }}
                                ({{ $fileSummary['tahun'] }})
                                - {{ $fileSummary['inserted'] }} baris</div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="upload-form-card">
            <div class="upload-form-head">
                <div>
                    <h2>Form Upload Data</h2>
                    <p>Pilih kabupaten/kota tujuan lalu unggah file Excel sesuai tahun yang sudah ditentukan sistem.</p>
                </div>
                <a href="{{ route('peta.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke peta
                </a>
            </div>

            <div class="upload-hero-points mb-4">
                <div class="upload-point">
                    <i class="bi bi-pin-map"></i>
                    <div>
                        <strong>Pemda dipilih manual</strong>
                        <span>Nilai kota/kabupaten dari file akan mengikuti pilihan di form saat proses import.</span>
                    </div>
                </div>
                <div class="upload-point">
                    <i class="bi bi-calculator"></i>
                    <div>
                        <strong>Persentase dihitung otomatis</strong>
                        <span>Nilai <code>persentase</code> dibentuk dari <code>realisasi / anggaran * 100</code>.</span>
                    </div>
                </div>
            </div>

            <div class="card border-0 bg-transparent">
                <div class="card-body p-0">
                    <form action="{{ route('pad.import.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="kota" class="form-label">Kota/Kabupaten</label>
                            <select name="kota" id="kota" class="form-select" required>
                                <option value="">Pilih kota/kabupaten</option>
                                @foreach ($kotaOptions as $kota)
                                    @php
                                        $kotaLabel = trim((string) $kota->kabupaten);
                                        $kotaLabel = preg_replace('/^\s*\(?\s*ogc_fid\s*\)?\s*=>\s*/i', '', $kotaLabel);
                                        $isSelected = (string) old('kota') === (string) $kota->ogc_fid;

                                        if (strpos($kotaLabel, '=>') !== false) {
                                            $parts = explode('=>', $kotaLabel);
                                            $kotaLabel = trim(end($parts));
                                        }
                                    @endphp
                                    <option value="{{ $kota->ogc_fid }}" {{ $isSelected ? 'selected' : '' }}>
                                        {{ $kotaLabel }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Kolom kota di file dianggap kosong, jadi nilainya diambil dari pilihan
                                ini saat import.</div>
                        </div>

                        <div class="upload-file-grid">
                            @for ($i = 0; $i < 5; $i++)
                                <div class="upload-file-card {{ $i === 4 ? 'is-wide' : '' }}">
                                    <div class="upload-file-year"><i class="bi bi-calendar3"></i> Tahun
                                        {{ $tahunOptions[$i] }}</div>
                                    <div>
                                        <label for="uploads_{{ $i }}_file" class="form-label">File Excel
                                            {{ $tahunOptions[$i] }}</label>
                                        <input type="file" name="uploads[{{ $i }}][file]"
                                            id="uploads_{{ $i }}_file" class="form-control"
                                            accept=".xlsx,.xls,.csv" required>
                                        <div class="form-text">Gunakan file Excel 97-2003 (`.xls`) atau format Excel lain
                                            yang didukung.</div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <div class="mb-4 form-text">
                            Header kolom dibaca tanpa membedakan huruf besar dan kecil. Kolom yang dicari saat ini:
                            <code>akun</code>, <code>anggaran</code>, dan <code>realisasi</code>. Tahun dikunci otomatis ke
                            <code>2021</code>, <code>2022</code>, <code>2023</code>, <code>2024</code>, dan
                            <code>2025</code>
                            sesuai urutan upload, bukan dari isi Excel. Nilai <code>anggaran</code> dan
                            <code>realisasi</code>
                            akan dinormalisasi otomatis termasuk jika masih berbentuk scientific notation seperti
                            <code>3,26397E+12</code>. Format Excel 97-2003 <code>.xls</code> juga didukung. Nilai
                            <code>persentase</code> selalu dihitung otomatis dari
                            <code>realisasi / anggaran * 100</code>.
                        </div>

                        <div class="rounded border bg-light p-3 mb-4">
                            <strong class="d-block mb-2">Jaminan import</strong>
                            <div class="text-muted small">
                                Kelima file diproses dalam satu transaksi database. Kalau ada satu file, satu baris, atau
                                satu kolom
                                yang salah, seluruh proses dibatalkan dan tidak ada data yang disimpan sebagian.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="import_password" class="form-label">Password Import</label>
                            <input type="password" name="import_password" id="import_password" class="form-control"
                                required>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Import PAD
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <section class="upload-guide-card">
            <h3>Detail format data download dan upload</h3>
            <div class="upload-guide-list">
                <div>File upload dibaca berdasarkan kolom <code>akun</code>, <code>anggaran</code>, dan
                    <code>realisasi</code>.
                </div>
                <div>Urutan file mewakili tahun <code>2021</code>, <code>2022</code>, <code>2023</code>, <code>2024</code>,
                    dan <code>2025</code>.</div>
                <div>Nilai scientific notation seperti <code>3,26397E+12</code> akan dinormalisasi otomatis saat import.
                </div>
                <div>Jika satu file atau satu baris gagal diproses, seluruh batch dibatalkan agar data tidak tersimpan
                    setengah.</div>
            </div>
        </section>
    </div>
@endsection
