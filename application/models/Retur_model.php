<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Retur_model extends CI_Model {

    // Daftar retur + info transaksi/konsumen/produk + refund terakhir (kalau ada)
    public function get_all()
    {
        $this->db->select('trx_return.*,
                            trx_transaction.i_invoice,
                            mst_user.i_username,
                            mst_product.e_product,
                            trx_refund.id_refund,
                            trx_refund.v_refund_amount,
                            trx_refund.e_status AS e_refund_status', false);
        $this->db->from('trx_return');
        $this->db->join('trx_transaction', 'trx_transaction.id_transaction = trx_return.id_transaction');
        $this->db->join('mst_user', 'mst_user.id_user = trx_transaction.id_user');
        $this->db->join('mst_product', 'mst_product.id_product = trx_return.id_product');
        $this->db->join('trx_refund', 'trx_refund.id_return = trx_return.id_return', 'left');
        $this->db->order_by('trx_return.id_return', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_by_id($id)
    {
        $this->db->select('trx_return.*, trx_transaction.i_invoice, mst_user.i_username, mst_product.e_product');
        $this->db->from('trx_return');
        $this->db->join('trx_transaction', 'trx_transaction.id_transaction = trx_return.id_transaction');
        $this->db->join('mst_user', 'mst_user.id_user = trx_transaction.id_user');
        $this->db->join('mst_product', 'mst_product.id_product = trx_return.id_product');
        $this->db->where('trx_return.id_return', $id);
        return $this->db->get()->row_array();
    }

    // Cari transaksi lewat no invoice, dipakai form "Tambah Retur" biar admin
    // tinggal ketik invoice lalu pilih barang yang mau diretur dari situ.
    public function find_transaction_by_invoice($invoice)
    {
        $this->db->select('trx_transaction.id_transaction, trx_transaction.i_invoice, mst_user.i_username');
        $this->db->from('trx_transaction');
        $this->db->join('mst_user', 'mst_user.id_user = trx_transaction.id_user');
        $this->db->where('trx_transaction.i_invoice', $invoice);
        return $this->db->get()->row_array();
    }

    public function get_transaction_items($id_transaction)
    {
        $this->db->select('trx_transaction_detail.id_product, mst_product.e_product,
                            trx_transaction_detail.n_qty, trx_transaction_detail.v_price', false);
        $this->db->from('trx_transaction_detail');
        $this->db->join('mst_product', 'mst_product.id_product = trx_transaction_detail.id_product');
        $this->db->where('trx_transaction_detail.id_transaction', $id_transaction);
        return $this->db->get()->result_array();
    }

    public function insert($data)
    {
        $this->db->insert('trx_return', $data);
        return $this->db->insert_id();
    }

    public function update_status($id, $status)
    {
        $this->db->where('id_return', $id);
        return $this->db->update('trx_return', array('e_status' => $status));
    }

    public function insert_refund($data)
    {
        $this->db->insert('trx_refund', $data);
        return $this->db->insert_id();
    }

    public function get_refund_by_return($id_return)
    {
        $this->db->where('id_return', $id_return);
        return $this->db->get('trx_refund')->row_array();
    }

    public function update_refund_status($id_refund, $status)
    {
        $this->db->where('id_refund', $id_refund);
        return $this->db->update('trx_refund', array('e_status' => $status));
    }
}
