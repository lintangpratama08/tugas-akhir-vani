# Panduan Ubah Chart Peta PAD

File utama chart ada di:

- `resources/views/peta/script_dashboard.blade.php`

Konsepnya:

- Setiap chart dirender dari data backend.
- Mapping elemen HTML chart ada di `app.chartDefinitions`.
- Konfigurasi Chart.js dipusatkan di fungsi `buildChartConfig(chart)`.

## 1. Ganti tipe chart

Tipe chart berasal dari backend, tepatnya dari service:

- `app/Services/PetaDashboardService.php`

Cari bagian `makeChart(...)`, contoh:

```php
$this->makeChart(
    'komposisi',
    'Komposisi Realisasi',
    '...',
    'doughnut',
    ...
)
```

Kalau ingin ubah dari pie/doughnut ke batang:

```php
'doughnut'
```

menjadi:

```php
'bar'
```

Kalau ingin jadi line:

```php
'line'
```

## 2. Ganti orientasi chart batang

Orientasi horizontal/vertikal diatur dari `options.indexAxis`.

Contoh di backend:

```php
[
    'indexAxis' => 'y',
]
```

Artinya chart horizontal.

Kalau ingin chart vertikal biasa:

```php
[
    'indexAxis' => 'x',
]
```

atau hapus saja `indexAxis`.

## 3. Ubah warna chart

Warna default frontend ada di:

```js
window.PetaDashboardApp.palette
```

di file:

- `resources/views/peta/script_peta.blade.php`

Kalau ingin warna khusus per chart, ubah warna dataset dari backend di:

- `app/Services/PetaDashboardService.php`

Contoh:

```php
$this->dataset('Realisasi', $data, 'currency', '#14b8a6')
```

## 4. Ubah label yang kepanjangan

Frontend sudah memakai helper:

- `wrapLabel(label, maxChars)`
- `limitLabel(label, maxChars)`

Keduanya ada di:

- `resources/views/peta/script_dashboard.blade.php`

Kalau label masih terlalu panjang, kecilkan angka `maxChars`.

## 5. Kalau mau tambah chart baru

Langkah ringkas:

1. Tambah data chart di `app/Services/PetaDashboardService.php`.
2. Tambah card/canvas baru di `resources/views/peta/dashboard.blade.php`.
3. Tambah mapping baru di `app.chartDefinitions` pada `resources/views/peta/script_dashboard.blade.php`.

## 6. Kalau chart tidak sesuai format angka

Format angka ditentukan lewat nilai dataset:

- `currency`
- `percent`

Contoh:

```php
$this->dataset('Persentase', $data, 'percent', '#ef4444')
```

Jika sumbu atau tooltip salah, cek:

- `formatNumericTick()`
- `formatValue()`

di:

- `resources/views/peta/script_dashboard.blade.php`
