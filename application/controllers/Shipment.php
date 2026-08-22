<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shipment extends MY_Controller {

    private $_valid_status = array('Dikemas', 'Dikirim', 'Diterima', 'Retur');

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Shipment_model');
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

        $data['username'] = $this->username;
        $this->load->view('shipment_list', $data);
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
