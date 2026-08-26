-- =====================================================================
-- FINAL DATA CONSISTENCY FIX v2 — PostgreSQL
-- Run this AFTER the existing schema files.
-- Safe to rerun: UPDATE/INSERT only, no DROP/TRUNCATE/DELETE.
-- =====================================================================

BEGIN;

-- ---------------------------------------------------------------------
-- 1) Canonical customer names.
-- IDs below match the current mst_user rows shown in DBeaver.
-- ---------------------------------------------------------------------
UPDATE mst_user SET i_username = 'admin',       e_name = 'Administrator' WHERE id_user = 1;
UPDATE mst_user SET i_username = 'Paimon',      e_name = 'Paimon'        WHERE id_user = 2;
UPDATE mst_user SET i_username = 'Furina',      e_name = 'Furina'        WHERE id_user = 3;
UPDATE mst_user SET i_username = 'Raiden',      e_name = 'Raiden'        WHERE id_user = 4;
UPDATE mst_user SET i_username = 'Nahida',      e_name = 'Nahida'        WHERE id_user = 5;
UPDATE mst_user SET i_username = 'Zhongli',     e_name = 'Zhongli'       WHERE id_user = 6;
UPDATE mst_user SET i_username = 'Venti',       e_name = 'Venti'         WHERE id_user = 7;
UPDATE mst_user SET i_username = 'Neuvillette', e_name = 'Neuvillette'   WHERE id_user = 8;
UPDATE mst_user SET i_username = 'Kazuha',      e_name = 'Kazuha'        WHERE id_user = 9;
UPDATE mst_user SET i_username = 'Firefly',     e_name = 'Firefly'       WHERE id_user = 10;
UPDATE mst_user SET i_username = 'Kafka',       e_name = 'Kafka'         WHERE id_user = 11;
UPDATE mst_user SET i_username = 'Acheron',     e_name = 'Acheron'       WHERE id_user = 12;
UPDATE mst_user SET i_username = 'Jingliu',     e_name = 'Jingliu'       WHERE id_user = 13;
UPDATE mst_user SET i_username = 'Navia',       e_name = 'Navia'         WHERE id_user = 14;
UPDATE mst_user SET i_username = 'Clorinde',    e_name = 'Clorinde'      WHERE id_user = 15;
UPDATE mst_user SET i_username = 'Hutao',       e_name = 'Hutao'         WHERE id_user = 16;
UPDATE mst_user SET i_username = 'Ayaka',       e_name = 'Ayaka'         WHERE id_user = 17;
UPDATE mst_user SET i_username = 'Xiao',        e_name = 'Xiao'          WHERE id_user = 18;
UPDATE mst_user SET i_username = 'Diluc',       e_name = 'Diluc'         WHERE id_user = 19;

-- ---------------------------------------------------------------------
-- 2) Every address recipient follows the owner in mst_user.
-- This fixes cases such as Acheron -> Siti Nurhaliza.
-- ---------------------------------------------------------------------
UPDATE mst_address a
SET e_recipient = u.e_name
FROM mst_user u
WHERE u.id_user = a.id_user;

-- ---------------------------------------------------------------------
-- 3) Every bank-account holder follows mst_user.
-- ---------------------------------------------------------------------
UPDATE mst_bank_account b
SET e_account_holder = u.e_name
FROM mst_user u
WHERE u.id_user = b.id_user;

-- ---------------------------------------------------------------------
-- 4) Ensure each customer has exactly one primary bank account when
-- accounts exist. Keep the lowest bank-account id as primary.
-- ---------------------------------------------------------------------
WITH ranked AS (
    SELECT
        id_bank_account,
        id_user,
        ROW_NUMBER() OVER (PARTITION BY id_user ORDER BY id_bank_account) AS rn
    FROM mst_bank_account
)
UPDATE mst_bank_account b
SET f_primary = CASE WHEN r.rn = 1 THEN 't' ELSE 'f' END
FROM ranked r
WHERE r.id_bank_account = b.id_bank_account;

-- ---------------------------------------------------------------------
-- 5) Make every transaction point to the primary bank account of the
-- same customer. This keeps payment/account details consistent.
-- ---------------------------------------------------------------------
UPDATE trx_transaction t
SET id_bank_account = b.id_bank_account
FROM mst_bank_account b
WHERE b.id_user = t.id_user
  AND b.f_primary = 't';

-- ---------------------------------------------------------------------
-- 6) Normalize ALL invoice numbers. No more INV/SEED2/... mixed formats.
-- ---------------------------------------------------------------------
UPDATE trx_transaction
SET i_invoice = 'TMP-FIX-' || id_transaction;

WITH numbered AS (
    SELECT
        id_transaction,
        ROW_NUMBER() OVER (ORDER BY id_transaction) AS seq
    FROM trx_transaction
)
UPDATE trx_transaction t
SET i_invoice = 'INV/2026/08/' || LPAD(n.seq::text, 4, '0')
FROM numbered n
WHERE t.id_transaction = n.id_transaction;

-- ---------------------------------------------------------------------
-- 7) Normalize customer-facing transaction dates to the existing
-- August 2026 demo period when they are null. Existing dates remain.
-- ---------------------------------------------------------------------
UPDATE trx_transaction
SET dt_created = COALESCE(dt_created, TIMESTAMP '2026-08-22 11:12:00');

COMMIT;

-- Verification queries:
-- SELECT id_user, i_username, e_name FROM mst_user ORDER BY id_user;
-- SELECT a.id_address, a.id_user, u.i_username, a.e_recipient FROM mst_address a JOIN mst_user u ON u.id_user=a.id_user ORDER BY a.id_address;
-- SELECT t.id_transaction, t.i_invoice, u.i_username, t.id_bank_account FROM trx_transaction t JOIN mst_user u ON u.id_user=t.id_user ORDER BY t.id_transaction;
