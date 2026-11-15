# 📦 Simple Courier API Documentation

Dokumentasi lengkap RESTful API untuk manajemen data kurir berbasis **Laravel 11 / 12**.

---

## 📋 Daftar Isi

1. [Prasyarat Sistem](#1-prasyarat-sistem)
2. [Instalasi & Inisialisasi Project](#2-instalasi--inisialisasi-project)
3. [Inisialisasi Database, Migration & Seeding](#3-inisialisasi-database-migration--seeding)
4. [Menjalankan Development Server](#4-menjalankan-development-server)
5. [Struktur Data & Validasi](#5-struktur-data--validasi)
6. [Dokumentasi Endpoint API](#6-dokumentasi-endpoint-api)
    - [6.1 Ambil Semua Kurir](#61-ambil-semua-kurir)
    - [6.2 Pagination](#62-pagination)
    - [6.3 Sorting Berdasarkan Nama](#63-sorting-berdasarkan-nama)
    - [6.4 Sorting Berdasarkan Tanggal](#64-sorting-berdasarkan-tanggal)
    - [6.5 Pencarian (Multi-keyword Search)](#65-pencarian-multi-keyword-search)
    - [6.6 Filter Berdasarkan Level](#66-filter-berdasarkan-level)
    - [6.7 Kombinasi Search, Level & Sort](#67-kombinasi-search-level--sort)
    - [6.8 Detail Kurir](#68-detail-kurir)
    - [6.9 Tambah Kurir Baru](#69-tambah-kurir-baru)
    - [6.10 Update Data Kurir](#610-update-data-kurir)
    - [6.11 Hapus Kurir](#611-hapus-kurir)
7. [Pengujian Otomatis (Automated Testing)](#7-pengujian-otomatis-automated-testing)

---

## 1. Prasyarat Sistem

Pastikan environment Anda telah terpasang:

- **PHP** >= 8.2 dengan ekstensi `pdo`, `pdo_mysql` / `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl`.
- **Composer** v2.x
- **Database Server**: MySQL / MariaDB (misalnya via Laragon, XAMPP, atau Docker)

---

## 2. Instalasi & Inisialisasi Project

### Langkah 1: Clone Repository

```bash
git clone https://github.com/Adesvian/basic-l13-api.git simple-courier-api
cd simple-courier-api
```

### Langkah 2: Install Dependensi PHP

```bash
composer install
```

### Langkah 3: Konfigurasi File Environment (`.env`)

Salin file `.env.example` menjadi `.env`.

**Linux / macOS / Git Bash:**

```bash
cp .env.example .env
```

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
```

### Langkah 4: Generate Application Key

```bash
php artisan key:generate
```

---

## 3. Inisialisasi Database, Migration & Seeding

### Langkah 1: Konfigurasi Koneksi Database

Buka file `.env` dan sesuaikan pengaturan database Anda:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=root
DB_PASSWORD=
```

> **Catatan:** untuk state awal DB_DATABASE bisa langsung di isi melalui env atau bisa melalui command `php artisan db:initialize {nama_db_yang_diinginkan}` yang mana nanti command tersebut akan membuatkan db di root acces mysql anda.

### Langkah 2: Jalankan Migration & Seeding

Jalankan perintah berikut untuk mengeksekusi migrasi tabel dan mengisi data awal (_Level_ dan kurir bawaan):

```bash
php artisan migrate --seed
```

Untuk mereset database dari nol kapan saja:

```bash
php artisan migrate:fresh --seed
```

---

## 4. Menjalankan Development Server

Jalankan server bawaan Laravel:

```bash
php artisan serve
```

Aplikasi aktif di `http://127.0.0.1:8000` (Base URL API: `http://127.0.0.1:8000/api`).

> **Penting untuk semua request API**, selalu sertakan header:
>
> ```http
> Accept: application/json
> Content-Type: application/json
> ```

---

## 5. Struktur Data & Validasi

### Fields Kurir

| Field        | Tipe      | Status    | Aturan Validasi                                             |
| :----------- | :-------- | :-------- | :---------------------------------------------------------- |
| `first_name` | `string`  | **Wajib** | Maks. 150 karakter                                          |
| `last_name`  | `string`  | **Wajib** | Maks. 150 karakter                                          |
| `phone`      | `string`  | Opsional  | Maks. 20 karakter                                           |
| `email`      | `string`  | Opsional  | Format email valid, unik di tabel kurir, maks. 150 karakter |
| `address`    | `string`  | Opsional  | Teks bebas                                                  |
| `level_id`   | `integer` | **Wajib** | Harus ada di tabel `levels` (ID 1 - 5)                      |
| `is_active`  | `boolean` | **Wajib** | `true` (aktif) atau `false` (nonaktif)                      |

---

## 6. Dokumentasi Endpoint API

### 6.1 Ambil Semua Kurir

Mengambil daftar kurir dengan konfigurasi default (diurutkan berdasarkan nama).

- **URL:** `/api/couriers`
- **Method:** `GET`

**Success Response (`200 OK`)**

```json
{
    "message": "Couriers retrieved successfully.",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "first_name": "Agung",
                "last_name": "Pratama",
                "phone": "081234567890",
                "email": "agung.pratama@example.com",
                "address": "Jl. Sudirman No. 10, Jakarta",
                "level_id": 1,
                "is_active": true,
                "created_at": "2026-11-15T14:00:00.000000Z",
                "updated_at": "2026-11-15T14:00:00.000000Z",
                "level": {
                    "id": 1,
                    "name": "Level 1"
                }
            }
        ],
        "first_page_url": "http://127.0.0.1:8000/api/couriers?page=1",
        "from": 1,
        "last_page": 5,
        "per_page": 10,
        "total": 50
    }
}
```

---

### 6.2 Pagination

Mengatur jumlah data per halaman dan memilih nomor halaman.

- **URL:** `/api/couriers?per_page=10&page=2`
- **Method:** `GET`
- **Query Params:**
    - `per_page`: jumlah item per halaman (min: 1, maks: 100)
    - `page`: nomor halaman

**Success Response (`200 OK`)**

```json
{
    "message": "Couriers retrieved successfully.",
    "data": {
        "current_page": 2,
        "data": [
            {
                "id": 11,
                "first_name": "Budi",
                "last_name": "Santoso",
                "phone": "081298765432",
                "email": "budi.santoso@example.com",
                "address": "Jl. Thamrin No. 5, Jakarta",
                "level_id": 2,
                "is_active": true,
                "created_at": "2026-11-15T14:10:00.000000Z",
                "updated_at": "2026-11-15T14:10:00.000000Z",
                "level": {
                    "id": 2,
                    "name": "Level 2"
                }
            }
        ],
        "per_page": 10,
        "total": 50
    }
}
```

**Failed Response (`422 Unprocessable Entity`)**

Contoh query: `?per_page=200`

```json
{
    "message": "The per page field must not be greater than 100.",
    "errors": {
        "per_page": ["The per page field must not be greater than 100."]
    }
}
```

---

### 6.3 Sorting Berdasarkan Nama

Mengurutkan kurir berdasarkan `first_name`, lalu `last_name` (A-Z).

- **URL:** `/api/couriers?sort=name`
- **Method:** `GET`

**Success Response (`200 OK`)**

```json
{
    "message": "Couriers retrieved successfully.",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 5,
                "first_name": "Agus",
                "last_name": "Kurniawan",
                "level_id": 1
            },
            {
                "id": 9,
                "first_name": "Bambang",
                "last_name": "Pamungkas",
                "level_id": 3
            }
        ]
    }
}
```

---

### 6.4 Sorting Berdasarkan Tanggal

Mengurutkan kurir berdasarkan tanggal pendaftaran terbaru (`created_at` descending).

- **URL:** `/api/couriers?sort=created_at`
- **Method:** `GET`

**Success Response (`200 OK`)**

```json
{
    "message": "Couriers retrieved successfully.",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 50,
                "first_name": "Zaky",
                "last_name": "Firmansyah",
                "created_at": "2026-11-15T20:30:00.000000Z"
            },
            {
                "id": 49,
                "first_name": "Yoga",
                "last_name": "Pratama",
                "created_at": "2026-11-15T19:00:00.000000Z"
            }
        ]
    }
}
```

---

### 6.5 Pencarian (Multi-keyword Search)

Mencari kurir berdasarkan potongan nama depan atau nama belakang. Beberapa kata kunci dipisahkan dengan spasi (`+` pada URL).

- **URL:** `/api/couriers?search=budi+agung`
- **Method:** `GET`

**Success Response (`200 OK`)**

```json
{
    "message": "Couriers retrieved successfully.",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 2,
                "first_name": "Budi",
                "last_name": "Agung",
                "email": "budi.agung@example.com"
            }
        ],
        "total": 1
    }
}
```

---

### 6.6 Filter Berdasarkan Level

Menyaring kurir berdasarkan satu atau beberapa ID level (dipisahkan koma).

- **URL:** `/api/couriers?level=2,3`
- **Method:** `GET`

**Success Response (`200 OK`)**

```json
{
    "message": "Couriers retrieved successfully.",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 14,
                "first_name": "Doni",
                "last_name": "Kusuma",
                "level_id": 2,
                "level": { "id": 2, "name": "Level 2" }
            },
            {
                "id": 18,
                "first_name": "Eko",
                "last_name": "Patrio",
                "level_id": 3,
                "level": { "id": 3, "name": "Level 3" }
            }
        ]
    }
}
```

**Failed Response (`422 Unprocessable Entity`)**

Contoh query: `?level=abc` atau `?level=9`

```json
{
    "message": "The level field format is invalid.",
    "errors": {
        "level": ["The level field format is invalid."]
    }
}
```

---

### 6.7 Kombinasi Search, Level & Sort

Menggabungkan pencarian kata kunci, filter level, dan sorting sekaligus.

- **URL:** `/api/couriers?search=ag+sa&level=2,3&sort=created_at`
- **Method:** `GET`

**Success Response (`200 OK`)**

```json
{
    "message": "Couriers retrieved successfully.",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 21,
                "first_name": "Agung",
                "last_name": "Santoso",
                "level_id": 2,
                "created_at": "2026-11-15T18:00:00.000000Z",
                "level": {
                    "id": 2,
                    "name": "Level 2"
                }
            }
        ],
        "total": 1
    }
}
```

---

### 6.8 Detail Kurir

Mengambil satu data kurir berdasarkan ID.

- **URL:** `/api/couriers/{id}` (contoh: `/api/couriers/25`)
- **Method:** `GET`

**Success Response (`200 OK`)**

```json
{
    "message": "Courier retrieved successfully.",
    "data": {
        "id": 25,
        "first_name": "Rian",
        "last_name": "Ardianto",
        "phone": "085712345678",
        "email": "rian.ardianto@example.com",
        "address": "Jl. Gatot Subroto No. 25, Bandung",
        "level_id": 2,
        "is_active": true,
        "created_at": "2026-11-15T10:00:00.000000Z",
        "updated_at": "2026-11-15T10:00:00.000000Z",
        "level": {
            "id": 2,
            "name": "Level 2"
        }
    }
}
```

**Failed Response (`200 OK`, data tidak ditemukan)**

Jika ID tidak ditemukan di database:

```json
{
    "message": "Courier not found.",
    "data": []
}
```

---

### 6.9 Tambah Kurir Baru

Menambahkan entri kurir baru ke dalam sistem.

- **URL:** `/api/couriers`
- **Method:** `POST`

**Request Body (valid)**

```json
{
    "first_name": "Don Pablo",
    "last_name": "Escobar",
    "phone": "081234567890",
    "email": "don.pablo@example.com",
    "address": "Medellin, Colombia",
    "level_id": 1,
    "is_active": true
}
```

**Success Response (`201 Created`)**

```json
{
    "message": "Courier created successfully.",
    "data": {
        "id": 51,
        "first_name": "Don Pablo",
        "last_name": "Escobar",
        "phone": "081234567890",
        "email": "don.pablo@example.com",
        "address": "Medellin, Colombia",
        "level_id": 1,
        "is_active": true,
        "created_at": "2026-11-15T22:30:00.000000Z",
        "updated_at": "2026-11-15T22:30:00.000000Z",
        "level": {
            "id": 1,
            "name": "Level 1"
        }
    }
}
```

**Request Body (invalid)**

```json
{
    "first_name": "",
    "last_name": "Escobar",
    "email": "email-bukan-format-valid",
    "level_id": 999,
    "is_active": null
}
```

**Failed Response (`422 Unprocessable Entity`)**

```json
{
    "message": "The first name field is required. (and 3 other errors)",
    "errors": {
        "first_name": ["The first name field is required."],
        "email": ["The email field must be a valid email address."],
        "level_id": ["The selected level id is invalid."],
        "is_active": ["The is active field is required."]
    }
}
```

---

### 6.10 Update Data Kurir

Memperbarui informasi data kurir yang sudah ada.

- **URL:** `/api/couriers/{id}` (contoh: `/api/couriers/13`)
- **Method:** `PUT` / `PATCH`

**Request Body (valid)**

```json
{
    "first_name": "Budi",
    "last_name": "Santoso Update",
    "phone": "089988776655",
    "email": "budi.update@example.com",
    "address": "Jl. Kebon Jeruk No. 88, Jakarta Barat",
    "level_id": 3,
    "is_active": true
}
```

**Success Response (`200 OK`)**

```json
{
    "message": "Courier updated successfully.",
    "data": {
        "id": 13,
        "first_name": "Budi",
        "last_name": "Santoso Update",
        "phone": "089988776655",
        "email": "budi.update@example.com",
        "address": "Jl. Kebon Jeruk No. 88, Jakarta Barat",
        "level_id": 3,
        "is_active": true,
        "created_at": "2026-11-15T14:10:00.000000Z",
        "updated_at": "2026-11-15T22:35:00.000000Z",
        "level": {
            "id": 3,
            "name": "Level 3"
        }
    }
}
```

**Failed Response, validasi gagal (`422 Unprocessable Entity`)**

Contoh email duplikat dengan kurir lain:

```json
{
    "message": "The email has already been taken.",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

**Failed Response, ID tidak ada (`404 Not Found`)**

```json
{
    "message": "No query results for model [App\\Models\\Courier] 99999"
}
```

---

### 6.11 Hapus Kurir

Menghapus data kurir secara permanen dari database.

- **URL:** `/api/couriers/{id}` (contoh: `/api/couriers/66`)
- **Method:** `DELETE`

**Success Response (`200 OK`)**

```json
{
    "message": "Courier deleted successfully."
}
```

**Failed Response (`404 Not Found`)**

Jika ID kurir tidak ditemukan:

```json
{
    "message": "No query results for model [App\\Models\\Courier] 66"
}
```

---

## 7. Pengujian Otomatis (Automated Testing)

Project ini dilengkapi automated feature test menggunakan **PHPUnit / Pest** yang mencakup pengujian CRUD, pencarian multi-keyword, filter level, pagination, dan aturan validasi.

### Persiapan Konfigurasi Testing

Konfigurasi testing dapat disesuaikan pada `.env.testing` atau `phpunit.xml`. Secara default, pengujian menggunakan database SQLite in-memory atau database test khusus:

```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### Menjalankan Test

**1. Menjalankan seluruh test suite**

```bash
php artisan test
```

atau menggunakan binary vendor:

```bash
./vendor/bin/phpunit
```

**2. Menjalankan khusus Courier feature test**

```bash
php artisan test tests/Feature/CourierApiTest.php
```

**3. Menjalankan satu skenario test tertentu**

```bash
php artisan test --filter=test_can_search_courier_by_multiple_keywords
```

---
