<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Courier extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Courier_model');
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
        $this->load->view('courier_list', $data);
    }

    public function list_data()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $rows = $this->Courier_model->get_summary();

        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'id_courier'      => $row['id_courier'],
                'e_courier_name'  => $row['e_courier_name'],
                'e_courier_code'  => $row['e_courier_code'],
                'e_contact_person' => $row['e_contact_person'],
                'i_phone'         => $row['i_phone'],
                'e_coverage_area' => $row['e_coverage_area'],
                'e_keterangan'    => $row['e_keterangan'],
                'n_shipments'     => (int) $row['n_shipments'],
                'f_active'        => $row['f_active'],
            );
        }

        $this->_json(array('status' => true, 'data' => $out));
    }

    public function get($id)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $row = $this->Courier_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('status' => false, 'message' => 'Kurir tidak ditemukan.'), 404);
            return;
        }
        $this->_json(array('status' => true, 'data' => $row));
    }

    // Nested table: daftar pengiriman yang pernah ditangani kurir ini
    public function shipments($id)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $rows = $this->Courier_model->get_shipments_by_courier($id);
        $this->_json(array('status' => true, 'data' => $rows));
    }

    public function save()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $id_courier = trim((string) $this->input->post('id_courier'));
        $is_edit    = ($id_courier !== '');

        $this->form_validation->set_rules('e_courier_name', 'Nama Kurir/Distributor', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('e_courier_code', 'Kode Kurir', 'required|trim|max_length[20]');

        if (!$this->form_validation->run()) {
            $this->_json(array('status' => false, 'message' => strip_tags(validation_errors())), 422);
            return;
        }

        $data = array(
            'e_courier_name'   => $this->input->post('e_courier_name'),
            'e_courier_code'   => strtoupper($this->input->post('e_courier_code')),
            'e_contact_person' => $this->input->post('e_contact_person'),
            'i_phone'          => $this->input->post('i_phone'),
            'e_coverage_area'  => $this->input->post('e_coverage_area'),
            'e_keterangan'     => $this->input->post('e_keterangan'),
        );

        if ($is_edit) {
            $data['dt_updated'] = date('Y-m-d H:i:s');
            $this->Courier_model->update($id_courier, $data);
            $this->_json(array('status' => true, 'message' => 'Data kurir berhasil diperbarui.'));
        } else {
            $data['f_active'] = 't';
            $this->Courier_model->insert($data);
            $this->_json(array('status' => true, 'message' => 'Kurir/distributor berhasil ditambahkan.'));
        }
    }

    public function toggle_active($id)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $row = $this->Courier_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('status' => false, 'message' => 'Kurir tidak ditemukan.'), 404);
            return;
        }

        $new_state = !($row['f_active'] === 't');
        $this->Courier_model->set_active($id, $new_state);
        $this->_json(array(
            'status'  => true,
            'message' => $new_state ? 'Kurir diaktifkan kembali.' : 'Kurir dinonaktifkan.',
        ));
    }
}
