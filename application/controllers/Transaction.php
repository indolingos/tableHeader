<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction extends MY_Controller {

    private $_valid_status = array('Menunggu Pembayaran', 'Dibayar', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan');

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Transaction_model');
        $this->load->model('User_model');
        $this->load->model('Address_model');
        $this->load->model('Bank_account_model');
        $this->load->model('Product_model');
        $this->load->model('Tax_model');
        $this->load->helper('url');
        $this->load->library('form_validation');
    }

    private function _require_ajax()
    {
        if (!$this->input->is_ajax_request() && ENVIRONMENT !== 'testing') {
            show_404();
            return false;
        }
        return true;
    }

    private function _json($payload, $http_code = 200)
    {
        $this->output
            ->set_status_header($http_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    private function _is_admin()
    {
        return $this->username === 'admin';
    }

    private function _require_admin()
    {
        if (!$this->_is_admin()) {
            if ($this->input->is_ajax_request()) {
                $this->_json(array('status' => false, 'message' => 'Anda tidak memiliki akses ke halaman ini.'), 403);
            } else {
                redirect(site_url('home'));
            }
            return false;
        }
        return true;
    }

    // Halaman master: daftar transaksi (header)
    public function index()
    {
        if (!$this->_require_admin()) return;

        $data['username']  = $this->username;
        $data['statuses']  = $this->_valid_status;
        $data['customers'] = $this->User_model->get_all_customers();
        $data['products']  = $this->Product_model->get_all();
        $data['taxes']     = $this->Tax_model->get_all();
        $this->load->view('transaction_list', $data);
    }

    // Dipanggil saat admin memilih konsumen di form "Tambah Transaksi":
    // isi pilihan alamat & rekening bank milik konsumen tersebut.
    public function customer_options($id_user = null)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $id_user = (int) $id_user;
        $this->_json(array('status' => true, 'data' => array(
            'addresses'     => $this->Address_model->get_by_user($id_user),
            'bank_accounts' => $this->Bank_account_model->get_by_user($id_user),
        )));
    }

    // Simpan transaksi baru (header + item barang) yang diinput manual oleh admin
    public function create()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $this->form_validation->set_rules('id_user', 'Konsumen', 'required|integer');

        $items = $this->input->post('items');
        if (!$this->form_validation->run() || empty($items) || !is_array($items)) {
            $this->_json(array(
                'status'  => false,
                'message' => empty($items) ? 'Minimal harus ada 1 barang pada transaksi.' : strip_tags(validation_errors()),
            ), 422);
            return;
        }

        $id_user = (int) $this->input->post('id_user');
        $customer = $this->User_model->get_by_id($id_user);
        if (!$customer || $customer['i_username'] === 'admin') {
            $this->_json(array('status' => false, 'message' => 'Konsumen tidak valid.'), 422);
            return;
        }

        $id_address      = (int) $this->input->post('id_address');
        $id_bank_account = (int) $this->input->post('id_bank_account');
        $id_tax          = (int) $this->input->post('id_tax');
        $shipping_cost   = (float) $this->input->post('v_shipping_cost');
        $keterangan      = trim((string) $this->input->post('e_keterangan'));
        $status          = trim((string) $this->input->post('e_status'));
        if (!in_array($status, $this->_valid_status, true)) {
            $status = 'Menunggu Pembayaran';
        }

        // Hitung ulang harga & subtotal di server (jangan percaya harga dari client)
        $line_items = array();
        $subtotal   = 0;
        foreach ($items as $item) {
            $id_product = (int) ($item['id_product'] ?? 0);
            $qty        = (int) ($item['qty'] ?? 0);
            if ($id_product <= 0 || $qty <= 0) continue;

            $product = $this->Product_model->get_by_id($id_product);
            if (!$product || (isset($product['f_active']) && $product['f_active'] !== 't')) continue;

            $line_subtotal = (float) $product['v_price'] * $qty;
            $subtotal += $line_subtotal;
            $line_items[] = array(
                'id_product' => $id_product,
                'n_qty'      => $qty,
                'v_price'    => (float) $product['v_price'],
                'v_subtotal' => $line_subtotal,
            );
        }

        if (empty($line_items)) {
            $this->_json(array('status' => false, 'message' => 'Barang yang dipilih tidak valid.'), 422);
            return;
        }

        $tax_amount = 0;
        if ($id_tax > 0) {
            $tax = $this->Tax_model->get_by_id($id_tax);
            if ($tax) {
                $tax_amount = round($subtotal * (float) $tax['n_percentage'] / 100, 2);
            }
        }

        $header = array(
            'i_invoice'       => $this->Transaction_model->next_invoice_number(),
            'id_user'         => $id_user,
            'id_address'      => $id_address > 0 ? $id_address : null,
            'id_bank_account' => $id_bank_account > 0 ? $id_bank_account : null,
            'id_tax'          => $id_tax > 0 ? $id_tax : null,
            'v_subtotal'      => $subtotal,
            'v_tax'           => $tax_amount,
            'v_shipping_cost' => $shipping_cost,
            'v_total'         => $subtotal + $tax_amount + $shipping_cost,
            'e_status'        => $status,
            'e_keterangan'    => $keterangan !== '' ? $keterangan : null,
        );

        $id_transaction = $this->Transaction_model->insert_header($header);

        foreach ($line_items as $li) {
            $li['id_transaction'] = $id_transaction;
            $li['e_keterangan']   = null;
            $this->Transaction_model->insert_detail($li);
        }

        $this->_json(array(
            'status'         => true,
            'message'        => 'Transaksi berhasil dibuat.',
            'id_transaction' => $id_transaction,
            'i_invoice'      => $header['i_invoice'],
        ));
    }

    public function list_data()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $status = trim((string) $this->input->get_post('status'));
        $rows = $this->Transaction_model->get_all($status !== '' ? $status : null);

        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'id_transaction' => $row['id_transaction'],
                'i_invoice'      => $row['i_invoice'],
                'i_username'     => $row['i_username'],
                'v_subtotal'     => (float) $row['v_subtotal'],
                'v_tax'          => (float) $row['v_tax'],
                'v_shipping_cost' => (float) $row['v_shipping_cost'],
                'v_total'        => (float) $row['v_total'],
                'e_status'       => $row['e_status'],
                'dt_created'     => $row['dt_created'],
            );
        }

        $this->_json(array('status' => true, 'data' => $out));
    }

    // Halaman detail: header + nested table barang yang dibeli + histori pengiriman
    public function detail($id_transaction = null)
    {
        if (!$this->_require_admin()) return;

        $id_transaction = (int) $id_transaction;
        $trx = $this->Transaction_model->get_by_id($id_transaction);
        if (!$trx) {
            show_404();
            return;
        }

        $data['username']       = $this->username;
        $data['id_transaction'] = $id_transaction;
        $data['trx']            = $trx;
        $data['statuses']       = $this->_valid_status;
        $this->load->view('transaction_detail', $data);
    }

    public function detail_items($id_transaction = null)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $id_transaction = (int) $id_transaction;
        $items     = $this->Transaction_model->get_detail($id_transaction);
        $shipments = $this->Transaction_model->get_shipments($id_transaction);

        $this->_json(array('status' => true, 'data' => array(
            'items'     => $items,
            'shipments' => $shipments,
        )));
    }

    public function update_status($id_transaction = null)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $status = trim((string) $this->input->post('e_status'));
        if (!in_array($status, $this->_valid_status, true)) {
            $this->_json(array('status' => false, 'message' => 'Status tidak valid.'), 422);
            return;
        }

        $this->Transaction_model->update_status((int) $id_transaction, $status);
        $this->_json(array('status' => true, 'message' => 'Status transaksi berhasil diperbarui.'));
    }

    // Ringkasan untuk halaman Pajak & Keuangan
    public function finance_summary()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $summary   = $this->Transaction_model->get_finance_summary();
        $by_status = $this->Transaction_model->get_finance_by_status();

        $this->_json(array('status' => true, 'data' => array(
            'summary'   => $summary,
            'by_status' => $by_status,
        )));
    }
}
