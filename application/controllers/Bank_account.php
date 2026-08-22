<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bank_account extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Bank_account_model');
        $this->load->model('User_model');
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
        $this->load->view('bank_account_list', $data);
    }

    public function list_data()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $rows = $this->Bank_account_model->get_user_summary();
        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'id_user'    => $row['id_user'],
                'i_username' => $row['i_username'],
                'n_account'  => (int) $row['n_account'],
            );
        }
        $this->_json(array('status' => true, 'data' => $out));
    }

    public function user_detail($id_user = null)
    {
        if (!$this->_require_admin()) return;

        $id_user = (int) $id_user;
        $user = $this->User_model->get_by_id($id_user);
        if (!$user) {
            show_404();
            return;
        }

        $data['username']        = $this->username;
        $data['id_user']         = $id_user;
        $data['target_username'] = $user['i_username'];
        $this->load->view('bank_account_user_detail', $data);
    }

    public function user_items($id_user = null)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $rows = $this->Bank_account_model->get_by_user((int) $id_user);
        $this->_json(array('status' => true, 'data' => $rows));
    }

    public function save()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $id_bank_account = trim((string) $this->input->post('id_bank_account'));
        $is_edit         = ($id_bank_account !== '');

        $this->form_validation->set_rules('id_user', 'Konsumen', 'required|integer');
        $this->form_validation->set_rules('e_bank_name', 'Nama Bank', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('i_account_number', 'No Rekening', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('e_account_holder', 'Nama Pemilik Rekening', 'required|trim|max_length[100]');

        if (!$this->form_validation->run()) {
            $this->_json(array('status' => false, 'message' => strip_tags(validation_errors())), 422);
            return;
        }

        $data = array(
            'id_user'          => (int) $this->input->post('id_user'),
            'e_bank_name'      => $this->input->post('e_bank_name'),
            'i_account_number' => $this->input->post('i_account_number'),
            'e_account_holder' => $this->input->post('e_account_holder'),
            'e_keterangan'     => $this->input->post('e_keterangan'),
            'f_primary'        => $this->input->post('f_primary') ? 't' : 'f',
        );

        if ($is_edit) {
            $this->Bank_account_model->update($id_bank_account, $data);
            $this->_json(array('status' => true, 'message' => 'Rekening berhasil diperbarui.'));
        } else {
            $this->Bank_account_model->insert($data);
            $this->_json(array('status' => true, 'message' => 'Rekening berhasil ditambahkan.'));
        }
    }

    public function delete($id)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $this->Bank_account_model->delete($id);
        $this->_json(array('status' => true, 'message' => 'Rekening berhasil dihapus.'));
    }
}
