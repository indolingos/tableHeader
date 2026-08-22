<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction_model extends CI_Model {

    public function get_all($status = null)
    {
        $this->db->select('trx_transaction.*, mst_user.i_username, mst_tax.e_tax_name');
        $this->db->from('trx_transaction');
        $this->db->join('mst_user', 'mst_user.id_user = trx_transaction.id_user');
        $this->db->join('mst_tax', 'mst_tax.id_tax = trx_transaction.id_tax', 'left');
        if ($status !== null && $status !== '') {
            $this->db->where('trx_transaction.e_status', $status);
        }
        $this->db->order_by('trx_transaction.id_transaction', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_by_id($id)
    {
        $this->db->select('trx_transaction.*, mst_user.i_username,
                            mst_address.e_recipient, mst_address.e_address_full, mst_address.e_city, mst_address.e_province, mst_address.i_phone,
                            mst_bank_account.e_bank_name, mst_bank_account.i_account_number, mst_bank_account.e_account_holder,
                            mst_tax.e_tax_name, mst_tax.n_percentage');
        $this->db->from('trx_transaction');
        $this->db->join('mst_user', 'mst_user.id_user = trx_transaction.id_user');
        $this->db->join('mst_address', 'mst_address.id_address = trx_transaction.id_address', 'left');
        $this->db->join('mst_bank_account', 'mst_bank_account.id_bank_account = trx_transaction.id_bank_account', 'left');
        $this->db->join('mst_tax', 'mst_tax.id_tax = trx_transaction.id_tax', 'left');
        $this->db->where('trx_transaction.id_transaction', $id);
        return $this->db->get()->row_array();
    }

    // Nested table: item barang yang dibeli pada transaksi ini
    public function get_detail($id_transaction)
    {
        $this->db->select('trx_transaction_detail.*, mst_product.i_product, mst_product.e_product, mst_category.e_category');
        $this->db->from('trx_transaction_detail');
        $this->db->join('mst_product', 'mst_product.id_product = trx_transaction_detail.id_product');
        $this->db->join('mst_category', 'mst_category.id_category = mst_product.id_category', 'left');
        $this->db->where('trx_transaction_detail.id_transaction', $id_transaction);
        $this->db->order_by('trx_transaction_detail.id_transaction_detail', 'ASC');
        return $this->db->get()->result_array();
    }

    // Nested table: histori pengiriman yang terkait transaksi ini
    public function get_shipments($id_transaction)
    {
        $this->db->select('trx_shipment.*, mst_courier.e_courier_name, mst_courier.e_courier_code');
        $this->db->from('trx_shipment');
        $this->db->join('mst_courier', 'mst_courier.id_courier = trx_shipment.id_courier');
        $this->db->where('trx_shipment.id_transaction', $id_transaction);
        $this->db->order_by('trx_shipment.id_shipment', 'ASC');
        return $this->db->get()->result_array();
    }

    public function insert_header($data)
    {
        $this->db->insert('trx_transaction', $data);
        return $this->db->insert_id();
    }

    public function insert_detail($data)
    {
        return $this->db->insert('trx_transaction_detail', $data);
    }

    public function update_status($id, $status)
    {
        $this->db->where('id_transaction', $id);
        return $this->db->update('trx_transaction', array(
            'e_status'   => $status,
            'dt_updated' => date('Y-m-d H:i:s'),
        ));
    }

    public function next_invoice_number()
    {
        $prefix = 'INV/' . date('Y') . '/' . date('m') . '/';
        $this->db->select("i_invoice");
        $this->db->like('i_invoice', $prefix, 'after');
        $this->db->order_by('id_transaction', 'DESC');
        $this->db->limit(1);
        $row = $this->db->get('trx_transaction')->row_array();

        $seq = 1;
        if ($row) {
            $parts = explode('/', $row['i_invoice']);
            $seq = (int) end($parts) + 1;
        }
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // Rekap untuk halaman Pajak & Keuangan
    public function get_finance_summary()
    {
        $this->db->select("
            COUNT(id_transaction) AS n_transactions,
            COALESCE(SUM(v_subtotal), 0) AS v_subtotal,
            COALESCE(SUM(v_tax), 0) AS v_tax,
            COALESCE(SUM(v_shipping_cost), 0) AS v_shipping_cost,
            COALESCE(SUM(v_total), 0) AS v_total
        ", false);
        $this->db->where('e_status !=', 'Dibatalkan');
        return $this->db->get('trx_transaction')->row_array();
    }

    public function get_finance_by_status()
    {
        $this->db->select('e_status, COUNT(id_transaction) AS n_transactions, COALESCE(SUM(v_total),0) AS v_total', false);
        $this->db->group_by('e_status');
        return $this->db->get('trx_transaction')->result_array();
    }
}
