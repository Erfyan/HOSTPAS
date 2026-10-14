# CLAUDE.md — HOSTPAS (Hosting Optimization & Selection Tool using WASPAS)

Pedoman perancangan dan pengerjaan sistem. Baca seluruh file ini sebelum menulis kode. Kalau ada konflik antara file ini dan asumsi lain, file ini yang berlaku.

## 1. Ringkasan proyek

Aplikasi web Sistem Pendukung Keputusan (SPK) bernama **HOSTPAS** (*Hosting Optimization & Selection Tool using WASPAS* atau *Hosting SPK WASPAS*) untuk memilih paket web hosting terbaik dengan metode **WASPAS** (gabungan SAW dan WP). Dibuat sebagai pendamping jurnal ilmiah berjudul "SPK Pemilihan Hosting Terbaik untuk Website Menggunakan Metode WASPAS".

- **Nama Sistem**: **HOSTPAS**
- **Subjudul UI**: *Sistem Pendukung Keputusan Pemilihan Hosting Terbaik*
- **Pengguna**: satu orang (peneliti) untuk demo dan lampiran jurnal. Tidak ada multi-user.
- **Data**: 20 paket hosting dari 4 penyedia (Hostinger, Niagahoster, Rumahweb, IDCloudHost), 4 kriteria.
- **Prinsip**: **sederhana, benar, transparan, mudah dijelaskan di jurnal**. Perhitungan harus transparan langkah demi langkah.
- **Database**: MySQL (nama database yang disarankan: `spk_waspas` atau `hostpas`).

Stack tetap (jangan diganti): **Laravel (PHP 8.2+) + Vue.js 3 (Vite) + Tailwind CSS 3.4 + Chart.js + MySQL**.

## 2. Cakupan fitur

### Wajib ada (9 fitur)

1. **Data alternatif**: daftar paket hosting (bisa difilter per penyedia).
2. **Data kriteria**: kode, nama, satuan, atribut Benefit atau Cost.
3. **Bobot kriteria**: bisa diubah, total harus 1 (100%), ada indikator valid atau tidak.
4. **Input nilai alternatif**: nilai tiap paket untuk tiap kriteria, bisa diedit langsung di tabel.
5. **Perhitungan WASPAS bertahap**: matriks keputusan X, normalisasi R, skor SAW, skor WP, nilai akhir Q.
6. **Parameter λ (lambda)**: slider 0 sampai 1, langkah 0,1, bawaan 0,5.
7. **Hasil perankingan**: urut dari Q tertinggi, rekomendasi teratas jelas.
8. **Beranda**: ringkasan (jumlah alternatif, kriteria, λ), rekomendasi teratas, 5 peringkat teratas.
9. **Grafik** perbandingan skor WASPAS (batang).

### Di luar cakupan (jangan dibuat kecuali diminta)

Tambah atau hapus alternatif dan kriteria (CRUD penuh), ekspor atau cetak hasil, halaman panduan metode, login, multi-user, mode gelap, riwayat perhitungan.

Konsekuensinya: alternatif dan kriteria **diisi lewat seeder**. Di UI hanya bobot, atribut, dan nilai yang bisa diedit. Skema database dan controller sengaja dibuat supaya CRUD mudah ditambahkan nanti.

## 3. Arsitektur

Satu aplikasi Laravel yang menyajikan SPA Vue dan API JSON. Satu repo, satu deploy, tanpa Inertia, tanpa Pinia, tanpa TypeScript.

```
Browser (Vue 3 SPA + Vue Router)
   │  fetch JSON
   ▼
routes/api.php ──► Controller (tipis) ──► WaspasService (logika murni, tanpa DB)
                         │                        ▲
                         ▼                        │ array angka
                   Eloquent Model ──► MySQL ──────┘
```

Keputusan arsitektur:

