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

`PAD per 1.000 Penduduk`

- hanya dipakai saat level provinsi
- sumber:
  - nilai PAD dari `tabel_pad`
  - nama wilayah dari `peta`
  - penduduk dari `tb_penduduk`
- rumus:
  - `(SUM(realisasi) / jumlah_penduduk) * 1000`

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
