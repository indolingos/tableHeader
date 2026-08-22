<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Transaksi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; }
    .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    #tableLoading { display: none; }
    .badge-user { background-color: #0d6efd; font-weight: 600; }
    .btn-icon { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
    .badge-status { font-weight: 600; }
</style>
</head>
<body>

<?php $this->load->view('partials/nav', array('active' => 'transaction', 'username' => $username)); ?>

<div class="container-fluid px-4 pb-5">

    <div id="alertPlaceholder"></div>

    <div class="card">
        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Daftar Transaksi</h5>
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm" id="statusFilter" style="max-width: 220px;">
                        <option value="">Semua Status</option>
                        <?php foreach ($statuses as $s): ?>
                        <option value="<?= htmlspecialchars($s); ?>"><?= htmlspecialchars($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTrxModal">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Transaksi
                    </button>
                </div>
            </div>
            <p class="text-muted small">Keterangan: setiap transaksi berisi satu atau lebih barang yang dibeli konsumen. Klik ikon mata untuk melihat rincian barang, qty, dan histori pengiriman.</p>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Invoice</th>
                            <th>Konsumen</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Pajak</th>
                            <th class="text-end">Ongkir</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th class="text-center" style="width:80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr id="tableLoading">
                            <td colspan="10" class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div>Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="text-muted small mt-2" id="summaryInfo"></div>

        </div>
    </div>
</div>

<!-- Modal: Tambah Transaksi -->
<div class="modal fade" id="addTrxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Tambah Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="addTrxAlert"></div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Konsumen</label>
                        <select class="form-select" id="trxUser">
                            <option value="">-- Pilih Konsumen --</option>
                            <?php foreach ($customers as $c): ?>
                            <option value="<?= (int) $c['id_user']; ?>"><?= htmlspecialchars($c['i_username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Status Awal</label>
                        <select class="form-select" id="trxStatus">
                            <?php foreach ($statuses as $s): ?>
                            <option value="<?= htmlspecialchars($s); ?>" <?= $s === 'Menunggu Pembayaran' ? 'selected' : ''; ?>><?= htmlspecialchars($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Alamat Pengiriman</label>
                        <select class="form-select" id="trxAddress" disabled>
                            <option value="">-- Pilih konsumen dulu --</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Rekening Pembayaran</label>
                        <select class="form-select" id="trxBankAccount" disabled>
                            <option value="">-- Pilih konsumen dulu --</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Pajak</label>
                        <select class="form-select" id="trxTax">
                            <option value="0">Tanpa Pajak</option>
                            <?php foreach ($taxes as $t): ?>
                            <option value="<?= (int) $t['id_tax']; ?>" <?= $t['e_tax_name'] === 'PPN' ? 'selected' : ''; ?>><?= htmlspecialchars($t['e_tax_name']); ?> (<?= htmlspecialchars($t['n_percentage']); ?>%)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Ongkos Kirim (Rp)</label>
                        <input type="number" class="form-control" id="trxShipping" value="0" min="0">
                    </div>

                    <div class="col-12">
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label small fw-semibold mb-0">Barang</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRow"><i class="bi bi-plus-lg me-1"></i>Tambah Baris</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th style="width:90px;">Qty</th>
                                        <th class="text-end" style="width:140px;">Subtotal</th>
                                        <th style="width:40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="trxItemsBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12">
                        <hr>
                        <label class="form-label small fw-semibold">Keterangan (opsional)</label>
                        <textarea class="form-control" id="trxNote" rows="2"></textarea>
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-4">
                            <div class="text-end small text-muted">
                                Subtotal: <span id="trxSubtotalDisp">Rp 0</span><br>
                                Pajak: <span id="trxTaxDisp">Rp 0</span><br>
                                Ongkir: <span id="trxShippingDisp">Rp 0</span>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">Total</div>
                                <div class="fs-5 fw-bold text-primary" id="trxTotalDisp">Rp 0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveTrx"><i class="bi bi-check2 me-1"></i>Simpan Transaksi</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function () {
    var BASE_URL = '<?= site_url('transaction'); ?>/';

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
    function statusBadgeClass(status) {
        switch (status) {
            case 'Menunggu Pembayaran': return 'bg-secondary';
            case 'Dibayar': return 'bg-info text-dark';
            case 'Diproses': return 'bg-primary';
            case 'Dikirim': return 'bg-warning text-dark';
            case 'Selesai': return 'bg-success';
            case 'Dibatalkan': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }

    function load() {
        $('#tableLoading').show();
        $.ajax({ url: BASE_URL + 'list_data', method: 'POST', dataType: 'json', data: { status: $('#statusFilter').val() } })
            .done(function (res) { render((res && res.data) || []); })
            .fail(function () { $('#tableBody').html('<tr><td colspan="10" class="text-center text-danger py-4">Gagal memuat data.</td></tr>'); $('#summaryInfo').text(''); })
            .always(function () { $('#tableLoading').hide(); });
    }

    function render(rows) {
        var $body = $('#tableBody');
        $body.empty();
        if (rows.length === 0) {
            $body.append('<tr><td colspan="10" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>');
            $('#summaryInfo').text('');
            return;
        }
        var grand = 0;
        rows.forEach(function (row, idx) {
            grand += row.v_total;
            var tr = $('<tr>');
            tr.append('<td>' + (idx + 1) + '</td>');
            tr.append('<td class="fw-semibold">' + escapeHtml(row.i_invoice) + '</td>');
            tr.append('<td><span class="badge badge-user"><i class="bi bi-person-fill me-1"></i>' + escapeHtml(row.i_username) + '</span></td>');
            tr.append('<td class="text-end">' + formatRupiah(row.v_subtotal) + '</td>');
            tr.append('<td class="text-end">' + formatRupiah(row.v_tax) + '</td>');
            tr.append('<td class="text-end">' + formatRupiah(row.v_shipping_cost) + '</td>');
            tr.append('<td class="text-end fw-semibold">' + formatRupiah(row.v_total) + '</td>');
            tr.append('<td><span class="badge badge-status ' + statusBadgeClass(row.e_status) + '">' + escapeHtml(row.e_status) + '</span></td>');
            tr.append('<td>' + formatDate(row.dt_created) + '</td>');

            var actionTd = $('<td class="text-center">');
            var viewBtn = $('<a class="btn btn-sm btn-outline-primary btn-icon" title="Lihat detail transaksi"><i class="bi bi-eye"></i></a>');
            viewBtn.attr('href', BASE_URL + 'detail/' + row.id_transaction).attr('target', '_blank');
            actionTd.append(viewBtn);
            tr.append(actionTd);

            $body.append(tr);
        });
        $('#summaryInfo').text(rows.length + ' transaksi, total nilai ' + formatRupiah(grand) + '.');
    }

    $('#statusFilter').on('change', load);
    load();

    // ---------------------------------------------------------------
    // Modal: Tambah Transaksi
    // ---------------------------------------------------------------
    var PRODUCTS = <?= json_encode(array_map(function ($p) {
        return array(
            'id_product' => (int) $p['id_product'],
            'i_product'  => $p['i_product'],
            'e_product'  => $p['e_product'],
            'v_price'    => (float) $p['v_price'],
        );
    }, $products)); ?>;
    var TAX_RATES = <?= json_encode(array_map(function ($t) {
        return array('id_tax' => (int) $t['id_tax'], 'n_percentage' => (float) $t['n_percentage']);
    }, $taxes)); ?>;
    var rowIdx = 0;

    function productOptionsHtml() {
        var html = '<option value="">-- Pilih Produk --</option>';
        PRODUCTS.forEach(function (p) {
            html += '<option value="' + p.id_product + '" data-price="' + p.v_price + '">' +
                    escapeHtml(p.i_product) + ' - ' + escapeHtml(p.e_product) + ' (' + formatRupiah(p.v_price) + ')</option>';
        });
        return html;
    }

    function addTrxRow() {
        rowIdx++;
        var tr = $('<tr data-row="' + rowIdx + '">');
        tr.append('<td><select class="form-select form-select-sm trx-row-product">' + productOptionsHtml() + '</select></td>');
        tr.append('<td><input type="number" class="form-control form-control-sm trx-row-qty" value="1" min="1"></td>');
        tr.append('<td class="text-end trx-row-subtotal">Rp 0</td>');
        tr.append('<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-icon trx-row-remove"><i class="bi bi-trash"></i></button></td>');
        $('#trxItemsBody').append(tr);
    }

    $('#btnAddRow').on('click', addTrxRow);

    $('#trxItemsBody').on('change', '.trx-row-product, .trx-row-qty', recalcTrxTotals);
    $('#trxItemsBody').on('click', '.trx-row-remove', function () {
        $(this).closest('tr').remove();
        recalcTrxTotals();
    });

    function recalcTrxTotals() {
        var subtotal = 0;
        $('#trxItemsBody tr').each(function () {
            var $tr = $(this);
            var $opt = $tr.find('.trx-row-product option:selected');
            var price = parseFloat($opt.data('price')) || 0;
            var qty = parseInt($tr.find('.trx-row-qty').val(), 10) || 0;
            var lineSubtotal = price * qty;
            $tr.find('.trx-row-subtotal').text(formatRupiah(lineSubtotal));
            subtotal += lineSubtotal;
        });

        var taxId = parseInt($('#trxTax').val(), 10) || 0;
        var taxPct = 0;
        TAX_RATES.forEach(function (t) { if (t.id_tax === taxId) taxPct = t.n_percentage; });
        var taxAmount = Math.round(subtotal * taxPct / 100);
        var shipping = parseFloat($('#trxShipping').val()) || 0;
        var total = subtotal + taxAmount + shipping;

        $('#trxSubtotalDisp').text(formatRupiah(subtotal));
        $('#trxTaxDisp').text(formatRupiah(taxAmount));
        $('#trxShippingDisp').text(formatRupiah(shipping));
        $('#trxTotalDisp').text(formatRupiah(total));
    }

    $('#trxTax, #trxShipping').on('input change', recalcTrxTotals);

    $('#trxUser').on('change', function () {
        var idUser = $(this).val();
        var $addr = $('#trxAddress');
        var $bank = $('#trxBankAccount');

        if (!idUser) {
            $addr.prop('disabled', true).html('<option value="">-- Pilih konsumen dulu --</option>');
            $bank.prop('disabled', true).html('<option value="">-- Pilih konsumen dulu --</option>');
            return;
        }

        $addr.prop('disabled', true).html('<option value="">Memuat...</option>');
        $bank.prop('disabled', true).html('<option value="">Memuat...</option>');

        $.ajax({ url: BASE_URL + 'customer_options/' + idUser, method: 'POST', dataType: 'json' })
            .done(function (res) {
                var data = (res && res.data) || { addresses: [], bank_accounts: [] };

                if (data.addresses.length === 0) {
                    $addr.html('<option value="">Konsumen belum punya alamat</option>');
                } else {
                    var h = '<option value="">-- Tanpa alamat --</option>';
                    data.addresses.forEach(function (a) {
                        h += '<option value="' + a.id_address + '">' + escapeHtml(a.e_label) + ' - ' + escapeHtml(a.e_recipient) + ', ' + escapeHtml(a.e_city) + '</option>';
                    });
                    $addr.html(h);
                }
                $addr.prop('disabled', false);

                if (data.bank_accounts.length === 0) {
                    $bank.html('<option value="">Konsumen belum punya rekening</option>');
                } else {
                    var h2 = '<option value="">-- Tanpa rekening --</option>';
                    data.bank_accounts.forEach(function (b) {
                        h2 += '<option value="' + b.id_bank_account + '">' + escapeHtml(b.e_bank_name) + ' - ' + escapeHtml(b.i_account_number) + '</option>';
                    });
                    $bank.html(h2);
                }
                $bank.prop('disabled', false);
            })
            .fail(function () {
                $addr.html('<option value="">Gagal memuat</option>');
                $bank.html('<option value="">Gagal memuat</option>');
            });
    });

    $('#addTrxModal').on('show.bs.modal', function () {
        $('#addTrxAlert').empty();
        $('#trxItemsBody').empty();
        rowIdx = 0;
        addTrxRow();
        $('#trxUser').val('');
        $('#trxAddress').prop('disabled', true).html('<option value="">-- Pilih konsumen dulu --</option>');
        $('#trxBankAccount').prop('disabled', true).html('<option value="">-- Pilih konsumen dulu --</option>');
        $('#trxShipping').val(0);
        $('#trxNote').val('');
        recalcTrxTotals();
    });

    $('#btnSaveTrx').on('click', function () {
        var idUser = $('#trxUser').val();
        if (!idUser) {
            $('#addTrxAlert').html('<div class="alert alert-danger py-2">Pilih konsumen terlebih dahulu.</div>');
            return;
        }

        var items = [];
        $('#trxItemsBody tr').each(function () {
            var $tr = $(this);
            var idProduct = $tr.find('.trx-row-product').val();
            var qty = parseInt($tr.find('.trx-row-qty').val(), 10) || 0;
            if (idProduct && qty > 0) {
                items.push({ id_product: idProduct, qty: qty });
            }
        });

        if (items.length === 0) {
            $('#addTrxAlert').html('<div class="alert alert-danger py-2">Tambahkan minimal 1 barang.</div>');
            return;
        }

        var $btn = $('#btnSaveTrx');
        $btn.prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'create',
            method: 'POST',
            dataType: 'json',
            data: {
                id_user: idUser,
                id_address: $('#trxAddress').val(),
                id_bank_account: $('#trxBankAccount').val(),
                id_tax: $('#trxTax').val(),
                v_shipping_cost: $('#trxShipping').val(),
                e_status: $('#trxStatus').val(),
                e_keterangan: $('#trxNote').val(),
                items: items
            }
        }).done(function (res) {
            $('#addTrxModal').modal('hide');
            showAlert('Transaksi ' + escapeHtml(res.i_invoice) + ' berhasil dibuat.', 'success');
            load();
        }).fail(function (xhr) {
            $('#addTrxAlert').html('<div class="alert alert-danger py-2">' + ((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan transaksi.') + '</div>');
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });
});
</script>
</body>
</html>
