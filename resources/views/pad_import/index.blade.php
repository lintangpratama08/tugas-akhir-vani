@extends('peta.layout')

@section('content')
    <div class="container py-5" style="max-width: 860px; margin-top: 90px;">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 mb-2">Import Data PAD</h1>
                <p class="text-muted mb-0">Upload file Excel, pilih kota/kabupaten secara manual, lalu simpan ke
                    <code>tabel_pad</code>.</p>
            </div>
            <a href="{{ route('peta.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke peta
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
                @if (session('import_summary'))
                    <div class="small mt-2">Tahun terimport: {{ implode(', ', session('import_summary')['tahun']) }}</div>
                    <div class="small mt-1">
                        @foreach (session('import_summary')['files'] as $fileSummary)
                            <div>Upload {{ $fileSummary['slot'] }}: {{ $fileSummary['filename'] }} ({{ $fileSummary['tahun'] }}) - {{ $fileSummary['inserted'] }} baris</div>
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

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('pad.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="kota" class="form-label">Kota/Kabupaten</label>
                        <select name="kota" id="kota" class="form-select" required>
                            <option value="">Pilih kota/kabupaten</option>
                            @foreach ($kotaOptions as $kota)
                                <option value="{{ $kota->ogc_fid }}" @selected((string) old('kota') === (string) $kota->ogc_fid)>
                                    {{ $kota->kabupaten }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Kolom kota di file dianggap kosong, jadi nilainya diambil dari pilihan ini saat import.</div>
                    </div>

                    @for ($i = 0; $i < 5; $i++)
                        <div class="border rounded p-3 mb-3">
                            <div class="fw-semibold mb-3">Upload Tahun {{ $tahunOptions[$i] }}</div>
                            <div>
                                <label for="uploads_{{ $i }}_file" class="form-label">File Excel {{ $tahunOptions[$i] }}</label>
                                <input type="file" name="uploads[{{ $i }}][file]" id="uploads_{{ $i }}_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                <div class="form-text">Gunakan file Excel 97-2003 (`.xls`) atau format Excel lain yang didukung.</div>
                            </div>
                        </div>
                    @endfor

                    <div class="mb-4 form-text">
                        Header kolom dibaca tanpa membedakan huruf besar dan kecil. Kolom yang dicari saat ini:
                        <code>akun</code>, <code>anggaran</code>, dan <code>realisasi</code>. Tahun dikunci otomatis ke
                        <code>2021</code>, <code>2022</code>, <code>2023</code>, <code>2024</code>, dan <code>2025</code>
                        sesuai urutan upload, bukan dari isi Excel. Nilai <code>anggaran</code> dan <code>realisasi</code>
                        akan dinormalisasi otomatis termasuk jika masih berbentuk scientific notation seperti
                        <code>3,26397E+12</code>. Format Excel 97-2003 <code>.xls</code> juga didukung. Nilai <code>persentase</code> selalu dihitung otomatis dari
                        <code>realisasi / anggaran * 100</code>.
                    </div>

                    <div class="rounded border bg-light p-3 mb-4">
                        <strong class="d-block mb-2">Jaminan import</strong>
                        <div class="text-muted small">
                            Kelima file diproses dalam satu transaksi database. Kalau ada satu file, satu baris, atau satu kolom
                            yang salah, seluruh proses dibatalkan dan tidak ada data yang disimpan sebagian.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Import PAD
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
