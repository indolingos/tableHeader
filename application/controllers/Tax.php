<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tax extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Tax_model');
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

    // Halaman Pajak & Keuangan: master pajak + ringkasan keuangan toko
    public function index()
    {
        if (!$this->_require_admin()) return;

        $data['username'] = $this->username;
        $this->load->view('tax_finance', $data);
    }

    public function list_data()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $rows = $this->Tax_model->get_all();
        $this->_json(array('status' => true, 'data' => $rows));
    }

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

    public function save()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $id_tax  = trim((string) $this->input->post('id_tax'));
        $is_edit = ($id_tax !== '');

        $this->form_validation->set_rules('e_tax_name', 'Nama Pajak', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('n_percentage', 'Persentase', 'required|numeric|greater_than_equal_to[0]');

        if (!$this->form_validation->run()) {
            $this->_json(array('status' => false, 'message' => strip_tags(validation_errors())), 422);
            return;
        }

        $data = array(
            'e_tax_name'   => $this->input->post('e_tax_name'),
            'n_percentage' => (float) $this->input->post('n_percentage'),
            'e_keterangan' => $this->input->post('e_keterangan'),
        );

        if ($is_edit) {
            $this->Tax_model->update($id_tax, $data);
            $this->_json(array('status' => true, 'message' => 'Data pajak berhasil diperbarui.'));
        } else {
            $data['f_active'] = 't';
            $this->Tax_model->insert($data);
            $this->_json(array('status' => true, 'message' => 'Data pajak berhasil ditambahkan.'));
        }
    }

    public function toggle_active($id)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $row = $this->Tax_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('status' => false, 'message' => 'Data pajak tidak ditemukan.'), 404);
            return;
        }

        $new_state = !($row['f_active'] === 't');
        $this->Tax_model->set_active($id, $new_state);
        $this->_json(array(
            'status'  => true,
            'message' => $new_state ? 'Pajak diaktifkan.' : 'Pajak dinonaktifkan.',
        ));
    }
}
