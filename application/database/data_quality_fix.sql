-- ================================================================
-- Online Shop — data quality / demo data repair (PostgreSQL)
-- Safe to run repeatedly: no DROP/TRUNCATE/DELETE.
-- Run this AFTER the v2 schema has been applied.
-- ================================================================

BEGIN;

-- 1) Keep login usernames lowercase, but make every display name proper.
--    mst_user.e_name is the display name used by the UI.
UPDATE mst_user
SET e_name = INITCAP(REPLACE(TRIM(i_username), '_', ' '))
WHERE i_username IS NOT NULL;

-- 2) Bank account owner must come from mst_user.e_name, never from stale
--    hard-coded names such as "Siti Nurhaliza".
UPDATE mst_bank_account b
SET e_account_holder = u.e_name
FROM mst_user u
WHERE u.id_user = b.id_user;

-- 3) Address recipient follows the same customer display name.
UPDATE mst_address a
SET e_recipient = u.e_name
FROM mst_user u
WHERE u.id_user = a.id_user;

-- 4) Add a bank account for every non-admin customer that currently has none.
--    Values are demo data only; they are deterministic and easy to replace.
INSERT INTO mst_bank_account (
    id_user, e_bank_name, i_account_number, e_account_holder,
    e_keterangan, f_primary
)
SELECT
    u.id_user,
    CASE ((u.id_user - 2) % 4)
        WHEN 0 THEN 'BCA'
        WHEN 1 THEN 'BNI'
        WHEN 2 THEN 'Mandiri'
        ELSE 'BRI'
    END,
    '88' || LPAD(u.id_user::text, 10, '0'),
    u.e_name,
    'Rekening pembayaran customer',
    't'
FROM mst_user u
WHERE u.i_username <> 'admin'
  AND NOT EXISTS (
      SELECT 1
      FROM mst_bank_account b
      WHERE b.id_user = u.id_user
  );

-- 5) Courier contacts: use Wuthering Waves character names as requested.
WITH contacts AS (
    SELECT * FROM (VALUES
        (1, 'Jiyan',       '081231000001'),
        (2, 'Changli',     '081231000002'),
        (3, 'Yinlin',      '081231000003'),
        (4, 'Shorekeeper','081231000004'),
        (5, 'Camellya',    '081231000005')
    ) AS x(id_courier, e_contact_person, i_phone)
)
UPDATE mst_courier c
SET e_contact_person = x.e_contact_person,
    i_phone = x.i_phone
FROM contacts x
WHERE c.id_courier = x.id_courier;

-- 6) Ensure the requested automotive brands exist.
INSERT INTO mst_brand (e_brand, e_keterangan, f_active)
SELECT v.e_brand, 'Brand kendaraan / otomotif', 't'
FROM (VALUES
    ('Toyota'),
    ('Kawasaki'),
    ('Honda'),
    ('Mercedes-Benz'),
    ('BMW'),
    ('Ducati')
) AS v(e_brand)
WHERE NOT EXISTS (
    SELECT 1 FROM mst_brand b
    WHERE LOWER(b.e_brand) = LOWER(v.e_brand)
);

-- 7) Ensure product types / categories exist for a richer product master.
INSERT INTO mst_product_type (e_product_type)
SELECT v.e_product_type
FROM (VALUES ('Handphone'), ('Jam Tangan'), ('Otomotif'), ('Speaker')) v(e_product_type)
WHERE NOT EXISTS (
    SELECT 1 FROM mst_product_type t
    WHERE LOWER(t.e_product_type) = LOWER(v.e_product_type)
);

INSERT INTO mst_category (id_product_type, e_category)
SELECT t.id_product_type, v.e_category
FROM (VALUES
    ('Handphone', 'Smartphone'),
    ('Jam Tangan', 'Smartwatch'),
    ('Otomotif', 'Motor'),
    ('Otomotif', 'Mobil'),
    ('Speaker', 'Portable Speaker')
) v(e_product_type, e_category)
JOIN mst_product_type t ON LOWER(t.e_product_type) = LOWER(v.e_product_type)
WHERE NOT EXISTS (
    SELECT 1 FROM mst_category c
    WHERE LOWER(c.e_category) = LOWER(v.e_category)
);

-- 8) Add a proper set of stock-bearing products. Existing products are kept.
INSERT INTO mst_product (
    id_category, i_product, e_product, v_price, n_stock, f_active,
    id_brand, id_unit
)
SELECT
    c.id_category,
    v.i_product,
    v.e_product,
    v.v_price,
    v.n_stock,
    't',
    b.id_brand,
    u.id_unit
