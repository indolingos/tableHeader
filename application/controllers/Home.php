<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Product_model');
        $this->load->model('Cart_model');
        $this->load->model('Transaction_model');
    }

    private function _is_admin()
    {
        return $this->username === 'admin';
    }

    public function index()
    {
        if (!$this->_is_admin()) {
            redirect(site_url('product'));
            return;
        }

        $data['username'] = $this->username;

        // Statistik ringkas untuk deskripsi toko di homepage
        $n_products    = count($this->Product_model->get_all());
        $customers     = $this->Cart_model->get_user_summary();
        $n_customers   = count($customers);
        $transactions  = $this->Transaction_model->get_all();
        $n_transactions = count($transactions);
        $finance       = $this->Transaction_model->get_finance_summary();

        $data['stats'] = array(
            'n_products'      => $n_products,
            'n_customers'     => $n_customers,
            'n_transactions'  => $n_transactions,
            'v_total_sales'   => $finance ? $finance['v_total'] : 0,
        );

        $this->load->view('home', $data);
    }
}
