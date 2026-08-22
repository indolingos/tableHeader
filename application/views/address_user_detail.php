<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Alamat - <?= htmlspecialchars($target_username); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    #tableLoading { display: none; }
    .badge-user { background-color: #fd7e14; font-weight: 600; }
    .btn-icon { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'address', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?= site_url('address'); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Alamat
        </a>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addressModal" id="btnAdd">
            <i class="bi bi-plus-lg me-1"></i>Tambah Alamat
        </button>
    </div>

    <div id="alertPlaceholder"></div>

    <div class="card">
        <div class="card-body">
            <h6 class="mb-1"><i class="bi bi-list-ul me-2"></i>Daftar Alamat</h6>
            <p class="text-muted small">Keterangan: alamat dengan badge "Utama" akan digunakan sebagai alamat pengiriman default saat checkout.</p>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Label</th>
                            <th>Penerima</th>
                            <th>Alamat Lengkap</th>
                            <th>Kota/Provinsi</th>
                            <th>Keterangan</th>
                            <th class="text-center" style="width:110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr id="tableLoading">
                            <td colspan="7" class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div>Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addressForm">
                <div class="modal-header">
                    <h6 class="modal-title" id="modalTitle"><i class="bi bi-geo-alt me-2"></i>Tambah Alamat</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_address" id="id_address">
                    <input type="hidden" name="id_user" value="<?= (int) $id_user; ?>">
                    <div class="mb-2">
                        <label class="form-label">Label Alamat</label>
                        <input type="text" class="form-control" name="e_label" id="e_label" placeholder="Rumah / Kantor" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nama Penerima</label>
                        <input type="text" class="form-control" name="e_recipient" id="e_recipient" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">No Telepon</label>
                        <input type="text" class="form-control" name="i_phone" id="i_phone" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea class="form-control" name="e_address_full" id="e_address_full" rows="2" required></textarea>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Kota</label>
                            <input type="text" class="form-control" name="e_city" id="e_city">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Provinsi</label>
                            <input type="text" class="form-control" name="e_province" id="e_province">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Kode Pos</label>
                        <input type="text" class="form-control" name="i_postal_code" id="i_postal_code">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="e_keterangan" id="e_keterangan" rows="2" placeholder="Patokan lokasi, dsb"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="f_primary" id="f_primary" value="1">
                        <label class="form-check-label" for="f_primary">Jadikan alamat utama</label>
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
    var BASE_URL = '<?= site_url('address'); ?>/';
    var ID_USER  = <?= (int) $id_user; ?>;

    function escapeHtml(str) { return $('<div>').text(str == null ? '' : str).html(); }
    function showAlert(message, type) {
        $('#alertPlaceholder').html('<div class="alert alert-' + type + ' alert-dismissible fade show">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    }

    function load() {
        $('#tableLoading').show();
        $.ajax({ url: BASE_URL + 'user_items/' + ID_USER, method: 'POST', dataType: 'json' })
            .done(function (res) { render((res && res.data) || []); })
            .fail(function () { $('#tableBody').html('<tr><td colspan="7" class="text-center text-danger py-4">Gagal memuat data.</td></tr>'); })
            .always(function () { $('#tableLoading').hide(); });
    }

    function render(rows) {
        var $body = $('#tableBody');
        $body.empty();
        if (rows.length === 0) {
            $body.append('<tr><td colspan="7" class="text-center text-muted py-4">Belum ada alamat tersimpan.</td></tr>');
            return;
        }
        rows.forEach(function (row, idx) {
            var tr = $('<tr>');
            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td>' + escapeHtml(row.e_label) + (row.f_primary === 't' ? ' <span class="badge bg-warning text-dark">Utama</span>' : '') + '</td>');
            tr.append('<td>' + escapeHtml(row.e_recipient) + '<div class="text-muted small">' + escapeHtml(row.i_phone) + '</div></td>');
            tr.append('<td>' + escapeHtml(row.e_address_full) + (row.i_postal_code ? ' <span class="text-muted small">(' + escapeHtml(row.i_postal_code) + ')</span>' : '') + '</td>');
            tr.append('<td>' + escapeHtml([row.e_city, row.e_province].filter(Boolean).join(', ')) + '</td>');
            tr.append('<td class="text-muted small">' + escapeHtml(row.e_keterangan) + '</td>');

            var actionTd = $('<td class="text-center">');
            var group = $('<div class="d-inline-flex gap-1">');
            var editBtn = $('<button type="button" class="btn btn-sm btn-outline-primary btn-icon" title="Edit"><i class="bi bi-pencil"></i></button>');
            editBtn.on('click', function () { openEdit(row); });
            var delBtn = $('<button type="button" class="btn btn-sm btn-outline-danger btn-icon" title="Hapus"><i class="bi bi-trash"></i></button>');
            delBtn.on('click', function () { remove(row.id_address); });
            group.append(editBtn).append(delBtn);
            actionTd.append(group);
            tr.append(actionTd);

            $body.append(tr);
        });
    }

    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="bi bi-geo-alt me-2"></i>Tambah Alamat');
        $('#addressForm')[0].reset();
        $('#id_address').val('');
    });

    function openEdit(row) {
        $('#modalTitle').html('<i class="bi bi-pencil me-2"></i>Edit Alamat');
        $('#id_address').val(row.id_address);
        $('#e_label').val(row.e_label);
        $('#e_recipient').val(row.e_recipient);
        $('#i_phone').val(row.i_phone);
        $('#e_address_full').val(row.e_address_full);
        $('#e_city').val(row.e_city);
        $('#e_province').val(row.e_province);
        $('#i_postal_code').val(row.i_postal_code);
        $('#e_keterangan').val(row.e_keterangan);
        $('#f_primary').prop('checked', row.f_primary === 't');
        new bootstrap.Modal('#addressModal').show();
    }

    $('#addressForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({ url: BASE_URL + 'save', method: 'POST', dataType: 'json', data: $(this).serialize() })
            .done(function (res) {
                bootstrap.Modal.getInstance(document.getElementById('addressModal')).hide();
                showAlert(res.message, 'success');
                load();
            })
            .fail(function (xhr) { showAlert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan.', 'danger'); });
    });

    function remove(id) {
        if (!confirm('Hapus alamat ini?')) return;
        $.ajax({ url: BASE_URL + 'delete/' + id, method: 'POST', dataType: 'json' })
            .done(function (res) { showAlert(res.message, 'success'); load(); });
    }

    load();
});
</script>
</body>
</html>
