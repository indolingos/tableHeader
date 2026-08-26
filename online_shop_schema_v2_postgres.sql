-- =====================================================================
-- Online Shop Admin Panel — Schema Expansion v2 (PostgreSQL)
-- =====================================================================
-- TUJUAN
--   Menambahkan seluruh tabel yang dibutuhkan untuk struktur menu baru:
--   MASTER > (Produk, Harga, Customer, Gudang, Ekspedisi, Pembayaran, Pajak)
--   PENJUALAN, GUDANG, PENGIRIMAN, PELANGGAN, KEUANGAN, SETTING
--   plus pola EAV (kategori -> atribut kategori -> nilai atribut produk)
--   supaya satu sistem bisa menangani produk campuran (mobil, motor, HP,
--   speaker, jam tangan, dst) tanpa mengubah struktur tabel tiap kali ada
--   kategori baru.
--
-- PRINSIP AMAN (data lama tidak diganggu)
--   - Semua tabel baru pakai CREATE TABLE IF NOT EXISTS -> aman dijalankan
--     berulang, tidak akan menimpa tabel yang sudah ada.
--   - TIDAK ADA statement DROP / TRUNCATE / DELETE di file ini.
--   - Tabel-tabel lama (mst_user, mst_product, mst_category, mst_product_type,
--     mst_courier, mst_address, mst_bank_account, mst_tax, trx_cart,
--     trx_transaction, trx_transaction_detail, trx_shipment,
--     trx_shipment_detail, products, trx_product) TIDAK diubah strukturnya,
--     kecuali 2 ALTER TABLE ... ADD COLUMN IF NOT EXISTS yang ditandai
--     jelas di bagian SETTING (menambah kolom nullable/default, tidak
--     menghapus/mengubah kolom yang sudah ada, tidak menyentuh data yang
--     sudah ada).
--   - Semua foreign key ke tabel lama memakai nama kolom yang sudah ada
--     saat ini (id_user, id_product, id_category, id_product_type,
--     id_courier, dst) supaya nyambung langsung ke data existing.
--
-- CARA MENJALANKAN
--   psql -U <user> -d tableHeader -f application/database/online_shop_schema_v2_postgres.sql
--
-- Urutan bagian di file ini sengaja mengikuti urutan menu di README menu
-- (lihat application/views/partials/nav.php yang sudah direvisi).
-- =====================================================================


-- #####################################################################
-- # MASTER > PRODUK (tambahan di atas mst_product_type/mst_category/
-- #                   mst_product yang sudah ada)
-- #####################################################################

-- Sub Kategori Produk (anak dari mst_category yang sudah ada)
CREATE TABLE IF NOT EXISTS mst_sub_category (
    id_sub_category  SERIAL PRIMARY KEY,
    id_category      INT NOT NULL REFERENCES mst_category (id_category)
                        ON DELETE CASCADE ON UPDATE CASCADE,
    e_sub_category   VARCHAR(100) NOT NULL,
    e_keterangan     TEXT NULL,
    f_active         CHAR(1) NOT NULL DEFAULT 't'
);

-- Brand
CREATE TABLE IF NOT EXISTS mst_brand (
    id_brand      SERIAL PRIMARY KEY,
    e_brand       VARCHAR(100) NOT NULL,
    e_keterangan  TEXT NULL,
    f_active      CHAR(1) NOT NULL DEFAULT 't'
);

-- Series / Tipe (anak dari brand)
CREATE TABLE IF NOT EXISTS mst_series (
    id_series     SERIAL PRIMARY KEY,
    id_brand      INT NOT NULL REFERENCES mst_brand (id_brand)
                    ON DELETE CASCADE ON UPDATE CASCADE,
    e_series      VARCHAR(100) NOT NULL,
    e_keterangan  TEXT NULL,
    f_active      CHAR(1) NOT NULL DEFAULT 't'
);

-- Warna (referensi lepas, dipakai lewat mst_product_variation)
CREATE TABLE IF NOT EXISTS mst_color (
    id_color   SERIAL PRIMARY KEY,
    e_color    VARCHAR(50) NOT NULL UNIQUE
);

-- Ukuran (referensi lepas, dipakai lewat mst_product_variation)
CREATE TABLE IF NOT EXISTS mst_size (
    id_size    SERIAL PRIMARY KEY,
    e_size     VARCHAR(50) NOT NULL UNIQUE
);

