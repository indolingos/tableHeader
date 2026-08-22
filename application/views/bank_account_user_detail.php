<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rekening - <?= htmlspecialchars($target_username); ?></title>
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

<?php $this->load->view('partials/nav', array('active' => 'bank_account', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?= site_url('bank_account'); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Rekening
        </a>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bankModal" id="btnAdd">
            <i class="bi bi-plus-lg me-1"></i>Tambah Rekening
        </button>
    </div>

    <div id="alertPlaceholder"></div>

    <div class="card">
        <div class="card-body">
            <h6 class="mb-1"><i class="bi bi-list-ul me-2"></i>Daftar Rekening</h6>
            <p class="text-muted small">Keterangan: rekening dengan badge "Utama" digunakan sebagai tujuan refund default.</p>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Bank</th>
                            <th>No Rekening</th>
                            <th>Pemilik</th>
                            <th>Keterangan</th>
                            <th class="text-center" style="width:110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
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

<div class="modal fade" id="bankModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bankForm">
                <div class="modal-header">
                    <h6 class="modal-title" id="modalTitle"><i class="bi bi-credit-card me-2"></i>Tambah Rekening</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_bank_account" id="id_bank_account">
                    <input type="hidden" name="id_user" value="<?= (int) $id_user; ?>">
                    <div class="mb-2">
                        <label class="form-label">Nama Bank</label>
                        <input type="text" class="form-control" name="e_bank_name" id="e_bank_name" placeholder="BCA, BRI, Mandiri, dsb" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">No Rekening</label>
                        <input type="text" class="form-control" name="i_account_number" id="i_account_number" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nama Pemilik Rekening</label>
                        <input type="text" class="form-control" name="e_account_holder" id="e_account_holder" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="e_keterangan" id="e_keterangan" rows="2"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="f_primary" id="f_primary" value="1">
                        <label class="form-check-label" for="f_primary">Jadikan rekening utama</label>
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
    var BASE_URL = '<?= site_url('bank_account'); ?>/';
    var ID_USER  = <?= (int) $id_user; ?>;

    function escapeHtml(str) { return $('<div>').text(str == null ? '' : str).html(); }
    function showAlert(message, type) {
        $('#alertPlaceholder').html('<div class="alert alert-' + type + ' alert-dismissible fade show">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    }

    function load() {
        $('#tableLoading').show();
        $.ajax({ url: BASE_URL + 'user_items/' + ID_USER, method: 'POST', dataType: 'json' })
            .done(function (res) { render((res && res.data) || []); })
            .fail(function () { $('#tableBody').html('<tr><td colspan="6" class="text-center text-danger py-4">Gagal memuat data.</td></tr>'); })
            .always(function () { $('#tableLoading').hide(); });
    }

    function render(rows) {
        var $body = $('#tableBody');
        $body.empty();
        if (rows.length === 0) {
            $body.append('<tr><td colspan="6" class="text-center text-muted py-4">Belum ada rekening tersimpan.</td></tr>');
            return;
        }
        rows.forEach(function (row, idx) {
            var tr = $('<tr>');
            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td>' + escapeHtml(row.e_bank_name) + (row.f_primary === 't' ? ' <span class="badge bg-warning text-dark">Utama</span>' : '') + '</td>');
            tr.append('<td>' + escapeHtml(row.i_account_number) + '</td>');
            tr.append('<td>' + escapeHtml(row.e_account_holder) + '</td>');
            tr.append('<td class="text-muted small">' + escapeHtml(row.e_keterangan) + '</td>');

            var actionTd = $('<td class="text-center">');
            var group = $('<div class="d-inline-flex gap-1">');
            var editBtn = $('<button type="button" class="btn btn-sm btn-outline-primary btn-icon" title="Edit"><i class="bi bi-pencil"></i></button>');
            editBtn.on('click', function () { openEdit(row); });
            var delBtn = $('<button type="button" class="btn btn-sm btn-outline-danger btn-icon" title="Hapus"><i class="bi bi-trash"></i></button>');
            delBtn.on('click', function () { remove(row.id_bank_account); });
            group.append(editBtn).append(delBtn);
            actionTd.append(group);
            tr.append(actionTd);

            $body.append(tr);
        });
    }

    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="bi bi-credit-card me-2"></i>Tambah Rekening');
        $('#bankForm')[0].reset();
        $('#id_bank_account').val('');
    });

    function openEdit(row) {
        $('#modalTitle').html('<i class="bi bi-pencil me-2"></i>Edit Rekening');
        $('#id_bank_account').val(row.id_bank_account);
        $('#e_bank_name').val(row.e_bank_name);
        $('#i_account_number').val(row.i_account_number);
        $('#e_account_holder').val(row.e_account_holder);
        $('#e_keterangan').val(row.e_keterangan);
        $('#f_primary').prop('checked', row.f_primary === 't');
        new bootstrap.Modal('#bankModal').show();
    }

    $('#bankForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({ url: BASE_URL + 'save', method: 'POST', dataType: 'json', data: $(this).serialize() })
            .done(function (res) {
                bootstrap.Modal.getInstance(document.getElementById('bankModal')).hide();
                showAlert(res.message, 'success');
                load();
            })
            .fail(function (xhr) { showAlert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan.', 'danger'); });
    });

    function remove(id) {
        if (!confirm('Hapus rekening ini?')) return;
        $.ajax({ url: BASE_URL + 'delete/' + id, method: 'POST', dataType: 'json' })
            .done(function (res) { showAlert(res.message, 'success'); load(); });
    }

    load();
});
</script>
</body>
</html>
