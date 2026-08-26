<?php
// Expects: $active (string key of current page), $username (string)
$active = isset($active) ? $active : '';
function nav_active($key, $active) {
    return $key === $active ? ' active fw-semibold text-white' : '';
}
?>
<style>
    #mainNavbar .dropdown-menu-scrollable {
        max-height: min(60vh, 560px);
        max-width: min(420px, calc(100vw - 2rem));
        overflow-x: hidden;
        overflow-y: auto;
    }

    #mainNavbar .dropdown-menu-scrollable .dropdown-item {
        white-space: normal;
    }
</style>

<nav class="navbar navbar-dark bg-dark mb-4 navbar-expand-lg" id="mainNavbar">
    <div class="container-fluid">
        <a class="navbar-brand mb-0" href="<?= site_url('home'); ?>">
            <i class="bi bi-shop me-2"></i>Online Shop
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavCollapse" aria-controls="mainNavCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavCollapse">
            <ul class="navbar-nav me-auto gap-1">

                <li class="nav-item">
                    <a class="nav-link<?= nav_active('home', $active); ?>" href="<?= site_url('home'); ?>">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= nav_active('product', $active) . nav_active('product_type', $active) . nav_active('brand', $active); ?>" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-database me-1"></i>Master
                    </a>
                    <ul class="dropdown-menu dropdown-menu-scrollable">
                        <li><h6 class="dropdown-header">Produk</h6></li>
                        <li><a class="dropdown-item" href="<?= site_url('product'); ?>"><i class="bi bi-list-ul me-2"></i>Produk</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('product_type'); ?>"><i class="bi bi-diagram-3 me-2"></i>Kategori Produk</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('brand'); ?>"><i class="bi bi-award me-2"></i>Brand</a></li>

                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Ekspedisi</h6></li>
                        <li><a class="dropdown-item" href="<?= site_url('courier'); ?>"><i class="bi bi-signpost-split me-2"></i>Ekspedisi / Kurir</a></li>

                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Pembayaran &amp; Pajak</h6></li>
                        <li><a class="dropdown-item" href="<?= site_url('bank_account'); ?>"><i class="bi bi-credit-card me-2"></i>Rekening</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('tax'); ?>"><i class="bi bi-cash-coin me-2"></i>Jenis Pajak</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= nav_active('cart', $active) . nav_active('transaction', $active) . nav_active('retur', $active) . nav_active('banner', $active); ?>" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-cart-check me-1"></i>Penjualan
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= site_url('cart'); ?>"><i class="bi bi-basket me-2"></i>Order Online</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('transaction'); ?>"><i class="bi bi-receipt me-2"></i>Daftar Transaksi</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('retur'); ?>"><i class="bi bi-arrow-return-left me-2"></i>Retur &amp; Refund</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('banner'); ?>"><i class="bi bi-image me-2"></i>Banner Promo</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-truck me-1"></i>Pengiriman
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= site_url('shipment'); ?>"><i class="bi bi-box-seam-fill me-2"></i>Daftar Pengiriman / Bukti Terkirim</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= nav_active('address', $active) . nav_active('bank_account', $active) . nav_active('review', $active); ?>" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-people me-1"></i>Pelanggan
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= site_url('address'); ?>"><i class="bi bi-geo-alt me-2"></i>Daftar Alamat</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('bank_account'); ?>"><i class="bi bi-credit-card me-2"></i>Daftar Rekening</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('review'); ?>"><i class="bi bi-star me-2"></i>Review Produk</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= nav_active('tax', $active); ?>" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-cash-coin me-1"></i>Keuangan
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= site_url('transaction'); ?>"><i class="bi bi-receipt-cutoff me-2"></i>Faktur Penjualan</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('tax'); ?>"><i class="bi bi-percent me-2"></i>Pajak</a></li>
                    </ul>
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

<script>
(function () {
    var navbar = document.getElementById('mainNavbar');
    if (!navbar || !window.bootstrap) return;

    navbar.addEventListener('click', function (event) {
        var clickedItem = event.target.closest('.dropdown-item');
        if (clickedItem) {
            var dropdown = clickedItem.closest('.dropdown');
            if (dropdown) {
                var toggle = dropdown.querySelector('[data-bs-toggle="dropdown"]');
                var instance = toggle ? bootstrap.Dropdown.getInstance(toggle) : null;
                if (instance) instance.hide();
            }
        }
    });

    document.addEventListener('click', function (event) {
        if (!navbar.contains(event.target)) {
            navbar.querySelectorAll('.dropdown-menu.show').forEach(function (menu) {
                var toggle = menu.parentElement.querySelector('[data-bs-toggle="dropdown"]');
                var instance = toggle ? bootstrap.Dropdown.getInstance(toggle) : null;
                if (instance) instance.hide();
            });
        }
    });
})();
</script>