-- Satuan (pcs, unit, box, dll)
CREATE TABLE IF NOT EXISTS mst_unit (
    id_unit    SERIAL PRIMARY KEY,
    e_unit     VARCHAR(30) NOT NULL UNIQUE
);

-- Kolom tambahan opsional di mst_product agar nyambung ke brand/series/
-- sub kategori/satuan tanpa mengganggu kolom & data yang sudah ada.
-- (ADD COLUMN IF NOT EXISTS -> nullable, tidak mempengaruhi baris lama)
ALTER TABLE mst_product ADD COLUMN IF NOT EXISTS id_sub_category INT NULL
    REFERENCES mst_sub_category (id_sub_category) ON DELETE SET NULL;
ALTER TABLE mst_product ADD COLUMN IF NOT EXISTS id_brand INT NULL
    REFERENCES mst_brand (id_brand) ON DELETE SET NULL;
ALTER TABLE mst_product ADD COLUMN IF NOT EXISTS id_series INT NULL
    REFERENCES mst_series (id_series) ON DELETE SET NULL;
ALTER TABLE mst_product ADD COLUMN IF NOT EXISTS id_unit INT NULL
    REFERENCES mst_unit (id_unit) ON DELETE SET NULL;

-- Variasi Produk (kombinasi warna/ukuran per produk, punya stok & harga
-- sendiri jika berbeda dari produk induk)
CREATE TABLE IF NOT EXISTS mst_product_variation (
    id_product_variation  SERIAL PRIMARY KEY,
    id_product            INT NOT NULL REFERENCES mst_product (id_product)
                            ON DELETE CASCADE ON UPDATE CASCADE,
    id_color              INT NULL REFERENCES mst_color (id_color),
    id_size               INT NULL REFERENCES mst_size (id_size),
    i_sku_variation       VARCHAR(50) NULL UNIQUE,
    v_price_override      DECIMAL(15,2) NULL,
    n_stock               INT NOT NULL DEFAULT 0,
    f_active              CHAR(1) NOT NULL DEFAULT 't'
);

-- ---------------------------------------------------------------------
-- POLA EAV — Kategori -> Atribut Kategori -> Nilai Atribut Produk
-- Ini bagian intinya: satu sistem bisa menangani mobil, motor, HP,
-- speaker, jam tangan, dst tanpa ubah struktur tabel tiap kategori baru.
-- ---------------------------------------------------------------------

-- Atribut yang dimiliki sebuah kategori, misal kategori "Handphone" punya
-- atribut RAM, Storage, Warna, Garansi; kategori "Mobil" punya atribut
-- Tahun, Kilometer, Transmisi, Kapasitas Mesin, Warna.
CREATE TABLE IF NOT EXISTS mst_category_attribute (
    id_category_attribute  SERIAL PRIMARY KEY,
    id_category             INT NOT NULL REFERENCES mst_category (id_category)
                                ON DELETE CASCADE ON UPDATE CASCADE,
    e_attribute_name        VARCHAR(100) NOT NULL,
    e_data_type              VARCHAR(20) NOT NULL DEFAULT 'text'
                                CHECK (e_data_type IN ('text','number','date','boolean','select')),
    e_unit_label             VARCHAR(20) NULL,   -- misal "GB", "km", "cc"
    n_sort_order             INT NOT NULL DEFAULT 0,
    f_required               CHAR(1) NOT NULL DEFAULT 'f',
    UNIQUE (id_category, e_attribute_name)
);

-- Pilihan nilai untuk atribut bertipe "select" (misal Transmisi: Manual/
-- Matic). Opsional — hanya dipakai kalau e_data_type = 'select'.
CREATE TABLE IF NOT EXISTS mst_category_attribute_option (
    id_attribute_option     SERIAL PRIMARY KEY,
    id_category_attribute   INT NOT NULL REFERENCES mst_category_attribute (id_category_attribute)
                                ON DELETE CASCADE ON UPDATE CASCADE,
    e_option_value          VARCHAR(100) NOT NULL
);

-- Nilai aktual atribut untuk tiap produk, misal produk "iPhone 13" ->
-- atribut RAM -> nilai "6".
CREATE TABLE IF NOT EXISTS mst_product_attribute_value (
    id_product_attribute_value  SERIAL PRIMARY KEY,
    id_product                   INT NOT NULL REFERENCES mst_product (id_product)
                                    ON DELETE CASCADE ON UPDATE CASCADE,
    id_category_attribute        INT NOT NULL REFERENCES mst_category_attribute (id_category_attribute)
                                    ON DELETE CASCADE ON UPDATE CASCADE,
    e_value                      TEXT NOT NULL,
    UNIQUE (id_product, id_category_attribute)
);


