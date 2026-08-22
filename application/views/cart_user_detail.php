<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Pembelian - <?= htmlspecialchars($target_username); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .navbar-brand { font-weight: 600; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    #tableLoading { display: none; }
    .badge-user { background-color: #0d6efd; font-weight: 600; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'cart', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?= site_url('cart'); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Dashboard Pembelian
        </a>
        <a href="<?= site_url('cart/export_user/' . (int) $id_user); ?>" class="btn btn-sm btn-outline-success">
            <i class="bi bi-download me-1"></i>Download
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <h5 class="mb-3">
                <i class="bi bi-basket me-2"></i>
                Barang yang Mau Dibeli <span class="badge badge-user"><i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($target_username); ?></span>
            </h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Subtotal</th>
                            <th>Terakhir Diubah</th>
                        </tr>
                    </thead>
                    <tbody id="itemTableBody">
                        <tr id="tableLoading">
                            <td colspan="8" class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div>Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="text-muted small mt-2" id="summaryInfo"></div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function () {

    var BASE_URL = '<?= site_url('cart'); ?>/';
    var ID_USER  = <?= (int) $id_user; ?>;

    function escapeHtml(str) {
        return $('<div>').text(str == null ? '' : str).html();
    }

    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function formatDate(str) {
        if (!str) return '-';
        var d = new Date(str.replace(' ', 'T'));
        if (isNaN(d.getTime())) return str;
        return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    function loadItems() {
        $('#tableLoading').show();
        $.ajax({
            url: BASE_URL + 'user_items/' + ID_USER,
            method: 'POST',
            dataType: 'json'
        }).done(function (res) {
            renderTable((res && res.data) || []);
        }).fail(function () {
            $('#itemTableBody').html('<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat data.</td></tr>');
            $('#summaryInfo').text('');
        }).always(function () {
            $('#tableLoading').hide();
        });
    }

    function renderTable(rows) {
        var $body = $('#itemTableBody');
        $body.empty();

        if (rows.length === 0) {
            $body.append('<tr><td colspan="8" class="text-center text-muted py-4">Belum ada barang untuk konsumen ini.</td></tr>');
            $('#summaryInfo').text('');
            return;
        }

        var total = 0;
        rows.forEach(function (row, idx) {
            total += row.subtotal;
            var tr = $('<tr>');
            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td>' + escapeHtml(row.i_product) + '</td>');
            tr.append('<td>' + escapeHtml(row.e_product) + '</td>');
            tr.append('<td>' + escapeHtml(row.e_category || '-') + '</td>');
            tr.append('<td class="text-end">' + formatRupiah(row.v_price) + '</td>');
            tr.append('<td class="text-end">' + row.n_qty + '</td>');
            tr.append('<td class="text-end">' + formatRupiah(row.subtotal) + '</td>');
            tr.append('<td>' + formatDate(row.dt_updated) + '</td>');
            $body.append(tr);
        });

        $('#summaryInfo').text(rows.length + ' baris barang, total ' + formatRupiah(total) + '.');
    }

    loadItems();
});
</script>
</body>
</html>
