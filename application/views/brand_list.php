<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Master Brand</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    .btn-icon { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
    tr.row-inactive { color: #adb5bd; background-color: #f8f9fa; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'brand', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">
    <div id="alertPlaceholder"></div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h5 class="mb-1"><i class="bi bi-award me-2"></i>Master Brand</h5>
                    <p class="text-muted small mb-0">Kelola merek produk seperti Toyota, Honda, Yamaha, Apple, Samsung, JBL, dan Casio.</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="btnAdd" data-bs-toggle="modal" data-bs-target="#brandModal">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Brand
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px;">No</th>
                            <th>Nama Brand</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th class="text-center" style="width:120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="brandTableBody">
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div>Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="brandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="brandForm">
                <div class="modal-header">
                    <h6 class="modal-title" id="brandModalTitle"><i class="bi bi-award me-2"></i>Tambah Brand</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_brand" id="id_brand">

                    <div class="mb-3">
                        <label class="form-label">Nama Brand</label>
                        <input type="text" class="form-control" name="e_brand" id="e_brand" maxlength="100" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="e_keterangan" id="e_keterangan" rows="3" maxlength="255"></textarea>
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
    var BASE_URL = '<?= site_url('brand'); ?>/';

    function escapeHtml(str) {
        return $('<div>').text(str == null ? '' : str).html();
    }

    function showAlert(message, type) {
        $('#alertPlaceholder').html(
            '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            escapeHtml(message) +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>'
        );
    }

    function loadBrands() {
        $.ajax({
            url: BASE_URL + 'list_data',
            method: 'POST',
            dataType: 'json'
        })
        .done(function (res) {
            renderTable((res && res.data) || []);
        })
        .fail(function () {
            $('#brandTableBody').html(
                '<tr><td colspan="5" class="text-center text-danger py-4">Gagal memuat data brand.</td></tr>'
            );
        });
    }

    function renderTable(rows) {
        var $body = $('#brandTableBody');
        $body.empty();

        if (!rows.length) {
            $body.append('<tr><td colspan="5" class="text-center text-muted py-4">Belum ada data brand.</td></tr>');
            return;
        }

        rows.forEach(function (row, idx) {
            var active = row.f_active === 't';
            var tr = $('<tr>').toggleClass('row-inactive', !active);

            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td class="fw-semibold">' + escapeHtml(row.e_brand) + '</td>');
            tr.append('<td class="text-muted">' + escapeHtml(row.e_keterangan || '-') + '</td>');
            tr.append('<td>' + (active
                ? '<span class="badge bg-success">Aktif</span>'
                : '<span class="badge bg-secondary">Nonaktif</span>') + '</td>');

            var actionTd = $('<td class="text-center">');
            var group = $('<div class="d-inline-flex gap-1">');

            var editBtn = $('<button type="button" class="btn btn-sm btn-outline-primary btn-icon" title="Edit"><i class="bi bi-pencil"></i></button>');
            editBtn.on('click', function () { openEdit(row); });

            var toggleBtn = $('<button type="button" class="btn btn-sm ' +
                (active ? 'btn-outline-danger' : 'btn-outline-success') +
                ' btn-icon" title="' + (active ? 'Nonaktifkan' : 'Aktifkan') + '">' +
                '<i class="bi ' + (active ? 'bi-slash-circle' : 'bi-check-circle') + '"></i></button>');
            toggleBtn.on('click', function () { toggleActive(row.id_brand); });

            group.append(editBtn).append(toggleBtn);
            actionTd.append(group);
            tr.append(actionTd);
            $body.append(tr);
        });
    }

    $('#btnAdd').on('click', function () {
        $('#brandModalTitle').html('<i class="bi bi-award me-2"></i>Tambah Brand');
        $('#brandForm')[0].reset();
        $('#id_brand').val('');
    });

    function openEdit(row) {
        if (row.f_active !== 't') {
            showAlert('Brand yang nonaktif tidak bisa diedit.', 'warning');
            return;
        }

        $('#brandModalTitle').html('<i class="bi bi-pencil me-2"></i>Edit Brand');
        $('#id_brand').val(row.id_brand);
        $('#e_brand').val(row.e_brand);
        $('#e_keterangan').val(row.e_keterangan || '');
        new bootstrap.Modal('#brandModal').show();
    }

    $('#brandForm').on('submit', function (e) {
        e.preventDefault();

        var $submit = $(this).find('button[type="submit"]');
        $submit.prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'save',
            method: 'POST',
            dataType: 'json',
            data: $(this).serialize()
        })
        .done(function (res) {
            bootstrap.Modal.getInstance(document.getElementById('brandModal')).hide();
            showAlert(res.message || 'Berhasil disimpan.', 'success');
            loadBrands();
        })
        .fail(function (xhr) {
            var res = xhr.responseJSON || {};
            showAlert(res.message || 'Gagal menyimpan brand.', 'danger');
        })
        .always(function () {
            $submit.prop('disabled', false);
        });
    });

    function toggleActive(id) {
        $.ajax({
            url: BASE_URL + 'toggle_active/' + id,
            method: 'POST',
            dataType: 'json'
        })
        .done(function (res) {
            showAlert(res.message || 'Status berhasil diubah.', 'success');
            loadBrands();
        })
        .fail(function (xhr) {
            var res = xhr.responseJSON || {};
            showAlert(res.message || 'Gagal mengubah status brand.', 'danger');
        });
    }

    loadBrands();
});
</script>
</body>
</html>
