<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pajak & Keuangan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    #taxLoading, #statusLoading { display: none; }
    .btn-icon { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
    .stat-card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); border-radius: .75rem; }
    .stat-card .stat-value { font-size: 1.5rem; font-weight: 700; }
    .stat-card .stat-label { font-size: .8rem; color: #6c757d; }
    tr.row-inactive { color: #adb5bd; background-color: #f8f9fa; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'tax', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div id="alertPlaceholder"></div>

    <h5 class="mb-3"><i class="bi bi-cash-coin me-2"></i>Ringkasan Keuangan Toko</h5>
    <p class="text-muted small">Keterangan: ringkasan ini dihitung dari seluruh transaksi yang tidak berstatus "Dibatalkan".</p>
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-value" id="statCountTrx">&mdash;</div>
                    <div class="stat-label"><i class="bi bi-receipt me-1"></i>Jumlah Transaksi</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-value" id="statSubtotal">&mdash;</div>
                    <div class="stat-label"><i class="bi bi-box-seam me-1"></i>Subtotal Penjualan</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-value" id="statTax">&mdash;</div>
                    <div class="stat-label"><i class="bi bi-receipt-cutoff me-1"></i>Total Pajak Terkumpul</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-value" id="statTotal">&mdash;</div>
                    <div class="stat-label"><i class="bi bi-cash-stack me-1"></i>Total Pendapatan</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h6 class="mb-1"><i class="bi bi-bar-chart me-2"></i>Transaksi per Status</h6>
            <p class="text-muted small">Keterangan: jumlah transaksi dan total nilainya dikelompokkan berdasarkan status saat ini.</p>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Status</th><th class="text-end">Jumlah Transaksi</th><th class="text-end">Total Nilai</th></tr>
                    </thead>
                    <tbody id="statusBody">
                        <tr id="statusLoading"><td colspan="3" class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                <h6 class="mb-0"><i class="bi bi-receipt-cutoff me-2"></i>Master Pajak</h6>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#taxModal" id="btnAdd">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Pajak
                </button>
            </div>
            <p class="text-muted small">Keterangan: pajak yang berstatus "Aktif" akan bisa dipilih saat membuat transaksi baru.</p>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Nama Pajak</th>
                            <th class="text-end">Persentase</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th class="text-center" style="width:110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="taxBody">
                        <tr id="taxLoading"><td colspan="6" class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="taxModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="taxForm">
                <div class="modal-header">
                    <h6 class="modal-title" id="modalTitle"><i class="bi bi-receipt-cutoff me-2"></i>Tambah Pajak</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_tax" id="id_tax">
                    <div class="mb-2">
                        <label class="form-label">Nama Pajak</label>
                        <input type="text" class="form-control" name="e_tax_name" id="e_tax_name" placeholder="PPN, Pajak Layanan, dsb" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Persentase (%)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="n_percentage" id="n_percentage" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="e_keterangan" id="e_keterangan" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function () {
    var BASE_URL = '<?= site_url('tax'); ?>/';

    function escapeHtml(str) { return $('<div>').text(str == null ? '' : str).html(); }
    function formatRupiah(num) { return 'Rp ' + Number(num || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 }); }
    function showAlert(message, type) {
        $('#alertPlaceholder').html('<div class="alert alert-' + type + ' alert-dismissible fade show">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
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

    function loadFinance() {
        $.ajax({ url: BASE_URL + 'finance_summary', method: 'POST', dataType: 'json' })
            .done(function (res) {
                var data = (res && res.data) || {};
                var s = data.summary || {};
                $('#statCountTrx').text(s.n_transactions || 0);
                $('#statSubtotal').text(formatRupiah(s.v_subtotal));
                $('#statTax').text(formatRupiah(s.v_tax));
                $('#statTotal').text(formatRupiah(s.v_total));

                var $body = $('#statusBody');
                $body.empty();
                var rows = data.by_status || [];
                if (rows.length === 0) {
                    $body.append('<tr><td colspan="3" class="text-center text-muted py-3">Belum ada transaksi.</td></tr>');
                } else {
                    rows.forEach(function (r) {
                        var tr = $('<tr>');
                        tr.append('<td><span class="badge ' + statusBadgeClass(r.e_status) + '">' + escapeHtml(r.e_status) + '</span></td>');
                        tr.append('<td class="text-end">' + r.n_transactions + '</td>');
                        tr.append('<td class="text-end">' + formatRupiah(r.v_total) + '</td>');
                        $body.append(tr);
                    });
                }
            })
            .fail(function () {
                $('#statCountTrx, #statSubtotal, #statTax, #statTotal').text('-');
                $('#statusBody').html('<tr><td colspan="3" class="text-center text-danger py-3">Gagal memuat ringkasan keuangan.</td></tr>');
            });
    }

    function loadTax() {
        $('#taxLoading').show();
        $.ajax({ url: BASE_URL + 'list_data', method: 'POST', dataType: 'json' })
            .done(function (res) { renderTax((res && res.data) || []); })
            .fail(function () { $('#taxBody').html('<tr><td colspan="6" class="text-center text-danger py-3">Gagal memuat data pajak.</td></tr>'); })
            .always(function () { $('#taxLoading').hide(); });
    }

    function renderTax(rows) {
        var $body = $('#taxBody');
        $body.empty();
        if (rows.length === 0) {
            $body.append('<tr><td colspan="6" class="text-center text-muted py-3">Belum ada data pajak.</td></tr>');
            return;
        }
        rows.forEach(function (row, idx) {
            var tr = $('<tr>').toggleClass('row-inactive', row.f_active !== 't');
            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td>' + escapeHtml(row.e_tax_name) + '</td>');
            tr.append('<td class="text-end">' + Number(row.n_percentage).toLocaleString('id-ID') + '%</td>');
            tr.append('<td class="text-muted small">' + escapeHtml(row.e_keterangan) + '</td>');
            tr.append('<td>' + (row.f_active === 't' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>') + '</td>');

            var actionTd = $('<td class="text-center">');
            var group = $('<div class="d-inline-flex gap-1">');
            var editBtn = $('<button type="button" class="btn btn-sm btn-outline-primary btn-icon" title="Edit"><i class="bi bi-pencil"></i></button>');
            editBtn.on('click', function () { openEdit(row); });
            var toggleBtn = $('<button type="button" class="btn btn-sm ' + (row.f_active === 't' ? 'btn-outline-danger' : 'btn-outline-success') + ' btn-icon" title="' + (row.f_active === 't' ? 'Nonaktifkan' : 'Aktifkan') + '"><i class="bi ' + (row.f_active === 't' ? 'bi-slash-circle' : 'bi-check-circle') + '"></i></button>');
            toggleBtn.on('click', function () { toggleActive(row.id_tax); });
            group.append(editBtn).append(toggleBtn);
            actionTd.append(group);
            tr.append(actionTd);

            $body.append(tr);
        });
    }

    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="bi bi-receipt-cutoff me-2"></i>Tambah Pajak');
        $('#taxForm')[0].reset();
        $('#id_tax').val('');
    });

    function openEdit(row) {
        $('#modalTitle').html('<i class="bi bi-pencil me-2"></i>Edit Pajak');
        $('#id_tax').val(row.id_tax);
        $('#e_tax_name').val(row.e_tax_name);
        $('#n_percentage').val(row.n_percentage);
        $('#e_keterangan').val(row.e_keterangan);
        new bootstrap.Modal('#taxModal').show();
    }

    $('#taxForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({ url: BASE_URL + 'save', method: 'POST', dataType: 'json', data: $(this).serialize() })
            .done(function (res) {
                bootstrap.Modal.getInstance(document.getElementById('taxModal')).hide();
                showAlert(res.message, 'success');
                loadTax();
            })
            .fail(function (xhr) { showAlert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan.', 'danger'); });
    });

    function toggleActive(id) {
        $.ajax({ url: BASE_URL + 'toggle_active/' + id, method: 'POST', dataType: 'json' })
            .done(function (res) { showAlert(res.message, 'success'); loadTax(); });
    }

    loadFinance();
    loadTax();
});
</script>
</body>
</html>
