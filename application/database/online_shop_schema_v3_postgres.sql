-- =====================================================================
-- Online Shop Admin Panel — Schema Expansion v3 (PostgreSQL)
-- =====================================================================
-- PENTING: project ini ternyata SUDAH punya
-- application/database/online_shop_schema_v2_postgres.sql yang sudah
-- mencakup brand/series/warna/ukuran/sub kategori/variasi produk,
-- promo, voucher, RETUR (trx_return) & REFUND (trx_refund), payment
-- (trx_customer_payment), kas/bank (trx_cashbank), gudang, dst — dan
-- databasenya memang PostgreSQL (lihat application/config/database.php),
-- bukan MySQL. Jalankan v2 itu dulu kalau belum.
--
-- File ini HANYA menambahkan 2 tabel yang belum ada di v2: BANNER
-- (marketing tools) dan REVIEW/RATING produk. Sama seperti v2, pakai
-- CREATE TABLE IF NOT EXISTS, tidak ada DROP/TRUNCATE/DELETE, aman
-- dijalankan berulang.
--
-- CARA MENJALANKAN
--   psql -U <user> -d tableHeader -f application/database/online_shop_schema_v3_postgres.sql
-- =====================================================================

-- #####################################################################
-- # PENJUALAN > MARKETING TOOLS — Banner Promo
-- #####################################################################

CREATE TABLE IF NOT EXISTS mst_banner (
    id_banner       SERIAL PRIMARY KEY,
    e_banner_title  VARCHAR(150) NOT NULL,
    e_image_url     VARCHAR(255) NOT NULL,
    e_link_url      VARCHAR(255) NULL,   -- diarahkan ke produk/kategori/promo tertentu saat diklik
    n_sort_order    INT NOT NULL DEFAULT 0,
    dt_start        DATE NULL,
    dt_end          DATE NULL,
    f_active        CHAR(1) NOT NULL DEFAULT 't'
);

-- #####################################################################
-- # PELANGGAN > RATING & REVIEW PRODUK
-- #####################################################################

CREATE TABLE IF NOT EXISTS trx_review (
    id_review       SERIAL PRIMARY KEY,
    id_product      INT NOT NULL REFERENCES mst_product (id_product)
                       ON DELETE CASCADE ON UPDATE CASCADE,
    id_user         INT NOT NULL REFERENCES mst_user (id_user)
                       ON DELETE CASCADE ON UPDATE CASCADE,
    id_transaction  INT NULL REFERENCES trx_transaction (id_transaction)
                       ON DELETE SET NULL,  -- diisi kalau mau syarat "verified purchase"
    n_rating        SMALLINT NOT NULL CHECK (n_rating BETWEEN 1 AND 5),
    e_review_text   TEXT NULL,
    f_approved      CHAR(1) NOT NULL DEFAULT 't',  -- moderasi admin sebelum tampil publik
    dt_created      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================================
-- SEED DATA (contoh, boleh dihapus)
-- =====================================================================

INSERT INTO mst_banner (e_banner_title, e_image_url, e_link_url, n_sort_order, f_active)
SELECT 'Diskon Awal Tahun', 'https://via.placeholder.com/1200x400', '/product', 1, 't'
WHERE NOT EXISTS (SELECT 1 FROM mst_banner WHERE e_banner_title = 'Diskon Awal Tahun');

-- =====================================================================
-- SELESAI.
-- =====================================================================
