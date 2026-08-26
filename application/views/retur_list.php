<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Retur &amp; Refund Penjualan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    #tableLoading { display: none; }
    .btn-icon { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'retur', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div id="alertPlaceholder"></div>

    <div class="card">
        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <h5 class="mb-0"><i class="bi bi-arrow-return-left me-2"></i>Retur &amp; Refund Penjualan</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#returModal" id="btnAdd">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Pengajuan Retur
                </button>
            </div>
            <p class="text-muted small">Pengajuan retur/refund dari konsumen atas barang yang sudah dibeli. Cari lewat no invoice transaksinya.</p>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>No Invoice</th>
                            <th>Konsumen</th>
                            <th>Produk</th>
                            <th class="text-end">Qty</th>
                            <th>Alasan</th>
                            <th>Status Retur</th>
                            <th>Refund</th>
                            <th class="text-center" style="width:170px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="returTableBody">
                        <tr id="tableLoading">
                            <td colspan="9" class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div>Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Modal Tambah Retur -->
<div class="modal fade" id="returModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="returForm">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="bi bi-arrow-return-left me-2"></i>Tambah Pengajuan Retur</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_transaction" id="id_transaction">
                    <div class="mb-2">
                        <label class="form-label">No Invoice</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="i_invoice" placeholder="mis. INV/2026/0001">
                            <button type="button" class="btn btn-outline-secondary" id="btnFindInvoice">Cari</button>
                        </div>
                        <div id="invoiceInfo" class="form-text"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Produk</label>
                        <select class="form-select" name="id_product" id="id_product" required disabled>
                            <option value="">-- Cari invoice dulu --</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Qty Diretur</label>
                        <input type="number" class="form-control" name="n_qty" id="n_qty" min="1" value="1" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Alasan Retur</label>
                        <textarea class="form-control" name="e_reason" id="e_reason" rows="2" required placeholder="mis. barang rusak, salah ukuran, dsb"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveRetur" disabled>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Proses Refund -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="refundForm">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="bi bi-cash-coin me-2"></i>Proses Refund</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_return" id="refund_id_return">
                    <div class="mb-2">
                        <label class="form-label">Nominal Refund (Rp)</label>
                        <input type="number" class="form-control" name="v_refund_amount" id="v_refund_amount" min="1" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Proses Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function () {
    var BASE_URL = '<?= site_url('retur'); ?>/';

    function escapeHtml(str) { return $('<div>').text(str == null ? '' : str).html(); }
    function formatMoney(n) { return 'Rp ' + (Number(n) || 0).toLocaleString('id-ID'); }
    function formatDate(str) {
        if (!str) return '-';
        var d = new Date(str.replace(' ', 'T'));
        if (isNaN(d.getTime())) return str;
        return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    function showAlert(message, type) {
        $('#alertPlaceholder').html(
            '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' + message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'
        );
    }

    function statusBadge(status) {
        var map = { 'Diajukan': 'bg-warning text-dark', 'Disetujui': 'bg-info text-dark', 'Ditolak': 'bg-danger', 'Selesai': 'bg-success' };
        return '<span class="badge ' + (map[status] || 'bg-secondary') + '">' + escapeHtml(status) + '</span>';
    }

    function loadReturs() {
        $('#tableLoading').show();
        $.ajax({ url: BASE_URL + 'list_data', method: 'POST', dataType: 'json' })
            .done(function (res) { renderTable((res && res.data) || []); })
            .fail(function () { $('#returTableBody').html('<tr><td colspan="9" class="text-center text-danger py-4">Gagal memuat data.</td></tr>'); })
            .always(function () { $('#tableLoading').hide(); });
    }

    function renderTable(rows) {
        var $body = $('#returTableBody');
        $body.empty();
        if (rows.length === 0) {
            $body.append('<tr><td colspan="9" class="text-center text-muted py-4">Belum ada pengajuan retur.</td></tr>');
            return;
        }
        rows.forEach(function (row, idx) {
            var tr = $('<tr>');
            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td>' + escapeHtml(row.i_invoice) + '</td>');
            tr.append('<td>' + escapeHtml(row.i_username) + '</td>');
            tr.append('<td>' + escapeHtml(row.e_product) + '</td>');
            tr.append('<td class="text-end">' + row.n_qty + '</td>');
            tr.append('<td class="small">' + escapeHtml(row.e_reason) + '</td>');
            tr.append('<td>' + statusBadge(row.e_status) + '</td>');

            var refundTd = $('<td>');
            if (row.id_refund) {
                refundTd.html(formatMoney(row.v_refund_amount) + '<br><span class="badge bg-light text-dark border">' + escapeHtml(row.e_refund_status) + '</span>');
            } else {
                refundTd.html('<span class="text-muted small">-</span>');
            }
            tr.append(refundTd);

            var actionTd = $('<td class="text-center">');
            var group = $('<div class="d-inline-flex flex-wrap gap-1 justify-content-center">');

            if (row.e_status === 'Diajukan') {
                var approveBtn = $('<button type="button" class="btn btn-sm btn-outline-success btn-icon" title="Setujui"><i class="bi bi-check-lg"></i></button>');
                approveBtn.on('click', function () { updateStatus(row.id_return, 'Disetujui'); });
                var rejectBtn = $('<button type="button" class="btn btn-sm btn-outline-danger btn-icon" title="Tolak"><i class="bi bi-x-lg"></i></button>');
                rejectBtn.on('click', function () { updateStatus(row.id_return, 'Ditolak'); });
                group.append(approveBtn).append(rejectBtn);
            } else if (row.e_status === 'Disetujui' && !row.id_refund) {
                var refundBtn = $('<button type="button" class="btn btn-sm btn-outline-primary" title="Proses Refund"><i class="bi bi-cash-coin me-1"></i>Refund</button>');
                refundBtn.on('click', function () { openRefund(row.id_return); });
                group.append(refundBtn);
            } else if (row.id_refund && row.e_refund_status === 'Diproses') {
                var doneBtn = $('<button type="button" class="btn btn-sm btn-outline-success" title="Tandai Selesai">Selesaikan</button>');
                doneBtn.on('click', function () { updateRefundStatus(row.id_refund, 'Selesai'); });
                group.append(doneBtn);
            } else {
                group.append('<span class="text-muted small">-</span>');
            }

            actionTd.append(group);
            tr.append(actionTd);

            $body.append(tr);
        });
    }

    function updateStatus(id, status) {
        $.ajax({ url: BASE_URL + 'update_status/' + id, method: 'POST', dataType: 'json', data: { e_status: status } })
            .done(function (res) { showAlert(res.message, 'success'); loadReturs(); })
            .fail(function (xhr) { showAlert((xhr.responseJSON || {}).message || 'Gagal mengubah status.', 'danger'); });
    }

    function updateRefundStatus(id_refund, status) {
        $.ajax({ url: BASE_URL + 'update_refund_status/' + id_refund, method: 'POST', dataType: 'json', data: { e_status: status } })
            .done(function (res) { showAlert(res.message, 'success'); loadReturs(); })
            .fail(function (xhr) { showAlert((xhr.responseJSON || {}).message || 'Gagal mengubah status refund.', 'danger'); });
    }

    function openRefund(id_return) {
        $('#refundForm')[0].reset();
        $('#refund_id_return').val(id_return);
        new bootstrap.Modal('#refundModal').show();
    }

    $('#refundForm').on('submit', function (e) {
        e.preventDefault();
        var id = $('#refund_id_return').val();
        $.ajax({ url: BASE_URL + 'process_refund/' + id, method: 'POST', dataType: 'json', data: $(this).serialize() })
            .done(function (res) {
                bootstrap.Modal.getInstance(document.getElementById('refundModal')).hide();
                showAlert(res.message, 'success');
                loadReturs();
            })
            .fail(function (xhr) { showAlert((xhr.responseJSON || {}).message || 'Gagal memproses refund.', 'danger'); });
    });

    // --- Form Tambah Retur ---
    $('#btnAdd').on('click', function () {
        $('#returForm')[0].reset();
        $('#id_transaction').val('');
        $('#invoiceInfo').text('');
        $('#id_product').html('<option value="">-- Cari invoice dulu --</option>').prop('disabled', true);
        $('#btnSaveRetur').prop('disabled', true);
    });

    $('#btnFindInvoice').on('click', function () {
        var invoice = $('#i_invoice').val().trim();
        if (!invoice) return;
        $('#invoiceInfo').html('<span class="text-muted">Mencari...</span>');
        $.ajax({ url: BASE_URL + 'find_invoice', method: 'POST', dataType: 'json', data: { i_invoice: invoice } })
            .done(function (res) {
                var d = res.data;
                $('#id_transaction').val(d.id_transaction);
                $('#invoiceInfo').html('<span class="text-success">Ditemukan: ' + escapeHtml(d.i_username) + '</span>');

                var $sel = $('#id_product').empty().prop('disabled', false);
                if (!d.items || d.items.length === 0) {
                    $sel.html('<option value="">Tidak ada barang di transaksi ini</option>');
                    $('#btnSaveRetur').prop('disabled', true);
                    return;
                }
                d.items.forEach(function (item) {
                    $sel.append('<option value="' + item.id_product + '">' + escapeHtml(item.e_product) + ' (dibeli ' + item.n_qty + ')</option>');
                });
                $('#btnSaveRetur').prop('disabled', false);
            })
            .fail(function (xhr) {
                $('#invoiceInfo').html('<span class="text-danger">' + ((xhr.responseJSON || {}).message || 'Invoice tidak ditemukan.') + '</span>');
                $('#id_transaction').val('');
                $('#id_product').html('<option value="">-- Cari invoice dulu --</option>').prop('disabled', true);
                $('#btnSaveRetur').prop('disabled', true);
            });
    });

    $('#returForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({ url: BASE_URL + 'save', method: 'POST', dataType: 'json', data: $(this).serialize() })
            .done(function (res) {
                bootstrap.Modal.getInstance(document.getElementById('returModal')).hide();
                showAlert(res.message, 'success');
                loadReturs();
            })
            .fail(function (xhr) { showAlert((xhr.responseJSON || {}).message || 'Gagal menyimpan pengajuan retur.', 'danger'); });
    });

    loadReturs();
});
</script>
</body>
</html>