-- #####################################################################
-- # MASTER > HARGA
-- #####################################################################

CREATE TABLE IF NOT EXISTS mst_price_promo (
    id_price_promo  SERIAL PRIMARY KEY,
    id_product      INT NOT NULL REFERENCES mst_product (id_product)
                        ON DELETE CASCADE ON UPDATE CASCADE,
    v_promo_price   DECIMAL(15,2) NOT NULL,
    dt_start        DATE NOT NULL,
    dt_end          DATE NOT NULL,
    e_keterangan    TEXT NULL,
    f_active        CHAR(1) NOT NULL DEFAULT 't'
);

CREATE TABLE IF NOT EXISTS mst_price_wholesale (
    id_price_wholesale  SERIAL PRIMARY KEY,
    id_product          INT NOT NULL REFERENCES mst_product (id_product)
                            ON DELETE CASCADE ON UPDATE CASCADE,
    n_min_qty           INT NOT NULL,
    v_price_per_unit    DECIMAL(15,2) NOT NULL,
    e_keterangan        TEXT NULL
);


-- #####################################################################
-- # MASTER > CUSTOMER  (tabel referensi baru, TIDAK mengubah mst_user)
-- #####################################################################

CREATE TABLE IF NOT EXISTS mst_customer_group (
    id_customer_group  SERIAL PRIMARY KEY,
    e_group_name       VARCHAR(100) NOT NULL,
    e_keterangan       TEXT NULL
);

CREATE TABLE IF NOT EXISTS mst_customer_level (
    id_customer_level  SERIAL PRIMARY KEY,
    e_level_name       VARCHAR(100) NOT NULL,
    n_min_transaksi    DECIMAL(15,2) NOT NULL DEFAULT 0,
    e_keterangan       TEXT NULL
);

-- Status customer (Aktif / Suspend / Blacklist, dst)
CREATE TABLE IF NOT EXISTS mst_customer_status (
    id_customer_status  SERIAL PRIMARY KEY,
    e_status_name       VARCHAR(50) NOT NULL UNIQUE
);