- **Semua perhitungan WASPAS dilakukan di backend** (`WaspasService`). Satu sumber kebenaran, mudah diuji, hasilnya sama dengan yang ditulis di jurnal. Frontend hanya menampilkan.
- `WaspasService` adalah kelas PHP murni: menerima array kriteria, alternatif, nilai, dan λ; mengembalikan array hasil. Tidak menyentuh database atau request.
- Controller tipis: validasi (FormRequest), panggil model atau service, kembalikan JSON.
- Satu endpoint `GET /api/waspas` mengembalikan **semua langkah** sekaligus (X, R, SAW, WP, Q, peringkat) supaya Beranda, Perhitungan, dan Grafik memakai data yang sama.
- State frontend: satu composable `useSpk()` berisi `ref` bersama. Tidak perlu library state.
- Fallback Laravel: route `/{any}` mengarah ke `app.blade.php` yang memuat Vue lewat `@vite`.

## 4. Struktur folder

```
app/
  Models/            Alternative.php, Criterion.php, Score.php
  Services/          WaspasService.php
  Http/
    Controllers/     CriterionController, AlternativeController, ScoreController,
                     WaspasController, ResetController
    Requests/        UpdateCriteriaRequest, UpdateScoresRequest
database/
  migrations/        alternatives, criteria, scores
  seeders/           HostingSeeder.php   (data di bagian 6)
routes/api.php
resources/
  views/app.blade.php
  css/app.css        (@tailwind base/components/utilities)
  js/
    app.js, App.vue, router.js, api.js
    composables/useSpk.js
    views/           Beranda.vue, Kriteria.vue, AlternatifNilai.vue, Perhitungan.vue
    components/      AppHeader.vue, StatCard.vue, SectionCard.vue, StepBadge.vue,
                     WeightBadge.vue, RankingTable.vue, RankingChart.vue
tests/
  Unit/WaspasServiceTest.php
  Feature/WaspasApiTest.php
docs/referensi/      coba1.html (acuan desain), spesifikasi_tempat_hosting.docx
```

Penamaan: kode, tabel, dan kolom memakai bahasa Inggris. Teks yang tampil di UI memakai bahasa Indonesia.

## 5. Database (MySQL)

| Tabel | Kolom |
|---|---|
| `alternatives` | `id`, `code` (A1..A20, unik), `provider`, `name`, `category`, `spec_raw` (text, spesifikasi asli dari docx), timestamps |
| `criteria` | `id`, `code` (C1..C4, unik), `name`, `unit`, `type` (enum `benefit`,`cost`), `weight` (decimal 5,4), timestamps |
| `scores` | `id`, `alternative_id` (FK, cascade), `criterion_id` (FK, cascade), `value` (decimal 15,4, nullable), unik `(alternative_id, criterion_id)`, timestamps |

Lambda **tidak disimpan di database**. Frontend menyimpannya di `localStorage` dan mengirimnya sebagai query `lambda`.

## 6. Data awal (HostingSeeder)

Sumber: `docs/referensi/spesifikasi_tempat_hosting.docx`. Nilai di bawah adalah hasil konversi. Cocokkan `spec_raw` dengan docx sebelum dipakai.

### Kriteria dan bobot awal

| Kode | Nama | Satuan | Atribut | Bobot |
|---|---|---|---|---|
| C1 | Harga per bulan | Rp | cost | 0,30 |
| C2 | Storage | GB | benefit | 0,25 |
| C3 | RAM | GB | benefit | 0,25 |
| C4 | CPU | vCPU | benefit | 0,20 |

Bobot ini **contoh**. Penelitian nanti menggantinya lewat UI. Jangan di-hardcode di logika.

### Aturan konversi spesifikasi ke angka

- Rentang (harga, storage, RAM, CPU) diambil **nilai tengahnya**.
- Storage "Unlimited" dihitung **200 GB** (storage terbesar yang bukan unlimited pada data). Rentang dengan batas atas Unlimited: nilai tengah antara batas bawah dan 200.
- CPU: Shared = 0,5; 1 core = 1; 2 core atau 2 vCPU = 2.

### 20 alternatif

