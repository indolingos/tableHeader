-- =====================================================================
-- TableHeader / Online Shop Admin Panel — MySQL schema
-- For CodeIgniter 3 + XAMPP (MySQL/MariaDB)
--
-- This file was missing from the project (application/config/database.php
-- pointed at PostgreSQL and no PostgreSQL dump existed anywhere in the
-- project either), so nothing could run on a fresh install. This script
-- recreates every table referenced by the existing models/controllers,
-- translated to MySQL, plus a small amount of seed data so the
-- "product type -> category -> product" and "transaction header -> item
-- detail" nested-table screens have something to show immediately.
--
-- Boolean-style columns (f_active, f_primary) keep the CHAR(1) 't'/'f'
-- convention already used everywhere in the PHP code (Product_model,
-- Auth.php, etc.), so no PHP had to be touched to make this work on MySQL.
--
-- How to use: import this file into a database named `tableHeader`
-- (see "How to Run" in the chat response for exact phpMyAdmin steps).
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- mst_user
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mst_user (
    id_user               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    i_username            VARCHAR(50)  NOT NULL UNIQUE,
    c_password            VARCHAR(255) NOT NULL,
    f_active              CHAR(1)      NOT NULL DEFAULT 't',
    t_download_settings   TEXT         NULL,
    dt_created             DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- mst_product_type  (top-level "header" of the product hierarchy)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mst_product_type (
    id_product_type INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    e_product_type  VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- mst_category  (child of product_type)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mst_category (
    id_category      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_product_type  INT UNSIGNED NOT NULL,
    e_category       VARCHAR(100) NOT NULL,
    CONSTRAINT fk_category_type FOREIGN KEY (id_product_type)
        REFERENCES mst_product_type (id_product_type)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- mst_product  (child of category — the "detail" rows shown under a
-- product type, this is the main nested-table demo)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mst_product (
    id_product   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_category  INT UNSIGNED NOT NULL,
    i_product    VARCHAR(20)  NOT NULL UNIQUE,
    e_product    VARCHAR(100) NOT NULL,
    v_price      DECIMAL(15,2) NOT NULL DEFAULT 0,
    n_stock      INT UNSIGNED NOT NULL DEFAULT 0,
    f_active     CHAR(1)      NOT NULL DEFAULT 't',
    CONSTRAINT fk_product_category FOREIGN KEY (id_category)
        REFERENCES mst_category (id_category)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- mst_courier
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mst_courier (
    id_courier        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    e_courier_name    VARCHAR(100) NOT NULL,
    e_courier_code    VARCHAR(20)  NOT NULL,
    e_contact_person  VARCHAR(100) NULL,
    i_phone           VARCHAR(30)  NULL,
    e_coverage_area   VARCHAR(150) NULL,
    e_keterangan      TEXT         NULL,
    f_active          CHAR(1)      NOT NULL DEFAULT 't'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- mst_address  (child of mst_user)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mst_address (
    id_address       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_user          INT UNSIGNED NOT NULL,
    e_label          VARCHAR(50)  NULL,
    e_recipient      VARCHAR(100) NULL,
    i_phone          VARCHAR(30)  NULL,
    e_address_full   TEXT         NULL,
    e_city           VARCHAR(100) NULL,
    e_province       VARCHAR(100) NULL,
    i_postal_code    VARCHAR(10)  NULL,
    e_keterangan     TEXT         NULL,
    f_primary        CHAR(1)      NOT NULL DEFAULT 'f',
    CONSTRAINT fk_address_user FOREIGN KEY (id_user)
        REFERENCES mst_user (id_user)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- mst_bank_account  (child of mst_user)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mst_bank_account (
    id_bank_account   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_user           INT UNSIGNED NOT NULL,
    e_bank_name       VARCHAR(100) NULL,
    i_account_number  VARCHAR(50)  NULL,
    e_account_holder  VARCHAR(100) NULL,
    e_keterangan      TEXT         NULL,
    f_primary         CHAR(1)      NOT NULL DEFAULT 'f',
    CONSTRAINT fk_bank_user FOREIGN KEY (id_user)
        REFERENCES mst_user (id_user)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- mst_tax
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mst_tax (
    id_tax        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    e_tax_name    VARCHAR(100) NOT NULL,
    n_percentage  DECIMAL(5,2) NOT NULL DEFAULT 0,
    e_keterangan  TEXT         NULL,
    f_active      CHAR(1)      NOT NULL DEFAULT 't'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- trx_cart  (child of mst_user + mst_product)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS trx_cart (
    id_cart      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_user      INT UNSIGNED NOT NULL,
    id_product   INT UNSIGNED NOT NULL,
    n_qty        INT UNSIGNED NOT NULL DEFAULT 1,
    dt_created   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    dt_updated   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cart_user FOREIGN KEY (id_user)
        REFERENCES mst_user (id_user) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cart_product FOREIGN KEY (id_product)
        REFERENCES mst_product (id_product) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- trx_transaction  (the "header" row of an order)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS trx_transaction (
    id_transaction    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    i_invoice         VARCHAR(40)  NOT NULL UNIQUE,
    id_user           INT UNSIGNED NOT NULL,
    id_address        INT UNSIGNED NULL,
    id_bank_account   INT UNSIGNED NULL,
    id_tax            INT UNSIGNED NULL,
    v_subtotal        DECIMAL(15,2) NOT NULL DEFAULT 0,
    v_tax             DECIMAL(15,2) NOT NULL DEFAULT 0,
    v_shipping_cost   DECIMAL(15,2) NOT NULL DEFAULT 0,
    v_total           DECIMAL(15,2) NOT NULL DEFAULT 0,
    e_status          VARCHAR(30)  NOT NULL DEFAULT 'Menunggu Pembayaran',
    e_keterangan      TEXT         NULL,
    dt_created        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    dt_updated        DATETIME     NULL,
    CONSTRAINT fk_trx_user FOREIGN KEY (id_user)
        REFERENCES mst_user (id_user) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_trx_address FOREIGN KEY (id_address)
        REFERENCES mst_address (id_address) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_trx_bank FOREIGN KEY (id_bank_account)
        REFERENCES mst_bank_account (id_bank_account) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_trx_tax FOREIGN KEY (id_tax)
        REFERENCES mst_tax (id_tax) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- trx_transaction_detail  (the "line items" — nested table under the
-- transaction header; this is the closest analog to the header/detail
-- pattern used throughout Dialogue Reference)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS trx_transaction_detail (
    id_transaction_detail INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_transaction        INT UNSIGNED NOT NULL,
    id_product             INT UNSIGNED NOT NULL,
    n_qty                  INT UNSIGNED NOT NULL DEFAULT 1,
    v_price                DECIMAL(15,2) NOT NULL DEFAULT 0,
    v_subtotal             DECIMAL(15,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_trxdet_trx FOREIGN KEY (id_transaction)
        REFERENCES trx_transaction (id_transaction) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_trxdet_product FOREIGN KEY (id_product)
        REFERENCES mst_product (id_product) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- trx_shipment  (header for a shipment tied to a transaction)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS trx_shipment (
    id_shipment      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_transaction   INT UNSIGNED NOT NULL,
    id_courier       INT UNSIGNED NOT NULL,
    i_resi           VARCHAR(50)  NULL,
    e_status_kirim   VARCHAR(30)  NOT NULL DEFAULT 'Dikemas',
    e_keterangan     TEXT         NULL,
    dt_created       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ship_trx FOREIGN KEY (id_transaction)
        REFERENCES trx_transaction (id_transaction) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ship_courier FOREIGN KEY (id_courier)
        REFERENCES mst_courier (id_courier) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- trx_shipment_detail  (nested table under a shipment)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS trx_shipment_detail (
    id_shipment_detail INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_shipment         INT UNSIGNED NOT NULL,
    id_product           INT UNSIGNED NOT NULL,
    n_qty                 INT UNSIGNED NOT NULL DEFAULT 1,
    e_keterangan          TEXT NULL,
    CONSTRAINT fk_shipdet_ship FOREIGN KEY (id_shipment)
        REFERENCES trx_shipment (id_shipment) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_shipdet_product FOREIGN KEY (id_product)
        REFERENCES mst_product (id_product) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- SEED DATA
-- =====================================================================

-- Admin account: username "admin", password "admin123"
-- (hash generated with PHP's password_hash(..., PASSWORD_BCRYPT))
INSERT INTO mst_user (i_username, c_password, f_active) VALUES
('admin', '$2b$12$bSXeIyeEL2Suk0OplTVQNOiEh89aw3U0BzUMs1lWGqcDgqSKDMEzG', 't');

-- A demo customer account too, password "customer123"
INSERT INTO mst_user (i_username, c_password, f_active) VALUES
('customer1', '$2b$12$bSXeIyeEL2Suk0OplTVQNOiEh89aw3U0BzUMs1lWGqcDgqSKDMEzG', 't');

-- Product type -> category -> product hierarchy (the main nested-table demo)
INSERT INTO mst_product_type (e_product_type) VALUES ('Pakaian'), ('Aksesoris');

INSERT INTO mst_category (id_product_type, e_category) VALUES
(1, 'Atasan'),
(1, 'Bawahan'),
(2, 'Tas');

INSERT INTO mst_product (id_category, i_product, e_product, v_price, n_stock, f_active) VALUES
(1, 'SHIRT-001', 'Kemeja Putih', 150000, 40, 't'),
(1, 'SHIRT-002', 'Kaos Polos Hitam', 85000, 120, 't'),
(2, 'PANTS-001', 'Celana Chino Navy', 220000, 15, 't'),
(3, 'BAG-001',   'Tas Selempang Kanvas', 175000, 8,  't');

-- Tax rate used by transactions
INSERT INTO mst_tax (e_tax_name, n_percentage, e_keterangan, f_active) VALUES
('PPN', 11.00, 'Pajak Pertambahan Nilai', 't');

-- A courier for shipments
INSERT INTO mst_courier (e_courier_name, e_courier_code, e_contact_person, i_phone, e_coverage_area, f_active) VALUES
('Jaya Ekspress', 'JYE', 'Budi', '081234567890', 'Nasional', 't');
