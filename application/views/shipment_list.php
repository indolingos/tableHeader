<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bukti Barang Terkirim</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    #tableLoading { display: none; }
    .badge-user { background-color: #0dcaf0; color: #212529; font-weight: 600; }
    .btn-icon { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'shipment', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div class="card">
        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-start mb-1 gap-2">
                <div>
                    <h5 class="mb-1"><i class="bi bi-box-seam-fill me-2"></i>Bukti Barang Terkirim</h5>
                    <p class="text-muted small mb-0">Keterangan: daftar pengiriman barang ke konsumen beserta nomor resi dan status. Klik ikon mata untuk melihat rincian barang yang dikirim dan mengunggah/ubah bukti kirim.</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#addShipModal">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Pengiriman
                </button>
            </div>

            <div id="alertPlaceholder" class="mt-2"></div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Invoice</th>
                            <th>Konsumen</th>
                            <th>Kurir</th>
                            <th>No Resi</th>
                            <th>Status</th>
                            <th>Tgl Kirim</th>
                            <th class="text-center" style="width:80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
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

<!-- Modal: Tambah Pengiriman -->
<div class="modal fade" id="addShipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Tambah Pengiriman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="addShipAlert"></div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Transaksi</label>
                        <select class="form-select" id="shipTrx">
                            <option value="">-- Pilih Transaksi --</option>
                            <?php foreach ($transactions as $t): ?>
                            <option value="<?= (int) $t['id_transaction']; ?>">
                                <?= htmlspecialchars($t['i_invoice']); ?> - <?= htmlspecialchars($t['i_username']); ?> (<?= htmlspecialchars($t['e_status']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Hanya menampilkan transaksi yang belum dibatalkan.</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Kurir</label>
                        <select class="form-select" id="shipCourier">
                            <option value="">-- Pilih Kurir --</option>
                            <?php foreach ($couriers as $c): ?>
                            <option value="<?= (int) $c['id_courier']; ?>"><?= htmlspecialchars($c['e_courier_name']); ?> (<?= htmlspecialchars($c['e_courier_code']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">No Resi</label>
                        <input type="text" class="form-control" id="shipResi" placeholder="mis. JNE1234567890">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Status Pengiriman</label>
                        <select class="form-select" id="shipStatus">
                            <option value="Dikemas" selected>Dikemas</option>
                            <option value="Dikirim">Dikirim</option>
                            <option value="Diterima">Diterima</option>
                            <option value="Retur">Retur</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <hr>
                        <label class="form-label small fw-semibold">Barang yang Dikirim</label>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:36px;"></th>
                                        <th>Produk</th>
                                        <th style="width:110px;">Qty Dikirim</th>
                                    </tr>
                                </thead>
                                <tbody id="shipItemsBody">
                                    <tr id="shipItemsPlaceholder"><td colspan="3" class="text-center text-muted py-3 small">Pilih transaksi terlebih dahulu untuk memuat barang.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12">
                        <hr>
                        <label class="form-label small fw-semibold">Keterangan (opsional)</label>
                        <textarea class="form-control" id="shipNote" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveShip"><i class="bi bi-check2 me-1"></i>Simpan Pengiriman</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function () {
    var BASE_URL = '<?= site_url('shipment'); ?>/';

    function escapeHtml(str) { return $('<div>').text(str == null ? '' : str).html(); }
    function formatDate(str) {
        if (!str) return '-';
        var d = new Date(str.replace(' ', 'T'));
        if (isNaN(d.getTime())) return str;
        return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
    function statusBadge(status) {
        var cls = { 'Dikemas': 'bg-secondary', 'Dikirim': 'bg-warning text-dark', 'Diterima': 'bg-success', 'Retur': 'bg-danger' };
        return '<span class="badge ' + (cls[status] || 'bg-secondary') + '">' + escapeHtml(status) + '</span>';
    }

    function load() {
        $('#tableLoading').show();
        $.ajax({ url: BASE_URL + 'list_data', method: 'POST', dataType: 'json' })
            .done(function (res) { render((res && res.data) || []); })
            .fail(function () { $('#tableBody').html('<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat data.</td></tr>'); })
            .always(function () { $('#tableLoading').hide(); });
    }

    function render(rows) {
        var $body = $('#tableBody');
        $body.empty();
        if (rows.length === 0) {
            $body.append('<tr><td colspan="8" class="text-center text-muted py-4">Belum ada data pengiriman.</td></tr>');
            return;
        }
        rows.forEach(function (row, idx) {
            var tr = $('<tr>');
            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td class="fw-semibold">' + escapeHtml(row.i_invoice) + '</td>');
            tr.append('<td><span class="badge badge-user"><i class="bi bi-person-fill me-1"></i>' + escapeHtml(row.i_username) + '</span></td>');
            tr.append('<td>' + escapeHtml(row.e_courier_name) + ' <span class="badge bg-light text-dark border">' + escapeHtml(row.e_courier_code) + '</span></td>');
            tr.append('<td>' + escapeHtml(row.i_resi) + '</td>');
            tr.append('<td>' + statusBadge(row.e_status_kirim) + '</td>');
            tr.append('<td>' + formatDate(row.dt_kirim) + '</td>');

            var actionTd = $('<td class="text-center">');
            var viewBtn = $('<a class="btn btn-sm btn-outline-primary btn-icon" title="Lihat detail"><i class="bi bi-eye"></i></a>');
            viewBtn.attr('href', BASE_URL + 'detail/' + row.id_shipment).attr('target', '_blank');
            actionTd.append(viewBtn);
            tr.append(actionTd);

            $body.append(tr);
        });
    }

    load();

    // ---------------------------------------------------------------
    // Modal: Tambah Pengiriman
    // ---------------------------------------------------------------
    function showAlert(message, type) {
        $('#alertPlaceholder').html('<div class="alert alert-' + type + ' alert-dismissible fade show">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    }

    $('#shipTrx').on('change', function () {
        var idTrx = $(this).val();
        var $body = $('#shipItemsBody');

        if (!idTrx) {
            $body.html('<tr><td colspan="3" class="text-center text-muted py-3 small">Pilih transaksi terlebih dahulu untuk memuat barang.</td></tr>');
            return;
        }

        $body.html('<tr><td colspan="3" class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></td></tr>');

        $.ajax({ url: BASE_URL + 'transaction_items/' + idTrx, method: 'POST', dataType: 'json' })
            .done(function (res) {
                var rows = (res && res.data) || [];
                if (rows.length === 0) {
                    $body.html('<tr><td colspan="3" class="text-center text-muted py-3 small">Transaksi ini belum punya barang.</td></tr>');
                    return;
                }
                $body.empty();
                rows.forEach(function (row) {
                    var tr = $('<tr>');
                    tr.append('<td class="text-center"><input type="checkbox" class="form-check-input ship-item-check" checked data-id-product="' + row.id_product + '"></td>');
                    tr.append('<td>' + escapeHtml(row.i_product) + ' - ' + escapeHtml(row.e_product) + '</td>');
                    tr.append('<td><input type="number" class="form-control form-control-sm ship-item-qty" value="' + row.n_qty + '" min="1" max="' + row.n_qty + '"></td>');
                    $body.append(tr);
                });
            })
            .fail(function () {
                $body.html('<tr><td colspan="3" class="text-center text-danger py-3 small">Gagal memuat barang transaksi.</td></tr>');
            });
    });

    $('#addShipModal').on('show.bs.modal', function () {
        $('#addShipAlert').empty();
        $('#shipCourier').val('');
        $('#shipResi').val('');
        $('#shipStatus').val('Dikemas');
        $('#shipNote').val('');
        $('#shipItemsBody').html('<tr id="shipItemsPlaceholder"><td colspan="3" class="text-center text-muted py-3 small">Pilih transaksi terlebih dahulu untuk memuat barang.</td></tr>');

        var presetTrx = $(this).data('preset-trx');
        if (presetTrx) {
            $('#shipTrx').val(presetTrx).trigger('change');
            $(this).removeData('preset-trx');
        } else {
            $('#shipTrx').val('');
        }
    });

    // Dibuka lewat link dari halaman detail transaksi: ?id_transaction=123
    var presetIdTrx = new URLSearchParams(window.location.search).get('id_transaction');
    if (presetIdTrx) {
        $('#addShipModal').data('preset-trx', presetIdTrx);
        var modal = new bootstrap.Modal(document.getElementById('addShipModal'));
        modal.show();
    }

    $('#btnSaveShip').on('click', function () {
        var idTrx = $('#shipTrx').val();
        var idCourier = $('#shipCourier').val();
        var resi = $('#shipResi').val().trim();

        if (!idTrx || !idCourier || !resi) {
            $('#addShipAlert').html('<div class="alert alert-danger py-2">Transaksi, kurir, dan no resi wajib diisi.</div>');
            return;
        }

        var items = [];
        $('#shipItemsBody tr').each(function () {
            var $tr = $(this);
            var $check = $tr.find('.ship-item-check');
            if ($check.length && $check.is(':checked')) {
                var qty = parseInt($tr.find('.ship-item-qty').val(), 10) || 0;
                if (qty > 0) {
                    items.push({ id_product: $check.data('id-product'), qty: qty });
                }
            }
        });

        if (items.length === 0) {
            $('#addShipAlert').html('<div class="alert alert-danger py-2">Pilih minimal 1 barang yang dikirim.</div>');
            return;
        }

        var $btn = $('#btnSaveShip');
        $btn.prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'create',
            method: 'POST',
            dataType: 'json',
            data: {
                id_transaction: idTrx,
                id_courier: idCourier,
                i_resi: resi,
                e_status_kirim: $('#shipStatus').val(),
                e_keterangan: $('#shipNote').val(),
                items: items
            }
        }).done(function () {
            $('#addShipModal').modal('hide');
            showAlert('Pengiriman berhasil dibuat.', 'success');
            load();
        }).fail(function (xhr) {
            $('#addShipAlert').html('<div class="alert alert-danger py-2">' + ((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan pengiriman.') + '</div>');
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });
});
</script>
</body>
</html>
