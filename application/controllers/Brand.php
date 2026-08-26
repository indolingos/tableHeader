<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Brand extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Brand_model');
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
                $this->_json(array(
                    'status'  => false,
                    'message' => 'Anda tidak memiliki akses ke halaman ini.'
                ), 403);
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
        $this->load->view('brand_list', $data);
    }

    public function list_data()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $rows = $this->Brand_model->get_all(true);
        $this->_json(array('status' => true, 'data' => $rows));
    }

    public function get($id)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $row = $this->Brand_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('status' => false, 'message' => 'Brand tidak ditemukan.'), 404);
            return;
        }

        $this->_json(array('status' => true, 'data' => $row));
    }

    public function save()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $id_brand = trim((string) $this->input->post('id_brand'));
        $is_edit  = ($id_brand !== '');

        $this->form_validation->set_rules(
            'e_brand',
            'Nama Brand',
            'required|trim|max_length[100]'
        );
        $this->form_validation->set_rules(
            'e_keterangan',
            'Keterangan',
            'trim|max_length[255]'
        );

        if (!$this->form_validation->run()) {
            $this->_json(array(
                'status'  => false,
                'message' => strip_tags(validation_errors())
            ), 422);
            return;
        }

        $name = trim($this->input->post('e_brand'));
        $existing = $this->Brand_model->find_by_name($name, $is_edit ? $id_brand : null);

        if ($existing) {
            $message = ($existing['f_active'] === 't')
                ? 'Nama brand sudah digunakan.'
                : 'Nama brand sudah digunakan (Nonaktif).';

            $this->_json(array(
                'status'  => false,
                'message' => $message
            ), 422);
            return;
        }

        $data = array(
            'e_brand'      => $name,
            'e_keterangan' => trim((string) $this->input->post('e_keterangan')),
        );

        if ($is_edit) {
            $current = $this->Brand_model->get_by_id($id_brand);
            if (!$current) {
                $this->_json(array('status' => false, 'message' => 'Brand tidak ditemukan.'), 404);
                return;
            }

            if ($current['f_active'] !== 't') {
                $this->_json(array(
                    'status'  => false,
                    'message' => 'Brand sudah dinonaktifkan dan tidak bisa diedit.'
                ), 422);
                return;
            }

            $this->Brand_model->update($id_brand, $data);
            $this->_json(array('status' => true, 'message' => 'Brand berhasil diperbarui.'));
            return;
        }

        $data['f_active'] = 't';
        $this->Brand_model->insert($data);
        $this->_json(array('status' => true, 'message' => 'Brand berhasil ditambahkan.'));
    }

    public function toggle_active($id)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $row = $this->Brand_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('status' => false, 'message' => 'Brand tidak ditemukan.'), 404);
            return;
        }

        $new_state = !($row['f_active'] === 't');
        $this->Brand_model->set_active($id, $new_state);

        $this->_json(array(
            'status'  => true,
            'message' => $new_state ? 'Brand diaktifkan kembali.' : 'Brand dinonaktifkan.'
        ));
    }
}
