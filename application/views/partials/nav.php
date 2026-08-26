<?php
// Expects: $active (string key of current page), $username (string)
$active = isset($active) ? $active : '';
function nav_active($key, $active) { return $key === $active ? ' active fw-semibold text-white' : ''; }

// Item baru yang skema-nya sudah ada (lihat online_shop_schema_v2_postgres.sql)
// tapi controller/view-nya belum dibuat. Ditandai badge "Segera" & non-klik
// supaya menu tidak 404 sebelum modulnya benar-benar dibangun.
function nav_soon($label, $icon) {
    return '<li><a class="dropdown-item disabled d-flex justify-content-between align-items-center" href="#" tabindex="-1" aria-disabled="true">'
        . '<span><i class="bi ' . $icon . ' me-2"></i>' . $label . '</span>'
        . '<span class="badge bg-secondary ms-2">Segera</span></a></li>';
}
?>
<style>
    /* Keep long Master menus usable without covering most of the page. */
    .dropdown-menu-scrollable {
        max-height: min(60vh, 560px) !important;
        max-width: min(420px, calc(100vw - 2rem));
        overflow-x: hidden;
        overflow-y: auto;
    }

    .dropdown-menu-scrollable .dropdown-item {
        white-space: normal;
    }
</style>

<nav class="navbar navbar-dark bg-dark mb-4 navbar-expand-lg" id="mainNavbar">
    <div class="container-fluid">
        <a class="navbar-brand mb-0" href="<?= site_url('home'); ?>">
            <i class="bi bi-shop me-2"></i>Online Shop
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavCollapse">
            <ul class="navbar-nav me-auto gap-1">

                <li class="nav-item">
                    <a class="nav-link<?= nav_active('home', $active); ?>" href="<?= site_url('home'); ?>">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>

                <!-- ============================== MASTER ============================== -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= nav_active('product', $active) . nav_active('product_type', $active) . nav_active('brand', $active); ?>" href="#" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                        <i class="bi bi-database me-1"></i>Master
                    </a>
                    <ul class="dropdown-menu dropdown-menu-scrollable">

                        <li><h6 class="dropdown-header">Produk</h6></li>
                        <li><a class="dropdown-item" href="<?= site_url('product'); ?>"><i class="bi bi-list-ul me-2"></i>Produk</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('product_type'); ?>"><i class="bi bi-diagram-3 me-2"></i>Kategori Produk</a></li>
                        <?= nav_soon('Sub Kategori Produk', 'bi-diagram-2'); ?>
                        <li><a class="dropdown-item" href="<?= site_url('brand'); ?>"><i class="bi bi-award me-2"></i>Brand</a></li>
                        <?= nav_soon('Series / Tipe', 'bi-tags'); ?>
                        <?= nav_soon('Variasi Produk (Warna/Ukuran)', 'bi-palette'); ?>
                        <?= nav_soon('Atribut Kategori (EAV)', 'bi-sliders'); ?>
                        <?= nav_soon('Satuan', 'bi-rulers'); ?>
                        <li><hr class="dropdown-divider"></li>

                        <li><h6 class="dropdown-header">Harga</h6></li>
                        <?= nav_soon('Harga Promo', 'bi-percent'); ?>
                        <?= nav_soon('Harga Grosir', 'bi-boxes'); ?>
                        <li><hr class="dropdown-divider"></li>

                        <li><h6 class="dropdown-header">Customer</h6></li>
                        <?= nav_soon('Grup Customer', 'bi-people-fill'); ?>
                        <?= nav_soon('Level Customer', 'bi-star'); ?>
                        <?= nav_soon('Status Customer', 'bi-person-check'); ?>
                        <li><hr class="dropdown-divider"></li>

                        <li><h6 class="dropdown-header">Gudang</h6></li>
                        <?= nav_soon('Gudang', 'bi-building'); ?>
                        <?= nav_soon('Lokasi & Rak Gudang', 'bi-grid-3x3-gap'); ?>
                        <li><hr class="dropdown-divider"></li>

                        <li><h6 class="dropdown-header">Ekspedisi</h6></li>
                        <li><a class="dropdown-item" href="<?= site_url('courier'); ?>"><i class="bi bi-signpost-split me-2"></i>Ekspedisi / Kurir</a></li>
                        <?= nav_soon('Jenis Pengiriman', 'bi-truck-front'); ?>
                        <?= nav_soon('Tarif Pengiriman', 'bi-cash-stack'); ?>
                        <li><hr class="dropdown-divider"></li>

                        <li><h6 class="dropdown-header">Pembayaran &amp; Pajak</h6></li>
                        <li><a class="dropdown-item" href="<?= site_url('bank_account'); ?>"><i class="bi bi-credit-card me-2"></i>Rekening</a></li>
                        <?= nav_soon('Cara Pembayaran', 'bi-wallet2'); ?>
                        <li><a class="dropdown-item" href="<?= site_url('tax'); ?>"><i class="bi bi-cash-coin me-2"></i>Jenis Pajak</a></li>
                    </ul>
                </li>

                <!-- ============================== PENJUALAN ============================== -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= nav_active('cart', $active) . nav_active('transaction', $active); ?>" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-cart-check me-1"></i>Penjualan
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= site_url('cart'); ?>"><i class="bi bi-basket me-2"></i>Order Online</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('transaction'); ?>"><i class="bi bi-receipt me-2"></i>Daftar Transaksi</a></li>
                        <?= nav_soon('Promo', 'bi-percent'); ?>
                        <?= nav_soon('Voucher', 'bi-ticket-perforated'); ?>
                        <?= nav_soon('Pengajuan Retur', 'bi-arrow-return-left'); ?>
                        <?= nav_soon('Refund', 'bi-cash-coin'); ?>
                    </ul>
                </li>

                <!-- ============================== GUDANG ============================== -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-box-seam me-1"></i>Gudang
                    </a>
                    <ul class="dropdown-menu">
                        <?= nav_soon('Stok Produk', 'bi-clipboard-data'); ?>
                        <?= nav_soon('Barang Masuk', 'bi-box-arrow-in-down'); ?>
                        <?= nav_soon('Barang Keluar', 'bi-box-arrow-up'); ?>
                        <?= nav_soon('Mutasi Stok', 'bi-arrow-left-right'); ?>
                        <?= nav_soon('Stock Opname', 'bi-clipboard-check'); ?>
                        <?= nav_soon('Adjustment Stok', 'bi-sliders2'); ?>
                    </ul>
                </li>

                <!-- ============================== PENGIRIMAN ============================== -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= nav_active('shipment', $active); ?>" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-truck me-1"></i>Pengiriman
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= site_url('shipment'); ?>"><i class="bi bi-box-seam-fill me-2"></i>Daftar Pengiriman / Bukti Terkirim</a></li>
                        <?= nav_soon('Surat Jalan', 'bi-file-earmark-text'); ?>
                        <?= nav_soon('Nomor Resi', 'bi-upc-scan'); ?>
                        <?= nav_soon('Tracking Pengiriman', 'bi-geo'); ?>
                    </ul>
                </li>

                <!-- ============================== PELANGGAN ============================== -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= nav_active('address', $active) . nav_active('bank_account', $active); ?>" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-people me-1"></i>Pelanggan
                    </a>
                    <ul class="dropdown-menu">
                        <?= nav_soon('Daftar Customer', 'bi-person-lines-fill'); ?>
                        <li><a class="dropdown-item" href="<?= site_url('address'); ?>"><i class="bi bi-geo-alt me-2"></i>Daftar Alamat</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('bank_account'); ?>"><i class="bi bi-credit-card me-2"></i>Daftar Rekening</a></li>
                        <?= nav_soon('Riwayat Pembelian', 'bi-clock-history'); ?>
                    </ul>
                </li>

                <!-- ============================== KEUANGAN ============================== -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= nav_active('tax', $active); ?>" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-cash-coin me-1"></i>Keuangan
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= site_url('transaction'); ?>"><i class="bi bi-receipt-cutoff me-2"></i>Faktur Penjualan</a></li>
                        <?= nav_soon('Pembayaran Customer', 'bi-credit-card-2-front'); ?>
                        <?= nav_soon('Kas/Bank Masuk', 'bi-arrow-down-circle'); ?>
                        <?= nav_soon('Kas/Bank Keluar', 'bi-arrow-up-circle'); ?>
                        <li><a class="dropdown-item" href="<?= site_url('tax'); ?>"><i class="bi bi-percent me-2"></i>Pajak</a></li>
                        <?= nav_soon('Laporan Penjualan', 'bi-graph-up'); ?>
                    </ul>
                </li>

                <!-- ============================== SETTING ============================== -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-gear me-1"></i>Setting
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?= nav_soon('User', 'bi-person-badge'); ?>
                        <?= nav_soon('Role', 'bi-person-gear'); ?>
                        <?= nav_soon('Hak Akses', 'bi-shield-lock'); ?>
                        <?= nav_soon('Menu', 'bi-list-nested'); ?>
                        <?= nav_soon('Pengaturan Toko', 'bi-shop-window'); ?>
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
    if (!navbar) return;

    function getOpenDropdowns() {
        return navbar.querySelectorAll('.dropdown-menu.show');
    }

    function hideDropdown(menu) {
        var toggle = menu.parentElement.querySelector('[data-bs-toggle="dropdown"]');
        if (toggle && window.bootstrap) {
            var instance = bootstrap.Dropdown.getInstance(toggle);
            if (instance) {
                instance.hide();
                return;
            }
        }

        menu.classList.remove('show');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
    }

    function closeAllDropdowns() {
        getOpenDropdowns().forEach(hideDropdown);
    }

    // Moving from the navbar into the page should immediately uncover the page.
    navbar.querySelectorAll('.nav-item.dropdown').forEach(function (dropdown) {
        dropdown.addEventListener('mouseleave', function () {
            window.setTimeout(function () {
                if (!dropdown.matches(':hover')) hideDropdown(dropdown.querySelector('.dropdown-menu'));
            }, 80);
        });
    });

    // Scrolling means the user is interacting with the page; don't leave a menu floating over it.
    window.addEventListener('scroll', closeAllDropdowns, { passive: true });

    // Keep Bootstrap's normal click-away behavior, with a fallback for cached/older Bootstrap state.
    document.addEventListener('click', function (event) {
        if (!navbar.contains(event.target)) closeAllDropdowns();
    }, true);
})();
</script>
