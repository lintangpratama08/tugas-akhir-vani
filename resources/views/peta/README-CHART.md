# README Peta, Dashboard, dan Relasi Data

File utama backend:

- `app/Services/PetaDashboardService.php`

File utama frontend:

- `resources/views/peta/dashboard.blade.php`
- `resources/views/peta/script_peta.blade.php`
- `resources/views/peta/script_dashboard.blade.php`
- `resources/views/peta/style.blade.php`

## 1. Sumber tabel

Tabel peta kabupaten:

- `peta`
- dipakai untuk polygon level provinsi ke kabupaten
- kolom utama:
  - `ogc_fid`
  - `kabupaten`
  - `wkb_geometry`

Tabel peta kecamatan:

- `data_kec`
- dipakai untuk polygon level kabupaten ke kecamatan
- kolom utama:
  - `id`
  - `kdcpum`
  - `kdpkab`
  - `kdppum`
  - `wadmkc`
  - `wadmkk`
  - `wkb_geometry`

Tabel PAD kabupaten:

- `tabel_pad`
- dipakai untuk analitik level provinsi
- kolom utama:
  - `id_pad`
  - `akun`
  - `kota`
  - `tahun`
  - `anggaran`
  - `realisasi`
  - `persentase`

Tabel PAD kecamatan:

- `tabel_pad_kecamatan`
- dipakai untuk analitik saat sudah memilih kabupaten atau kecamatan
- kolom utama:
  - `id_pad`
  - `kdcpum`
  - `kdpkab`
  - `kdppum`
  - `wadmkc`
  - `wadmkk`
  - `akun`
  - `kota`
  - `tahun`
  - `anggaran`
  - `realisasi`
  - `persentase`

Tabel penduduk:

- `tb_penduduk`
- dipakai untuk analitik wilayah berbasis jumlah penduduk
- kolom utama:
  - `kode`
  - `nama_kabupaten`
  - `jumlah_penduduk`
  - `tahun`

## 2. Relasi tabel yang dipakai sekarang

Relasi level provinsi:

- `tabel_pad.kota = peta.ogc_fid`

Relasi level kabupaten ke kecamatan untuk peta:

- `data_kec.wadmkk = filter wilayah`
- `data_kec.wadmkc = filter kecamatan`

Relasi data PAD kecamatan:

- dashboard level kabupaten/kecamatan membaca langsung dari `tabel_pad_kecamatan`
- filter yang dipakai:
  - `tabel_pad_kecamatan.wadmkk = wilayah`
  - `tabel_pad_kecamatan.wadmkc = kecamatan`

Relasi penduduk ke PAD kabupaten:

- `tb_penduduk.kode` adalah 4 digit
- `tabel_pad.kota` adalah 2 digit
- relasi yang dipakai:
  - `2 digit terakhir tb_penduduk.kode = tabel_pad.kota`

Contoh:

- `tb_penduduk.kode = 1234`
- `tabel_pad.kota = 34`
- berarti cocok lewat `34 = 34`

Rumus SQL relasi penduduk yang dipakai:

```sql
RIGHT(LPAD(CAST(tb_penduduk.kode AS TEXT), 4, '0'), 2)
=
LPAD(CAST(tabel_pad.kota AS TEXT), 2, '0')
```

## 3. Sumber data setiap chart

`Perbandingan Anggaran dan Realisasi`

- level provinsi: dari `tabel_pad`
- level kabupaten: dari `tabel_pad_kecamatan`
- level kecamatan: dari `tabel_pad_kecamatan`
- grup data: per `akun`

`Tren Tahunan`

- level provinsi: dari `tabel_pad`
- level kabupaten: dari `tabel_pad_kecamatan`
- level kecamatan: dari `tabel_pad_kecamatan`
- grup data: per `tahun`

`PAD per Penduduk Tertinggi`

- hanya dipakai saat level provinsi
- sumber:
  - nilai PAD dari `tabel_pad`
  - nama wilayah dari `peta`
  - penduduk dari `tb_penduduk`
- rumus:
  - `SUM(realisasi) / jumlah_penduduk`

`Realisasi PAD Tertinggi`

- hanya dipakai saat level provinsi
- sumber:
  - nilai PAD dari `tabel_pad`
  - nama wilayah dari `peta`
- rumus:
  - `SUM(realisasi)`

`Komposisi Realisasi`

- semua level
- grup data: per `akun`

`Pertumbuhan YoY`

- semua level
- grup data: per `tahun`
- rumus:
  - pertumbuhan realisasi dibanding tahun sebelumnya

## 4. Sumber tabel detail dashboard

`Detail Akun PAD`

- semua level
- grup data: per `akun`

`Detail Wilayah Jawa Timur`

- level provinsi
- sumber:
  - `tabel_pad`
  - `peta`
  - `tb_penduduk`
- kolom hasil:
  - wilayah
  - penduduk
  - anggaran
  - realisasi
  - PAD per penduduk
  - PAD per 1.000 penduduk
  - selisih
  - persentase

`Detail Kecamatan pada Wilayah Aktif`

- level kabupaten
- sumber:
  - `tabel_pad_kecamatan`
