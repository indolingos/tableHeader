<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Address extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Address_model');
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

    // Halaman master: daftar konsumen beserta jumlah alamat yang dimiliki
    public function index()
    {
        if (!$this->_require_admin()) return;

        $data['username'] = $this->username;
        $this->load->view('address_list', $data);
    }

    public function list_data()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $rows = $this->Address_model->get_user_summary();
        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'id_user'    => $row['id_user'],
                'i_username' => $row['i_username'],
                'n_address'  => (int) $row['n_address'],
            );
        }
        $this->_json(array('status' => true, 'data' => $out));
    }

    // Halaman detail per konsumen: nested table daftar alamatnya
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
        $this->load->view('address_user_detail', $data);
    }

    public function user_items($id_user = null)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $rows = $this->Address_model->get_by_user((int) $id_user);
        $this->_json(array('status' => true, 'data' => $rows));
    }

    public function save()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $id_address = trim((string) $this->input->post('id_address'));
        $is_edit    = ($id_address !== '');

        $this->form_validation->set_rules('id_user', 'Konsumen', 'required|integer');
        $this->form_validation->set_rules('e_label', 'Label Alamat', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('e_recipient', 'Nama Penerima', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('i_phone', 'No Telepon', 'required|trim|max_length[30]');
        $this->form_validation->set_rules('e_address_full', 'Alamat Lengkap', 'required|trim');

        if (!$this->form_validation->run()) {
            $this->_json(array('status' => false, 'message' => strip_tags(validation_errors())), 422);
            return;
        }

        $data = array(
            'id_user'        => (int) $this->input->post('id_user'),
            'e_label'        => $this->input->post('e_label'),
            'e_recipient'    => $this->input->post('e_recipient'),
            'i_phone'        => $this->input->post('i_phone'),
            'e_address_full' => $this->input->post('e_address_full'),
            'e_city'         => $this->input->post('e_city'),
            'e_province'     => $this->input->post('e_province'),
            'i_postal_code'  => $this->input->post('i_postal_code'),
            'e_keterangan'   => $this->input->post('e_keterangan'),
            'f_primary'      => $this->input->post('f_primary') ? 't' : 'f',
        );

        if ($is_edit) {
            $this->Address_model->update($id_address, $data);
            $this->_json(array('status' => true, 'message' => 'Alamat berhasil diperbarui.'));
        } else {
            $this->Address_model->insert($data);
            $this->_json(array('status' => true, 'message' => 'Alamat berhasil ditambahkan.'));
        }
    }

    public function delete($id)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $this->Address_model->delete($id);
        $this->_json(array('status' => true, 'message' => 'Alamat berhasil dihapus.'));
    }
}
