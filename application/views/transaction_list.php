<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Transaksi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    #tableLoading { display: none; }
    .badge-user { background-color: #0d6efd; font-weight: 600; }
    .btn-icon { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
    .badge-status { font-weight: 600; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'transaction', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div class="card">
        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Daftar Transaksi</h5>
                <select class="form-select form-select-sm" id="statusFilter" style="max-width: 220px;">
                    <option value="">Semua Status</option>
                    <?php foreach ($statuses as $s): ?>
                    <option value="<?= htmlspecialchars($s); ?>"><?= htmlspecialchars($s); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <p class="text-muted small">Keterangan: setiap transaksi berisi satu atau lebih barang yang dibeli konsumen. Klik ikon mata untuk melihat rincian barang, qty, dan histori pengiriman.</p>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Invoice</th>
                            <th>Konsumen</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Pajak</th>
                            <th class="text-end">Ongkir</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th class="text-center" style="width:80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr id="tableLoading">
                            <td colspan="10" class="text-center py-4 text-muted">
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
    var BASE_URL = '<?= site_url('transaction'); ?>/';

    function escapeHtml(str) { return $('<div>').text(str == null ? '' : str).html(); }
    function formatRupiah(num) { return 'Rp ' + Number(num || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 }); }
    function formatDate(str) {
        if (!str) return '-';
        var d = new Date(str.replace(' ', 'T'));
        if (isNaN(d.getTime())) return str;
        return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
    function statusBadgeClass(status) {
        switch (status) {
            case 'Menunggu Pembayaran': return 'bg-secondary';
            case 'Dibayar': return 'bg-info text-dark';
            case 'Diproses': return 'bg-primary';
            case 'Dikirim': return 'bg-warning text-dark';
            case 'Selesai': return 'bg-success';
            case 'Dibatalkan': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }

    function load() {
        $('#tableLoading').show();
        $.ajax({ url: BASE_URL + 'list_data', method: 'POST', dataType: 'json', data: { status: $('#statusFilter').val() } })
            .done(function (res) { render((res && res.data) || []); })
            .fail(function () { $('#tableBody').html('<tr><td colspan="10" class="text-center text-danger py-4">Gagal memuat data.</td></tr>'); $('#summaryInfo').text(''); })
            .always(function () { $('#tableLoading').hide(); });
    }

    function render(rows) {
        var $body = $('#tableBody');
        $body.empty();
        if (rows.length === 0) {
            $body.append('<tr><td colspan="10" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>');
            $('#summaryInfo').text('');
            return;
        }
        var grand = 0;
        rows.forEach(function (row, idx) {
            grand += row.v_total;
            var tr = $('<tr>');
            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td class="fw-semibold">' + escapeHtml(row.i_invoice) + '</td>');
            tr.append('<td><span class="badge badge-user"><i class="bi bi-person-fill me-1"></i>' + escapeHtml(row.i_username) + '</span></td>');
            tr.append('<td class="text-end">' + formatRupiah(row.v_subtotal) + '</td>');
            tr.append('<td class="text-end">' + formatRupiah(row.v_tax) + '</td>');
            tr.append('<td class="text-end">' + formatRupiah(row.v_shipping_cost) + '</td>');
            tr.append('<td class="text-end fw-semibold">' + formatRupiah(row.v_total) + '</td>');
            tr.append('<td><span class="badge badge-status ' + statusBadgeClass(row.e_status) + '">' + escapeHtml(row.e_status) + '</span></td>');
            tr.append('<td>' + formatDate(row.dt_created) + '</td>');

            var actionTd = $('<td class="text-center">');
            var viewBtn = $('<a class="btn btn-sm btn-outline-primary btn-icon" title="Lihat detail transaksi"><i class="bi bi-eye"></i></a>');
            viewBtn.attr('href', BASE_URL + 'detail/' + row.id_transaction).attr('target', '_blank');
            actionTd.append(viewBtn);
            tr.append(actionTd);

            $body.append(tr);
        });
        $('#summaryInfo').text(rows.length + ' transaksi, total nilai ' + formatRupiah(grand) + '.');
    }

    $('#statusFilter').on('change', load);
    load();
});
</script>
</body>
</html>
