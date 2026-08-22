<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Online Shop</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .navbar-brand { font-weight: 600; }
    .stat-card {
        border: none;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
        height: 100%;
    }
    .stat-card .stat-icon { font-size: 1.75rem; color: #0d6efd; }
    .stat-card .stat-value { font-size: 1.6rem; font-weight: 700; }
    .home-card {
        border: none;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
        transition: transform .15s ease, box-shadow .15s ease;
        height: 100%;
    }
    .home-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.12);
    }
    .home-card .card-body { text-align: center; padding: 2rem 1.25rem; }
    .home-card .home-icon { font-size: 2.25rem; color: #0d6efd; margin-bottom: .75rem; }
    .shop-desc-card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    .section-label {
        text-transform: uppercase;
        font-size: .78rem;
        letter-spacing: .04em;
        color: #6c757d;
        font-weight: 600;
    }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'home', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <!-- Deskripsi toko online -->
    <div class="card shop-desc-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-start gap-3">
                <i class="bi bi-shop" style="font-size:2.5rem;color:#0d6efd;"></i>
                <div>
                    <h4 class="mb-1">CV Creative Indoling Apparel — Online Shop</h4>
                    <p class="text-muted mb-0">
                        Panel admin untuk mengelola seluruh operasional toko online: mulai dari
                        katalog product, pembelian konsumen, transaksi, pengiriman, data pelanggan
                        (alamat &amp; rekening), sampai pajak dan keuangan. Semua data bisa
                        ditelusuri lebih dalam lewat tabel-di-dalam-tabel pada setiap menu.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik ringkas -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-box-seam stat-icon"></i>
                    <div>
                        <div class="stat-value"><?= (int) $stats['n_products']; ?></div>
                        <div class="text-muted small">Product</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-people stat-icon"></i>
                    <div>
                        <div class="stat-value"><?= (int) $stats['n_customers']; ?></div>
                        <div class="text-muted small">Konsumen Aktif</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-receipt stat-icon"></i>
                    <div>
                        <div class="stat-value"><?= (int) $stats['n_transactions']; ?></div>
                        <div class="text-muted small">Transaksi</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-cash-coin stat-icon"></i>
                    <div>
                        <div class="stat-value">Rp<?= number_format((float) $stats['v_total_sales'], 0, ',', '.'); ?></div>
                        <div class="text-muted small">Total Penjualan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Group: Product -->
    <div class="section-label mb-2">Product</div>
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-4">
            <a href="<?= site_url('product'); ?>" class="text-decoration-none">
                <div class="card home-card">
                    <div class="card-body">
                        <i class="bi bi-box-seam home-icon"></i>
                        <h6 class="text-dark mb-1">Product List</h6>
                        <p class="text-muted mb-0 small">Kelola data master product.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <a href="<?= site_url('cart'); ?>" class="text-decoration-none">
                <div class="card home-card">
                    <div class="card-body">
                        <i class="bi bi-cart-check home-icon"></i>
                        <h6 class="text-dark mb-1">Data Pembelian Konsumen</h6>
                        <p class="text-muted mb-0 small">Lihat dashboard daftar beli konsumen per user.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <a href="<?= site_url('product_type'); ?>" class="text-decoration-none">
                <div class="card home-card">
                    <div class="card-body">
                        <i class="bi bi-diagram-3 home-icon"></i>
                        <h6 class="text-dark mb-1">Jenis-jenis Product</h6>
                        <p class="text-muted mb-0 small">Kelola kategori dan jenis product.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Group: Transaksi & Pengiriman -->
    <div class="section-label mb-2">Transaksi &amp; Pengiriman</div>
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-4">
            <a href="<?= site_url('transaction'); ?>" class="text-decoration-none">
                <div class="card home-card">
                    <div class="card-body">
                        <i class="bi bi-receipt home-icon"></i>
                        <h6 class="text-dark mb-1">Daftar Transaksi</h6>
                        <p class="text-muted mb-0 small">Riwayat pembelian, status, dan detail barang.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <a href="<?= site_url('shipment'); ?>" class="text-decoration-none">
                <div class="card home-card">
                    <div class="card-body">
                        <i class="bi bi-box-seam-fill home-icon"></i>
                        <h6 class="text-dark mb-1">Bukti Barang Terkirim</h6>
                        <p class="text-muted mb-0 small">Status pengiriman dan bukti foto barang.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <a href="<?= site_url('courier'); ?>" class="text-decoration-none">
                <div class="card home-card">
                    <div class="card-body">
                        <i class="bi bi-signpost-split home-icon"></i>
                        <h6 class="text-dark mb-1">Distributor / Petugas Ongkir</h6>
                        <p class="text-muted mb-0 small">Daftar kurir/ekspedisi (JNT, JNE, dll).</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Group: Pelanggan & Keuangan -->
    <div class="section-label mb-2">Pelanggan &amp; Keuangan</div>
    <div class="row g-4">
        <div class="col-12 col-md-6 col-lg-4">
            <a href="<?= site_url('address'); ?>" class="text-decoration-none">
                <div class="card home-card">
                    <div class="card-body">
                        <i class="bi bi-geo-alt home-icon"></i>
                        <h6 class="text-dark mb-1">Daftar Alamat</h6>
                        <p class="text-muted mb-0 small">Alamat pengiriman milik tiap konsumen.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <a href="<?= site_url('bank_account'); ?>" class="text-decoration-none">
                <div class="card home-card">
                    <div class="card-body">
                        <i class="bi bi-credit-card home-icon"></i>
                        <h6 class="text-dark mb-1">Daftar Rekening Pembeli</h6>
                        <p class="text-muted mb-0 small">Rekening bank milik tiap konsumen.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <a href="<?= site_url('tax'); ?>" class="text-decoration-none">
                <div class="card home-card">
                    <div class="card-body">
                        <i class="bi bi-cash-coin home-icon"></i>
                        <h6 class="text-dark mb-1">Pajak &amp; Keuangan</h6>
                        <p class="text-muted mb-0 small">Rekap pendapatan toko dan master pajak.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

</div>

</body>
</html>
