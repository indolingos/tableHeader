<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Review extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Review_model');
        $this->load->model('Product_model');
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

    // Halaman admin: moderasi semua review
    public function index()
    {
        if (!$this->_require_admin()) return;

        $data['username'] = $this->username;
        $data['products']  = $this->Product_model->get_all();
        $data['customers'] = $this->User_model->get_all_customers();
        $this->load->view('review_list', $data);
    }

    public function list_data()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $rows = $this->Review_model->get_all();

        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'id_review'     => $row['id_review'],
                'e_product'     => $row['e_product'],
                'i_username'    => $row['i_username'],
                'i_invoice'     => $row['i_invoice'],
                'n_rating'      => (int) $row['n_rating'],
                'e_review_text' => $row['e_review_text'],
                'f_approved'    => $row['f_approved'],
                'dt_created'    => $row['dt_created'],
            );
        }

        $this->_json(array('status' => true, 'data' => $out));
    }

    // Admin mencatat review atas nama konsumen (aplikasi ini memang
    // dikelola penuh oleh admin, konsisten dengan modul Transaction)
    public function save()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $this->form_validation->set_rules('id_product', 'Produk', 'required|integer');
        $this->form_validation->set_rules('id_user', 'Konsumen', 'required|integer');
        $this->form_validation->set_rules('n_rating', 'Rating', 'required|integer|greater_than[0]|less_than_equal_to[5]');

        if (!$this->form_validation->run()) {
            $this->_json(array('status' => false, 'message' => strip_tags(validation_errors())), 422);
            return;
        }

        $id_transaction = trim((string) $this->input->post('id_transaction'));

        $data = array(
            'id_product'     => (int) $this->input->post('id_product'),
            'id_user'        => (int) $this->input->post('id_user'),
            'id_transaction' => $id_transaction !== '' ? (int) $id_transaction : null,
            'n_rating'       => (int) $this->input->post('n_rating'),
            'e_review_text'  => $this->input->post('e_review_text'),
            'f_approved'     => 't',
        );

        $this->Review_model->insert($data);
        $this->_json(array('status' => true, 'message' => 'Review berhasil dicatat.'));
    }

    public function toggle_approved($id)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $row = $this->Review_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('status' => false, 'message' => 'Review tidak ditemukan.'), 404);
            return;
        }

        $new_state = !($row['f_approved'] === 't');
        $this->Review_model->set_approved($id, $new_state);
        $this->_json(array(
            'status'  => true,
            'message' => $new_state ? 'Review ditampilkan ke publik.' : 'Review disembunyikan.',
        ));
    }

    public function delete($id)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $row = $this->Review_model->get_by_id($id);
        if (!$row) {
            $this->_json(array('status' => false, 'message' => 'Review tidak ditemukan.'), 404);
            return;
        }

        $this->Review_model->delete($id);
        $this->_json(array('status' => true, 'message' => 'Review berhasil dihapus.'));
    }

    // Dipanggil dari halaman produk (storefront) untuk menampilkan rating & review
    public function by_product($id_product)
    {
        if (!$this->_require_ajax()) return;

        $reviews = $this->Review_model->get_approved_by_product((int) $id_product);
        $summary = $this->Review_model->get_rating_summary((int) $id_product);

        $this->_json(array('status' => true, 'data' => array(
            'reviews' => $reviews,
            'summary' => array(
                'n_review'      => (int) $summary['n_review'],
                'n_avg_rating'  => $summary['n_avg_rating'] !== null ? round((float) $summary['n_avg_rating'], 1) : 0,
            ),
        )));
    }
}
