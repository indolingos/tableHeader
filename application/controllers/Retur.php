<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Retur extends MY_Controller {

    // Alur status: Diajukan -> Disetujui / Ditolak -> (kalau Disetujui) refund
    // diproses -> Selesai
    private $_valid_status  = array('Diajukan', 'Disetujui', 'Ditolak', 'Selesai');
    private $_refund_status = array('Diproses', 'Selesai', 'Ditolak');

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Retur_model');
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
        $this->load->view('retur_list', $data);
    }

    public function list_data()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $rows = $this->Retur_model->get_all();

        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'id_return'       => $row['id_return'],
                'i_invoice'       => $row['i_invoice'],
                'i_username'      => $row['i_username'],
                'e_product'       => $row['e_product'],
                'n_qty'           => (int) $row['n_qty'],
                'e_reason'        => $row['e_reason'],
                'e_status'        => $row['e_status'],
                'id_refund'       => $row['id_refund'],
                'v_refund_amount' => $row['v_refund_amount'] !== null ? (float) $row['v_refund_amount'] : null,
                'e_refund_status' => $row['e_refund_status'],
                'dt_created'      => $row['dt_created'],
            );
        }

        $this->_json(array('status' => true, 'data' => $out));
    }

    // Dipanggil saat admin ketik no invoice di form "Tambah Retur", supaya
    // dropdown produk terisi otomatis dari isi transaksi tersebut.
    public function find_invoice()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $invoice = trim((string) $this->input->post('i_invoice'));
        $trx = $this->Retur_model->find_transaction_by_invoice($invoice);
        if (!$trx) {
            $this->_json(array('status' => false, 'message' => 'No invoice tidak ditemukan.'), 404);
            return;
        }

        $items = $this->Retur_model->get_transaction_items($trx['id_transaction']);
        $this->_json(array('status' => true, 'data' => array(
            'id_transaction' => $trx['id_transaction'],
            'i_invoice'      => $trx['i_invoice'],
            'i_username'     => $trx['i_username'],
            'items'          => $items,
        )));
    }

    public function save()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $this->form_validation->set_rules('id_transaction', 'Transaksi', 'required|integer');
        $this->form_validation->set_rules('id_product', 'Produk', 'required|integer');
        $this->form_validation->set_rules('n_qty', 'Qty', 'required|integer|greater_than[0]');
        $this->form_validation->set_rules('e_reason', 'Alasan Retur', 'required|trim');

        if (!$this->form_validation->run()) {
            $this->_json(array('status' => false, 'message' => strip_tags(validation_errors())), 422);
            return;
        }

        $data = array(
            'id_transaction' => (int) $this->input->post('id_transaction'),
            'id_product'     => (int) $this->input->post('id_product'),
            'n_qty'          => (int) $this->input->post('n_qty'),
            'e_reason'       => $this->input->post('e_reason'),
            'e_status'       => 'Diajukan',
        );

        $this->Retur_model->insert($data);
        $this->_json(array('status' => true, 'message' => 'Pengajuan retur berhasil dicatat.'));
    }

    // Admin approve/reject pengajuan retur
    public function update_status($id = null)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $status = trim((string) $this->input->post('e_status'));
        if (!in_array($status, $this->_valid_status, true)) {
            $this->_json(array('status' => false, 'message' => 'Status tidak valid.'), 422);
            return;
        }

        $row = $this->Retur_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('status' => false, 'message' => 'Data retur tidak ditemukan.'), 404);
            return;
        }

        $this->Retur_model->update_status((int) $id, $status);
        $this->_json(array('status' => true, 'message' => 'Status retur berhasil diperbarui.'));
    }

    // Proses refund untuk retur yang sudah Disetujui
    public function process_refund($id = null)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $row = $this->Retur_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('status' => false, 'message' => 'Data retur tidak ditemukan.'), 404);
            return;
        }
        if ($row['e_status'] !== 'Disetujui') {
            $this->_json(array('status' => false, 'message' => 'Refund hanya bisa diproses untuk retur yang sudah Disetujui.'), 422);
            return;
        }

        $this->form_validation->set_rules('v_refund_amount', 'Nominal Refund', 'required|numeric|greater_than[0]');
        if (!$this->form_validation->run()) {
            $this->_json(array('status' => false, 'message' => strip_tags(validation_errors())), 422);
            return;
        }

        $this->Retur_model->insert_refund(array(
            'id_return'      => (int) $id,
            'v_refund_amount' => (float) $this->input->post('v_refund_amount'),
            'e_status'         => 'Diproses',
        ));

        $this->_json(array('status' => true, 'message' => 'Refund mulai diproses.'));
    }

    // Update status refund (Diproses -> Selesai / Ditolak); kalau Selesai,
    // status retur ikut ditutup jadi "Selesai".
    public function update_refund_status($id_refund = null)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $status = trim((string) $this->input->post('e_status'));
        if (!in_array($status, $this->_refund_status, true)) {
            $this->_json(array('status' => false, 'message' => 'Status refund tidak valid.'), 422);
            return;
        }

        $this->db->where('id_refund', $id_refund);
        $refund = $this->db->get('trx_refund')->row_array();
        if (!$refund) {
            $this->_json(array('status' => false, 'message' => 'Data refund tidak ditemukan.'), 404);
            return;
        }

        $this->Retur_model->update_refund_status((int) $id_refund, $status);
        if ($status === 'Selesai') {
            $this->Retur_model->update_status((int) $refund['id_return'], 'Selesai');
        }

        $this->_json(array('status' => true, 'message' => 'Status refund berhasil diperbarui.'));
    }
}
