<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Master Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .navbar-brand { font-weight: 600; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    .badge-status {
        font-size: .78rem;
        font-weight: 600;
        padding: .4em .65em;
    }
    .badge-habis   { background-color: #dc3545; }
    .badge-menipis { background-color: #fd7e14; }
    .badge-cukup   { background-color: #0d6efd; }
    .badge-banyak  { background-color: #198754; }
    #tableLoading { display: none; }
    .is-invalid ~ .invalid-feedback { display: block; }
    #kodeStatus { font-size: .8rem; margin-top: .25rem; display: block; }
    #kodeStatus.text-danger, #kodeStatus.text-success { display: block; }
    tr.row-deactivated { color: #adb5bd; background-color: #f8f9fa; }
    tr.row-deactivated .badge { opacity: .6; }
    tr.row-deactivated td { color: #adb5bd; }
    .active-icon.bi-check-circle-fill { color: #198754; }
    .active-icon.bi-x-circle-fill { color: #adb5bd; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'product', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div id="alertPlaceholder"></div>

    <div class="card mb-4">
        <div class="card-body">

            <h5 class="mb-3"><i class="bi bi-box-seam me-2"></i>Product Datas</h5>

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div class="input-group" style="max-width: 340px;">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari kode atau nama produk...">
                </div>
                <div class="d-flex gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-success" id="btnDownload" title="Download data">
                            <i class="bi bi-download me-1"></i>Download
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnDownloadSettings" title="Pengaturan download">
                            <i class="bi bi-gear"></i>
                        </button>
                    </div>
                    <?php if (empty($is_admin)): ?>
                    <button type="button" class="btn btn-outline-primary" id="btnAddToCart">
                        <i class="bi bi-cart-plus me-1"></i>Tambah ke Daftar Beli
                    </button>
                    <?php else: ?>
                    <button type="button" class="btn btn-primary" id="btnAdd">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Product
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr id="tableHeadRow">
                            <?php if (empty($is_admin)): ?>
                            <th style="width:36px;"><input type="checkbox" class="form-check-input" id="checkAllProducts" title="Pilih semua"></th>
                            <?php endif; ?>
                            <th style="width:40px;">No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Stock</th>
                            <th>Status</th>
                            <th class="text-center" style="width:70px;">Aktif</th>
                            <?php if (!empty($is_admin)): ?>
                            <th style="width:130px;">Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <tr id="tableLoading">
                            <td colspan="9" class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div>Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 gap-2">
                <div class="text-muted small" id="paginationInfo"></div>
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <label for="pageSizeSelect" class="text-muted small mb-0">Tampilkan</label>
                        <select id="pageSizeSelect" class="form-select form-select-sm" style="width:auto;">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="all">All</option>
                        </select>
                    </div>
                    <nav aria-label="Product pagination">
                        <ul class="pagination pagination-sm mb-0" id="paginationControls"></ul>
                    </nav>
                </div>
            </div>

        </div>
    </div>

    <?php if (empty($is_admin)): ?>
    <div class="card">
        <div class="card-body">

            <h5 class="mb-3"><i class="bi bi-cart-check me-2"></i>Barang yang mau dibeli konsumer</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end" style="width:110px;">Qty</th>
                            <th class="text-end">Subtotal</th>
                            <th style="width:70px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody">
                        <tr id="cartEmptyRow">
                            <td colspan="8" class="text-center text-muted py-4">Belum ada barang yang dipilih untuk dibeli.</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr id="cartTotalRow" class="d-none">
                            <th colspan="6" class="text-end">Total</th>
                            <th class="text-end" id="cartGrandTotal">Rp 0</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-shield-lock display-6 d-block mb-2"></i>
            <a href="<?= site_url('cart'); ?>">Buka Dashboard Pembelian Konsumen</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="productForm" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Tambah Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <input type="hidden" id="id_product" name="id_product">

                    <div class="mb-3">
                        <label class="form-label">Kode Produk <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="i_product" name="i_product" autocomplete="off" required>
                        <span id="kodeStatus"></span>
                        <div class="invalid-feedback">Kode produk wajib diisi.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="e_product" name="e_product" required>
                        <div class="invalid-feedback">Nama produk wajib diisi.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_category" name="id_category" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id_category']; ?>"><?= htmlspecialchars($cat['e_category']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Kategori wajib dipilih.</div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Harga <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="v_price" name="v_price" min="0" step="0.01" required>
                            <div class="invalid-feedback">Harga wajib diisi dan tidak boleh negatif.</div>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Stock <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="n_stock" name="n_stock" min="0" step="1" required>
                            <div class="invalid-feedback">Stock wajib diisi dan tidak boleh negatif.</div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSave">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="saveSpinner"></span>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Yakin ingin menghapus product <strong id="deleteProductName"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDelete">
                    <span class="spinner-border spinner-border-sm d-none me-1" id="deleteSpinner"></span>Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="restoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise text-success me-2"></i>Konfirmasi Restore</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Yakin ingin mengaktifkan kembali product <strong id="restoreProductName"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnConfirmRestore">
                    <span class="spinner-border spinner-border-sm d-none me-1" id="restoreSpinner"></span>Restore
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="downloadSettingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="downloadSettingsForm">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-gear me-2"></i>Pengaturan Download</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="d-flex justify-content-between mb-2">
                        <button type="button" class="btn btn-link btn-sm p-0" id="btnSelectAllCols">Pilih Semua</button>
                        <button type="button" class="btn btn-link btn-sm p-0" id="btnClearAllCols">Hapus Semua</button>
                    </div>
                    <div id="downloadColumnList" class="row row-cols-2 g-2 mb-3"></div>

                    <div class="mb-3">
                        <label class="form-label d-block">Format File</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="dl_format" id="fmtCsv" value="csv">
                            <label class="form-check-label" for="fmtCsv">CSV</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="dl_format" id="fmtExcel" value="excel">
                            <label class="form-check-label" for="fmtExcel">Excel (.xlsx)</label>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Nama File</label>
                        <input type="text" class="form-control" id="settingFilename" placeholder="products">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Simpan Pengaturan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
$(function () {

    var BASE_URL   = '<?= site_url('product'); ?>/';
    var CART_URL   = '<?= site_url('cart'); ?>/';
    var IS_ADMIN   = <?= !empty($is_admin) ? 'true' : 'false'; ?>;
    var productModal = new bootstrap.Modal(document.getElementById('productModal'));
    var deleteModal   = new bootstrap.Modal(document.getElementById('deleteModal'));
    var restoreModal  = new bootstrap.Modal(document.getElementById('restoreModal'));
    var deleteTargetId = null;
    var restoreTargetId = null;
    var searchTimer = null;
    var kodeCheckTimer = null;
    var kodeIsDuplicate = false;

    var PAGE_SIZE = 10;
    var currentPage = 1;
    var allRows = [];
    var cart = {};

    function showAlert(message, type) {
        var html = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        $('#alertPlaceholder').html(html);
    }

    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function statusBadgeClass(status) {
        switch (status) {
            case 'Habis':   return 'badge-habis';
            case 'Menipis': return 'badge-menipis';
            case 'Cukup':   return 'badge-cukup';
            default:        return 'badge-banyak';
        }
    }

    function escapeHtml(str) {
        return $('<div>').text(str == null ? '' : str).html();
    }

    function totalColumnCount() {
        return getDownloadSettings().columns.length + 1;
    }

    var COLUMN_TH_ATTRS = {
        no:         ' style="width:40px;"',
        i_product:  '',
        e_product:  '',
        e_category: '',
        v_price:    ' class="text-end"',
        n_stock:    ' class="text-end"',
        status:     '',
        f_active:   ' class="text-center" style="width:70px;"'
    };

    function visibleColumnKeys() {
        var settings = getDownloadSettings();
        return DOWNLOAD_COLUMNS
            .filter(function (c) { return settings.columns.indexOf(c.key) !== -1; })
            .map(function (c) { return c.key; });
    }

    function renderTableHead() {
        var $row = $('#tableHeadRow');
        $row.empty();
        if (!IS_ADMIN) {
            $row.append('<th style="width:36px;"><input type="checkbox" class="form-check-input" id="checkAllProducts" title="Pilih semua"></th>');
        }
        visibleColumnKeys().forEach(function (key) {
            var col = DOWNLOAD_COLUMNS.filter(function (c) { return c.key === key; })[0];
            $row.append('<th' + (COLUMN_TH_ATTRS[key] || '') + '>' + col.label + '</th>');
        });
        if (IS_ADMIN) {
            $row.append('<th style="width:130px;">Action</th>');
        }
    }

    function cellHtml(key, row, idx, start) {
        switch (key) {
            case 'no':
                return '<td>' + (start + idx + 1) + '</td>';
            case 'i_product':
                return '<td>' + escapeHtml(row.i_product) + '</td>';
            case 'e_product':
                return '<td>' + escapeHtml(row.e_product) + '</td>';
            case 'e_category':
                return '<td>' + escapeHtml(row.e_category || '-') + '</td>';
            case 'v_price':
                return '<td class="text-end">' + formatRupiah(row.v_price) + '</td>';
            case 'n_stock':
                return '<td class="text-end">' + row.n_stock + '</td>';
            case 'status':
                return '<td><span class="badge badge-status ' + statusBadgeClass(row.status) + '">' + row.status + '</span></td>';
            case 'f_active':
                var isActive = (row.f_active === 't');
                return '<td class="text-center">' +
                    (isActive
                        ? '<i class="bi bi-check-circle-fill active-icon" title="Aktif"></i>'
                        : '<i class="bi bi-x-circle-fill active-icon" title="Deactivated"></i>') +
                    '</td>';
            default:
                return '';
        }
    }

    function loadProducts(search) {
        $('#tableLoading td').attr('colspan', totalColumnCount());
        $('#tableLoading').show();
        $.ajax({
            url: BASE_URL + 'list_data',
            method: 'POST',
            data: { search: search || '' },
            dataType: 'json'
        }).done(function (res) {
            allRows = (res && res.data) || [];
            currentPage = 1;
            renderTable();
        }).fail(function () {
            allRows = [];
            $('#productTableBody').html('<tr><td colspan="' + totalColumnCount() + '" class="text-center text-danger py-4">Gagal memuat data.</td></tr>');
            $('#paginationControls').empty();
            $('#paginationInfo').text('');
        }).always(function () {
            $('#tableLoading').hide();
        });
    }

    function renderTable() {
        renderTableHead();
        var visibleCols = visibleColumnKeys();

        var $body = $('#productTableBody');
        $body.empty();

        var totalRows = allRows.length;
        var isAll = (PAGE_SIZE === 'all');
        var effectiveSize = isAll ? Math.max(totalRows, 1) : PAGE_SIZE;
        var totalPages = isAll ? 1 : Math.max(1, Math.ceil(totalRows / effectiveSize));
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        if (totalRows === 0) {
            $body.append('<tr><td colspan="' + totalColumnCount() + '" class="text-center text-muted py-4">Data produk tidak ditemukan.</td></tr>');
            $('#paginationControls').empty();
            $('#paginationInfo').text('');
            return;
        }

        var start = isAll ? 0 : (currentPage - 1) * effectiveSize;
        var pageRows = isAll ? allRows : allRows.slice(start, start + effectiveSize);

        pageRows.forEach(function (row, idx) {
            var isActive = (row.f_active === 't');
            var tr = $('<tr>');
            if (!isActive) tr.addClass('row-deactivated');

            if (!IS_ADMIN) {
                if (isActive) {
                    tr.append('<td><input type="checkbox" class="form-check-input product-select" data-id="' + row.id_product + '"' + (cart[row.id_product] ? ' checked' : '') + '></td>');
                } else {
                    tr.append('<td></td>');
                }
            }

            visibleCols.forEach(function (key) {
                tr.append(cellHtml(key, row, idx, start));
            });

            if (IS_ADMIN) {
                if (isActive) {
                    tr.append(
                        '<td>' +
                            '<button type="button" class="btn btn-sm btn-outline-primary btn-edit me-1" data-id="' + row.id_product + '"><i class="bi bi-pencil-square"></i></button>' +
                            '<button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="' + row.id_product + '" data-name="' + escapeHtml(row.e_product) + '"><i class="bi bi-trash"></i></button>' +
                        '</td>'
                    );
                } else {
                    tr.append(
                        '<td>' +
                            '<button type="button" class="btn btn-sm btn-outline-success btn-restore" data-id="' + row.id_product + '" data-name="' + escapeHtml(row.e_product) + '" title="Restore Produk"><i class="bi bi-arrow-counterclockwise"></i></button>' +
                        '</td>'
                    );
                }
            }

            $body.append(tr);
        });

        var infoText = 'Menampilkan ' + (start + 1) + '-' + (start + pageRows.length) + ' dari ' + totalRows + ' produk';
        $('#paginationInfo').text(infoText);

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        var $pg = $('#paginationControls');
        $pg.empty();

        if (totalPages <= 1) return;

        function pageItem(label, page, disabled, active) {
            var li = $('<li class="page-item">');
            if (disabled) li.addClass('disabled');
            if (active) li.addClass('active');
            var a = $('<a class="page-link" href="#">').text(label).attr('data-page', page);
            li.append(a);
            return li;
        }

        $pg.append(pageItem('Prev', currentPage - 1, currentPage === 1, false));

        for (var p = 1; p <= totalPages; p++) {
            $pg.append(pageItem(String(p), p, false, p === currentPage));
        }

        $pg.append(pageItem('Next', currentPage + 1, currentPage === totalPages, false));
    }

    function loadMyCart() {
        $.ajax({
            url: CART_URL + 'my_list',
            method: 'POST',
            dataType: 'json'
        }).done(function (res) {
            var rows = (res && res.data) || [];
            cart = {};
            rows.forEach(function (item) {
                cart[item.id_product] = {
                    id_product:  item.id_product,
                    i_product:   item.i_product,
                    e_product:   item.e_product,
                    e_category:  item.e_category,
                    v_price:     item.v_price,
                    n_stock:     Number(item.n_stock || 0),
                    qty:         item.qty
                };
            });
            renderCart();
        });
    }

    function saveCartItemToServer(idProduct, qty) {
        $.ajax({
            url: CART_URL + 'save',
            method: 'POST',
            data: { id_product: idProduct, qty: qty },
            dataType: 'json'
        }).fail(function () {
            showAlert('Gagal menyimpan daftar beli ke server.', 'danger');
        });
    }

    function removeCartItemFromServer(idProduct) {
        $.ajax({
            url: CART_URL + 'remove',
            method: 'POST',
            data: { id_product: idProduct },
            dataType: 'json'
        }).fail(function () {
            showAlert('Gagal menghapus item dari server.', 'danger');
        });
    }

    function cartCount() {
        return Object.keys(cart).length;
    }

    function renderCart() {
        var $body = $('#cartTableBody');
        $body.empty();

        var ids = Object.keys(cart);

        if (ids.length === 0) {
            $body.append('<tr id="cartEmptyRow"><td colspan="8" class="text-center text-muted py-4">Belum ada barang yang dipilih untuk dibeli.</td></tr>');
            $('#cartTotalRow').addClass('d-none');
            return;
        }

        var grandTotal = 0;

        ids.forEach(function (id, idx) {
            var item = cart[id];
            var product = allRows.filter(function (r) { return String(r.id_product) === String(id); })[0];
            if (product) {
                item.i_product  = product.i_product;
                item.e_product  = product.e_product;
                item.e_category = product.e_category;
                item.v_price    = product.v_price;
                item.n_stock    = Number(product.n_stock || 0);
            }

            var maxQty = Math.max(item.n_stock, 0);
            if (item.qty > maxQty) item.qty = maxQty;
            if (item.qty < 1 && maxQty >= 1) item.qty = 1;

            var subtotal = item.v_price * item.qty;
            grandTotal += subtotal;

            var tr = $('<tr>');
            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td>' + escapeHtml(item.i_product) + '</td>');
            tr.append('<td>' + escapeHtml(item.e_product) + '</td>');
            tr.append('<td>' + escapeHtml(item.e_category || '-') + '</td>');
            tr.append('<td class="text-end">' + formatRupiah(item.v_price) + '</td>');
            tr.append(
                '<td class="text-end">' +
                    '<input type="number" min="1" step="1" max="' + maxQty + '" class="form-control form-control-sm cart-qty text-end" data-id="' + id + '" value="' + item.qty + '">' +
                    '<div class="form-text text-muted" style="font-size:.72rem;">Stock: ' + maxQty + '</div>' +
                '</td>'
            );
            tr.append('<td class="text-end">' + formatRupiah(subtotal) + '</td>');
            tr.append(
                '<td>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-cart-remove" data-id="' + id + '"><i class="bi bi-x-lg"></i></button>' +
                '</td>'
            );
            $body.append(tr);
        });

        $('#cartGrandTotal').text(formatRupiah(grandTotal));
        $('#cartTotalRow').removeClass('d-none');
    }

    function addSelectedToCart() {
        var $checked = $('.product-select:checked');
        if ($checked.length === 0) {
            showAlert('Pilih minimal satu produk untuk ditambahkan ke daftar beli.', 'warning');
            return;
        }

        var blockedOutOfStock = [];
        var blockedMaxed = [];
        var toSave = [];

        $checked.each(function () {
            var id = $(this).data('id');
            var product = allRows.filter(function (r) { return String(r.id_product) === String(id); })[0];
            if (!product) return;

            var stock = Number(product.n_stock || 0);

            if (stock <= 0) {
                blockedOutOfStock.push(product.e_product);
                return;
            }

            if (cart[id]) {
                if (cart[id].qty >= stock) {
                    blockedMaxed.push(product.e_product);
                    return;
                }
                cart[id].qty += 1;
            } else {
                cart[id] = {
                    id_product:  product.id_product,
                    i_product:   product.i_product,
                    e_product:   product.e_product,
                    e_category:  product.e_category,
                    v_price:     product.v_price,
                    n_stock:     stock,
                    qty:         1
                };
            }
            toSave.push(id);
        });

        $checked.prop('checked', false);
        $('#checkAllProducts').prop('checked', false);
        renderCart();

        toSave.forEach(function (id) {
            saveCartItemToServer(id, cart[id].qty);
        });

        if (blockedOutOfStock.length || blockedMaxed.length) {
            var msgParts = [];
            if (blockedOutOfStock.length) msgParts.push('Stok habis: ' + blockedOutOfStock.join(', '));
            if (blockedMaxed.length) msgParts.push('Sudah mencapai batas stok: ' + blockedMaxed.join(', '));
            showAlert(msgParts.join('. ') + '.', 'warning');
        } else {
            showAlert('Barang berhasil ditambahkan ke daftar beli konsumer.', 'success');
        }
    }

    $('#btnAddToCart').on('click', addSelectedToCart);

    $('#productTableBody').on('change', '#checkAllProducts', function () {
        var checked = $(this).is(':checked');
        $('.product-select').prop('checked', checked);
    });

    $('#tableHeadRow').on('change', '#checkAllProducts', function () {
        var checked = $(this).is(':checked');
        $('.product-select').prop('checked', checked);
    });

    var cartQtySaveTimer = null;

    $('#cartTableBody').on('input change', '.cart-qty', function () {
        var id = $(this).data('id');
        if (!cart[id]) return;

        var maxQty = Math.max(Number(cart[id].n_stock || 0), 0);
        var qty = parseInt($(this).val(), 10);

        if (isNaN(qty) || qty < 1) qty = 1;
        if (qty > maxQty) {
            qty = maxQty;
            showAlert('Qty tidak boleh melebihi stock yang tersedia (' + maxQty + ').', 'warning');
        }

        cart[id].qty = qty;
        renderCart();

        clearTimeout(cartQtySaveTimer);
        cartQtySaveTimer = setTimeout(function () {
            saveCartItemToServer(id, qty);
        }, 400);
    });

    $('#cartTableBody').on('click', '.btn-cart-remove', function () {
        var id = $(this).data('id');
        delete cart[id];
        renderCart();
        removeCartItemFromServer(id);
        $('.product-select[data-id="' + id + '"]').prop('checked', false);
    });

    $('#paginationControls').on('click', 'a.page-link', function (e) {
        e.preventDefault();
        if ($(this).parent().hasClass('disabled') || $(this).parent().hasClass('active')) return;
        var page = parseInt($(this).attr('data-page'), 10);
        if (isNaN(page)) return;
        currentPage = page;
        renderTable();
    });

    $('#pageSizeSelect').on('change', function () {
        var val = $(this).val();
        PAGE_SIZE = (val === 'all') ? 'all' : parseInt(val, 10);
        currentPage = 1;
        renderTable();
    });

    $('#searchInput').on('keyup', function () {
        var val = $(this).val();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { loadProducts(val); }, 350);
    });

    function resetForm() {
        $('#productForm')[0].reset();
        $('#productForm .is-invalid').removeClass('is-invalid');
        $('#id_product').val('');
        $('#i_product').prop('readonly', false);
        $('#kodeStatus').text('').removeClass('text-danger text-success');
        kodeIsDuplicate = false;
    }

    $('#btnAdd').on('click', function () {
        resetForm();
        $('#productModalLabel').text('Tambah Product');
        productModal.show();
    });

    $('#productTableBody').on('click', '.btn-edit', function () {
        var id = $(this).data('id');
        $.ajax({
            url: BASE_URL + 'get/' + id,
            method: 'GET',
            dataType: 'json'
        }).done(function (res) {
            if (!res.status) {
                showAlert(res.message || 'Produk tidak ditemukan.', 'danger');
                return;
            }
            resetForm();
            var p = res.data;
            $('#productModalLabel').text('Edit Product');
            $('#id_product').val(p.id_product);
            $('#i_product').val(p.i_product).prop('readonly', true);
            $('#e_product').val(p.e_product);
            $('#id_category').val(p.id_category);
            $('#v_price').val(p.v_price);
            $('#n_stock').val(p.n_stock);
            productModal.show();
        }).fail(function () {
            showAlert('Gagal mengambil data produk.', 'danger');
        });
    });

    $('#i_product').on('keyup', function () {
        if ($(this).prop('readonly')) return;
        var kode = $(this).val().trim();
        clearTimeout(kodeCheckTimer);

        if (kode === '') {
            $('#kodeStatus').text('').removeClass('text-danger text-success');
            kodeIsDuplicate = false;
            return;
        }

        kodeCheckTimer = setTimeout(function () {
            $.ajax({
                url: BASE_URL + 'check_kode',
                method: 'POST',
                data: { i_product: kode, id_product: $('#id_product').val() },
                dataType: 'json'
            }).done(function (res) {
                if (res.exists) {
                    kodeIsDuplicate = true;
                    var msg = res.is_active ? 'Kode product sudah digunakan' : 'Kode product sudah digunakan (Deactivated)';
                    $('#kodeStatus').text(msg).addClass('text-danger').removeClass('text-success');
                } else {
                    kodeIsDuplicate = false;
                    $('#kodeStatus').text('Kode tersedia.').addClass('text-success').removeClass('text-danger');
                }
            });
        }, 400);
    });

    function validateForm() {
        var valid = true;
        $('#productForm .is-invalid').removeClass('is-invalid');

        if (!$('#i_product').prop('readonly') && $('#i_product').val().trim() === '') {
            $('#i_product').addClass('is-invalid'); valid = false;
        }
        if ($('#e_product').val().trim() === '') {
            $('#e_product').addClass('is-invalid'); valid = false;
        }
        if ($('#id_category').val() === '') {
            $('#id_category').addClass('is-invalid'); valid = false;
        }
        var price = parseFloat($('#v_price').val());
        if ($('#v_price').val() === '' || isNaN(price) || price < 0) {
            $('#v_price').addClass('is-invalid'); valid = false;
        }
        var stock = parseFloat($('#n_stock').val());
        if ($('#n_stock').val() === '' || isNaN(stock) || stock < 0) {
            $('#n_stock').addClass('is-invalid'); valid = false;
        }
        if (kodeIsDuplicate) {
            $('#i_product').addClass('is-invalid'); valid = false;
        }
        return valid;
    }

    $('#productForm').on('submit', function (e) {
        e.preventDefault();
        if (!validateForm()) return;

        $('#btnSave').prop('disabled', true);
        $('#saveSpinner').removeClass('d-none');

        $.ajax({
            url: BASE_URL + 'save',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json'
        }).done(function (res) {
            if (res.status) {
                productModal.hide();
                showAlert(res.message, 'success');
                loadProducts($('#searchInput').val());
            } else {
                showAlert(res.message || 'Gagal menyimpan data.', 'danger');
            }
        }).fail(function (xhr) {
            var msg = 'Gagal menyimpan data.';
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            showAlert(msg, 'danger');
        }).always(function () {
            $('#btnSave').prop('disabled', false);
            $('#saveSpinner').addClass('d-none');
        });
    });

    $('#productTableBody').on('click', '.btn-delete', function () {
        deleteTargetId = $(this).data('id');
        $('#deleteProductName').text($(this).data('name'));
        deleteModal.show();
    });

    $('#btnConfirmDelete').on('click', function () {
        if (!deleteTargetId) return;
        $('#btnConfirmDelete').prop('disabled', true);
        $('#deleteSpinner').removeClass('d-none');

        $.ajax({
            url: BASE_URL + 'delete/' + deleteTargetId,
            method: 'POST',
            dataType: 'json'
        }).done(function (res) {
            deleteModal.hide();
            showAlert(res.message, res.status ? 'success' : 'danger');
            if (res.status) loadProducts($('#searchInput').val());
        }).fail(function () {
            deleteModal.hide();
            showAlert('Gagal menghapus data.', 'danger');
        }).always(function () {
            $('#btnConfirmDelete').prop('disabled', false);
            $('#deleteSpinner').addClass('d-none');
            deleteTargetId = null;
        });
    });

    $('#productTableBody').on('click', '.btn-restore', function () {
        restoreTargetId = $(this).data('id');
        $('#restoreProductName').text($(this).data('name'));
        restoreModal.show();
    });

    $('#btnConfirmRestore').on('click', function () {
        if (!restoreTargetId) return;
        $('#btnConfirmRestore').prop('disabled', true);
        $('#restoreSpinner').removeClass('d-none');

        $.ajax({
            url: BASE_URL + 'restore/' + restoreTargetId,
            method: 'POST',
            dataType: 'json'
        }).done(function (res) {
            restoreModal.hide();
            showAlert(res.message, res.status ? 'success' : 'danger');
            if (res.status) loadProducts($('#searchInput').val());
        }).fail(function () {
            restoreModal.hide();
            showAlert('Gagal mengaktifkan kembali data.', 'danger');
        }).always(function () {
            $('#btnConfirmRestore').prop('disabled', false);
            $('#restoreSpinner').addClass('d-none');
            restoreTargetId = null;
        });
    });

    var DOWNLOAD_COLUMNS = [
        { key: 'no',         label: 'No' },
        { key: 'i_product',  label: 'Kode' },
        { key: 'e_product',  label: 'Nama' },
        { key: 'e_category', label: 'Kategori' },
        { key: 'v_price',    label: 'Harga' },
        { key: 'n_stock',    label: 'Stock' },
        { key: 'status',     label: 'Status' },
        { key: 'f_active',   label: 'Aktif' }
    ];

    function defaultDownloadSettings() {
        return {
            filenamePrefix: 'products',
            columns: DOWNLOAD_COLUMNS.map(function (c) { return c.key; }),
            format: 'csv'
        };
    }

    function normalizeDownloadSettings(raw) {
        var d = defaultDownloadSettings();
        if (!raw || typeof raw !== 'object') return d;
        return {
            filenamePrefix: (typeof raw.filenamePrefix === 'string' && raw.filenamePrefix.trim() !== '') ? raw.filenamePrefix : d.filenamePrefix,
            columns: (Array.isArray(raw.columns) && raw.columns.length > 0) ? raw.columns.slice() : d.columns,
            format: (raw.format === 'csv' || raw.format === 'excel') ? raw.format : d.format
        };
    }

    var downloadSettingsCache = null;  

    function getDownloadSettings() {
        return normalizeDownloadSettings(downloadSettingsCache);
    }

    function fetchDownloadSettings(callback) {
        $.ajax({
            url: BASE_URL + 'get_download_settings',
            method: 'GET',
            dataType: 'json'
        }).done(function (res) {
            downloadSettingsCache = (res && res.status) ? res.data : null;
        }).fail(function () {
            downloadSettingsCache = null;
        }).always(function () {
            renderTableHead();
            if (allRows.length) renderTable();
            if (callback) callback();
        });
    }

    var downloadSettingsModal = new bootstrap.Modal(document.getElementById('downloadSettingsModal'));

    function populateSettingsModal() {
        var s = getDownloadSettings();

        $('#settingFilename').val(s.filenamePrefix);
        $('input[name="dl_format"][value="' + s.format + '"]').prop('checked', true);

        var $list = $('#downloadColumnList');
        $list.empty();
        DOWNLOAD_COLUMNS.forEach(function (col) {
            var checked = s.columns.indexOf(col.key) !== -1;
            var id = 'col_' + col.key;
            $list.append(
                '<div class="col">' +
                    '<div class="form-check">' +
                        '<input class="form-check-input dl-col-check" type="checkbox" value="' + col.key + '" id="' + id + '"' + (checked ? ' checked' : '') + '>' +
                        '<label class="form-check-label" for="' + id + '">' + col.label + '</label>' +
                    '</div>' +
                '</div>'
            );
        });
    }

    $('#btnDownloadSettings').on('click', function () {
        fetchDownloadSettings(function () {
            populateSettingsModal();
            downloadSettingsModal.show();
        });
    });

    $('#btnSelectAllCols').on('click', function () {
        $('.dl-col-check').prop('checked', true);
    });

    $('#btnClearAllCols').on('click', function () {
        $('.dl-col-check').prop('checked', false);
    });

    $('#downloadSettingsForm').on('submit', function (e) {
        e.preventDefault();

        var checkedCols = $('.dl-col-check:checked').map(function () { return $(this).val(); }).get();
        if (checkedCols.length === 0) {
            showAlert('Pilih minimal satu kolom.', 'warning');
            return;
        }

        var settings = {
            filenamePrefix: ($('#settingFilename').val() || 'products').trim(),
            columns: checkedCols,
            format: $('input[name="dl_format"]:checked').val() || 'csv'
        };

        var $submitBtn = $(this).find('button[type="submit"]');
        $submitBtn.prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'save_download_settings',
            method: 'POST',
            data: {
                'columns[]':      settings.columns,
                format:           settings.format,
                filenamePrefix:   settings.filenamePrefix
            },
            traditional: true,
            dataType: 'json'
        }).done(function (res) {
            if (res.status) {
                downloadSettingsCache = settings;
                downloadSettingsModal.hide();
                showAlert(res.message || 'Pengaturan download berhasil disimpan.', 'success');
                renderTableHead();
                if (allRows.length) renderTable();
            } else {
                showAlert(res.message || 'Gagal menyimpan pengaturan.', 'danger');
            }
        }).fail(function (xhr) {
            var msg = 'Gagal menyimpan pengaturan.';
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            showAlert(msg, 'danger');
        }).always(function () {
            $submitBtn.prop('disabled', false);
        });
    });

    function csvEscape(value) {
        value = value === null || value === undefined ? '' : String(value);
        var needsQuote = value.indexOf('"') !== -1 || value.indexOf('\n') !== -1 || value.indexOf(',') !== -1;
        value = value.replace(/"/g, '""');
        return needsQuote ? '"' + value + '"' : value;
    }

    function rawCellValue(row, key, index) {
        switch (key) {
            case 'no':       return index + 1;
            case 'v_price':  return Number(row.v_price || 0);
            case 'n_stock':  return Number(row.n_stock || 0);
            case 'f_active': return (row.f_active === 't') ? 'Aktif' : 'Deactivated';
            default:         return row[key] != null ? row[key] : '';
        }
    }

    function csvCellValue(row, key, index) {
        var value = rawCellValue(row, key, index);
        if (key === 'v_price' || key === 'n_stock') {
            return '="' + value + '"';
        }
        return value;
    }

    function triggerBlobDownload(blob, filename) {
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
    }

    function downloadCsv(rows, cols, filenamePrefix) {
        var lines = [];
        lines.push(cols.map(function (c) { return csvEscape(c.label); }).join(','));

        rows.forEach(function (row, index) {
            var line = cols.map(function (c) {
                return csvEscape(csvCellValue(row, c.key, index));
            }).join(',');
            lines.push(line);
        });

        var csvContent = '\uFEFF' + lines.join('\r\n');
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        triggerBlobDownload(blob, filenamePrefix + '.csv');
    }

    function downloadExcel(rows, cols, filenamePrefix) {
        var aoa = [cols.map(function (c) { return c.label; })];
        rows.forEach(function (row, index) {
            aoa.push(cols.map(function (c) { return rawCellValue(row, c.key, index); }));
        });

        var ws = XLSX.utils.aoa_to_sheet(aoa);

        cols.forEach(function (c, colIdx) {
            if (c.key !== 'v_price' && c.key !== 'n_stock') return;
            var fmt = (c.key === 'v_price') ? '#,##0' : '0';
            for (var r = 1; r <= rows.length; r++) {
                var cellRef = XLSX.utils.encode_cell({ r: r, c: colIdx });
                if (ws[cellRef]) ws[cellRef].z = fmt;
            }
        });

        ws['!cols'] = cols.map(function (c) {
            return { wch: (c.key === 'e_product' || c.key === 'v_price') ? 22 : 14 };
        });

        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Products');
        XLSX.writeFile(wb, filenamePrefix + '.xlsx');
    }

    $('#btnDownload').on('click', function () {
        var settings = getDownloadSettings();
        var cols = DOWNLOAD_COLUMNS.filter(function (c) { return settings.columns.indexOf(c.key) !== -1; });

        if (cols.length === 0) {
            showAlert('Belum ada kolom yang dipilih. Buka pengaturan download (ikon gear) dulu.', 'warning');
            return;
        }

        var rows = allRows.slice();
        if (rows.length === 0) {
            showAlert('Tidak ada data untuk di-download.', 'warning');
            return;
        }

        if (settings.format === 'excel') {
            downloadExcel(rows, cols, settings.filenamePrefix);
        } else {
            downloadCsv(rows, cols, settings.filenamePrefix);
        }

        showAlert('Download dimulai (' + rows.length + ' baris).', 'success');
    });


    fetchDownloadSettings();
    loadProducts('');
    if (!IS_ADMIN) {
        loadMyCart();
    }
});
</script>
</body>
</html>