- grup data:
  - `wadmkc`

`Detail Akun Kecamatan Aktif`

- level kecamatan
- sumber:
  - `tabel_pad_kecamatan`
- grup data:
  - `akun`

## 5. Kalau kolom relasinya mau diganti

Bagian yang perlu disesuaikan:

- `padQuery()`
- `applyPadFilters()`
- `populationSubquery()`
- `queryRankingWilayah()`
- `queryKontribusi()`
- `queryDetailWilayah()`
- `queryDetailKecamatanDalamWilayah()`
- `aggregateByKecamatanSubquery()`
- `detailPerAkunByKecamatan()`

Semua ada di:

- `app/Services/PetaDashboardService.php`

## 6. Catatan penting

Jika nanti ternyata relasi penduduk tidak cocok hanya dengan `2 digit terakhir`, opsi berikut bisa dipakai:

- relasi nama:
  - `LOWER(tb_penduduk.nama_kabupaten) = LOWER(peta.kabupaten)`
- relasi kode kabupaten dari sumber lain
- tabel mapping manual khusus nama kabupaten

Jika ada kolom yang ingin Anda ganti, cukup beri tahu:

- nama tabel
- nama kolom lama
- nama kolom baru
- aturan relasinya

lalu service bisa disesuaikan langsung.

query isi kecamatan
ALTER SEQUENCE tabel_pad_kecamatan_id_seq RESTART WITH 1;
INSERT INTO tabel_pad_kecamatan (
    id_pad,
    kdcpum,
    kdpkab,
    kdppum,
    wadmkc,
    wadmkk,
    wadmpr,
    ogc_fid,
    kode,
    kabupaten,
    province,
    akun,
    kota,
    tahun,
    anggaran,
    realisasi,
    persentase
)

WITH base AS (
    SELECT
        tp.id_pad,
        tp.akun,
        tp.kota,
        tp.tahun,
        tp.anggaran,
        tp.realisasi,
        tp.persentase,

        dk.kdcpum,
        dk.kdpkab,
        dk.kdppum,
        dk.wadmkc,
        dk.wadmkk,
        dk.wadmpr,

        p.ogc_fid,
        p.kode,
        p.kabupaten,
        p.province,

        random() AS w_anggaran,
        random() AS w_realisasi,
        random() AS w_persentase

    FROM tabel_pad tp

    JOIN peta p
        ON p.ogc_fid = tp.kota

    JOIN data_kec dk
        ON REPLACE(dk.kdpkab, '.', '')::INT = p.kode
),

calc AS (
    SELECT
        b.*,

        SUM(w_anggaran) OVER (PARTITION BY id_pad) AS sum_w_anggaran,
        SUM(w_realisasi) OVER (PARTITION BY id_pad) AS sum_w_realisasi,
        SUM(w_persentase) OVER (PARTITION BY id_pad) AS sum_w_persentase,

        ROW_NUMBER() OVER (
            PARTITION BY id_pad
            ORDER BY kdcpum
        ) AS rn,

        COUNT(*) OVER (
            PARTITION BY id_pad
        ) AS cnt

    FROM base b
),

rounded AS (
    SELECT
        c.*,

        CASE
            WHEN rn < cnt THEN
                ROUND(
                    (anggaran * w_anggaran / sum_w_anggaran)::numeric,
                    2
                )
        END AS anggaran_part,

        CASE
            WHEN rn < cnt THEN
                ROUND(
                    (realisasi * w_realisasi / sum_w_realisasi)::numeric,
                    2
                )
        END AS realisasi_part,

        CASE
            WHEN rn < cnt THEN
                ROUND(
                    (persentase * w_persentase / sum_w_persentase)::numeric,
                    2
                )
        END AS persentase_part

    FROM calc c
),

final AS (
    SELECT
        r.*,

        SUM(COALESCE(anggaran_part, 0))
            OVER (PARTITION BY id_pad) AS sum_anggaran_nonlast,

        SUM(COALESCE(realisasi_part, 0))
            OVER (PARTITION BY id_pad) AS sum_realisasi_nonlast,

        SUM(COALESCE(persentase_part, 0))
            OVER (PARTITION BY id_pad) AS sum_persentase_nonlast

    FROM rounded r
)

SELECT
    id_pad,

    kdcpum,
    kdpkab,
    kdppum,
    wadmkc,
    wadmkk,
    wadmpr,

    ogc_fid,
    kode,
    kabupaten,
    province,

    akun,
    kota,
    tahun,

    CASE
        WHEN rn < cnt THEN anggaran_part
        ELSE ROUND(
            (anggaran - sum_anggaran_nonlast)::numeric,
            2
        )
    END AS anggaran,

    CASE
        WHEN rn < cnt THEN realisasi_part
        ELSE ROUND(
            (realisasi - sum_realisasi_nonlast)::numeric,
            2
        )
    END AS realisasi,

    CASE
        WHEN rn < cnt THEN persentase_part
        ELSE ROUND(
            (persentase - sum_persentase_nonlast)::numeric,
            2
        )
    END AS persentase

FROM final;
