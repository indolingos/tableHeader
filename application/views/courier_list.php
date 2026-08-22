<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Distributor / Petugas Ongkir</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    #tableLoading { display: none; }
    .badge-code { background-color: #dc3545; font-weight: 600; }
    .btn-icon { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
    tr.row-inactive { color: #adb5bd; background-color: #f8f9fa; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'courier', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div id="alertPlaceholder"></div>

    <div class="card">
        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <h5 class="mb-0"><i class="bi bi-signpost-split me-2"></i>Daftar Distributor / Petugas Ongkir</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#courierModal" id="btnAdd">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Kurir
                </button>
            </div>
            <p class="text-muted small">Data mitra pengiriman (JNE, JNT, SiCepat, kurir internal, dsb) yang menangani pengiriman barang toko.</p>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Nama Kurir/Distributor</th>
                            <th>Kode</th>
                            <th>Kontak</th>
                            <th>Area Layanan</th>
                            <th class="text-end">Total Pengiriman</th>
                            <th>Status</th>
                            <th class="text-center" style="width:140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="courierTableBody">
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
<div class="modal fade" id="courierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="courierForm">
                <div class="modal-header">
                    <h6 class="modal-title" id="courierModalTitle"><i class="bi bi-signpost-split me-2"></i>Tambah Kurir/Distributor</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_courier" id="id_courier">
                    <div class="mb-2">
                        <label class="form-label">Nama Kurir/Distributor</label>
                        <input type="text" class="form-control" name="e_courier_name" id="e_courier_name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Kode Kurir (mis. JNE, JNT)</label>
                        <input type="text" class="form-control" name="e_courier_code" id="e_courier_code" required maxlength="20">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Kontak Person</label>
                        <input type="text" class="form-control" name="e_contact_person" id="e_contact_person">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">No Telepon</label>
                        <input type="text" class="form-control" name="i_phone" id="i_phone">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Area Layanan</label>
                        <input type="text" class="form-control" name="e_coverage_area" id="e_coverage_area" placeholder="mis. Jabodetabek, Se-Indonesia">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="e_keterangan" id="e_keterangan" rows="2" placeholder="Catatan tambahan tentang kurir ini"></textarea>
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

<!-- Modal Nested Table: histori pengiriman -->
<div class="modal fade" id="shipmentsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"><i class="bi bi-box-seam-fill me-2"></i>Histori Pengiriman &mdash; <span id="shipmentsCourierName"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Keterangan: daftar seluruh pengiriman yang pernah ditangani oleh kurir/distributor ini.</p>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No Invoice</th>
                                <th>Konsumen</th>
                                <th>No Resi</th>
                                <th>Status</th>
                                <th>Tgl Kirim</th>
                            </tr>
                        </thead>
                        <tbody id="shipmentsTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function () {
    var BASE_URL = '<?= site_url('courier'); ?>/';

    function escapeHtml(str) { return $('<div>').text(str == null ? '' : str).html(); }
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

    function loadCouriers() {
        $('#tableLoading').show();
        $.ajax({ url: BASE_URL + 'list_data', method: 'POST', dataType: 'json' })
            .done(function (res) { renderTable((res && res.data) || []); })
            .fail(function () { $('#courierTableBody').html('<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat data.</td></tr>'); })
            .always(function () { $('#tableLoading').hide(); });
    }

    function renderTable(rows) {
        var $body = $('#courierTableBody');
        $body.empty();
        if (rows.length === 0) {
            $body.append('<tr><td colspan="8" class="text-center text-muted py-4">Belum ada data kurir/distributor.</td></tr>');
            return;
        }
        rows.forEach(function (row, idx) {
            var tr = $('<tr>').toggleClass('row-inactive', row.f_active !== 't');
            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td>' + escapeHtml(row.e_courier_name) + (row.e_keterangan ? '<div class="text-muted small">' + escapeHtml(row.e_keterangan) + '</div>' : '') + '</td>');
            tr.append('<td><span class="badge badge-code">' + escapeHtml(row.e_courier_code) + '</span></td>');
            tr.append('<td>' + escapeHtml(row.e_contact_person) + (row.i_phone ? '<div class="text-muted small">' + escapeHtml(row.i_phone) + '</div>' : '') + '</td>');
            tr.append('<td>' + escapeHtml(row.e_coverage_area) + '</td>');

            var shipTd = $('<td class="text-end">');
            var shipBtn = $('<a href="#" class="fw-semibold text-decoration-none">' + row.n_shipments + '</a>');
            shipBtn.on('click', function (e) { e.preventDefault(); openShipments(row.id_courier, row.e_courier_name); });
            shipTd.append(shipBtn);
            tr.append(shipTd);

            tr.append('<td>' + (row.f_active === 't'
                ? '<span class="badge bg-success">Aktif</span>'
                : '<span class="badge bg-secondary">Nonaktif</span>') + '</td>');

            var actionTd = $('<td class="text-center">');
            var group = $('<div class="d-inline-flex gap-1">');
            var editBtn = $('<button type="button" class="btn btn-sm btn-outline-primary btn-icon" title="Edit"><i class="bi bi-pencil"></i></button>');
            editBtn.on('click', function () { openEdit(row); });
            var toggleBtn = $('<button type="button" class="btn btn-sm ' + (row.f_active === 't' ? 'btn-outline-danger' : 'btn-outline-success') + ' btn-icon" title="' + (row.f_active === 't' ? 'Nonaktifkan' : 'Aktifkan') + '"><i class="bi ' + (row.f_active === 't' ? 'bi-slash-circle' : 'bi-check-circle') + '"></i></button>');
            toggleBtn.on('click', function () { toggleActive(row.id_courier); });
            group.append(editBtn).append(toggleBtn);
            actionTd.append(group);
            tr.append(actionTd);

            $body.append(tr);
        });
    }

    function openShipments(id, name) {
        $('#shipmentsCourierName').text(name);
        $('#shipmentsTableBody').html('<tr><td colspan="5" class="text-center text-muted py-3"><div class="spinner-border spinner-border-sm me-2"></div>Memuat...</td></tr>');
        var modal = new bootstrap.Modal('#shipmentsModal');
        modal.show();
        $.ajax({ url: BASE_URL + 'shipments/' + id, method: 'POST', dataType: 'json' }).done(function (res) {
            var rows = (res && res.data) || [];
            var $b = $('#shipmentsTableBody');
            $b.empty();
            if (rows.length === 0) {
                $b.append('<tr><td colspan="5" class="text-center text-muted py-3">Belum ada pengiriman.</td></tr>');
                return;
            }
            rows.forEach(function (r) {
                var tr = $('<tr>');
                tr.append('<td>' + escapeHtml(r.i_invoice) + '</td>');
                tr.append('<td>' + escapeHtml(r.i_username) + '</td>');
                tr.append('<td>' + escapeHtml(r.i_resi) + '</td>');
                tr.append('<td><span class="badge bg-info-subtle text-info">' + escapeHtml(r.e_status_kirim) + '</span></td>');
                tr.append('<td>' + formatDate(r.dt_kirim) + '</td>');
                $b.append(tr);
            });
        });
    }

    function openAdd() {
        $('#courierModalTitle').html('<i class="bi bi-signpost-split me-2"></i>Tambah Kurir/Distributor');
        $('#courierForm')[0].reset();
        $('#id_courier').val('');
    }
    $('#btnAdd').on('click', openAdd);

    function openEdit(row) {
        $('#courierModalTitle').html('<i class="bi bi-pencil me-2"></i>Edit Kurir/Distributor');
        $('#id_courier').val(row.id_courier);
        $('#e_courier_name').val(row.e_courier_name);
        $('#e_courier_code').val(row.e_courier_code);
        $('#e_contact_person').val(row.e_contact_person);
        $('#i_phone').val(row.i_phone);
        $('#e_coverage_area').val(row.e_coverage_area);
        $('#e_keterangan').val(row.e_keterangan);
        new bootstrap.Modal('#courierModal').show();
    }

    $('#courierForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({ url: BASE_URL + 'save', method: 'POST', dataType: 'json', data: $(this).serialize() })
            .done(function (res) {
                bootstrap.Modal.getInstance(document.getElementById('courierModal')).hide();
                showAlert(res.message, 'success');
                loadCouriers();
            })
            .fail(function (xhr) {
                var res = xhr.responseJSON || {};
                showAlert(res.message || 'Gagal menyimpan data.', 'danger');
            });
    });

    function toggleActive(id) {
        $.ajax({ url: BASE_URL + 'toggle_active/' + id, method: 'POST', dataType: 'json' })
            .done(function (res) { showAlert(res.message, 'success'); loadCouriers(); })
            .fail(function () { showAlert('Gagal mengubah status.', 'danger'); });
    }

    loadCouriers();
});
</script>
</body>
</html>
