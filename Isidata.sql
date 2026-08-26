BEGIN;

-- =========================================================
-- MASTER PRODUK
-- =========================================================

INSERT INTO mst_brand (e_brand, e_keterangan, f_active)
SELECT 'Toyota', 'Brand mobil', 't'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_brand WHERE LOWER(e_brand) = 'toyota'
);

INSERT INTO mst_brand (e_brand, e_keterangan, f_active)
SELECT 'Honda', 'Brand mobil dan motor', 't'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_brand WHERE LOWER(e_brand) = 'honda'
);

INSERT INTO mst_brand (e_brand, e_keterangan, f_active)
SELECT 'Yamaha', 'Brand motor', 't'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_brand WHERE LOWER(e_brand) = 'yamaha'
);

INSERT INTO mst_brand (e_brand, e_keterangan, f_active)
SELECT 'Apple', 'Brand handphone', 't'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_brand WHERE LOWER(e_brand) = 'apple'
);

INSERT INTO mst_brand (e_brand, e_keterangan, f_active)
SELECT 'Samsung', 'Brand handphone dan elektronik', 't'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_brand WHERE LOWER(e_brand) = 'samsung'
);

INSERT INTO mst_brand (e_brand, e_keterangan, f_active)
SELECT 'JBL', 'Brand speaker', 't'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_brand WHERE LOWER(e_brand) = 'jbl'
);

INSERT INTO mst_brand (e_brand, e_keterangan, f_active)
SELECT 'Casio', 'Brand jam tangan', 't'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_brand WHERE LOWER(e_brand) = 'casio'
);

-- =========================================================
-- SATUAN
-- =========================================================

INSERT INTO mst_unit (e_unit)
SELECT 'Unit'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_unit WHERE LOWER(e_unit) = 'unit'
);

INSERT INTO mst_unit (e_unit)
SELECT 'Pcs'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_unit WHERE LOWER(e_unit) = 'pcs'
);

INSERT INTO mst_unit (e_unit)
SELECT 'Box'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_unit WHERE LOWER(e_unit) = 'box'
);

-- =========================================================
-- WARNA
-- =========================================================

INSERT INTO mst_color (e_color)
SELECT 'Hitam'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_color WHERE LOWER(e_color) = 'hitam'
);

INSERT INTO mst_color (e_color)
SELECT 'Putih'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_color WHERE LOWER(e_color) = 'putih'
);

INSERT INTO mst_color (e_color)
SELECT 'Merah'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_color WHERE LOWER(e_color) = 'merah'
);

INSERT INTO mst_color (e_color)
SELECT 'Biru'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_color WHERE LOWER(e_color) = 'biru'
);

INSERT INTO mst_color (e_color)
SELECT 'Silver'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_color WHERE LOWER(e_color) = 'silver'
);

-- =========================================================
-- UKURAN
-- =========================================================

INSERT INTO mst_size (e_size)
SELECT 'Small'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_size WHERE LOWER(e_size) = 'small'
);

INSERT INTO mst_size (e_size)
SELECT 'Medium'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_size WHERE LOWER(e_size) = 'medium'
);

INSERT INTO mst_size (e_size)
SELECT 'Large'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_size WHERE LOWER(e_size) = 'large'
);

-- =========================================================
-- CUSTOMER
-- =========================================================

INSERT INTO mst_customer_group (e_group_name, e_keterangan)
SELECT 'Retail', 'Customer pembelian satuan'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_customer_group
    WHERE LOWER(e_group_name) = 'retail'
);

INSERT INTO mst_customer_group (e_group_name, e_keterangan)
SELECT 'Grosir', 'Customer pembelian jumlah besar'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_customer_group
    WHERE LOWER(e_group_name) = 'grosir'
);

INSERT INTO mst_customer_group (e_group_name, e_keterangan)
SELECT 'Dealer', 'Customer dealer / partner'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_customer_group
    WHERE LOWER(e_group_name) = 'dealer'
);

INSERT INTO mst_customer_level
(e_level_name, n_min_transaksi, e_keterangan)
SELECT 'Bronze', 0, 'Customer baru'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_customer_level
    WHERE LOWER(e_level_name) = 'bronze'
);

INSERT INTO mst_customer_level
(e_level_name, n_min_transaksi, e_keterangan)
SELECT 'Silver', 10000000, 'Customer transaksi menengah'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_customer_level
    WHERE LOWER(e_level_name) = 'silver'
);

INSERT INTO mst_customer_level
(e_level_name, n_min_transaksi, e_keterangan)
SELECT 'Gold', 50000000, 'Customer transaksi tinggi'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_customer_level
    WHERE LOWER(e_level_name) = 'gold'
);

INSERT INTO mst_customer_status (e_status_name)
SELECT 'Aktif'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_customer_status
    WHERE LOWER(e_status_name) = 'aktif'
);

INSERT INTO mst_customer_status (e_status_name)
SELECT 'Suspend'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_customer_status
    WHERE LOWER(e_status_name) = 'suspend'
);

INSERT INTO mst_customer_status (e_status_name)
SELECT 'Blacklist'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_customer_status
    WHERE LOWER(e_status_name) = 'blacklist'
);

-- =========================================================
-- GUDANG
-- =========================================================

INSERT INTO mst_warehouse
(e_warehouse, e_address, f_active)
SELECT 'Gudang Utama', 'Jakarta', 't'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_warehouse
    WHERE LOWER(e_warehouse) = 'gudang utama'
);

INSERT INTO mst_warehouse
(e_warehouse, e_address, f_active)
SELECT 'Gudang Cabang', 'Bandung', 't'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_warehouse
    WHERE LOWER(e_warehouse) = 'gudang cabang'
);

