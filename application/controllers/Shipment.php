<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shipment extends MY_Controller {

    private $_valid_status = array('Dikemas', 'Dikirim', 'Diterima', 'Retur');

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Shipment_model');
        $this->load->model('Transaction_model');
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

    public function index()
    {
        if (!$this->_require_admin()) return;

        $all_trx = $this->Transaction_model->get_all();
        $shippable = array();
        foreach ($all_trx as $t) {
            if ($t['e_status'] !== 'Dibatalkan') {
                $shippable[] = array(
                    'id_transaction' => $t['id_transaction'],
                    'i_invoice'      => $t['i_invoice'],
                    'i_username'     => $t['i_username'],
                    'e_status'       => $t['e_status'],
                );
            }
        }

        $data['username']     = $this->username;
        $data['couriers']     = $this->Shipment_model->get_couriers();
        $data['transactions'] = $shippable;
        $this->load->view('shipment_list', $data);
    }

    // Dipanggil saat admin memilih transaksi di form "Tambah Pengiriman":
    // isi daftar barang transaksi tsb supaya qty kirim bisa disesuaikan.
    public function transaction_items($id_transaction = null)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $rows = $this->Transaction_model->get_detail((int) $id_transaction);
        $this->_json(array('status' => true, 'data' => $rows));
    }

    // Simpan pengiriman baru (header + barang yang dikirim) untuk sebuah transaksi
    public function create()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $this->form_validation->set_rules('id_transaction', 'Transaksi', 'required|integer');
        $this->form_validation->set_rules('id_courier', 'Kurir', 'required|integer');
        $this->form_validation->set_rules('i_resi', 'No Resi', 'required|trim|max_length[50]');

        $items = $this->input->post('items');
        if (!$this->form_validation->run() || empty($items) || !is_array($items)) {
            $this->_json(array(
                'status'  => false,
                'message' => empty($items) ? 'Minimal harus ada 1 barang yang dikirim.' : strip_tags(validation_errors()),
            ), 422);
            return;
        }

        $status = trim((string) $this->input->post('e_status_kirim'));
        if (!in_array($status, $this->_valid_status, true)) {
            $status = 'Dikemas';
        }

        $header = array(
            'id_transaction' => (int) $this->input->post('id_transaction'),
            'id_courier'     => (int) $this->input->post('id_courier'),
            'i_resi'         => trim((string) $this->input->post('i_resi')),
            'e_status_kirim' => $status,
            'e_keterangan'   => trim((string) $this->input->post('e_keterangan')) ?: null,
        );
        if ($status === 'Dikirim') {
            $header['dt_kirim'] = date('Y-m-d H:i:s');
        } elseif ($status === 'Diterima') {
            $header['dt_kirim'] = date('Y-m-d H:i:s');
            $header['dt_diterima'] = date('Y-m-d H:i:s');
        }

        $id_shipment = $this->Shipment_model->insert_header($header);

        foreach ($items as $item) {
            $id_product = (int) ($item['id_product'] ?? 0);
            $qty        = (int) ($item['qty'] ?? 0);
            if ($id_product <= 0 || $qty <= 0) continue;

            $this->Shipment_model->insert_detail(array(
                'id_shipment' => $id_shipment,
                'id_product'  => $id_product,
                'n_qty'       => $qty,
                'e_keterangan' => null,
            ));
        }

        $this->_json(array(
            'status'      => true,
            'message'     => 'Pengiriman berhasil dibuat.',
            'id_shipment' => $id_shipment,
        ));
    }

    public function list_data()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $rows = $this->Shipment_model->get_all();
        $this->_json(array('status' => true, 'data' => $rows));
    }

    // Halaman detail: bukti kirim + nested table barang yang dikirim
    public function detail($id_shipment = null)
    {
        if (!$this->_require_admin()) return;

        $id_shipment = (int) $id_shipment;
        $shipment = $this->Shipment_model->get_by_id($id_shipment);
        if (!$shipment) {
            show_404();
            return;
        }

        $data['username']    = $this->username;
        $data['id_shipment'] = $id_shipment;
        $data['shipment']    = $shipment;
        $data['statuses']    = $this->_valid_status;
        $this->load->view('shipment_detail', $data);
    }

    public function detail_items($id_shipment = null)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $rows = $this->Shipment_model->get_detail((int) $id_shipment);
        $this->_json(array('status' => true, 'data' => $rows));
    }

    public function update_status($id_shipment = null)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $status = trim((string) $this->input->post('e_status_kirim'));
        if (!in_array($status, $this->_valid_status, true)) {
            $this->_json(array('status' => false, 'message' => 'Status tidak valid.'), 422);
            return;
        }

        $data = array('e_status_kirim' => $status);
        if ($status === 'Dikirim') {
            $data['dt_kirim'] = date('Y-m-d H:i:s');
        } elseif ($status === 'Diterima') {
            $data['dt_diterima'] = date('Y-m-d H:i:s');
        }

        $bukti_url = trim((string) $this->input->post('e_bukti_foto_url'));
        if ($bukti_url !== '') {
            $data['e_bukti_foto_url'] = $bukti_url;
        }

        $keterangan = $this->input->post('e_keterangan');
        if ($keterangan !== null) {
            $data['e_keterangan'] = $keterangan;
        }

        $this->Shipment_model->update_status((int) $id_shipment, $data);
        $this->_json(array('status' => true, 'message' => 'Status pengiriman berhasil diperbarui.'));
    }
}
