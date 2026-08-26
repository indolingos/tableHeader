<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bukti Kirim - <?= htmlspecialchars($shipment['i_resi']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    .info-label { font-size: .75rem; color: #6c757d; text-transform: uppercase; letter-spacing: .03em; }
    .info-value { font-weight: 600; }
    #itemsLoading { display: none; }
    .bukti-preview { max-width: 260px; border-radius: .5rem; border: 1px solid #dee2e6; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'shipment', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?= site_url('shipment'); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Bukti Barang Terkirim
        </a>
        <a href="<?= site_url('transaction/detail/' . (int) $shipment['id_transaction']); ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-receipt me-1"></i>Lihat Transaksi <?= htmlspecialchars($shipment['i_invoice']); ?>
        </a>
    </div>

    <div id="alertPlaceholder"></div>

    <!-- Header pengiriman -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Pengiriman</h6>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="info-label">No Invoice</div>
                    <div class="info-value"><?= htmlspecialchars($shipment['i_invoice']); ?></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-label">Konsumen</div>
                    <div class="info-value"><?= htmlspecialchars($shipment['i_username']); ?></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-label">Kurir / Distributor</div>
                    <div class="info-value"><?= htmlspecialchars($shipment['e_courier_name']); ?> (<?= htmlspecialchars($shipment['e_courier_code']); ?>)</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-label">No Resi</div>
                    <div class="info-value"><?= htmlspecialchars($shipment['i_resi']); ?></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-label">Status Saat Ini</div>
                    <div class="info-value"><?= htmlspecialchars($shipment['e_status_kirim']); ?></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-label">Tgl Dikirim</div>
                    <div class="info-value"><?= htmlspecialchars($shipment['dt_kirim'] ?: '-'); ?></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-label">Tgl Diterima</div>
                    <div class="info-value"><?= htmlspecialchars($shipment['dt_diterima'] ?: '-'); ?></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-label">Kontak Kurir</div>
                    <div class="info-value"><?= htmlspecialchars($shipment['courier_phone'] ?: '-'); ?></div>
                </div>
                <?php if (!empty($shipment['e_bukti_foto_url'])): ?>
                <div class="col-12">
                    <div class="info-label mb-1">Bukti Foto Pengiriman</div>
                    <img src="<?= htmlspecialchars($shipment['e_bukti_foto_url']); ?>" class="bukti-preview" alt="Bukti kirim">
                </div>
                <?php endif; ?>
                <?php if (!empty($shipment['e_keterangan'])): ?>
                <div class="col-12">
                    <div class="info-label">Keterangan</div>
                    <div class="fw-normal"><?= nl2br(htmlspecialchars($shipment['e_keterangan'])); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Nested table: barang yang dikirim -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="mb-1"><i class="bi bi-box-seam me-2"></i>Barang yang Dikirim</h6>
            <p class="text-muted small">Keterangan: daftar produk beserta jumlah (qty) yang termasuk dalam pengiriman ini.</p>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th class="text-end">Qty</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr id="itemsLoading"><td colspan="4" class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Update status / bukti kirim -->
    <div class="card">
        <div class="card-body">
            <h6 class="mb-3"><i class="bi bi-pencil-square me-2"></i>Update Status &amp; Bukti Kirim</h6>
            <form id="statusForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status Pengiriman</label>
                    <select class="form-select" name="e_status_kirim" id="e_status_kirim">
                        <?php foreach ($statuses as $s): ?>
                        <option value="<?= htmlspecialchars($s); ?>" <?= $s === $shipment['e_status_kirim'] ? 'selected' : ''; ?>><?= htmlspecialchars($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">URL Foto Bukti Kirim (opsional)</label>
                    <input type="text" class="form-control" name="e_bukti_foto_url" id="e_bukti_foto_url" placeholder="https://..." value="<?= htmlspecialchars($shipment['e_bukti_foto_url'] ?: ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Keterangan</label>
                    <input type="text" class="form-control" name="e_keterangan" id="e_keterangan" value="<?= htmlspecialchars($shipment['e_keterangan'] ?: ''); ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check2 me-1"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function () {
    var BASE_URL = '<?= site_url('shipment'); ?>/';
    var ID_SHIPMENT = <?= (int) $id_shipment; ?>;

    function escapeHtml(str) { return $('<div>').text(str == null ? '' : str).html(); }
    function showAlert(message, type) {
        $('#alertPlaceholder').html('<div class="alert alert-' + type + ' alert-dismissible fade show">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    }

    $.ajax({ url: BASE_URL + 'detail_items/' + ID_SHIPMENT, method: 'POST', dataType: 'json' })
        .done(function (res) { renderItems((res && res.data) || []); })
        .fail(function () { $('#itemsBody').html('<tr><td colspan="4" class="text-center text-danger py-3">Gagal memuat data barang.</td></tr>'); });

    function renderItems(rows) {
        var $body = $('#itemsBody');
        $body.empty();
        if (rows.length === 0) {
            $body.append('<tr><td colspan="4" class="text-center text-muted py-3">Belum ada barang tercatat pada pengiriman ini.</td></tr>');
            return;
        }
        rows.forEach(function (row) {
            var tr = $('<tr>');
            tr.append('<td>' + escapeHtml(row.i_product) + '</td>');
            tr.append('<td>' + escapeHtml(row.e_product) + '</td>');
            tr.append('<td class="text-end fw-semibold">' + row.n_qty + '</td>');
            tr.append('<td class="text-muted small">' + escapeHtml(row.e_keterangan) + '</td>');
            $body.append(tr);
        });
    }

    $('#statusForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({ url: BASE_URL + 'update_status/' + ID_SHIPMENT, method: 'POST', dataType: 'json', data: $(this).serialize() })
            .done(function (res) { showAlert(res.message, 'success'); })
            .fail(function (xhr) { showAlert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memperbarui data.', 'danger'); });
    });
});
</script>
</body>
</html>