FROM (VALUES
    ('Smartphone','HP-S25U','Samsung Galaxy S25 Ultra',21999000,18,'Samsung'),
    ('Smartphone','HP-IP16PM','iPhone 16 Pro Max',24999000,12,'Apple'),
    ('Smartphone','HP-PIX9P','Google Pixel 9 Pro',16999000,15,'Google'),
    ('Smartphone','HP-X14U','Xiaomi 14 Ultra',14999000,20,'Xiaomi'),
    ('Smartwatch','WT-GW7','Garmin Fenix 7',12999000,8,NULL),
    ('Smartwatch','WT-APP9','Apple Watch Series 9',7999000,14,'Apple'),
    ('Smartwatch','WT-GW6C','Casio G-Shock GA-2100',1899000,25,'Casio'),
    ('Motor','MTR-N250','Kawasaki Ninja 250',66200000,6,'Kawasaki'),
    ('Motor','MTR-CBR25','Honda CBR250RR',78000000,7,'Honda'),
    ('Motor','MTR-PANV2','Ducati Panigale V2',505000000,3,'Ducati'),
    ('Motor','MTR-R1300','BMW R 1300 GS',1000000000,2,'BMW'),
    ('Mobil','CAR-COR','Toyota Corolla Cross',540000000,4,'Toyota'),
    ('Mobil','CAR-C200','Mercedes-Benz C200',1240000000,2,'Mercedes-Benz'),
    ('Mobil','CAR-CAM','Toyota Camry',820000000,3,'Toyota'),
    ('Mobil','CAR-BX5','BMW X5',1700000000,2,'BMW'),
    ('Portable Speaker','SP-JP4','JBL PartyBox 320',8999000,10,NULL),
    ('Portable Speaker','SP-S8','Sony SRS-XV800',8499000,9,NULL),
    ('Portable Speaker','SP-XE300','JBL Xtreme 3',4999000,16,NULL),
    ('Portable Speaker','SP-SRS5','Sony ULT Field 5',6999000,11,NULL),
    ('Portable Speaker','SP-HK4','Harman Kardon Onyx Studio 8',4699000,13,NULL),
    ('Smartphone','HP-NP14','Nothing Phone (3a) Pro',8999000,17,NULL),
    ('Smartphone','HP-OP13','OnePlus 13',12999000,14,NULL),
    ('Smartwatch','WT-GW6','Garmin Vivoactive 5',4699000,19,NULL),
    ('Motor','MTR-Z900','Kawasaki Z900',154000000,5,'Kawasaki')
) v(category_name, i_product, e_product, v_price, n_stock, brand_name)
JOIN mst_category c ON LOWER(c.e_category) = LOWER(v.category_name)
LEFT JOIN mst_brand b ON v.brand_name IS NOT NULL AND LOWER(b.e_brand) = LOWER(v.brand_name)
LEFT JOIN mst_unit u ON LOWER(u.e_unit) = 'unit'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_product p WHERE LOWER(p.i_product) = LOWER(v.i_product)
);

-- If "unit" did not exist, create it and populate the newly inserted rows.
INSERT INTO mst_unit (e_unit)
SELECT 'unit'
WHERE NOT EXISTS (SELECT 1 FROM mst_unit WHERE LOWER(e_unit) = 'unit');
UPDATE mst_product p
SET id_unit = u.id_unit
FROM mst_unit u
WHERE LOWER(u.e_unit) = 'unit'
  AND p.id_unit IS NULL;

-- 9) Seed a main warehouse and stock detail for every active product.
INSERT INTO mst_warehouse (e_warehouse, e_address, f_active)
SELECT 'Gudang Utama', 'Jakarta', 't'
WHERE NOT EXISTS (
    SELECT 1 FROM mst_warehouse WHERE LOWER(e_warehouse) = 'gudang utama'
);

INSERT INTO mst_warehouse_location (id_warehouse, e_location_name)
SELECT w.id_warehouse, 'Rak Utama'
FROM mst_warehouse w
WHERE LOWER(w.e_warehouse) = 'gudang utama'
  AND NOT EXISTS (
      SELECT 1 FROM mst_warehouse_location wl
      WHERE wl.id_warehouse = w.id_warehouse
  );

INSERT INTO trx_stock (id_product, id_warehouse, id_rack, n_qty)
SELECT p.id_product, w.id_warehouse, r.id_rack, p.n_stock
FROM mst_product p
CROSS JOIN LATERAL (
    SELECT id_warehouse FROM mst_warehouse
    WHERE LOWER(e_warehouse) = 'gudang utama'
    ORDER BY id_warehouse LIMIT 1
) w
LEFT JOIN LATERAL (
    SELECT r2.id_rack
    FROM mst_rack r2
    JOIN mst_warehouse_location wl2 ON wl2.id_warehouse_location = r2.id_warehouse_location
    WHERE wl2.id_warehouse = w.id_warehouse
    ORDER BY r2.id_rack LIMIT 1
) r ON TRUE
WHERE p.f_active = 't'
  AND NOT EXISTS (
      SELECT 1 FROM trx_stock s
      WHERE s.id_product = p.id_product
        AND s.id_warehouse = w.id_warehouse
  );

-- 10) Make invoice numbers consistent and professional.
-- Temporary values avoid collisions with the unique invoice index.
UPDATE trx_transaction
SET i_invoice = 'TMP-FIX-' || id_transaction;

WITH numbered AS (
    SELECT id_transaction,
           ROW_NUMBER() OVER (ORDER BY id_transaction) AS seq
    FROM trx_transaction
)
UPDATE trx_transaction t
SET i_invoice = 'INV/2026/08/' || LPAD(n.seq::text, 4, '0')
FROM numbered n
WHERE t.id_transaction = n.id_transaction;

-- 11) Make transaction payment accounts follow the same user's account.
UPDATE trx_transaction t
SET id_bank_account = b.id_bank_account
FROM mst_bank_account b
WHERE b.id_user = t.id_user
  AND b.f_primary = 't';

COMMIT;

-- Verify quickly in DBeaver:
-- SELECT id_user, i_username, e_name FROM mst_user ORDER BY id_user;
-- SELECT b.id_bank_account, u.i_username, u.e_name, b.e_bank_name, b.e_account_holder
-- FROM mst_bank_account b JOIN mst_user u ON u.id_user=b.id_user ORDER BY b.id_user, b.id_bank_account;
-- SELECT id_transaction, i_invoice, id_user, id_bank_account FROM trx_transaction ORDER BY id_transaction;
-- SELECT id_brand, e_brand FROM mst_brand ORDER BY id_brand;
-- SELECT id_courier, e_courier_name, e_contact_person FROM mst_courier ORDER BY id_courier;
-- SELECT id_product, i_product, e_product, n_stock FROM mst_product ORDER BY id_product;