| Kode | Penyedia | Paket | Kategori | Spesifikasi asli (storage / RAM / CPU / harga) | C1 | C2 | C3 | C4 |
|---|---|---|---|---|---|---|---|---|
| A1 | Hostinger | Hostinger Single | Shared Hosting | 50 GB / 1 GB / Shared / Rp12.900–29.900 | 21400 | 50 | 1 | 0,5 |
| A2 | Hostinger | Hostinger Premium | Shared Hosting | 100 GB / 1,5 GB / Shared / Rp24.900–39.900 | 32400 | 100 | 1,5 | 0,5 |
| A3 | Hostinger | Hostinger Business | Advanced Web | 200 GB / 1,5 GB / 2 vCPU / Rp38.900–59.900 | 49400 | 200 | 1,5 | 2 |
| A4 | Hostinger | Hostinger Cloud Startup | Cloud Hosting | 200 GB / 3 GB / 2 Cores / Rp116.900 | 116900 | 200 | 3 | 2 |
| A5 | Hostinger | Hostinger KVM 1 (VPS) | Virtual Private Server | 50 GB / 4 GB / 1 Core / Rp116.900 | 116900 | 50 | 4 | 1 |
| A6 | Niagahoster | Niagahoster Bayi / Single | Shared Hosting | 500 MB–10 GB / 1 GB / Shared / Rp10.000–19.800 | 14900 | 5,25 | 1 | 0,5 |
| A7 | Niagahoster | Niagahoster Pelajar / Premium | Shared Hosting | 100 GB / 1,5 GB / Shared / Rp24.900–40.200 | 32550 | 100 | 1,5 | 0,5 |
| A8 | Niagahoster | Niagahoster Personal / Business | Unlimited / Advanced | 100 GB–Unlimited / 2 GB / 2 vCPU / Rp38.900–59.900 | 49400 | 150 | 2 | 2 |
| A9 | Niagahoster | Niagahoster Cloud Startup | Cloud Hosting | 100 GB / 3 GB / 2 Cores / Rp116.900 | 116900 | 100 | 3 | 2 |
| A10 | Niagahoster | Niagahoster Cloud VPS KVM 1 | Virtual Private Server | 50 GB / 4 GB / 1 Core / Rp116.900 | 116900 | 50 | 4 | 1 |
| A11 | Rumahweb | Rumahweb Entry / Small | Shared Hosting | 1–5 GB / 1 GB / Shared / Rp15.000–25.000 | 20000 | 3 | 1 | 0,5 |
| A12 | Rumahweb | Rumahweb Medium / Unlimited | Shared Hosting | 10 GB–Unlimited / 2 GB / Shared / Rp30.000–50.000 | 40000 | 105 | 2 | 0,5 |
| A13 | Rumahweb | Rumahweb Corporate / Pro | Unlimited Business | Unlimited / 3 GB / 2 vCPU / Rp75.000–100.000 | 87500 | 200 | 3 | 2 |
| A14 | Rumahweb | Rumahweb Cloud Hosting | Cloud Hosting | 20–50 GB / 4 GB / 2 Cores / Rp120.000–150.000 | 135000 | 35 | 4 | 2 |
| A15 | Rumahweb | Rumahweb VPS Linux | Virtual Private Server | 30–60 GB / 2–4 GB / 1–2 vCPU / Rp150.000–200.000 | 175000 | 45 | 3 | 1,5 |
| A16 | IDCloudHost | IDCloudHost Cloud Hosting Starter | Shared Hosting | 10 GB–Unlimited / 1 GB / Shared / Rp15.000–30.000 | 22500 | 105 | 1 | 0,5 |
| A17 | IDCloudHost | IDCloudHost Cloud Hosting Pro | Advanced Shared | Unlimited / 2 GB / Shared / Rp40.000–70.000 | 55000 | 200 | 2 | 0,5 |
| A18 | IDCloudHost | IDCloudHost Server VPS Basic | VPS Standard | 20–40 GB / 1–2 GB / 1 vCore / Rp40.000–75.000 | 57500 | 30 | 1,5 | 1 |
| A19 | IDCloudHost | IDCloudHost Cloud VPS eXtreme | Cloud VPS | 20–60 GB / 2–4 GB / 2 vCore / Rp87.000–149.000 | 118000 | 40 | 3 | 2 |
| A20 | IDCloudHost | IDCloudHost Managed VPS | Managed WHM/cPanel | 50 GB / 4 GB / 2 Core / Rp680.000 | 680000 | 50 | 4 | 2 |

