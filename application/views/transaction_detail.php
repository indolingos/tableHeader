<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Transaksi - <?= htmlspecialchars($trx['i_invoice']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    .info-label { font-size: .75rem; color: #6c757d; text-transform: uppercase; letter-spacing: .03em; }
    .info-value { font-weight: 600; }
    #itemsLoading, #shipLoading { display: none; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'transaction', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?= site_url('transaction'); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Transaksi
        </a>
        <div class="d-flex align-items-center gap-2">
            <label class="small text-muted mb-0">Ubah Status:</label>
            <select class="form-select form-select-sm" id="statusSelect" style="width: auto;">
                <?php foreach ($statuses as $s): ?>
                <option value="<?= htmlspecialchars($s); ?>" <?= $s === $trx['e_status'] ? 'selected' : ''; ?>><?= htmlspecialchars($s); ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary btn-sm" id="btnUpdateStatus"><i class="bi bi-check2 me-1"></i>Simpan</button>
        </div>
    </div>

    <div id="alertPlaceholder"></div>

    <!-- Header transaksi -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Transaksi</h6>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="info-label">No Invoice</div>
                    <div class="info-value"><?= htmlspecialchars($trx['i_invoice']); ?></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-label">Konsumen</div>
                    <div class="info-value"><?= htmlspecialchars($trx['i_username']); ?></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-label">Status</div>
                    <div class="info-value"><?= htmlspecialchars($trx['e_status']); ?></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-label">Tanggal Transaksi</div>
                    <div class="info-value"><?= htmlspecialchars($trx['dt_created']); ?></div>
                </div>

                <div class="col-12"><hr class="my-1"></div>

                <div class="col-12 col-md-6">
                    <div class="info-label">Alamat Pengiriman</div>
                    <div class="info-value">
                        <?php if (!empty($trx['e_recipient'])): ?>
                            <?= htmlspecialchars($trx['e_recipient']); ?> (<?= htmlspecialchars($trx['i_phone']); ?>)<br>
                            <span class="fw-normal"><?= htmlspecialchars($trx['e_address_full']); ?>, <?= htmlspecialchars($trx['e_city']); ?>, <?= htmlspecialchars($trx['e_province']); ?></span>
                        <?php else: ?>
                            <span class="text-muted fw-normal">Belum ada alamat terpilih</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="info-label">Rekening Pembayaran</div>
                    <div class="info-value">
                        <?php if (!empty($trx['e_bank_name'])): ?>
                            <?= htmlspecialchars($trx['e_bank_name']); ?> &mdash; <?= htmlspecialchars($trx['i_account_number']); ?><br>
                            <span class="fw-normal">a.n. <?= htmlspecialchars($trx['e_account_holder']); ?></span>
                        <?php else: ?>
                            <span class="text-muted fw-normal">Belum ada rekening terpilih</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-12"><hr class="my-1"></div>

                <div class="col-6 col-md-3">
                    <div class="info-label">Subtotal</div>
                    <div class="info-value" id="hdrSubtotal"></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-label">Pajak <?php if (!empty($trx['e_tax_name'])): ?>(<?= htmlspecialchars($trx['e_tax_name']); ?> <?= htmlspecialchars($trx['n_percentage']); ?>%)<?php endif; ?></div>
                    <div class="info-value" id="hdrTax"></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-label">Ongkos Kirim</div>
                    <div class="info-value" id="hdrShipping"></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-label">Total</div>
                    <div class="info-value text-primary" id="hdrTotal"></div>
                </div>

                <?php if (!empty($trx['e_keterangan'])): ?>
                <div class="col-12">
                    <div class="info-label">Keterangan</div>
                    <div class="fw-normal"><?= nl2br(htmlspecialchars($trx['e_keterangan'])); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Nested table 1: barang yang dibeli & qty -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="mb-1"><i class="bi bi-box-seam me-2"></i>Barang yang Dibeli</h6>
            <p class="text-muted small">Keterangan: daftar produk beserta jumlah (qty) dan harga satuan saat transaksi dibuat.</p>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Subtotal</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr id="itemsLoading"><td colspan="7" class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Nested table 2: histori pengiriman -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                <div>
                    <h6 class="mb-1"><i class="bi bi-truck me-2"></i>Histori Pengiriman</h6>
                    <p class="text-muted small mb-0">Keterangan: satu transaksi bisa memiliki lebih dari satu pengiriman jika barang dikirim bertahap.</p>
                </div>
                <a class="btn btn-primary btn-sm text-nowrap" id="btnAddShipment" target="_blank">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Pengiriman
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kurir</th>
                            <th>No Resi</th>
                            <th>Status</th>
                            <th>Tgl Kirim</th>
                            <th>Tgl Diterima</th>
                            <th class="text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody id="shipBody">
                        <tr id="shipLoading"><td colspan="6" class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function () {
    var BASE_URL = '<?= site_url('transaction'); ?>/';
    var ID_TRX   = <?= (int) $id_transaction; ?>;

    function escapeHtml(str) { return $('<div>').text(str == null ? '' : str).html(); }
    function formatRupiah(num) { return 'Rp ' + Number(num || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 }); }
    function formatDate(str) {
        if (!str) return '-';
        var d = new Date(str.replace(' ', 'T'));
        if (isNaN(d.getTime())) return str;
        return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
    function showAlert(message, type) {
        $('#alertPlaceholder').html('<div class="alert alert-' + type + ' alert-dismissible fade show">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    }

    $('#btnAddShipment').attr('href', '<?= site_url('shipment'); ?>?id_transaction=' + ID_TRX);

    $('#hdrSubtotal').text(formatRupiah(<?= (float) $trx['v_subtotal']; ?>));
    $('#hdrTax').text(formatRupiah(<?= (float) $trx['v_tax']; ?>));
    $('#hdrShipping').text(formatRupiah(<?= (float) $trx['v_shipping_cost']; ?>));
    $('#hdrTotal').text(formatRupiah(<?= (float) $trx['v_total']; ?>));

    $.ajax({ url: BASE_URL + 'detail_items/' + ID_TRX, method: 'POST', dataType: 'json' })
        .done(function (res) {
            var data = (res && res.data) || { items: [], shipments: [] };
            renderItems(data.items || []);
            renderShipments(data.shipments || []);
        })
        .fail(function () {
            $('#itemsBody').html('<tr><td colspan="7" class="text-center text-danger py-3">Gagal memuat data barang.</td></tr>');
            $('#shipBody').html('<tr><td colspan="6" class="text-center text-danger py-3">Gagal memuat data pengiriman.</td></tr>');
        });

    function renderItems(rows) {
        var $body = $('#itemsBody');
        $body.empty();
        if (rows.length === 0) {
            $body.append('<tr><td colspan="7" class="text-center text-muted py-3">Belum ada barang pada transaksi ini.</td></tr>');
            return;
        }
        rows.forEach(function (row) {
            var tr = $('<tr>');
            tr.append('<td>' + escapeHtml(row.i_product) + '</td>');
            tr.append('<td>' + escapeHtml(row.e_product) + '</td>');
            tr.append('<td>' + escapeHtml(row.e_category) + '</td>');
            tr.append('<td class="text-end">' + formatRupiah(row.v_price) + '</td>');
            tr.append('<td class="text-end fw-semibold">' + row.n_qty + '</td>');
            tr.append('<td class="text-end">' + formatRupiah(row.v_subtotal) + '</td>');
            tr.append('<td class="text-muted small">' + escapeHtml(row.e_keterangan) + '</td>');
            $body.append(tr);
        });
    }

    function renderShipments(rows) {
        var $body = $('#shipBody');
        $body.empty();
        if (rows.length === 0) {
            $body.append('<tr><td colspan="6" class="text-center text-muted py-3">Belum ada pengiriman untuk transaksi ini.</td></tr>');
            return;
        }
        rows.forEach(function (row) {
            var tr = $('<tr>');
            tr.append('<td>' + escapeHtml(row.e_courier_name) + ' <span class="badge bg-light text-dark border">' + escapeHtml(row.e_courier_code) + '</span></td>');
            tr.append('<td>' + escapeHtml(row.i_resi) + '</td>');
            tr.append('<td><span class="badge bg-info-subtle text-info">' + escapeHtml(row.e_status_kirim) + '</span></td>');
            tr.append('<td>' + formatDate(row.dt_kirim) + '</td>');
            tr.append('<td>' + formatDate(row.dt_diterima) + '</td>');
            var td = $('<td class="text-center">');
            var a = $('<a class="btn btn-sm btn-outline-primary" title="Lihat bukti kirim"><i class="bi bi-eye"></i></a>');
            a.attr('href', '<?= site_url('shipment/detail/'); ?>' + row.id_shipment).attr('target', '_blank');
            td.append(a);
            tr.append(td);
            $body.append(tr);
        });
    }

    $('#btnUpdateStatus').on('click', function () {
        $.ajax({
            url: BASE_URL + 'update_status/' + ID_TRX,
            method: 'POST',
            dataType: 'json',
            data: { e_status: $('#statusSelect').val() }
        }).done(function (res) {
            showAlert(res.message, 'success');
        }).fail(function (xhr) {
            showAlert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memperbarui status.', 'danger');
        });
    });
});
</script>
</body>
</html>
