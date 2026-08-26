# Perubahan v2 — Skema DB + Menu Struktur "ONLINE SHOP"

Update ini berisi **dua file**, sesuai permintaan: rancang skema SQL + revisi
menu. Data & tabel lama **tidak diubah/dihapus** — hanya ditambah.

## 1. File SQL baru
`application/database/online_shop_schema_v2_postgres.sql`

- **PostgreSQL** (menyesuaikan `config/database.php` yang sudah pakai
  `'dbdriver' => 'postgre'` dan database live `tableHeader` di DBeaver kamu).
- Semua `CREATE TABLE IF NOT EXISTS` → aman dijalankan berulang.
- **Tidak ada** `DROP` / `TRUNCATE` / `DELETE`.
- Hanya 2 `ALTER TABLE ... ADD COLUMN IF NOT EXISTS` (kolom nullable baru di
  `mst_product` untuk brand/series/sub kategori/satuan, dan `id_role` di
  `mst_user`) — tidak menyentuh baris data yang sudah ada.
- Mencakup semua modul di struktur menu yang kamu kirim: Produk (+EAV),
  Harga, Customer, Gudang, Ekspedisi, Pembayaran, Penjualan, Gudang
  (transaksi stok), Pengiriman (tracking), Keuangan, Setting (role & menu).
- Bagian **EAV** (`mst_category_attribute`, `mst_category_attribute_option`,
  `mst_product_attribute_value`) adalah inti dari saran kamu: kategori
  "Handphone" bisa punya atribut RAM/Storage/Warna, kategori "Mobil" bisa
  punya atribut Tahun/KM/Transmisi, tanpa nambah kolom ke `mst_product`.

**Cara jalankan:**
```
psql -U <user> -d tableHeader -f application/database/online_shop_schema_v2_postgres.sql
```

## 2. Menu (`application/views/partials/nav.php`)
Sudah direvisi mengikuti struktur besar yang kamu kirim: **Dashboard, Master
(Produk/Harga/Customer/Gudang/Ekspedisi/Pembayaran/Pajak), Penjualan, Gudang,
Pengiriman, Pelanggan, Keuangan, Setting.**

- Menu yang **controller-nya sudah ada** tetap link aktif ke halaman yang
  sudah berfungsi sekarang (Produk, Kategori Produk, Order Online, Daftar
  Transaksi, Pengiriman, Ekspedisi, Alamat, Rekening, Pajak).
- Menu untuk modul **baru** (Sub Kategori, Brand, EAV, Gudang, Voucher,
  Retur, Role, dll) tampil dengan badge **"Segera"** dan non-klik — supaya
  menu tidak 404, sambil menunjukkan arah struktur lengkapnya. Skema
  database-nya sudah siap (lihat file SQL di atas), tinggal dibuatkan
  Controller + Model + View saat modul itu mau dikerjakan.
- Backup menu lama ada di `application/views/partials/nav.php.bak`.

## Yang sengaja TIDAK disentuh
- Tidak ada data (`INSERT` seed lama) yang diubah.
- Tidak ada tabel lama yang di-drop/rename.
- Controller/model/view existing (`Product.php`, `Cart.php`,
  `Transaction.php`, dst) tidak diubah.

## Langkah selanjutnya (kalau mau lanjut)
Modul mana yang mau dibangun functional-nya duluan? Yang paling sering
dipakai biasanya: **Gudang (stok)** atau **EAV atribut produk** (biar
form tambah produk otomatis nampilin field sesuai kategori).
