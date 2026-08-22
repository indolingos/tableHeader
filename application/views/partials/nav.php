<?php
// Expects: $active (string key of current page), $username (string)
$active = isset($active) ? $active : '';
function nav_active($key, $active) { return $key === $active ? ' active fw-semibold text-white' : ''; }
?>
<nav class="navbar navbar-dark bg-dark mb-4 navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand mb-0" href="<?= site_url('home'); ?>">
            <i class="bi bi-shop me-2"></i>Online Shop
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavCollapse">
            <ul class="navbar-nav me-auto gap-1">

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= nav_active('product', $active) . nav_active('product_type', $active); ?>" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-box-seam me-1"></i>Produk
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= site_url('product'); ?>"><i class="bi bi-list-ul me-2"></i>Product List</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('product_type'); ?>"><i class="bi bi-diagram-3 me-2"></i>Jenis-jenis Product</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= nav_active('cart', $active) . nav_active('transaction', $active); ?>" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-cart-check me-1"></i>Transaksi
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= site_url('cart'); ?>"><i class="bi bi-basket me-2"></i>Data Pembelian Konsumen</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('transaction'); ?>"><i class="bi bi-receipt me-2"></i>Daftar Transaksi</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= nav_active('shipment', $active) . nav_active('courier', $active); ?>" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-truck me-1"></i>Pengiriman
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= site_url('shipment'); ?>"><i class="bi bi-box-seam-fill me-2"></i>Bukti Barang Terkirim</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('courier'); ?>"><i class="bi bi-signpost-split me-2"></i>Daftar Distributor / Petugas Ongkir</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= nav_active('address', $active) . nav_active('bank_account', $active); ?>" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-people me-1"></i>Pelanggan
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= site_url('address'); ?>"><i class="bi bi-geo-alt me-2"></i>Daftar Alamat</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('bank_account'); ?>"><i class="bi bi-credit-card me-2"></i>Daftar Rekening Pembeli</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link<?= nav_active('tax', $active); ?>" href="<?= site_url('tax'); ?>">
                        <i class="bi bi-cash-coin me-1"></i>Pajak &amp; Keuangan
                    </a>
                </li>

            </ul>
            <div class="d-flex align-items-center gap-3">
                <span class="text-light small">
                    <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($username ?: '-'); ?>
                </span>
                <a href="<?= site_url('auth/logout'); ?>" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </div>
</nav>