Seeder harus **idempoten** (pakai `updateOrCreate` pada `code`) karena dipakai juga oleh tombol Reset Default.

## 7. Logika WASPAS

Untuk alternatif i dan kriteria j. Bobot yang dipakai adalah `w_j = weight_j / Σ weight` (dinormalkan, jadi total selalu 1).

1. Matriks keputusan **X** = nilai mentah.
2. Nilai pembanding per kriteria: `max(x)` untuk benefit, `min(x)` untuk cost.
3. Normalisasi **R**:
   - benefit: `r = x / max(x)`
   - cost: `r = min(x) / x`
4. SAW: `Q¹ᵢ = Σ (wⱼ × rᵢⱼ)`
5. WP: `Q²ᵢ = Π (rᵢⱼ ^ wⱼ)`
6. WASPAS: `Qᵢ = λ × Q¹ᵢ + (1 − λ) × Q²ᵢ`
7. Peringkat: Q terbesar = peringkat 1.

**λ = 1 berarti hanya SAW, λ = 0 berarti hanya WP.** (File acuan `coba1.html` menulis kebalikannya di teks; rumus di kodenya yang benar. Ikuti rumus di atas.)

Aturan khusus:

- Alternatif dengan nilai kosong pada salah satu kriteria **dikeluarkan** dari perhitungan dan dilaporkan di `excluded`.
- Perhitungan butuh minimal 2 alternatif lengkap. Kalau kurang, kembalikan `ok: false` dan UI menampilkan pesan kosong.
- Cost dengan `x = 0`: `r = 1` bila `min = 0`, selain itu `r = 0`. Benefit dengan `max = 0`: `r = 0`.
- Bobot total ≠ 1 tidak memblokir perhitungan, tapi `weights_valid` = false dan UI menampilkan peringatan.
- Kalkulasi memakai `float` PHP. Pembulatan hanya saat tampil (4 desimal).

### Bentuk respons `GET /api/waspas?lambda=0.5&provider=Hostinger`

`provider` opsional (kosong = semua penyedia).

```json
{
  "ok": true,
  "lambda": 0.5,
  "weights_valid": true,
  "criteria": [
    { "code": "C1", "name": "Harga per bulan", "unit": "Rp", "type": "cost", "weight": 0.3, "normalized_weight": 0.3 }
  ],
  "extremes": { "C1": 14900, "C2": 200, "C3": 4, "C4": 2 },
  "alternatives": [
    { "id": 1, "code": "A1", "provider": "Hostinger", "name": "Hostinger Single",
      "x": { "C1": 21400 }, "r": { "C1": 0.6963 },
      "saw": 0.0, "wp": 0.0, "q": 0.0, "rank": 1 }
  ],
  "excluded": [ { "id": 21, "name": "…", "reason": "Nilai belum lengkap" } ]
}
```

`alternatives` diurutkan menurut kode. Frontend mengurutkan sendiri berdasarkan `rank` untuk tabel peringkat.

## 8. API (routes/api.php)

| Method | Path | Fungsi |
|---|---|---|
| GET | `/api/criteria` | daftar kriteria |
| PUT | `/api/criteria` | ubah bobot dan atribut sekaligus (array). Validasi: bobot angka > 0 dan ≤ 1, atribut `benefit` atau `cost` |
| GET | `/api/alternatives?provider=` | daftar alternatif beserta nilainya |
| PUT | `/api/alternatives/{id}/scores` | ubah nilai satu alternatif: `{ "C1": 21400, "C2": 50, ... }`. Validasi: angka ≥ 0 atau null |
| GET | `/api/waspas?lambda=&provider=` | semua langkah perhitungan (bagian 7) |
| POST | `/api/reset` | jalankan ulang `HostingSeeder` (Reset Default) |

