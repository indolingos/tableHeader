<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rating &amp; Review Produk</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    #tableLoading { display: none; }
    .btn-icon { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
    tr.row-hidden { color: #adb5bd; background-color: #f8f9fa; }
    .stars { color: #f5a623; letter-spacing: 1px; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'review', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div id="alertPlaceholder"></div>

    <div class="card">
        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <h5 class="mb-0"><i class="bi bi-star me-2"></i>Rating &amp; Review Produk</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#reviewModal" id="btnAdd">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Review
                </button>
            </div>
            <p class="text-muted small">Review dari konsumen atas produk yang sudah dibeli. Review baru langsung tayang; sembunyikan kalau ada yang perlu dimoderasi.</p>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Produk</th>
                            <th>Konsumen</th>
                            <th>Invoice</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th class="text-center" style="width:110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="reviewTableBody">
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

<!-- Modal Form -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reviewForm">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="bi bi-star me-2"></i>Tambah Review</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Produk</label>
                        <select class="form-select" name="id_product" required>
                            <option value="">-- Pilih Produk --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id_product']; ?>"><?= htmlspecialchars($p['e_product']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Konsumen</label>
                        <select class="form-select" name="id_user" required>
                            <option value="">-- Pilih Konsumen --</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?= $c['id_user']; ?>"><?= htmlspecialchars($c['i_username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">No Invoice (opsional, untuk verified purchase)</label>
                        <input type="number" class="form-control" name="id_transaction" placeholder="id_transaction, boleh dikosongkan">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Rating</label>
                        <select class="form-select" name="n_rating" required>
                            <option value="">-- Pilih Rating --</option>
                            <option value="5">5 - Sangat Puas</option>
                            <option value="4">4 - Puas</option>
                            <option value="3">3 - Cukup</option>
                            <option value="2">2 - Kurang</option>
                            <option value="1">1 - Sangat Kurang</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Isi Review</label>
                        <textarea class="form-control" name="e_review_text" rows="3" placeholder="Tulis review dari konsumen di sini"></textarea>
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
    var BASE_URL = '<?= site_url('review'); ?>/';

    function escapeHtml(str) { return $('<div>').text(str == null ? '' : str).html(); }
    function stars(n) { return '<span class="stars">' + '★'.repeat(n) + '☆'.repeat(5 - n) + '</span>'; }
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

    function loadReviews() {
        $('#tableLoading').show();
        $.ajax({ url: BASE_URL + 'list_data', method: 'POST', dataType: 'json' })
            .done(function (res) { renderTable((res && res.data) || []); })
            .fail(function () { $('#reviewTableBody').html('<tr><td colspan="9" class="text-center text-danger py-4">Gagal memuat data.</td></tr>'); })
            .always(function () { $('#tableLoading').hide(); });
    }

    function renderTable(rows) {
        var $body = $('#reviewTableBody');
        $body.empty();
        if (rows.length === 0) {
            $body.append('<tr><td colspan="9" class="text-center text-muted py-4">Belum ada review.</td></tr>');
            return;
        }
        rows.forEach(function (row, idx) {
            var tr = $('<tr>').toggleClass('row-hidden', row.f_approved !== 't');
            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td>' + escapeHtml(row.e_product) + '</td>');
            tr.append('<td>' + escapeHtml(row.i_username) + '</td>');
            tr.append('<td>' + escapeHtml(row.i_invoice || '-') + '</td>');
            tr.append('<td>' + stars(row.n_rating) + '</td>');
            tr.append('<td class="small" style="max-width:220px;">' + escapeHtml(row.e_review_text || '-') + '</td>');
            tr.append('<td class="small">' + formatDate(row.dt_created) + '</td>');
            tr.append('<td>' + (row.f_approved === 't'
                ? '<span class="badge bg-success">Tayang</span>'
                : '<span class="badge bg-secondary">Disembunyikan</span>') + '</td>');

            var actionTd = $('<td class="text-center">');
            var group = $('<div class="d-inline-flex gap-1">');
            var toggleBtn = $('<button type="button" class="btn btn-sm ' + (row.f_approved === 't' ? 'btn-outline-secondary' : 'btn-outline-success') + ' btn-icon" title="' + (row.f_approved === 't' ? 'Sembunyikan' : 'Tampilkan') + '"><i class="bi ' + (row.f_approved === 't' ? 'bi-eye-slash' : 'bi-eye') + '"></i></button>');
            toggleBtn.on('click', function () { toggleApproved(row.id_review); });
            var deleteBtn = $('<button type="button" class="btn btn-sm btn-outline-danger btn-icon" title="Hapus"><i class="bi bi-trash"></i></button>');
            deleteBtn.on('click', function () { deleteReview(row.id_review); });
            group.append(toggleBtn).append(deleteBtn);
            actionTd.append(group);
            tr.append(actionTd);

            $body.append(tr);
        });
    }

    function toggleApproved(id) {
        $.ajax({ url: BASE_URL + 'toggle_approved/' + id, method: 'POST', dataType: 'json' })
            .done(function (res) { showAlert(res.message, 'success'); loadReviews(); })
            .fail(function () { showAlert('Gagal mengubah status.', 'danger'); });
    }

    function deleteReview(id) {
        if (!confirm('Hapus review ini?')) return;
        $.ajax({ url: BASE_URL + 'delete/' + id, method: 'POST', dataType: 'json' })
            .done(function (res) { showAlert(res.message, 'success'); loadReviews(); })
            .fail(function () { showAlert('Gagal menghapus review.', 'danger'); });
    }

    $('#btnAdd').on('click', function () { $('#reviewForm')[0].reset(); });

    $('#reviewForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({ url: BASE_URL + 'save', method: 'POST', dataType: 'json', data: $(this).serialize() })
            .done(function (res) {
                bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
                showAlert(res.message, 'success');
                loadReviews();
            })
            .fail(function (xhr) { showAlert((xhr.responseJSON || {}).message || 'Gagal menyimpan review.', 'danger'); });
    });

    loadReviews();
});
</script>
</body>
</html>
