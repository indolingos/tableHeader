<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Pembelian Konsumen</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .navbar-brand { font-weight: 600; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    #tableLoading { display: none; }
    .badge-user { background-color: #0d6efd; font-weight: 600; }
    .btn-icon { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'cart', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div id="alertPlaceholder"></div>

    <div class="card">
        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Data Pembelian per Konsumen</h5>
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari username...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Konsumen</th>
                            <th class="text-end">Jumlah Produk</th>
                            <th class="text-end">Total Qty</th>
                            <th class="text-end">Total Pembelian</th>
                            <th>Terakhir Diubah</th>
                            <th class="text-center" style="width:110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody">
                        <tr id="tableLoading">
                            <td colspan="7" class="text-center py-4 text-muted">
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
    var allRows  = [];
    var searchTimer = null;

    function showAlert(message, type) {
        var html = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        $('#alertPlaceholder').html(html);
    }

    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function escapeHtml(str) {
        return $('<div>').text(str == null ? '' : str).html();
    }

    function formatDate(str) {
        if (!str) return '-';
        var d = new Date(str.replace(' ', 'T'));
        if (isNaN(d.getTime())) return str;
        return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    function loadDashboard() {
        $('#tableLoading').show();
        $.ajax({
            url: BASE_URL + 'dashboard_data',
            method: 'POST',
            dataType: 'json'
        }).done(function (res) {
            allRows = (res && res.data) || [];
            renderTable($('#searchInput').val());
        }).fail(function () {
            allRows = [];
            $('#cartTableBody').html('<tr><td colspan="7" class="text-center text-danger py-4">Gagal memuat data.</td></tr>');
            $('#summaryInfo').text('');
        }).always(function () {
            $('#tableLoading').hide();
        });
    }

    function renderTable(search) {
        var $body = $('#cartTableBody');
        $body.empty();

        var rows = allRows;
        if (search) {
            var q = search.toLowerCase();
            rows = allRows.filter(function (r) {
                return (r.i_username || '').toLowerCase().indexOf(q) !== -1;
            });
        }

        if (rows.length === 0) {
            $body.append('<tr><td colspan="7" class="text-center text-muted py-4">Belum ada konsumen yang memilih barang untuk dibeli.</td></tr>');
            $('#summaryInfo').text('');
            return;
        }

        var grandTotal = 0;

        rows.forEach(function (row, idx) {
            grandTotal += row.v_total;

            var tr = $('<tr>');
            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td><span class="badge badge-user"><i class="bi bi-person-fill me-1"></i>' + escapeHtml(row.i_username) + '</span></td>');
            tr.append('<td class="text-end">' + row.n_items + '</td>');
            tr.append('<td class="text-end">' + row.n_total_qty + '</td>');
            tr.append('<td class="text-end fw-semibold">' + formatRupiah(row.v_total) + '</td>');
            tr.append('<td>' + formatDate(row.dt_last_updated) + '</td>');

            var actionTd = $('<td class="text-center">');
            var btnGroup = $('<div class="d-inline-flex gap-1">');
            var viewBtn = $('<a class="btn btn-sm btn-outline-primary btn-icon" title="Lihat detail di halaman baru"><i class="bi bi-eye"></i></a>');
            viewBtn.attr('href', BASE_URL + 'user_detail/' + row.id_user);
            viewBtn.attr('target');
            var downloadBtn = $('<a class="btn btn-sm btn-outline-success btn-icon" title="Download data pembelian"><i class="bi bi-download"></i></a>');
            downloadBtn.attr('href', BASE_URL + 'export_user/' + row.id_user);
            btnGroup.append(viewBtn).append(downloadBtn);
            actionTd.append(btnGroup);
            tr.append(actionTd);

            $body.append(tr);
        });

        var userCount = rows.length;
        $('#summaryInfo').text(userCount + ' konsumen, total pembelian ' + formatRupiah(grandTotal) + '.');
    }

    $('#searchInput').on('keyup', function () {
        var val = $(this).val();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { renderTable(val); }, 250);
    });

    loadDashboard();
});
</script>
</body>
</html>