INSERT INTO mst_warehouse_location
(id_warehouse, e_location_name)
SELECT w.id_warehouse, 'Area Display'
FROM mst_warehouse w
WHERE LOWER(w.e_warehouse) = 'gudang utama'
AND NOT EXISTS (
    SELECT 1
    FROM mst_warehouse_location l
    WHERE l.id_warehouse = w.id_warehouse
      AND LOWER(l.e_location_name) = 'area display'
);

INSERT INTO mst_warehouse_location
(id_warehouse, e_location_name)
SELECT w.id_warehouse, 'Area Stok'
FROM mst_warehouse w
WHERE LOWER(w.e_warehouse) = 'gudang utama'
AND NOT EXISTS (
    SELECT 1
    FROM mst_warehouse_location l
    WHERE l.id_warehouse = w.id_warehouse
      AND LOWER(l.e_location_name) = 'area stok'
);

-- =========================================================
-- EKSPEDISI
-- =========================================================

INSERT INTO mst_shipping_type
(e_shipping_type, n_estimasi_hari)
SELECT 'Reguler', 3
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_shipping_type
    WHERE LOWER(e_shipping_type) = 'reguler'
);

INSERT INTO mst_shipping_type
(e_shipping_type, n_estimasi_hari)
SELECT 'Express', 1
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_shipping_type
    WHERE LOWER(e_shipping_type) = 'express'
);

INSERT INTO mst_shipping_type
(e_shipping_type, n_estimasi_hari)
SELECT 'Same Day', 0
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_shipping_type
    WHERE LOWER(e_shipping_type) = 'same day'
);

-- =========================================================
-- PEMBAYARAN
-- =========================================================

INSERT INTO mst_payment_method
(e_payment_method, f_active)
SELECT 'Transfer Bank', 't'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_payment_method
    WHERE LOWER(e_payment_method) = 'transfer bank'
);

INSERT INTO mst_payment_method
(e_payment_method, f_active)
SELECT 'Cash', 't'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_payment_method
    WHERE LOWER(e_payment_method) = 'cash'
);

INSERT INTO mst_payment_method
(e_payment_method, f_active)
SELECT 'COD', 't'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_payment_method
    WHERE LOWER(e_payment_method) = 'cod'
);

INSERT INTO mst_payment_method
(e_payment_method, f_active)
SELECT 'QRIS', 't'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_payment_method
    WHERE LOWER(e_payment_method) = 'qris'
);

INSERT INTO mst_payment_method
(e_payment_method, f_active)
SELECT 'Virtual Account', 't'
WHERE NOT EXISTS (
    SELECT 1
    FROM mst_payment_method
    WHERE LOWER(e_payment_method) = 'virtual account'
);

-- =========================================================
-- ROLE
-- =========================================================

INSERT INTO mst_role (e_role_name)
VALUES
    ('Admin'),
    ('Staff Gudang'),
    ('Kasir'),
    ('Customer')
ON CONFLICT (e_role_name) DO NOTHING;

-- =========================================================
-- MENU
-- =========================================================

INSERT INTO mst_menu
(id_parent, e_menu_name, e_menu_url, e_icon, n_sort_order)
SELECT
    NULL,
    'Dashboard',
    'home',
    'bi-speedometer2',
    1
WHERE NOT EXISTS (
    SELECT 1 FROM mst_menu WHERE LOWER(e_menu_name) = 'dashboard'
);

INSERT INTO mst_menu
(id_parent, e_menu_name, e_menu_url, e_icon, n_sort_order)
SELECT
    NULL,
    'Master',
    NULL,
    'bi-database',
    10
WHERE NOT EXISTS (
    SELECT 1 FROM mst_menu WHERE LOWER(e_menu_name) = 'master'
);

INSERT INTO mst_menu
(id_parent, e_menu_name, e_menu_url, e_icon, n_sort_order)
SELECT
    NULL,
    'Penjualan',
    NULL,
    'bi-cart-check',
    20
WHERE NOT EXISTS (
    SELECT 1 FROM mst_menu WHERE LOWER(e_menu_name) = 'penjualan'
);

INSERT INTO mst_menu
(id_parent, e_menu_name, e_menu_url, e_icon, n_sort_order)
SELECT
    NULL,
    'Gudang',
    NULL,
    'bi-box-seam',
    30
WHERE NOT EXISTS (
    SELECT 1 FROM mst_menu WHERE LOWER(e_menu_name) = 'gudang'
);

INSERT INTO mst_menu
(id_parent, e_menu_name, e_menu_url, e_icon, n_sort_order)
SELECT
    NULL,
    'Pengiriman',
    NULL,
    'bi-truck',
    40
WHERE NOT EXISTS (
    SELECT 1 FROM mst_menu WHERE LOWER(e_menu_name) = 'pengiriman'
);

INSERT INTO mst_menu
(id_parent, e_menu_name, e_menu_url, e_icon, n_sort_order)
SELECT
    NULL,
    'Pelanggan',
    NULL,
    'bi-people',
    50
WHERE NOT EXISTS (
    SELECT 1 FROM mst_menu WHERE LOWER(e_menu_name) = 'pelanggan'
);

INSERT INTO mst_menu
(id_parent, e_menu_name, e_menu_url, e_icon, n_sort_order)
SELECT
    NULL,
    'Keuangan',
    NULL,
    'bi-cash-coin',
    60
WHERE NOT EXISTS (
    SELECT 1 FROM mst_menu WHERE LOWER(e_menu_name) = 'keuangan'
);

INSERT INTO mst_menu
(id_parent, e_menu_name, e_menu_url, e_icon, n_sort_order)
SELECT
    NULL,
    'Setting',
    NULL,
    'bi-gear',
    70
WHERE NOT EXISTS (
    SELECT 1 FROM mst_menu WHERE LOWER(e_menu_name) = 'setting'
);

COMMIT;