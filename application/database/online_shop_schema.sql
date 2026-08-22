-- =========================================================================
-- ONLINE SHOP SCHEMA ADD-ON
-- Jalankan script ini di database PostgreSQL "login" (sesuai config/database.php)
-- Menambahkan modul: Distributor/Kurir, Alamat, Rekening Pembeli,
-- Bukti Barang Terkirim, Transaksi, dan Pajak.
-- Semua tabel punya kolom "e_keterangan" untuk catatan/deskripsi per baris
-- data, dipakai di halaman detail (tabel di dalam tabel).
-- =========================================================================

-- 1. Distributor / Petugas Pengiriman (kurir: JNE, JNT, dll)
CREATE TABLE IF NOT EXISTS mst_courier (
    id_courier      SERIAL PRIMARY KEY,
    e_courier_name  VARCHAR(100) NOT NULL,          -- nama kurir/distributor, mis. "JNE Cabang Jakarta"
    e_courier_code  VARCHAR(20)  NOT NULL,           -- kode singkat, mis. "JNE", "JNT", "SICEPAT"
    e_contact_person VARCHAR(100),
    i_phone         VARCHAR(30),
    e_coverage_area VARCHAR(150),                    -- area layanan
    e_keterangan    TEXT,                            -- catatan bebas tentang kurir ini
    f_active        CHAR(1) NOT NULL DEFAULT 't',
    dt_created      TIMESTAMP NOT NULL DEFAULT NOW(),
    dt_updated      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- 2. Alamat pengiriman milik konsumen
CREATE TABLE IF NOT EXISTS mst_address (
    id_address      SERIAL PRIMARY KEY,
    id_user         INTEGER NOT NULL REFERENCES mst_user(id_user),
    e_label         VARCHAR(50)  NOT NULL,           -- "Rumah", "Kantor", dll
    e_recipient     VARCHAR(100) NOT NULL,           -- nama penerima
    i_phone         VARCHAR(30)  NOT NULL,
    e_address_full  TEXT NOT NULL,
    e_city          VARCHAR(100),
    e_province      VARCHAR(100),
    i_postal_code   VARCHAR(10),
    e_keterangan    TEXT,                            -- patokan / catatan alamat
    f_primary       CHAR(1) NOT NULL DEFAULT 'f',     -- alamat utama
    dt_created      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- 3. Rekening bank milik konsumen (untuk refund / verifikasi pembayaran)
CREATE TABLE IF NOT EXISTS mst_bank_account (
    id_bank_account SERIAL PRIMARY KEY,
    id_user         INTEGER NOT NULL REFERENCES mst_user(id_user),
    e_bank_name     VARCHAR(100) NOT NULL,
    i_account_number VARCHAR(50) NOT NULL,
    e_account_holder VARCHAR(100) NOT NULL,
    e_keterangan    TEXT,
    f_primary       CHAR(1) NOT NULL DEFAULT 'f',
    dt_created      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- 4. Master pajak (bisa lebih dari satu, mis. PPN 11%)
CREATE TABLE IF NOT EXISTS mst_tax (
    id_tax          SERIAL PRIMARY KEY,
    e_tax_name      VARCHAR(100) NOT NULL,           -- "PPN", "Pajak Layanan"
    n_percentage    NUMERIC(5,2) NOT NULL DEFAULT 0,  -- persentase, mis. 11.00
    e_keterangan    TEXT,
    f_active        CHAR(1) NOT NULL DEFAULT 't',
    dt_created      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- 5. Transaksi / Pesanan (header) - hasil checkout dari trx_cart
CREATE TABLE IF NOT EXISTS trx_transaction (
    id_transaction  SERIAL PRIMARY KEY,
    i_invoice       VARCHAR(30) NOT NULL UNIQUE,      -- no invoice, mis. INV/2026/08/0001
    id_user         INTEGER NOT NULL REFERENCES mst_user(id_user),
    id_address      INTEGER REFERENCES mst_address(id_address),
    id_bank_account INTEGER REFERENCES mst_bank_account(id_bank_account),
    id_tax          INTEGER REFERENCES mst_tax(id_tax),
    v_subtotal      NUMERIC(15,2) NOT NULL DEFAULT 0,
    v_tax           NUMERIC(15,2) NOT NULL DEFAULT 0,
    v_shipping_cost NUMERIC(15,2) NOT NULL DEFAULT 0,
    v_total         NUMERIC(15,2) NOT NULL DEFAULT 0,
    e_status        VARCHAR(30) NOT NULL DEFAULT 'Menunggu Pembayaran',
                    -- Menunggu Pembayaran | Dibayar | Diproses | Dikirim | Selesai | Dibatalkan
    e_keterangan    TEXT,                             -- catatan transaksi
    dt_created      TIMESTAMP NOT NULL DEFAULT NOW(),
    dt_updated      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- 6. Detail item per transaksi (barang apa saja & qty berapa) - nested table
CREATE TABLE IF NOT EXISTS trx_transaction_detail (
    id_transaction_detail SERIAL PRIMARY KEY,
    id_transaction  INTEGER NOT NULL REFERENCES trx_transaction(id_transaction),
    id_product      INTEGER NOT NULL REFERENCES mst_product(id_product),
    n_qty           INTEGER NOT NULL DEFAULT 1,
    v_price         NUMERIC(15,2) NOT NULL DEFAULT 0, -- harga satuan saat transaksi
    v_subtotal      NUMERIC(15,2) NOT NULL DEFAULT 0,
    e_keterangan    TEXT                               -- mis. catatan varian/ukuran
);

-- 7. Bukti barang terkirim (header per pengiriman, 1 transaksi bisa dikirim bertahap)
CREATE TABLE IF NOT EXISTS trx_shipment (
    id_shipment     SERIAL PRIMARY KEY,
    id_transaction  INTEGER NOT NULL REFERENCES trx_transaction(id_transaction),
    id_courier      INTEGER NOT NULL REFERENCES mst_courier(id_courier),
    i_resi          VARCHAR(50) NOT NULL,              -- nomor resi
    e_status_kirim  VARCHAR(30) NOT NULL DEFAULT 'Dikemas',
                    -- Dikemas | Dikirim | Diterima | Retur
    e_bukti_foto_url VARCHAR(255),                     -- link foto bukti kirim (optional)
    e_keterangan    TEXT,
    dt_kirim        TIMESTAMP,
    dt_diterima     TIMESTAMP,
    dt_created      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- 8. Detail barang yang dikirim per shipment - nested table
CREATE TABLE IF NOT EXISTS trx_shipment_detail (
    id_shipment_detail SERIAL PRIMARY KEY,
    id_shipment     INTEGER NOT NULL REFERENCES trx_shipment(id_shipment),
    id_product      INTEGER NOT NULL REFERENCES mst_product(id_product),
    n_qty           INTEGER NOT NULL DEFAULT 1,
    e_keterangan    TEXT
);

-- Indexes pendukung pencarian
CREATE INDEX IF NOT EXISTS idx_address_user ON mst_address(id_user);
CREATE INDEX IF NOT EXISTS idx_bank_account_user ON mst_bank_account(id_user);
CREATE INDEX IF NOT EXISTS idx_transaction_user ON trx_transaction(id_user);
CREATE INDEX IF NOT EXISTS idx_transaction_detail_trx ON trx_transaction_detail(id_transaction);
CREATE INDEX IF NOT EXISTS idx_shipment_trx ON trx_shipment(id_transaction);
CREATE INDEX IF NOT EXISTS idx_shipment_detail_shipment ON trx_shipment_detail(id_shipment);

-- Contoh data pajak default (opsional, boleh dihapus/diubah)
INSERT INTO mst_tax (e_tax_name, n_percentage, e_keterangan, f_active)
SELECT 'PPN', 11.00, 'Pajak Pertambahan Nilai sesuai ketentuan pemerintah', 't'
WHERE NOT EXISTS (SELECT 1 FROM mst_tax WHERE e_tax_name = 'PPN');
