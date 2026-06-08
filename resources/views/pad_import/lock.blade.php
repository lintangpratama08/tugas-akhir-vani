@extends('peta.layout')

@section('css')
    @include('peta.style')
    <style>
        .upload-lock-shell {
            max-width: 760px;
            margin: 0 auto;
            padding: 8.6rem 0.45rem 1.5rem;
        }

        .upload-lock-card {
            border: 1px solid rgba(23, 58, 100, 0.08);
            border-radius: 30px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(245, 249, 253, 0.96));
            box-shadow: 0 24px 52px rgba(15, 23, 42, 0.08);
            padding: 1.7rem;
        }

        .upload-lock-badge {
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

        .upload-lock-card h1 {
            margin: 1rem 0 0.7rem;
            color: #14335b;
            font-size: clamp(2rem, 3vw, 2.7rem);
            line-height: 1.08;
            font-weight: 900;
        }

        .upload-lock-card p {
            margin: 0;
            color: #617287;
            line-height: 1.7;
        }

        .upload-lock-notes {
            display: grid;
            gap: 0.75rem;
            margin: 1.25rem 0 1.35rem;
        }

        .upload-lock-note {
            display: flex;
            gap: 0.8rem;
            align-items: flex-start;
            padding: 0.95rem 1rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.76);
            border: 1px solid rgba(23, 58, 100, 0.07);
        }

        .upload-lock-note i {
            color: #c58d2f;
            font-size: 1.05rem;
            margin-top: 0.1rem;
        }

        .upload-lock-note strong {
            display: block;
            color: #173a64;
            margin-bottom: 0.18rem;
        }

        .upload-lock-note span {
            color: #617287;
            font-size: 0.92rem;
            line-height: 1.55;
        }

        @media (max-width: 768px) {
            .upload-lock-shell {
                padding-top: 7.9rem;
            }

            .upload-lock-card {
                border-radius: 24px;
                padding: 1.3rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="upload-lock-shell">
        <section class="upload-lock-card">
            <span class="upload-lock-badge"><i class="bi bi-shield-lock"></i> Akses Upload Data</span>
            <h1>Masukkan password untuk membuka halaman upload data.</h1>
            <p>Menu `Upload Data` sekarang diproteksi password. Setelah halaman terbuka, proses impor tetap akan meminta
                password kedua saat menekan tombol impor.</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('pad.import.unlock') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="access_password" class="form-label">Password Akses Upload Data</label>
                    <input type="password" name="access_password" id="access_password" class="form-control" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-unlock"></i> Buka Halaman Upload
                </button>
            </form>
        </section>
    </div>
@endsection
