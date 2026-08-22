# Ringkasan Perubahan — Online Shop Admin Panel

## 1. Langkah pertama: jalankan migrasi database
File baru: `application/database/online_shop_schema.sql`
Jalankan isi file ini di database PostgreSQL yang dipakai project (lihat
`application/config/database.php`), misalnya:

```
psql -U <user> -d <nama_database> -f application/database/online_shop_schema.sql
```

Script ini menambahkan tabel: `mst_courier`, `mst_address`, `mst_bank_account`,
`mst_tax`, `trx_transaction`, `trx_transaction_detail`, `trx_shipment`,
`trx_shipment_detail`. Semua aman dijalankan berulang (`CREATE TABLE IF NOT EXISTS`).

## 2. Homepage (`home.php`)
- Judul "Mau lihat apa hari ini?" diganti jadi bagian **"Product"** (grup pertama).
- Halaman utama sekarang berisi deskripsi singkat tentang toko online + statistik
  ringkas (jumlah produk, konsumen, transaksi, total penjualan).
- **Product List** dan **Data Pembelian Konsumen** digabung dalam satu grup "Product".
- Navbar brand diganti dari "Home" menjadi **"Online Shop"**.

## 3. Menu baru di sebelah "Online Shop"
Navbar (`views/partials/nav.php`, dipakai di semua halaman admin) sekarang punya
dropdown:
- **Produk**: Product List, Jenis-jenis Product
- **Transaksi**: Data Pembelian Konsumen, Daftar Transaksi
- **Pengiriman**: Bukti Barang Terkirim, Daftar Distributor / Petugas Ongkir
- **Pelanggan**: Daftar Alamat, Daftar Rekening Pembeli
- **Pajak & Keuangan** (link langsung)

## 4. Modul-modul baru
| Fitur | Controller | Model | View |
|---|---|---|---|
| Daftar Distributor/Ongkir | `Courier.php` | `Courier_model.php` | `courier_list.php` |
| Daftar Alamat | `Address.php` | `Address_model.php` | `address_list.php`, `address_user_detail.php` |
| Daftar Rekening Pembeli | `Bank_account.php` | `Bank_account_model.php` | `bank_account_list.php`, `bank_account_user_detail.php` |
| Daftar Transaksi | `Transaction.php` | `Transaction_model.php` | `transaction_list.php`, `transaction_detail.php` |
| Bukti Barang Terkirim | `Shipment.php` | `Shipment_model.php` | `shipment_list.php`, `shipment_detail.php` |
| Pajak & Keuangan | `Tax.php` | `Tax_model.php` | `tax_finance.php` |

Semua modul ini **khusus admin** (memakai pola `_is_admin()` / `_require_admin()`
yang sudah ada di project, mengecek `$this->username === 'admin'`).

## 5. Pola "tabel di dalam tabel"
- **Daftar Distributor**: klik jumlah pengiriman → modal nested table histori pengiriman kurir tsb.
- **Daftar Alamat / Rekening**: halaman master menampilkan daftar konsumen +
  jumlah data, klik ikon mata → halaman nested table berisi seluruh alamat/rekening
  milik konsumen tersebut (bisa tambah/edit/hapus).
- **Daftar Transaksi**: halaman detail transaksi menampilkan 2 nested table:
  barang yang dibeli beserta qty & harga, dan histori pengiriman.
- **Bukti Barang Terkirim**: halaman detail menampilkan nested table barang yang
  dikirim beserta qty, plus form update status & bukti foto.
- **Pajak & Keuangan**: ringkasan pendapatan toko per status transaksi + master
  data pajak (bisa tambah/edit/nonaktifkan).

Setiap baris data pada tabel-tabel di atas punya kolom **"Keterangan"** (catatan
bebas) sesuai permintaan, ditampilkan di kolom terpisah pada tiap tabel.

## Update terbaru
Saat diperiksa ulang, ternyata partial `partials/nav.php` sudah dibuat tapi
**belum benar-benar dipasang** di beberapa halaman (`home.php`, `product_list.php`,
`product_type_list.php`, `product_type_detail.php`, `cart_dashboard.php`,
`cart_user_detail.php`, `address_user_detail.php`, `bank_account_user_detail.php`,
`shipment_detail.php`, `transaction_detail.php`) — halaman-halaman itu masih pakai
navbar lama tanpa dropdown menu. Sudah diperbaiki: semua halaman sekarang memanggil
`$this->load->view('partials/nav', ...)` sehingga menu "Online Shop" dengan seluruh
dropdown-nya konsisten tampil di setiap halaman.

`home.php` juga sudah ditulis ulang sesuai permintaan awal: judul "Mau lihat apa hari
ini?" diganti jadi deskripsi toko online + 4 kartu statistik (jumlah product, konsumen
aktif, transaksi, total penjualan), lalu menu-menu dikelompokkan jadi 3 bagian:
**Product** (Product List, Data Pembelian Konsumen, Jenis-jenis Product), **Transaksi
& Pengiriman** (Daftar Transaksi, Bukti Barang Terkirim, Distributor/Ongkir), dan
**Pelanggan & Keuangan** (Alamat, Rekening, Pajak). `Home.php` controller diperbarui
untuk menghitung statistik tersebut dari `Product_model`, `Cart_model`, dan
`Transaction_model`.

## Catatan
- Karena database aktual project tidak bisa diakses dari sandbox ini, semua kode
  sudah diperiksa manual (struktur tabel/kolom dicocokkan dengan model yang sudah
  ada seperti `Product_model.php`, `User_model.php`) dan lolos pengecekan sintaks
  PHP dasar, tapi **belum diuji langsung terhadap database live**. Setelah migrasi
  SQL dijalankan, coba akses `/home`, lalu telusuri tiap menu baru untuk memastikan
  semuanya berjalan sesuai environment Anda.
- Beberapa data (transaksi, pengiriman) awalnya masih kosong karena ini tabel baru —
  Anda perlu menambahkan data lewat modul terkait, atau saya bisa bantu buatkan
  seed data contoh bila diperlukan.