Semua error validasi memakai HTTP 422 dengan format bawaan Laravel.

## 9. Frontend

Halaman (Vue Router, hash atau history mode):

| Route | View | Isi |
|---|---|---|
| `/` | `Beranda.vue` | hero, 3 kartu ringkasan (+ slider λ), tabel 5 peringkat teratas |
| `/kriteria` | `Kriteria.vue` | tabel kriteria, edit bobot dan atribut, indikator total bobot, Reset Default |
| `/alternatif` | `AlternatifNilai.vue` | filter penyedia, tabel nilai yang bisa diedit, catatan konversi, Reset Default |
| `/perhitungan` | `Perhitungan.vue` | slider λ, Langkah 1 sampai 4, tabel peringkat, grafik |

Aturan frontend:

- Gunakan Vue 3 Composition API dengan `<script setup>`. JavaScript biasa, tanpa TypeScript.
- `useSpk()` menyimpan: `criteria`, `alternatives`, `lambda`, `provider`, `result`. Setiap perubahan bobot, atribut, nilai, λ, atau filter memanggil ulang `/api/waspas`.
- Slider λ dan input angka di-**debounce 300 ms** sebelum memanggil API. Simpan perubahan input saat `change` atau debounce, bukan di setiap ketukan tombol.
- Input angka menerima koma dan titik desimal. Nilai tidak valid diberi border `border-rose-400` dan tidak dikirim.
- Tampilan: harga `Rp 21.400` (locale `id-ID`); nilai R, SAW, WP, dan Q dengan 4 desimal.
- `RankingChart.vue` membuat dan menghancurkan instance Chart.js lewat `onMounted` dan `onBeforeUnmount`.
- Teks rumus ditulis dengan HTML (`Q<sub>i</sub>`, `&lambda;`, `&Sigma;`, `&Pi;`), tanpa library LaTeX.
- Ikon: paket `lucide-vue-next`.

## 10. Desain UI (wajib sama dengan `docs/referensi/coba1.html`)

Desain mengikuti file referensi persis. **Saat ragu, salin string class Tailwind dari `coba1.html`.** Pakai Tailwind **v3.4** (bukan v4) supaya nama class dan nilai bawaannya sama. Hanya tema terang.

- Font: **Inter** (300 sampai 700) dari Google Fonts. `body` memakai `bg-slate-50 text-slate-800`.
- Header: `bg-indigo-600 text-white shadow-md sticky top-0 z-50`; ikon di `bg-white/10 p-2.5 rounded-xl` (ikon `Server`); judul `text-xl font-bold tracking-tight` (**HOSTPAS**); subjudul `text-xs text-indigo-200` (**Sistem Pendukung Keputusan Pemilihan Hosting Terbaik**).
- Wadah tab: `bg-indigo-700/50 p-1.5 rounded-xl border border-indigo-500/30`.
  - Tab aktif: `bg-white text-indigo-700 shadow-sm`.
  - Tab biasa: `text-indigo-100 hover:bg-indigo-600`.
  - Semua tab: `px-4 py-2 text-sm font-medium rounded-lg transition-all flex items-center gap-2`.
