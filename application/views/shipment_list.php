<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bukti Barang Terkirim</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    #tableLoading { display: none; }
    .badge-user { background-color: #0dcaf0; color: #212529; font-weight: 600; }
    .btn-icon { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'shipment', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div class="card">
        <div class="card-body">

            <h5 class="mb-1"><i class="bi bi-box-seam-fill me-2"></i>Bukti Barang Terkirim</h5>
            <p class="text-muted small">Keterangan: daftar pengiriman barang ke konsumen beserta nomor resi dan status. Klik ikon mata untuk melihat rincian barang yang dikirim dan mengunggah/ubah bukti kirim.</p>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Invoice</th>
                            <th>Konsumen</th>
                            <th>Kurir</th>
                            <th>No Resi</th>
                            <th>Status</th>
                            <th>Tgl Kirim</th>
                            <th class="text-center" style="width:80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr id="tableLoading">
                            <td colspan="8" class="text-center py-4 text-muted">
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
    var BASE_URL = '<?= site_url('shipment'); ?>/';

    function escapeHtml(str) { return $('<div>').text(str == null ? '' : str).html(); }
    function formatDate(str) {
        if (!str) return '-';
        var d = new Date(str.replace(' ', 'T'));
        if (isNaN(d.getTime())) return str;
        return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
    function statusBadge(status) {
        var cls = { 'Dikemas': 'bg-secondary', 'Dikirim': 'bg-warning text-dark', 'Diterima': 'bg-success', 'Retur': 'bg-danger' };
        return '<span class="badge ' + (cls[status] || 'bg-secondary') + '">' + escapeHtml(status) + '</span>';
    }

    function load() {
        $('#tableLoading').show();
        $.ajax({ url: BASE_URL + 'list_data', method: 'POST', dataType: 'json' })
            .done(function (res) { render((res && res.data) || []); })
            .fail(function () { $('#tableBody').html('<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat data.</td></tr>'); })
            .always(function () { $('#tableLoading').hide(); });
    }

    function render(rows) {
        var $body = $('#tableBody');
        $body.empty();
        if (rows.length === 0) {
            $body.append('<tr><td colspan="8" class="text-center text-muted py-4">Belum ada data pengiriman.</td></tr>');
            return;
        }
        rows.forEach(function (row, idx) {
            var tr = $('<tr>');
            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td class="fw-semibold">' + escapeHtml(row.i_invoice) + '</td>');
            tr.append('<td><span class="badge badge-user"><i class="bi bi-person-fill me-1"></i>' + escapeHtml(row.i_username) + '</span></td>');
            tr.append('<td>' + escapeHtml(row.e_courier_name) + ' <span class="badge bg-light text-dark border">' + escapeHtml(row.e_courier_code) + '</span></td>');
            tr.append('<td>' + escapeHtml(row.i_resi) + '</td>');
            tr.append('<td>' + statusBadge(row.e_status_kirim) + '</td>');
            tr.append('<td>' + formatDate(row.dt_kirim) + '</td>');

            var actionTd = $('<td class="text-center">');
            var viewBtn = $('<a class="btn btn-sm btn-outline-primary btn-icon" title="Lihat detail"><i class="bi bi-eye"></i></a>');
            viewBtn.attr('href', BASE_URL + 'detail/' + row.id_shipment).attr('target', '_blank');
            actionTd.append(viewBtn);
            tr.append(actionTd);

            $body.append(tr);
        });
    }

    load();
});
</script>
</body>
</html>
