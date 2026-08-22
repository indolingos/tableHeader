<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction extends MY_Controller {

    private $_valid_status = array('Menunggu Pembayaran', 'Dibayar', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan');

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Transaction_model');
        $this->load->helper('url');
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

        $data['username'] = $this->username;
        $data['statuses'] = $this->_valid_status;
        $this->load->view('transaction_list', $data);
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