- Konten utama: `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8`, antar-blok `space-y-6`.
- Hero Beranda: `bg-gradient-to-r from-indigo-900 to-indigo-700 rounded-2xl p-8 text-white shadow-xl`, lencana `bg-indigo-500/40 text-indigo-200 text-xs font-semibold px-3 py-1 rounded-full`, kotak statistik kanan `bg-white/10 p-6 rounded-2xl border border-white/15 backdrop-blur-md`.
- Kartu: `bg-white rounded-2xl shadow-sm border border-slate-200 p-6`.
- Ikon kartu ringkasan: `p-3.5 rounded-xl` dengan warna `bg-emerald-100 text-emerald-600`, `bg-blue-100 text-blue-600`, `bg-purple-100 text-purple-600`.
- Tabel: `w-full text-left border-collapse text-sm`; `thead` = `bg-slate-50 text-slate-500 uppercase text-xs font-semibold tracking-wider`; `tbody` = `divide-y divide-slate-100`; baris = `hover:bg-slate-50 transition`; sel `px-4 py-3` atau `px-6 py-3`; dibungkus `overflow-x-auto`.
- Tombol utama: `bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl transition flex items-center gap-2 shadow-sm`.
- Tombol sekunder (Reset Default): `bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm px-4 py-2.5 rounded-xl transition flex items-center gap-2`.
- Input: `px-3 py-1.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm`. Slider: `accent-indigo-600`.
- Lencana status:
  - Bobot valid: `px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full`.
  - Bobot tidak valid: `px-3 py-1 bg-rose-100 text-rose-800 text-xs font-bold rounded-full`.
  - Atribut: Benefit `bg-emerald-100 text-emerald-800`, Cost `bg-amber-100 text-amber-800` (`px-2.5 py-1 rounded-full text-xs font-semibold`).
  - Peringkat 1: `bg-emerald-100 text-emerald-800 border border-emerald-200`; lainnya `bg-slate-100 text-slate-700`.
- Lencana langkah: `bg-indigo-100 text-indigo-700 text-xs px-2.5 py-1 rounded-full font-bold` (Langkah 1 sampai 3); Langkah 4 memakai `bg-emerald-100 text-emerald-800`.
- Kotak info: `bg-indigo-50 border border-indigo-100 rounded-xl p-4`, teks `text-indigo-900`.
- Grafik: batang `rgba(99, 102, 241, 0.8)`, garis `rgb(99, 102, 241)`, `borderRadius: 8`, legenda mati, grid `rgba(226, 232, 240, 0.6)`, tinggi `h-80` (`relative`, `maintainAspectRatio: false`).
- Ikon (Lucide): `server`, `layout-dashboard`, `list-filter`, `hard-drive`, `calculator`, `play`, `settings-2`, `cpu`, `award`, `layers`, `sliders`, `bar-chart-3`, `arrow-right`, `info`, `rotate-ccw`.
- Responsif: header menumpuk di layar kecil (`flex-col sm:flex-row`), semua tabel scroll horizontal, kartu ringkasan `grid-cols-1 md:grid-cols-3`, SAW dan WP berdampingan `md:grid-cols-2`.

## 11. Pengujian dan angka acuan

Wajib ada dan harus lulus sebelum dianggap selesai.

**Unit test `WaspasServiceTest`** — contoh kecil yang bisa dihitung tangan. Kriteria: C1 benefit (w 0,6), C2 cost (w 0,4). Alternatif A = (10, 4), B = (5, 2), λ = 0,5.

- Pembanding: max C1 = 10, min C2 = 2.
- R: A = (1; 0,5), B = (0,5; 1).
- SAW: A = 0,8; B = 0,7.
- WP: A ≈ 0,7579; B ≈ 0,6598.
- **Q: A ≈ 0,7789 (peringkat 1); B ≈ 0,6799 (peringkat 2).**

Tambahan: λ = 1 menghasilkan Q = SAW, λ = 0 menghasilkan Q = WP; alternatif bernilai kosong masuk `excluded`; kurang dari 2 alternatif lengkap menghasilkan `ok: false`; bobot total ≠ 1 tetap menghitung dengan bobot ternormalisasi dan `weights_valid = false`.

**Feature test `WaspasApiTest`** — dengan data seeder dan bobot awal, λ = 0,5, semua penyedia:

| Peringkat | Alternatif | Q |
|---|---|---|
| 1 | Rumahweb Corporate / Pro | 0,6179 |
| 2 | Hostinger Business | 0,5902 |
| 3 | Hostinger Cloud Startup | 0,5887 |
| 4 | Niagahoster Personal / Business | 0,5746 |
| 5 | Niagahoster Cloud Startup | 0,4863 |

