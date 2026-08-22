<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jenis-jenis Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .navbar-brand { font-weight: 600; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    #tableLoading { display: none; }
    .badge-type { background-color: #6f42c1; font-weight: 600; }
    .btn-icon { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'product_type', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div class="card">
        <div class="card-body">

            <h5 class="mb-3"><i class="bi bi-tags me-2"></i>Jenis-jenis Product</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Jenis Product</th>
                            <th class="text-end">Jumlah Kategori</th>
                            <th class="text-end">Jumlah Produk</th>
                            <th class="text-end">Produk Aktif</th>
                            <th class="text-center" style="width:80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="typeTableBody">
                        <tr id="tableLoading">
                            <td colspan="6" class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div>Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function () {

    var BASE_URL = '<?= site_url('product_type'); ?>/';

    function escapeHtml(str) {
        return $('<div>').text(str == null ? '' : str).html();
    }

    function loadTypes() {
        $('#tableLoading').show();
        $.ajax({
            url: BASE_URL + 'list_data',
            method: 'POST',
            dataType: 'json'
        }).done(function (res) {
            renderTable((res && res.data) || []);
        }).fail(function () {
            $('#typeTableBody').html('<tr><td colspan="6" class="text-center text-danger py-4">Gagal memuat data.</td></tr>');
        }).always(function () {
            $('#tableLoading').hide();
        });
    }

    function renderTable(rows) {
        var $body = $('#typeTableBody');
        $body.empty();

        if (rows.length === 0) {
            $body.append('<tr><td colspan="6" class="text-center text-muted py-4">Belum ada jenis product.</td></tr>');
            return;
        }

        rows.forEach(function (row, idx) {
            var tr = $('<tr>');
            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td><span class="badge badge-type"><i class="bi bi-tag-fill me-1"></i>' + escapeHtml(row.e_product_type) + '</span></td>');
            tr.append('<td class="text-end">' + row.n_categories + '</td>');
            tr.append('<td class="text-end">' + row.n_products + '</td>');
            tr.append('<td class="text-end">' + row.n_active_products + '</td>');

            var actionTd = $('<td class="text-center">');
            var viewBtn = $('<a class="btn btn-sm btn-outline-primary btn-icon" title="Lihat detail produk"><i class="bi bi-eye"></i></a>');
            viewBtn.attr('href', BASE_URL + 'detail/' + row.id_product_type);
            actionTd.append(viewBtn);
            tr.append(actionTd);

            $body.append(tr);
        });
    }

    loadTypes();
});
</script>
</body>
</html>
