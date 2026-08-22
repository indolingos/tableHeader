<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_type extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Product_type_model');
        $this->load->helper('url');
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
                    'message' => 'Anda tidak memiliki akses ke halaman ini.',
                ), 403);
            } else {
                redirect(site_url('product'));
            }
            return false;
        }
        return true;
    }

    private function _stock_status($stock)
    {
        $stock = (int) $stock;
        if ($stock == 0)   return 'Habis';
        if ($stock <= 20)  return 'Menipis';
        if ($stock <= 100) return 'Cukup';
        return 'Banyak';
    }

    public function index()
    {
        if (!$this->_require_admin()) return;

        $data['username'] = $this->username;
        $this->load->view('product_type_list', $data);
    }

    public function list_data()
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $rows = $this->Product_type_model->get_summary();

        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'id_product_type'   => $row['id_product_type'],
                'e_product_type'    => $row['e_product_type'],
                'n_categories'      => (int) $row['n_categories'],
                'n_products'        => (int) $row['n_products'],
                'n_active_products' => (int) $row['n_active_products'],
            );
        }

        $this->_json(array('status' => true, 'data' => $out));
    }

    public function detail($id_product_type = null)
    {
        if (!$this->_require_admin()) return;

        $id_product_type = (int) $id_product_type;
        $type = $this->Product_type_model->get_by_id($id_product_type);

        if (!$type) {
            show_404();
            return;
        }

        $data['username']        = $this->username;
        $data['id_product_type'] = $id_product_type;
        $data['e_product_type']  = $type['e_product_type'];
        $data['categories']      = $this->Product_type_model->get_categories_by_type($id_product_type);
        $this->load->view('product_type_detail', $data);
    }

    public function detail_data($id_product_type = null)
    {
        if (!$this->_require_ajax()) return;
        if (!$this->_require_admin()) return;

        $id_product_type = (int) $id_product_type;
        $rows = $this->Product_type_model->get_products_by_type($id_product_type);

        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'id_product'  => $row['id_product'],
                'i_product'   => $row['i_product'],
                'e_product'   => $row['e_product'],
                'id_category' => $row['id_category'],
                'e_category'  => $row['e_category'],
                'v_price'     => (float) $row['v_price'],
                'n_stock'     => (int) $row['n_stock'],
                'status'      => $this->_stock_status($row['n_stock']),
                'f_active'    => $row['f_active'],
            );
        }

        $this->_json(array('status' => true, 'data' => $out));
    }
}
