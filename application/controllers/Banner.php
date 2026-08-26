<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Banner extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Banner_model');
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
        $this->load->view('banner_list', $data);
    }

    public function list_data()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $rows = $this->Banner_model->get_all();
        $this->_json(array('status' => true, 'data' => $rows));
    }

    public function get($id)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $row = $this->Banner_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('status' => false, 'message' => 'Banner tidak ditemukan.'), 404);
            return;
        }
        $this->_json(array('status' => true, 'data' => $row));
    }

    public function save()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $id_banner = trim((string) $this->input->post('id_banner'));
        $is_edit   = ($id_banner !== '');

        $this->form_validation->set_rules('e_banner_title', 'Judul Banner', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('e_image_url', 'URL Gambar', 'required|trim|max_length[255]');

        if (!$this->form_validation->run()) {
            $this->_json(array('status' => false, 'message' => strip_tags(validation_errors())), 422);
            return;
        }

        $dt_start = trim((string) $this->input->post('dt_start'));
        $dt_end   = trim((string) $this->input->post('dt_end'));

        $data = array(
            'e_banner_title' => $this->input->post('e_banner_title'),
            'e_image_url'    => $this->input->post('e_image_url'),
            'e_link_url'     => $this->input->post('e_link_url'),
            'n_sort_order'   => (int) $this->input->post('n_sort_order'),
            'dt_start'       => $dt_start !== '' ? $dt_start : null,
            'dt_end'         => $dt_end !== '' ? $dt_end : null,
        );

        if ($is_edit) {
            $this->Banner_model->update($id_banner, $data);
            $this->_json(array('status' => true, 'message' => 'Banner berhasil diperbarui.'));
        } else {
            $data['f_active'] = 't';
            $this->Banner_model->insert($data);
            $this->_json(array('status' => true, 'message' => 'Banner berhasil ditambahkan.'));
        }
    }

    public function toggle_active($id)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $row = $this->Banner_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('status' => false, 'message' => 'Banner tidak ditemukan.'), 404);
            return;
        }

        $new_state = !($row['f_active'] === 't');
        $this->Banner_model->set_active($id, $new_state);
        $this->_json(array(
            'status'  => true,
            'message' => $new_state ? 'Banner ditayangkan.' : 'Banner disembunyikan.',
        ));
    }

    public function delete($id)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $row = $this->Banner_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('status' => false, 'message' => 'Banner tidak ditemukan.'), 404);
            return;
        }

        $this->Banner_model->delete($id);
        $this->_json(array('status' => true, 'message' => 'Banner berhasil dihapus.'));
    }
}