-- Tabel penghubung: memetakan mst_user (customer) ke group/level/status
-- tanpa menambah kolom ke mst_user, sekaligus bisa menyimpan histori.
CREATE TABLE IF NOT EXISTS mst_customer_profile (
    id_customer_profile  SERIAL PRIMARY KEY,
    id_user               INT NOT NULL UNIQUE REFERENCES mst_user (id_user)
                             ON DELETE CASCADE ON UPDATE CASCADE,
    id_customer_group     INT NULL REFERENCES mst_customer_group (id_customer_group),
    id_customer_level     INT NULL REFERENCES mst_customer_level (id_customer_level),
    id_customer_status    INT NULL REFERENCES mst_customer_status (id_customer_status),
    dt_updated            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- #####################################################################
-- # MASTER > GUDANG
-- #####################################################################

CREATE TABLE IF NOT EXISTS mst_warehouse (
    id_warehouse   SERIAL PRIMARY KEY,
    e_warehouse    VARCHAR(100) NOT NULL,
    e_address      TEXT NULL,
    f_active       CHAR(1) NOT NULL DEFAULT 't'
);

CREATE TABLE IF NOT EXISTS mst_warehouse_location (
    id_warehouse_location  SERIAL PRIMARY KEY,
    id_warehouse            INT NOT NULL REFERENCES mst_warehouse (id_warehouse)
                                ON DELETE CASCADE ON UPDATE CASCADE,
    e_location_name          VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS mst_rack (
    id_rack                 SERIAL PRIMARY KEY,
    id_warehouse_location   INT NOT NULL REFERENCES mst_warehouse_location (id_warehouse_location)
                                ON DELETE CASCADE ON UPDATE CASCADE,
    e_rack_code              VARCHAR(50) NOT NULL
);


-- #####################################################################
-- # MASTER > EKSPEDISI  (mst_courier sudah ada, ini tambahannya)
-- #####################################################################

CREATE TABLE IF NOT EXISTS mst_shipping_type (
    id_shipping_type  SERIAL PRIMARY KEY,
    e_shipping_type   VARCHAR(100) NOT NULL,   -- Reguler, Express, Same Day, dst
    n_estimasi_hari   INT NULL
);

CREATE TABLE IF NOT EXISTS mst_shipping_rate (
    id_shipping_rate  SERIAL PRIMARY KEY,
    id_courier         INT NOT NULL REFERENCES mst_courier (id_courier)
                          ON DELETE CASCADE ON UPDATE CASCADE,
    id_shipping_type    INT NOT NULL REFERENCES mst_shipping_type (id_shipping_type)
                          ON DELETE CASCADE ON UPDATE CASCADE,
    e_destination_zone   VARCHAR(100) NOT NULL,
    v_rate_per_kg        DECIMAL(15,2) NOT NULL DEFAULT 0
);


-- #####################################################################
-- # MASTER > PEMBAYARAN  (mst_bank_account sudah ada = "Rekening")
-- #####################################################################

CREATE TABLE IF NOT EXISTS mst_payment_method (
    id_payment_method  SERIAL PRIMARY KEY,
    e_payment_method   VARCHAR(100) NOT NULL,   -- Transfer Bank, COD, QRIS, VA, dst
    f_active           CHAR(1) NOT NULL DEFAULT 't'
);

-- MASTER > PAJAK: mst_tax sudah ada dan sudah representasi "Jenis Pajak",
-- tidak perlu tabel baru.


-- #####################################################################
-- # PENJUALAN  (Order Online / Daftar Transaksi -> trx_transaction sudah
-- #             ada; ini menambah Promo, Voucher, Retur, Refund)
-- #####################################################################

CREATE TABLE IF NOT EXISTS mst_promo (
    id_promo       SERIAL PRIMARY KEY,
    e_promo_name   VARCHAR(150) NOT NULL,
    e_promo_type   VARCHAR(20) NOT NULL DEFAULT 'percentage'
                     CHECK (e_promo_type IN ('percentage','nominal')),
    n_value        DECIMAL(15,2) NOT NULL,
    dt_start       DATE NOT NULL,
    dt_end         DATE NOT NULL,
    f_active       CHAR(1) NOT NULL DEFAULT 't'
);

CREATE TABLE IF NOT EXISTS mst_voucher (
    id_voucher     SERIAL PRIMARY KEY,
    e_voucher_code VARCHAR(50) NOT NULL UNIQUE,
    e_voucher_type VARCHAR(20) NOT NULL DEFAULT 'percentage'
                     CHECK (e_voucher_type IN ('percentage','nominal')),
    n_value        DECIMAL(15,2) NOT NULL,
    n_quota        INT NOT NULL DEFAULT 0,
    n_used         INT NOT NULL DEFAULT 0,
    dt_start       DATE NOT NULL,
    dt_end         DATE NOT NULL,
    f_active       CHAR(1) NOT NULL DEFAULT 't'
);

CREATE TABLE IF NOT EXISTS trx_voucher_usage (
    id_voucher_usage  SERIAL PRIMARY KEY,
    id_voucher         INT NOT NULL REFERENCES mst_voucher (id_voucher)
                          ON DELETE CASCADE ON UPDATE CASCADE,
    id_transaction      INT NOT NULL REFERENCES trx_transaction (id_transaction)
                          ON DELETE CASCADE ON UPDATE CASCADE,
    v_discount_applied   DECIMAL(15,2) NOT NULL DEFAULT 0,
    dt_created           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trx_return (
    id_return      SERIAL PRIMARY KEY,
    id_transaction  INT NOT NULL REFERENCES trx_transaction (id_transaction)
                       ON DELETE CASCADE ON UPDATE CASCADE,
    id_product      INT NOT NULL REFERENCES mst_product (id_product)
                       ON DELETE RESTRICT ON UPDATE CASCADE,
    n_qty            INT NOT NULL DEFAULT 1,
    e_reason         TEXT NULL,
    e_status         VARCHAR(30) NOT NULL DEFAULT 'Diajukan',
    dt_created       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trx_refund (
    id_refund      SERIAL PRIMARY KEY,
    id_return       INT NOT NULL REFERENCES trx_return (id_return)
                       ON DELETE CASCADE ON UPDATE CASCADE,
    v_refund_amount  DECIMAL(15,2) NOT NULL DEFAULT 0,
    e_status         VARCHAR(30) NOT NULL DEFAULT 'Diproses',
    dt_created       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- #####################################################################
-- # GUDANG  (Stok, Barang Masuk/Keluar, Mutasi, Stock Opname, Adjustment)
-- #####################################################################

-- Stok produk per gudang (n_stock di mst_product tetap dipakai sebagai
-- total keseluruhan; tabel ini untuk rincian per lokasi gudang).
CREATE TABLE IF NOT EXISTS trx_stock (
    id_stock       SERIAL PRIMARY KEY,
    id_product      INT NOT NULL REFERENCES mst_product (id_product)
                       ON DELETE CASCADE ON UPDATE CASCADE,
    id_warehouse    INT NOT NULL REFERENCES mst_warehouse (id_warehouse)
                       ON DELETE CASCADE ON UPDATE CASCADE,
    id_rack         INT NULL REFERENCES mst_rack (id_rack) ON DELETE SET NULL,
    n_qty           INT NOT NULL DEFAULT 0,
    UNIQUE (id_product, id_warehouse)
);

CREATE TABLE IF NOT EXISTS trx_stock_in (
    id_stock_in    SERIAL PRIMARY KEY,
    id_product      INT NOT NULL REFERENCES mst_product (id_product)
                       ON DELETE CASCADE ON UPDATE CASCADE,
    id_warehouse    INT NOT NULL REFERENCES mst_warehouse (id_warehouse)
                       ON DELETE CASCADE ON UPDATE CASCADE,
    n_qty            INT NOT NULL,
    e_source          VARCHAR(150) NULL,   -- supplier / retur / produksi, dst
    e_keterangan      TEXT NULL,
    dt_created        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trx_stock_out (
    id_stock_out   SERIAL PRIMARY KEY,
    id_product      INT NOT NULL REFERENCES mst_product (id_product)
                       ON DELETE CASCADE ON UPDATE CASCADE,
    id_warehouse    INT NOT NULL REFERENCES mst_warehouse (id_warehouse)
                       ON DELETE CASCADE ON UPDATE CASCADE,
    n_qty            INT NOT NULL,
    e_reason          VARCHAR(150) NULL,   -- penjualan / rusak / hilang, dst
    e_keterangan      TEXT NULL,
    dt_created        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trx_stock_mutation (
    id_stock_mutation  SERIAL PRIMARY KEY,
    id_product          INT NOT NULL REFERENCES mst_product (id_product)
                           ON DELETE CASCADE ON UPDATE CASCADE,
    id_warehouse_from    INT NOT NULL REFERENCES mst_warehouse (id_warehouse),
    id_warehouse_to      INT NOT NULL REFERENCES mst_warehouse (id_warehouse),
    n_qty                INT NOT NULL,
    e_keterangan          TEXT NULL,
    dt_created            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trx_stock_opname (
    id_stock_opname  SERIAL PRIMARY KEY,
    id_product        INT NOT NULL REFERENCES mst_product (id_product)
                         ON DELETE CASCADE ON UPDATE CASCADE,
    id_warehouse       INT NOT NULL REFERENCES mst_warehouse (id_warehouse)
                         ON DELETE CASCADE ON UPDATE CASCADE,
    n_qty_system        INT NOT NULL,
    n_qty_actual         INT NOT NULL,
    e_keterangan          TEXT NULL,
    dt_created            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trx_stock_adjustment (
    id_stock_adjustment  SERIAL PRIMARY KEY,
    id_stock_opname       INT NULL REFERENCES trx_stock_opname (id_stock_opname)
                             ON DELETE SET NULL,
    id_product             INT NOT NULL REFERENCES mst_product (id_product)
                             ON DELETE CASCADE ON UPDATE CASCADE,
    id_warehouse            INT NOT NULL REFERENCES mst_warehouse (id_warehouse)
                             ON DELETE CASCADE ON UPDATE CASCADE,
    n_qty_adjustment         INT NOT NULL,  -- boleh negatif (koreksi turun) atau positif
    e_keterangan              TEXT NULL,
    dt_created                TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- #####################################################################
-- # PENGIRIMAN  (trx_shipment / trx_shipment_detail sudah ada = surat
-- #              jalan + barang dikirim; ini menambah tracking history)
-- #####################################################################

CREATE TABLE IF NOT EXISTS trx_shipment_tracking (
    id_shipment_tracking  SERIAL PRIMARY KEY,
    id_shipment            INT NOT NULL REFERENCES trx_shipment (id_shipment)
                              ON DELETE CASCADE ON UPDATE CASCADE,
    e_status_kirim           VARCHAR(50) NOT NULL,  -- Dikemas, Dikirim, Transit, Terkirim
    e_lokasi                 VARCHAR(150) NULL,
    e_keterangan              TEXT NULL,
    dt_created                TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- #####################################################################
-- # KEUANGAN  (Faktur = trx_transaction; ini menambah pembayaran
-- #            customer & kas/bank masuk-keluar toko)
-- #####################################################################

CREATE TABLE IF NOT EXISTS trx_customer_payment (
    id_customer_payment  SERIAL PRIMARY KEY,
    id_transaction         INT NOT NULL REFERENCES trx_transaction (id_transaction)
                              ON DELETE CASCADE ON UPDATE CASCADE,
    id_payment_method       INT NULL REFERENCES mst_payment_method (id_payment_method),
    v_amount                 DECIMAL(15,2) NOT NULL,
    e_bukti_bayar             VARCHAR(255) NULL,  -- path file upload bukti transfer
    e_status                  VARCHAR(30) NOT NULL DEFAULT 'Menunggu Verifikasi',
    dt_created                TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trx_cashbank (
    id_cashbank    SERIAL PRIMARY KEY,
    e_type          VARCHAR(10) NOT NULL CHECK (e_type IN ('masuk','keluar')),
    e_category      VARCHAR(100) NULL,   -- operasional, gaji, pembelian stok, dll
    v_amount         DECIMAL(15,2) NOT NULL,
    id_transaction    INT NULL REFERENCES trx_transaction (id_transaction)
                        ON DELETE SET NULL,
    e_keterangan       TEXT NULL,
    dt_created          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Laporan Penjualan tidak butuh tabel baru — cukup query agregat dari
-- trx_transaction + trx_transaction_detail yang sudah ada.


-- #####################################################################
-- # SETTING  (User, Role, Hak Akses, Menu)
-- #####################################################################

CREATE TABLE IF NOT EXISTS mst_role (
    id_role     SERIAL PRIMARY KEY,
    e_role_name VARCHAR(50) NOT NULL UNIQUE   -- Admin, Staff Gudang, Kasir, dst
);

CREATE TABLE IF NOT EXISTS mst_menu (
    id_menu      SERIAL PRIMARY KEY,
    id_parent     INT NULL REFERENCES mst_menu (id_menu) ON DELETE CASCADE,
    e_menu_name   VARCHAR(100) NOT NULL,
    e_menu_url    VARCHAR(150) NULL,
    e_icon         VARCHAR(50) NULL,
    n_sort_order   INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS mst_role_menu_access (
    id_role_menu_access  SERIAL PRIMARY KEY,
    id_role                INT NOT NULL REFERENCES mst_role (id_role)
                              ON DELETE CASCADE ON UPDATE CASCADE,
    id_menu                 INT NOT NULL REFERENCES mst_menu (id_menu)
                              ON DELETE CASCADE ON UPDATE CASCADE,
    f_can_view                CHAR(1) NOT NULL DEFAULT 't',
    f_can_create               CHAR(1) NOT NULL DEFAULT 'f',
    f_can_edit                  CHAR(1) NOT NULL DEFAULT 'f',
    f_can_delete                 CHAR(1) NOT NULL DEFAULT 'f',
    UNIQUE (id_role, id_menu)
);

-- Tambah kolom id_role ke mst_user (NULLABLE, default NULL) supaya user
-- lama tetap jalan seperti biasa (aplikasi masih boleh cek admin lewat
-- i_username = 'admin' seperti sekarang; kolom ini hanya disiapkan untuk
-- role management ke depan, tidak wajib dipakai langsung).
ALTER TABLE mst_user ADD COLUMN IF NOT EXISTS id_role INT NULL
    REFERENCES mst_role (id_role) ON DELETE SET NULL;

-- Seed role dasar (aman — ON CONFLICT DO NOTHING, tidak menimpa apa pun)
INSERT INTO mst_role (e_role_name) VALUES ('Admin'), ('Staff Gudang'), ('Kasir'), ('Customer')
    ON CONFLICT (e_role_name) DO NOTHING;

-- =====================================================================
-- SELESAI. Tidak ada data lama yang diubah/dihapus oleh script ini.
-- =====================================================================
