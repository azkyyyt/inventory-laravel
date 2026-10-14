# DOKUMENTASI LENGKAP & PANDUAN PENGUMPULAN TUGAS
# MINI PROJECT 3 — INVENTORY MANAGEMENT SYSTEM
**Pertemuan 4 (Dasar Bisnis & Database) ➔ Pertemuan 5 (Implementasi Arsitektur Laravel MVC)**

---

## DAFTAR ISI
1. [Tentang Proyek](#1-tentang-proyek)
2. [Pemetaan Relasi Database & Referential Integrity (P4 & P5)](#2-pemetaan-relasi-database--referential-integrity)
3. [Daftar Aturan Bisnis (Business Rules Wajib)](#3-daftar-aturan-bisnis-business-rules-wajib)
4. [Arsitektur MVC & Siklus Alur Request](#4-arsitektur-mvc--siklus-alur-request)
5. [Struktur Folder & Penjelasan File](#5-struktur-folder--penjelasan-file)
6. [Panduan Langkah Instalasi & Menjalankan Proyek](#6-panduan-langkah-instalasi--menjalankan-proyek)
7. [Akun Pengujian (Demo Credentials)](#7-akun-pengujian-demo-credentials)
8. [Checklist Evaluasi Kelengkapan Pertemuan 5 (Slide 46)](#8-checklist-evaluasi-kelengkapan-pertemuan-5-slide-46)
9. [Panduan Screenshot Pengujian & Demo Fitur (Checklist Tugas)](#9-panduan-screenshot-pengujian--demo-fitur)

---

## 1. TENTANG PROYEK
Aplikasi ini adalah **Inventory Management System** yang dirancang untuk mengelola master data persediaan barang secara terintegrasi, aman, dan konsisten.
Aplikasi ini memindahkan (*migrasi/refactoring*) logika bisnis dari **PHP Native (Pertemuan 4)** ke kerangka kerja **Laravel (Pertemuan 5)** menggunakan pola **MVC (Model-View-Controller)**, Eloquent ORM, Database Migration, Seeder, dan Blade Templating.

---

## 2. PEMETAAN RELASI DATABASE & REFERENTIAL INTEGRITY

Sesuai dengan skema database Pertemuan 4 (Slide 5-11) yang dimigrasikan ke Laravel Migrations (Pertemuan 5, Slide 26):

```text
users (1) ────────── (1) user_profiles [One-to-One: user_id + UNIQUE, CASCADE]
users (1) ────────── (N) stock_movements [One-to-Many: RESTRICT]

categories (1) ───── (N) products [One-to-Many: category_id, RESTRICT on delete]

products (N) ─────── (M) suppliers [Many-to-Many via Junction: product_suppliers, CASCADE]

products (1) ─────── (N) stock_movements [One-to-Many: product_id, RESTRICT on delete]
```

### Tabel & Relasi:
1. **`users` ↔ `user_profiles` (One-to-One)**:
   - Foreign key `user_id` pada tabel `user_profiles` bersifat `UNIQUE` dan `ON DELETE CASCADE`.
2. **`categories` ↔ `products` (One-to-Many)**:
   - Satu kategori menampung banyak produk. `category_id` menggunakan `ON DELETE RESTRICT`.
3. **`products` ↔ `suppliers` (Many-to-Many)**:
   - Dihubungkan melalui *junction table* `product_suppliers` dengan *composite primary key* (`product_id`, `supplier_id`) dan menyimpan atribut `supplier_price`.
4. **`products` & `users` ↔ `stock_movements` (Histori Mutasi Stok)**:
   - Mencatat setiap transaksi barang masuk (`IN`) dan keluar (`OUT`) berserta `quantity`, `user_id` petugas, dan catatan.
   - Aturan Integritas: Produk yang memiliki riwayat mutasi stok **dilarang dihapus** (`ON DELETE RESTRICT`).

---

## 3. DAFTAR ATURAN BISNIS (BUSINESS RULES WAJIB)

Sesuai materi Pertemuan 4 Slide 33 & Pertemuan 5 Slide 41:
1. **Keunikan SKU**: Kode SKU produk harus unik di seluruh database (`unique:products,sku`).
2. **Nilai Non-Negatif**: Harga jual (`price`), jumlah mutasi (`quantity`), stok awal (`stock`), dan batas minimum stok (`minimum_stock`) tidak boleh bernilai negatif ($\ge 0$).
3. **Aturan Barang Keluar (Stock OUT)**:
   - Jumlah barang keluar **tidak boleh melebihi stok yang tersedia**.
   - Stok fisik tidak boleh menjadi negatif.
   - Menggunakan query penguncian baris (`lockForUpdate()`) di dalam **Database Transaction (`DB::transaction`)** untuk mencegah *race condition*.
4. **Formula Konsistensi Stok**:
   $$\text{Stok Akhir} = \text{Stok Awal} + \sum \text{Barang Masuk (IN)} - \sum \text{Barang Keluar (OUT)}$$
5. **Integritas Referensial Penghapusan**:
   - Produk yang sudah memiliki histori transaksi di tabel `stock_movements` tidak dapat dihapus sembarangan.
6. **Autentikasi & Otorisasi Hak Akses (RBAC)**:
   - Halaman internal mewajibkan login via sesi.
   - Password disimpan menggunakan algoritma hashing aman (`bcrypt` / `password_hash`).
   - Role **Admin**: Memiliki akses penuh (Kelola Pengguna, Master Kategori, Master Supplier, Master Produk, dan Transaksi).
   - Role **Staff**: Hanya dapat melihat katalog produk dan mencatat transaksi barang masuk/keluar. Dilarang mengakses menu kelola pengguna (`HTTP 403 Forbidden`).
7. **Sorting Allowlist**:
   - Kolom pengurutan data dibatasi hanya pada kolom yang diizinkan (`id`, `name`, `sku`, `price`, `stock`) untuk mencegah eksploitasi SQL.
8. **Keamanan Tambahan**:
   - Mencegah SQL Injection dengan prepared statements Eloquent.
   - Mencegah XSS dengan auto-escaping Blade `{{ $variable }}`.
   - Mencegah CSRF dengan token `@csrf` di setiap form POST/PUT/DELETE.

---

## 4. ARSITEKTUR MVC & SIKLUS ALUR REQUEST

Arsitektur aplikasi mengikuti standar Laravel:
$$\text{Request} \longrightarrow \text{Route} \longrightarrow \text{Controller} \longrightarrow \text{Model} \longrightarrow \text{Database} \longrightarrow \text{Response}$$

1. **Request**: Pengguna mengakses tautan atau menekan tombol simpan form via browser.
2. **Route (`routes/web.php`)**: Memetakan URL dan HTTP method (`GET`, `POST`, `PUT`, `DELETE`) ke Action Controller terkait dengan penamaan *named routes* (misal: `products.index`, `products.store`).
3. **Controller (`app/Http/Controllers/`)**:
   - Menerima request input dan memvalidasi tipe serta semantiknya (`$request->validate()`).
   - Mengatur interaksi ke database melalui Model atau transaksi database (`DB::transaction`).
   - Memilih response yang dikembalikan (View Blade atau Redirect dengan Flash Message).
4. **Model (`app/Models/`)**:
   - Mengatur relasi antar entitas menggunakan Eloquent (`hasMany`, `belongsTo`, `belongsToMany`, `hasOne`).
   - Menjaga *mass assignment* melalui atribut `protected $fillable`.
5. **Database**:
   - MySQL / MariaDB yang dikelola melalui skema migration dan seeder.
6. **Response / View (`resources/views/`)**:
   - Template engine Blade yang menyajikan data ke pengguna menggunakan layout reusable (`layouts/app.blade.php`), direktif `@forelse`, `@if`, `@error`, dan `old()`.

---

## 5. STRUKTUR FOLDER & PENJELASAN FILE

```text
inventory-laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php            # Menangani Login, Logout, Session
│   │   │   ├── DashboardController.php       # Menghitung 5 widget statistik & agregat
│   │   │   ├── ProductController.php         # Resource Controller CRUD Produk + Filter/Search/Sort/Pagination
│   │   │   ├── ProductSupplierController.php # Kelola relasi Many-to-Many Produk-Supplier
│   │   │   ├── CategoryController.php        # Resource Controller Master Kategori
│   │   │   ├── SupplierController.php        # Resource Controller Master Supplier
│   │   │   ├── StockController.php           # Transaksi IN/OUT (DB Transaction) & Histori
│   │   │   └── UserController.php            # Kelola User & Profil (Admin Only)
│   │   └── Middleware/
│   │       ├── AuthMiddleware.php            # Memastikan pengguna telah login
│   │       └── AdminMiddleware.php           # Membatasi akses khusus Role Admin (403 Forbidden)
│   └── Models/
│       ├── User.php                          # Model User
│       ├── UserProfile.php                   # Model UserProfile (Relasi One-to-One)
│       ├── Category.php                      # Model Category (Relasi One-to-Many ke Product)
│       ├── Product.php                       # Model Product (Relasi BelongsTo, BelongsToMany, HasMany)
│       ├── Supplier.php                      # Model Supplier (Relasi Many-to-Many ke Product)
│       └── StockMovement.php                 # Model Histori Mutasi Stok
├── database/
│   ├── factories/                            # Factory dummy data (CategoryFactory, ProductFactory)
│   ├── migrations/                           # 7 file migrasi pembentuk skema database relasional
│   └── seeders/DatabaseSeeder.php            # Seeder data awal uji coba
├── resources/views/
│   ├── layouts/app.blade.php                 # Layout induk UI Bootstrap 5 & navbar navigasi
│   ├── auth/login.blade.php                  # Halaman Login
│   ├── dashboard.blade.php                   # Dashboard 5 widget & laporan pergerakan stok
│   ├── products/                             # Tampilan index, create, edit, dan suppliers
│   ├── categories/                           # Tampilan index, create, edit kategori
│   ├── suppliers/                            # Tampilan index, create, edit supplier
│   ├── stock/                                # Tampilan form barang masuk, keluar, & histori
│   └── users/                                # Tampilan kelola pengguna (Admin)
├── routes/web.php                            # Definisi seluruh rute aplikasi (Resource & Named Routes)
└── .env                                      # Konfigurasi environment (koneksi database & session)
```

---

## 6. PANDUAN LANGKAH INSTALASI & MENJALANKAN PROYEK

Ikuti langkah-langkah berikut untuk menjalankan aplikasi:

### Langkah 1: Buka Terminal di Folder Proyek
Buka PowerShell atau Command Prompt:
```powershell
cd C:\xampp\htdocs\inventory-laravel
```

### Langkah 2: Pastikan Database MySQL Aktif
Pastikan modul **MySQL** pada control panel XAMPP sudah dalam status **Running** (Port `3306`).

### Langkah 3: Konfigurasi File Environment (`.env`)
Pastikan file `.env` memiliki baris konfigurasi berikut:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_laravel
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
```

### Langkah 4: Generate Application Key (Jika Diperlukan)
```powershell
php artisan key:generate
```

### Langkah 5: Eksekusi Migrasi & Pengisian Data Awal (Seeder)
Jalankan perintah ini untuk membuat seluruh tabel dan mengisikan data dummy secara otomatis:
```powershell
php artisan migrate:fresh --seed
```

### Langkah 6: Jalankan Server Lokal Laravel
```powershell
php artisan serve
```
Aplikasi dapat diakses melalui browser pada alamat:
👉 **`http://127.0.0.1:8000`**

---

## 7. AKUN PENGUJIAN (DEMO CREDENTIALS)

| Role | Username | Password | Deskripsi Izin Akses |
|---|---|---|---|
| **Admin** | `admin` | `admin123` | Akses penuh: Dashboard, Master Produk, Kategori, Supplier, Transaksi Stok, dan Menu Pengguna |
| **Staff** | `staff` | `staff123` | Terbatas: Melihat Produk, Mencatat Barang Masuk/Keluar, Melihat Histori Transaksi (Ditolak dari menu Pengguna) |

---

## 8. CHECKLIST EVALUASI KELENGKAPAN PERTEMUAN 5 (SLIDE 46)

Tabel evaluasi mandiri berikut disusun persis berdasarkan **Checklist Pertemuan 5 (Slide 46)** pada silabus perkuliahan untuk membuktikan secara teknis dan faktual bahwa seluruh kriteria kelulusan modul telah diselesaikan 100%:

| No | Kriteria Checklist (Slide 46) | Status | File Implementasi | Bukti & Penjelasan Teknis |
|:--:|---|:---:|---|---|
| **1** | **Project Laravel dapat dijalankan** | `[x] LENGKAP` | `bootstrap/app.php`, `public/index.php` | Project dikembangkan menggunakan arsitektur Laravel standar yang bersih, siap dijalankan melalui `php artisan serve` pada `http://127.0.0.1:8000`. Seluruh bootstrap dependency & autoloader composer terintegrasi penuh. |
| **2** | **.env dan koneksi database benar** | `[x] LENGKAP` | `.env` | Parameter koneksi database di `.env` (`DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=inventory_laravel`, `DB_USERNAME=root`) terhubung langsung ke MySQL XAMPP tanpa error koneksi. |
| **3** | **route:list menampilkan route yang diharapkan** | `[x] LENGKAP` | `routes/web.php` | Perintah `php artisan route:list` memetakan 17+ rute terdaftar dengan *Named Routes* (`products.index`, `products.create`, `products.store`, dll.) dan terproteksi middleware (`auth.custom`, `admin`). |
| **4** | **Migration dapat dijalankan dari database kosong** | `[x] LENGKAP` | `database/migrations/` (7 file) | 7 file migrasi disusun hierarkis dengan urutan dependensi foreign key yang tepat (`users` ➔ `user_profiles` ➔ `categories` ➔ `suppliers` ➔ `products` ➔ `product_suppliers` ➔ `stock_movements`). Berjalan sukses dari nol dengan `php artisan migrate:fresh`. |
| **5** | **Seeder menghasilkan data uji** | `[x] LENGKAP` | `database/seeders/DatabaseSeeder.php`, `database/factories/` | Mengisi data akun (`admin` & `staff`), 4 profil, 4 kategori master, 3 supplier, 6 produk ritel, relasi harga pasokan pivot, dan riwayat mutasi stok awal secara otomatis. |
| **6** | **Model memiliki relationship yang benar** | `[x] LENGKAP` | `app/Models/*.php` | • `User` ➔ `UserProfile` (`hasOne` / `belongsTo`)<br>• `Category` ➔ `Product` (`hasMany` / `belongsTo`)<br>• `Product` ➔ `Supplier` (`belongsToMany` via `product_suppliers` + pivot `supplier_price`)<br>• `Product` & `User` ➔ `StockMovement` (`hasMany` / `belongsTo`). |
| **7** | **Resource controller menangani CRUD** | `[x] LENGKAP` | `ProductController.php`, `CategoryController.php`, `SupplierController.php`, `UserController.php` | Controller menggunakan standar method RESTful (`index`, `create`, `store`, `edit`, `update`, `destroy`) yang mengorkestrasi logika CRUD ke Eloquent ORM secara rapi dan terisolasi. |
| **8** | **Blade memakai layout dan escaping** | `[x] LENGKAP` | `resources/views/layouts/app.blade.php`, `resources/views/**/*.blade.php` | Seluruh halaman menginduk pada layout reusable `@extends('layouts.app')` dengan `@section('content')`. Seluruh pencetakan variabel memakai kurung kurawal ganda `{{ $var }}` yang otomatis memanggil `htmlspecialchars()` anti-XSS. |
| **9** | **Form memiliki @csrf dan validasi** | `[x] LENGKAP` | Seluruh form Blade & `ProductController@store` dkk | Seluruh form input (`POST`, `PUT`, `DELETE`) dilindungi token `@csrf`. Controller menerapkan `$request->validate([...])` dengan umpan balik pesan error `@error` dan mempertahankan teks input via `old()`. |
| **10** | **Search dan pagination mempertahankan query string** | `[x] LENGKAP` | `ProductController.php` (L38) & `products/index.blade.php` | Pemanggilan `$query->paginate(10)->withQueryString()` memastikan parameter pencarian (`search`), filter kategori (`category_id`), dan pengurutan (`sort`, `dir`) tidak hilang saat berpindah antar nomor halaman pagination. |
| **11** | **Inventory Management System P4 berhasil dipindahkan** | `[x] LENGKAP` | Seluruh modul sistem | Seluruh aturan bisnis P4 aktif di Laravel: pencegahan stok negatif, penguncian baris (`lockForUpdate`), Database Transaction (`DB::transaction`), referential integrity (`RESTRICT`), RBAC Admin/Staff, dan 5 widget analitik dashboard. |

---

### Rincian Pembuktian Masing-Masing Poin Checklist:

#### 1. Project Laravel Dapat Dijalankan
- **Mekanisme**: Menggunakan standar runtime framework Laravel dengan public document root `public/index.php`.
- **Verifikasi**: Jalankan perintah `php artisan serve`. Terminal akan menampilkan pesan:
  ```text
  INFO  Server running on [http://127.0.0.1:8000].
  ```
- **Hasil**: Aplikasi dapat diakses dengan lancar tanpa error dependensi.

#### 2. `.env` dan Koneksi Database Benar
- **Mekanisme**: Konfigurasi koneksi MySQL terisolasi di file `.env`:
  ```dotenv
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=inventory_laravel
  DB_USERNAME=root
  DB_PASSWORD=
  ```
- **Verifikasi**: Pengujian koneksi PDO berhasil dan Laravel dapat melakukan query read/write ke basis data MariaDB/MySQL XAMPP.

#### 3. `route:list` Menampilkan Route yang Diharapkan
- **Mekanisme**: Rute dikelompokkan berdasarkan otentikasi menggunakan middleware `auth.custom` dan `admin`.
- **Verifikasi**: Jalankan `php artisan route:list`.
- **Hasil**: Menampilkan rute CRUD produk (`products.index`, `products.create`, `products.store`, `products.edit`, `products.update`, `products.destroy`), master kategori, supplier, transaksi stok masuk/keluar/histori, dashboard, otentikasi, dan kelola user.

#### 4. Migration Dapat Dijalankan dari Database Kosong
- **Mekanisme**: 7 berkas migrasi diatur urutannya menggunakan timestamp agar tabel induk selalu di-create sebelum tabel anak:
  1. `2024_01_01_000001_create_users_table.php` (Tabel Induk User)
  2. `2024_01_01_000002_create_user_profiles_table.php` (Foreign Key `user_id` ➔ `users`)
  3. `2024_01_01_000003_create_categories_table.php` (Tabel Induk Kategori)
  4. `2024_01_01_000004_create_suppliers_table.php` (Tabel Induk Supplier)
  5. `2024_01_01_000005_create_products_table.php` (Foreign Key `category_id` ➔ `categories`)
  6. `2024_01_01_000006_create_product_suppliers_table.php` (Junction Table M:N `product_id` & `supplier_id`)
  7. `2024_01_01_000007_create_stock_movements_table.php` (Histori Mutasi, FK `product_id` & `user_id`)
- **Verifikasi**: Perintah `php artisan migrate:fresh` berhasil 100% tanpa error constraint violation.

#### 5. Seeder Menghasilkan Data Uji
- **Mekanisme**: File `database/seeders/DatabaseSeeder.php` mengisi database dengan dataset realistis yang persis dengan schema P4:
  - Akun Administrator (`admin` / `admin123`) & Staff Gudang (`staff` / `staff123`).
  - 4 Kategori: *Elektronik, Perabot, Aksesoris, Alat Tulis*.
  - 3 Supplier: *PT Sumber Makmur, CV Maju Jaya, Global Tech Supplies*.
  - 6 Master Produk awal (Laptop Asus, Mouse Wireless, Keyboard Mechanical, Meja Kerja, Kursi Ergonomis, Monitor LED).
  - Data pemasok produk & mutasi stok awal.
- **Verifikasi**: Jalankan `php artisan db:seed`.

#### 6. Model Memiliki Relationship yang Benar
- **Mekanisme**: Seluruh relasi diatur melalui Eloquent ORM:
  - `User.php`: `hasOne(UserProfile::class)` & `hasMany(StockMovement::class)`
  - `UserProfile.php`: `belongsTo(User::class)`
  - `Category.php`: `hasMany(Product::class)`
  - `Product.php`: `belongsTo(Category::class)`, `belongsToMany(Supplier::class, 'product_suppliers')->withPivot('supplier_price')`, `hasMany(StockMovement::class)`
  - `Supplier.php`: `belongsToMany(Product::class, 'product_suppliers')->withPivot('supplier_price')`
  - `StockMovement.php`: `belongsTo(Product::class)` & `belongsTo(User::class)`
- **Verifikasi**: Lazy / Eager loading `$product->category->name` dan `$product->suppliers` bekerja tanpa query manual SQL.

#### 7. Resource Controller Menangani CRUD
- **Mekanisme**: `ProductController.php` mengimplementasikan method aksi CRUD RESTful secara penuh:
  - `index()`: Menampilkan data dengan pencarian, filter, dan pagination.
  - `create()`: Menampilkan form tambah produk beserta daftar kategori.
  - `store()`: Validasi payload request dan menyimpan record baru (`Product::create()`).
  - `edit()`: Mengambil data produk lama dan menampilkan form ubah.
  - `update()`: Validasi payload dan memperbarui record (`$product->update()`).
  - `destroy()`: Memeriksa referential integrity sebelum menghapus produk (`$product->delete()`).

#### 8. Blade Memakai Layout dan Escaping
- **Mekanisme**:
  - File `resources/views/layouts/app.blade.php` bertindak sebagai *Master Template* (Navbar, Alert Flash Message, Kontainer Bootstrap 5, Footer).
  - Setiap view anak memakai `@extends('layouts.app')` dan `@section('content')`.
  - Semua pencetakan teks menggunakan kurung kurawal ganda `{{ ... }}` yang secara otomatis menerapkan sanitasi `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` sehingga kebal dari serangan Cross-Site Scripting (XSS).

#### 9. Form Memiliki `@csrf` dan Validasi
- **Mekanisme**:
  - Setiap form `POST`, `PUT`, `DELETE` memuat direktif `@csrf` untuk mencegah eksploitasi Cross-Site Request Forgery.
  - Di controller, input divalidasi ketat:
    ```php
    $request->validate([
        'sku'           => 'required|unique:products,sku,' . $id,
        'name'          => 'required|string|max:150',
        'category_id'   => 'required|exists:categories,id',
        'price'         => 'required|numeric|min:0',
        'stock'         => 'required|integer|min:0',
        'minimum_stock' => 'required|integer|min:0',
    ]);
    ```
  - Pada Blade, pesan kesalahan ditampilkan secara spesifik di bawah input terkait dengan `@error('field') <div class="invalid-feedback">{{ $message }}</div> @enderror` dan mempertahankan teks sebelumnya menggunakan fungsi helper `old('field')`.

#### 10. Search dan Pagination Mempertahankan Query String
- **Mekanisme**:
  - Di `ProductController.php` baris 38:
    ```php
    $products = $query->paginate(10)->withQueryString();
    ```
  - Method `withQueryString()` secara otomatis mengikat seluruh parameter URL aktif (`search`, `category_id`, `sort`, `dir`) ke dalam link tautan pagination.
- **Verifikasi**: Saat pengguna mencari kata "Mouse" lalu mengklik halaman 2, URL browser tetap memuat parameter `http://127.0.0.1:8000/products?search=Mouse&page=2` tanpa kehilangan filter pencarian.

#### 11. Inventory Management System P4 Berhasil Dipindahkan
- **Mekanisme**: Logika bisnis penting dari sistem PHP Native P4 berhasil dikonversi ke fitur standar Laravel:
  - **Integritas Transaksi Mutasi**: Pengurangan stok (Stock OUT) di `StockController.php` dibungkus di dalam `DB::transaction()` dan menggunakan *Pessimistic Locking* (`lockForUpdate()`) guna mencegah stok menjadi minus akibat konkurensi data (*race condition*).
  - **Referential Integrity**: Menghapus produk yang sudah memiliki riwayat mutasi diblokir dengan pesan error ramah pengguna.
  - **Dashboard Analitik**: 5 widget kartu metrik dan tabel rekonsiliasi agregat SQL P4 (Slide 29) ditampilkan secara presisi.
  - **RBAC**: Pemisahan hak akses role `admin` dan `staff` ditegakkan lewat `AdminMiddleware`.

---

## 9. PANDUAN SCREENSHOT PENGUJIAN & DEMO FITUR

Untuk melengkapi berkas pengumpulan tugas (*Deliverable* Slide 48 P5 & Slide 38 P4), ambil screenshot layar berikut:

### 📸 Screenshot 1: Daftar Rute Aplikasi (`route:list`)
- **Cara melihat**: Jalankan di terminal:
  ```powershell
  php artisan route:list
  ```
- **Objek tangkapan layar**: Tampilan daftar resource route dan named route di terminal.

---

### 📸 Screenshot 2: Halaman Login & Hashing
- **Cara melihat**: Buka `http://127.0.0.1:8000/login`.
- **Objek tangkapan layar**: Tampilan form login dengan info demo akun admin dan staff.

---

### 📸 Screenshot 3: Dashboard Ringkasan & Laporan Agregat (Slide 29 P4)
- **Cara melihat**: Login sebagai `admin`, lalu akses `http://127.0.0.1:8000/dashboard`.
- **Objek tangkapan layar**:
  1. Lima widget ringkasan: **Total Produk**, **Nilai Inventory**, **Stok Rendah**, **Barang Masuk (IN)**, dan **Barang Keluar (OUT)**.
  2. Tabel **Peringatan Produk Stok Rendah** (`stock <= minimum_stock`).
  3. Tabel **Laporan Rincian Inventory & Pergerakan Stok** berbasis fungsi agregat SQL.

---

### 📸 Screenshot 4: Listing Produk (Search, Filter, Sorting, Pagination)
- **Cara melihat**: Buka menu **Produk** (`http://127.0.0.1:8000/products`).
- **Objek tangkapan layar**:
  1. Fitur pencarian kata kunci nama/SKU.
  2. Filter *dropdown* kategori.
  3. Pengurutan data (*Sorting*) naik/turun (ASC/DESC).
  4. Kontrol navigasi *Pagination* di bagian bawah tabel.

---

### 📸 Screenshot 5: Relasi Many-to-Many Produk ↔ Supplier
- **Cara melihat**: Pada daftar produk, klik tombol **Supplier** pada salah satu baris produk (misal: Keyboard Mechanical).
- **Objek tangkapan layar**: Halaman kelola supplier terhubung dengan tabel pasokan harga beli dan form penghubung supplier baru.

---

### 📸 Screenshot 6: Demonstrasi Satu Alur Lengkap CRUD Produk
*(Kebutuhan Utama Slide 48 P5)*
1. **Buka Form Tambah**: Klik tombol **Tambah Produk** (`http://127.0.0.1:8000/products/create`).
   - *Ambil screenshot form kosong*.
2. **Uji Validasi Error**: Kosongkan nama/SKU atau masukkan harga negatif, lalu tekan tombol **Simpan Produk**.
   - *Ambil screenshot notifikasi peringatan error merah* (`@error`).
3. **Penyimpanan Berhasil**: Masukkan data yang benar, lalu klik **Simpan Produk**.
4. **Redirect & Tampil**: Browser otomatis dialihkan ke halaman tabel produk dengan pesan alert hijau *"Produk ... berhasil ditambahkan."* dan baris produk baru terlihat.
   - *Ambil screenshot pesan sukses dan data yang baru bertambah*.

---

### 📸 Screenshot 7: Transaksi Barang Masuk (IN) & Barang Keluar (OUT)
- **Barang Masuk**: Buka menu **Transaksi ➔ Barang Masuk** (`http://127.0.0.1:8000/stock/in`). Catat penambahan unit.
- **Barang Keluar**: Buka menu **Transaksi ➔ Barang Keluar** (`http://127.0.0.1:8000/stock/out`).
  - *Uji Kasus Error Stok*: Masukkan jumlah melebihi stok yang tersedia (misal: 9999). Ambil screenshot penolakan transaksi: *"Jumlah barang keluar melebihi stok yang tersedia."*
- **Histori Transaksi**: Buka menu **Transaksi ➔ Histori Transaksi** (`http://127.0.0.1:8000/stock/history`). Ambil screenshot mutasi stok lengkap dengan badge hijau MASUK dan badge merah KELUAR.

---

### 📸 Screenshot 8: Uji Kasus Akses Terlarang (Role Authorization 403)
- **Cara melihat**:
  1. Klik tombol **Logout**, kemudian login menggunakan akun **staff** (`staff` / `staff123`).
  2. Coba akses URL administrasi pengguna secara langsung di address bar browser:
     `http://127.0.0.1:8000/users`
- **Objek tangkapan layar**: Tampilan pesan penolakan server **403 Forbidden** (*"Anda tidak memiliki izin untuk mengakses halaman ini"*).

---

### 📸 Screenshot 9: Uji Kasus Integritas Referensial (Hapus Produk Berhistori)
- **Cara melihat**: Login kembali sebagai `admin`, buka menu **Produk**, lalu coba hapus produk yang telah memiliki riwayat transaksi (misal: `Keyboard Mechanical RGB`).
- **Objek tangkapan layar**: Pesan penolakan merah:
  *"Gagal menghapus produk ... Produk ini telah memiliki histori transaksi barang masuk/keluar (Referential Integrity Constraint)."*
