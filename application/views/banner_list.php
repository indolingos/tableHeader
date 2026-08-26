<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Banner Promo (Marketing Tools)</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    #tableLoading { display: none; }
    .btn-icon { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
    .banner-thumb { width: 90px; height: 45px; object-fit: cover; border-radius: 4px; background: #eee; }
    tr.row-inactive { color: #adb5bd; background-color: #f8f9fa; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'banner', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div id="alertPlaceholder"></div>

    <div class="card">
        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <h5 class="mb-0"><i class="bi bi-image me-2"></i>Banner Promo (Marketing Tools)</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bannerModal" id="btnAdd">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Banner
                </button>
            </div>
            <p class="text-muted small">Banner promo yang tampil di halaman depan toko online. Urutan tampil diatur lewat kolom "Urutan" (angka kecil tampil duluan).</p>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Preview</th>
                            <th>Judul</th>
                            <th>Link</th>
                            <th class="text-center">Urutan</th>
                            <th>Periode Tayang</th>
                            <th>Status</th>
                            <th class="text-center" style="width:140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="bannerTableBody">
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

<!-- Modal Form -->
<div class="modal fade" id="bannerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bannerForm">
                <div class="modal-header">
                    <h6 class="modal-title" id="bannerModalTitle"><i class="bi bi-image me-2"></i>Tambah Banner</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_banner" id="id_banner">
                    <div class="mb-2">
                        <label class="form-label">Judul Banner</label>
                        <input type="text" class="form-control" name="e_banner_title" id="e_banner_title" required maxlength="150">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">URL Gambar</label>
                        <input type="text" class="form-control" name="e_image_url" id="e_image_url" required placeholder="https://...">
                        <div class="form-text">Upload gambar ke hosting/CDN dulu, lalu tempel URL-nya di sini.</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Link Tujuan (opsional)</label>
                        <input type="text" class="form-control" name="e_link_url" id="e_link_url" placeholder="/product atau /promo/DISKON10">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="form-label">Tgl Mulai Tayang</label>
                            <input type="date" class="form-control" name="dt_start" id="dt_start">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label">Tgl Selesai Tayang</label>
                            <input type="date" class="form-control" name="dt_end" id="dt_end">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Urutan Tampil</label>
                        <input type="number" class="form-control" name="n_sort_order" id="n_sort_order" value="0" min="0">
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
    var BASE_URL = '<?= site_url('banner'); ?>/';

    function escapeHtml(str) { return $('<div>').text(str == null ? '' : str).html(); }
    function showAlert(message, type) {
        $('#alertPlaceholder').html(
            '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' + message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'
        );
    }

    function loadBanners() {
        $('#tableLoading').show();
        $.ajax({ url: BASE_URL + 'list_data', method: 'POST', dataType: 'json' })
            .done(function (res) { renderTable((res && res.data) || []); })
            .fail(function () { $('#bannerTableBody').html('<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat data.</td></tr>'); })
            .always(function () { $('#tableLoading').hide(); });
    }

    function renderTable(rows) {
        var $body = $('#bannerTableBody');
        $body.empty();
        if (rows.length === 0) {
            $body.append('<tr><td colspan="8" class="text-center text-muted py-4">Belum ada banner.</td></tr>');
            return;
        }
        rows.forEach(function (row, idx) {
            var tr = $('<tr>').toggleClass('row-inactive', row.f_active !== 't');
            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td><img class="banner-thumb" src="' + escapeHtml(row.e_image_url) + '" onerror="this.style.opacity=0.3"></td>');
            tr.append('<td>' + escapeHtml(row.e_banner_title) + '</td>');
            tr.append('<td class="small text-truncate" style="max-width:180px;">' + escapeHtml(row.e_link_url || '-') + '</td>');
            tr.append('<td class="text-center">' + row.n_sort_order + '</td>');
            tr.append('<td class="small">' + (row.dt_start || '-') + ' s/d ' + (row.dt_end || '-') + '</td>');
            tr.append('<td>' + (row.f_active === 't'
                ? '<span class="badge bg-success">Tayang</span>'
                : '<span class="badge bg-secondary">Disembunyikan</span>') + '</td>');

            var actionTd = $('<td class="text-center">');
            var group = $('<div class="d-inline-flex gap-1">');
            var editBtn = $('<button type="button" class="btn btn-sm btn-outline-primary btn-icon" title="Edit"><i class="bi bi-pencil"></i></button>');
            editBtn.on('click', function () { openEdit(row); });
            var toggleBtn = $('<button type="button" class="btn btn-sm ' + (row.f_active === 't' ? 'btn-outline-secondary' : 'btn-outline-success') + ' btn-icon" title="' + (row.f_active === 't' ? 'Sembunyikan' : 'Tayangkan') + '"><i class="bi ' + (row.f_active === 't' ? 'bi-eye-slash' : 'bi-eye') + '"></i></button>');
            toggleBtn.on('click', function () { toggleActive(row.id_banner); });
            var deleteBtn = $('<button type="button" class="btn btn-sm btn-outline-danger btn-icon" title="Hapus"><i class="bi bi-trash"></i></button>');
            deleteBtn.on('click', function () { deleteBanner(row.id_banner); });
            group.append(editBtn).append(toggleBtn).append(deleteBtn);
            actionTd.append(group);
            tr.append(actionTd);

            $body.append(tr);
        });
    }

    $('#btnAdd').on('click', function () {
        $('#bannerModalTitle').html('<i class="bi bi-image me-2"></i>Tambah Banner');
        $('#bannerForm')[0].reset();
        $('#id_banner').val('');
    });

    function openEdit(row) {
        $('#bannerModalTitle').html('<i class="bi bi-pencil me-2"></i>Edit Banner');
        $('#id_banner').val(row.id_banner);
        $('#e_banner_title').val(row.e_banner_title);
        $('#e_image_url').val(row.e_image_url);
        $('#e_link_url').val(row.e_link_url);
        $('#dt_start').val(row.dt_start);
        $('#dt_end').val(row.dt_end);
        $('#n_sort_order').val(row.n_sort_order);
        new bootstrap.Modal('#bannerModal').show();
    }

    $('#bannerForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({ url: BASE_URL + 'save', method: 'POST', dataType: 'json', data: $(this).serialize() })
            .done(function (res) {
                bootstrap.Modal.getInstance(document.getElementById('bannerModal')).hide();
                showAlert(res.message, 'success');
                loadBanners();
            })
            .fail(function (xhr) { showAlert((xhr.responseJSON || {}).message || 'Gagal menyimpan banner.', 'danger'); });
    });

    function toggleActive(id) {
        $.ajax({ url: BASE_URL + 'toggle_active/' + id, method: 'POST', dataType: 'json' })
            .done(function (res) { showAlert(res.message, 'success'); loadBanners(); })
            .fail(function () { showAlert('Gagal mengubah status.', 'danger'); });
    }

    function deleteBanner(id) {
        if (!confirm('Hapus banner ini?')) return;
        $.ajax({ url: BASE_URL + 'delete/' + id, method: 'POST', dataType: 'json' })
            .done(function (res) { showAlert(res.message, 'success'); loadBanners(); })
            .fail(function () { showAlert('Gagal menghapus banner.', 'danger'); });
    }

    loadBanners();
});
</script>
</body>
</html>
