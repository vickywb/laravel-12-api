# Laravel 12 API — Toko Online (E-commerce)

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [API Overview](#api-overview)
- [Postman Collection](#postman-collection)
- [Requirements](#requirements)
- [Installation](#installation)
- [Environment Variables](#environment-variables)
- [Running Locally](#running-locally)
- [Project Structure](#project-structure)
- [Contributing](#contributing)
- [License](#license)

## Overview

Proyek ini adalah **backend API saja**, dibangun dengan Laravel 12 untuk mendukung aplikasi toko online. API ini dibuat bertujuan untuk mengasah skill saya sebagai backend developer dengan case toko online yang mana terdapat race condition didalamnya.

Proyek ini menyediakan:

- autentikasi pengguna,
- katalog produk publik,
- keranjang belanja,
- pembuatan order dan checkout,
- manajemen transaksi,
- integrasi pembayaran Midtrans,
- panel admin untuk manajemen data.

## Features

- Autentikasi pengguna: register, login, profil, logout.
- Katalog produk publik dengan detail produk melalui slug.
- Keranjang belanja: tambah, kurangi, update, hapus, ringkasan, sinkronisasi, validasi checkout.
- Order: buat order, lihat daftar order, lihat detail order.
- Transaksi: checkout order, lihat status transaksi, detail transaksi.
- Upload file: endpoint upload file untuk gambar / asset.
- Redis support: konfigurasi Redis tersedia untuk cache, queue, dan session jika diperlukan.
- Admin API: manajemen users, roles, categories, products, discounts, product-discounts, orders, transactions.
- Diskon & promosi: dukungan diskon umum dan diskon produk.
- Rate limiting: throttle untuk endpoint sensitif.

## API Overview

API utama berada di prefix `/api/v1`.

### Public endpoints

- `GET /api/v1/` — Home
- `POST /api/v1/auth/login` — Login
- `POST /api/v1/auth/register` — Register
- `GET /api/v1/products` — Daftar produk
- `GET /api/v1/products/{product:slug}/product-detail` — Detail produk
- `POST /api/v1/files/upload` — Upload file
- `POST /api/v1/midtrans/webhook` — Callback Midtrans

### Authenticated endpoints

Memerlukan middleware `auth-api`.

- `GET /api/v1/auth/me` — Ambil profil pengguna
- `POST /api/v1/auth/logout` — Logout
- `GET /api/v1/user/carts` — List keranjang
- `POST /api/v1/user/carts/add` — Tambah item ke keranjang
- `POST /api/v1/user/carts/{cart}/decrease` — Kurangi kuantitas
- `PATCH /api/v1/user/carts/{cart}/replace` — Ubah kuantitas item
- `DELETE /api/v1/user/carts/{cart}/remove` — Hapus item
- `GET /api/v1/user/carts/summary` — Ringkasan keranjang
- `POST /api/v1/user/carts/sync` — Sinkronisasi keranjang
- `POST /api/v1/user/carts/validate-checkout` — Validasi sebelum checkout
- `DELETE /api/v1/user/carts/clear` — Bersihkan keranjang
- `GET /api/v1/user/orders` — Daftar order
- `POST /api/v1/user/orders` — Buat order baru
- `GET /api/v1/user/orders/{order}` — Detail order
- `GET /api/v1/user/transactions` — Daftar transaksi pengguna
- `GET /api/v1/user/transactions/{transaction}/transaction-detail` — Detail transaksi
- `POST /api/v1/user/orders/{order}/checkout` — Checkout order

### Admin endpoints

Memerlukan middleware `admin-api`.

- `GET /api/v1/admin/users` — Daftar users
- `GET /api/v1/admin/roles` — Daftar roles
- `POST /api/v1/admin/roles/store` — Buat role
- `GET /api/v1/admin/roles/{role}/detail` — Detail role
- `PATCH /api/v1/admin/roles/{role}/update` — Update role
- `DELETE /api/v1/admin/roles/{role}/delete` — Hapus role
- `GET /api/v1/admin/categories` — Daftar categories
- `POST /api/v1/admin/categories/store` — Buat category
- `GET /api/v1/admin/categories/{category}/detail` — Detail category
- `PATCH /api/v1/admin/categories/{category}/update` — Update category
- `DELETE /api/v1/admin/categories/{category}/delete` — Hapus category
- `GET /api/v1/admin/products` — Daftar products
- `POST /api/v1/admin/products/store` — Buat product
- `GET /api/v1/admin/products/{product}` — Detail product
- `PATCH /api/v1/admin/products/{product}/update` — Update product
- `DELETE /api/v1/admin/products/{product}/delete` — Hapus product
- `GET /api/v1/admin/product-discounts` — Daftar product discounts
- `POST /api/v1/admin/product-discounts/store` — Buat product discount
- `PATCH /api/v1/admin/product-discounts/{productDiscount}/update` — Update product discount
- `DELETE /api/v1/admin/product-discounts/{productDiscount}/delete` — Hapus product discount
- `GET /api/v1/admin/discounts` — Daftar discounts
- `POST /api/v1/admin/discounts/store` — Buat discount
- `PATCH /api/v1/admin/discounts/{discount}/update` — Update discount
- `DELETE /api/v1/admin/discounts/{discount}/delete` — Hapus discount
- `GET /api/v1/admin/transactions` — Daftar transaksi admin
- `GET /api/v1/admin/transactions/{transaction}/transaction-detail` — Detail transaksi admin
- `PATCH /api/v1/admin/transactions/{transaction}/update` — Update transaksi admin
- `DELETE /api/v1/admin/transactions/{transaction}/delete` — Hapus transaksi admin
- `GET /api/v1/admin/orders` — Daftar order admin
- `GET /api/v1/admin/orders/{order}/detail` — Detail order admin
- `PATCH /api/v1/admin/orders/{order}/update` — Update order admin
- `DELETE /api/v1/admin/orders/{order}/delete` — Hapus order admin

## Requirements

- PHP 8.1 atau lebih baru
- Composer
- MySQL / MariaDB / database lain yang didukung
- Redis server (opsional, tetapi direkomendasikan untuk cache / queue / session)
- Ekstensi PHP umum: `php-curl`, `php-mbstring`, `php-xml`, `php-bcmath`
- Driver Redis: `predis` atau `phpredis`

## Installation

1. Clone repository:

```bash
git clone <repository-url>
cd laravel-12-api
```

2. Pasang dependensi:

```bash
composer install
```

3. Salin file environment:

```bash
cp .env.example .env
```

4. Buat application key:

```bash
php artisan key:generate
```

5. Lengkapi konfigurasi di `.env`.

## Environment Variables

Variabel utama yang perlu diatur di `.env`:

- `APP_URL`
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `MIDTRANS_SERVER_KEY`
- `MIDTRANS_CLIENT_KEY`
- `MIDTRANS_IS_PRODUCTION`
- `REDIS_CLIENT`
- `REDIS_HOST`
- `REDIS_PASSWORD`
- `REDIS_PORT`
- `REDIS_DB`
- `REDIS_CACHE_DB`

## Running Locally

1. Jalankan migrasi dan seeder jika diperlukan:

```bash
php artisan migrate --seed
```

2. Jalankan server lokal:

```bash
php artisan serve
```

3. Akses API pada `http://127.0.0.1:8000/api/v1/`.