Dengan filter `provider=Hostinger`, peringkat 1 adalah Hostinger Business dengan Q ≈ 0,6413. Bandingkan dengan toleransi 0,0001.

Uji juga validasi: bobot ≤ 0 atau > 1 ditolak (422), atribut selain `benefit` atau `cost` ditolak, nilai negatif ditolak.

## 12. Aturan kerja untuk Claude

Urutan pengerjaan (selesaikan dan jalankan tes tiap tahap sebelum lanjut):

1. **Inisialisasi Proyek & Lingkungan**:
   - Inisialisasi framework Laravel (PHP 8.2+) di direktori ini tanpa menghapus `CLAUDE.md`.
   - Jalankan `php artisan install:api` (bila menggunakan Laravel 11+) agar file routing `routes/api.php` dan endpoint API siap digunakan.
   - Konfigurasi `.env`: Atur `DB_DATABASE=spk_waspas` (atau `hostpas`), `DB_USERNAME=root`, `DB_PASSWORD=` (sesuai MySQL Laragon).
   - Pasang dependensi frontend: `npm install vue@^3.4.0 @vitejs/plugin-vue vue-router@^4.0.0 lucide-vue-next chart.js`.
   - Pasang Tailwind CSS v3.4 (wajib v3.4, jangan v4): `npm install -D tailwindcss@^3.4.0 postcss autoprefixer`.
   - Inisialisasi Tailwind config: `npx tailwindcss init -p`.
   - Konfigurasi `vite.config.js` untuk plugin Laravel dan `@vitejs/plugin-vue`.
2. Migrasi, model, dan `HostingSeeder` (bagian 5 dan 6).
3. `WaspasService` dan `WaspasServiceTest` (bagian 7 dan 11).
4. Controller, FormRequest, route API, dan `WaspasApiTest` (bagian 8).
5. Kerangka frontend: `App.vue`, header (**HOSTPAS**), router, `useSpk()`, `api.js`.
6. Halaman Kriteria, lalu Alternatif & Nilai, lalu Perhitungan (+ grafik), lalu Beranda.
7. Poles desain dan cocokkan dengan `coba1.html` secara visual.

Aturan:

- Kerjakan hanya fitur 1 sampai 9. Jangan menambah fitur di bagian "Di luar cakupan" tanpa diminta. Kalau menurutmu ada yang kurang, tanyakan dulu.
- Jangan memindahkan logika perhitungan ke frontend, dan jangan menduplikasinya.
- Jangan mengubah angka acuan di bagian 11 supaya tes lulus. Kalau tes gagal, cari bug-nya.
- Tulis kode sederhana dan mudah dibaca. Penulis proyek masih belajar JavaScript lanjutan, jadi hindari trik rumit dan beri komentar singkat berbahasa Indonesia di bagian yang tidak jelas.
- Semua teks UI berbahasa Indonesia. Nama kode berbahasa Inggris.
- Perintah yang boleh dijalankan tanpa bertanya: `php artisan test`, `npm run build`, `php artisan migrate:fresh --seed` di database lokal.

Definisi selesai: semua tes lulus, `npm run build` berhasil tanpa error, empat halaman berfungsi, Reset Default mengembalikan data seeder, dan tampilan cocok dengan `coba1.html`.

## 13. Catatan keamanan dan deploy

- Endpoint tulis (`PUT`, `POST /api/reset`) tidak memakai login karena aplikasi ini untuk demo lokal atau internal. **Jangan dipublikasikan ke internet apa adanya.** Kalau akan dipublikasikan, tambahkan minimal Laravel Sanctum atau HTTP basic auth pada route tulis, dan `throttle` pada `POST /api/reset`.
- Validasi semua input di FormRequest. Jangan percaya data dari browser.
- `.env` tidak boleh masuk repo.
