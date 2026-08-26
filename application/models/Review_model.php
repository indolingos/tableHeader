<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Review_model extends CI_Model {

    public function get_all()
    {
        $this->db->select('trx_review.*, mst_product.e_product, mst_user.i_username, trx_transaction.i_invoice', false);
        $this->db->from('trx_review');
        $this->db->join('mst_product', 'mst_product.id_product = trx_review.id_product');
        $this->db->join('mst_user', 'mst_user.id_user = trx_review.id_user');
        $this->db->join('trx_transaction', 'trx_transaction.id_transaction = trx_review.id_transaction', 'left');
        $this->db->order_by('trx_review.id_review', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_by_id($id)
    {
        $this->db->where('id_review', $id);
        return $this->db->get('trx_review')->row_array();
    }

    // Dipakai storefront: review yang sudah disetujui untuk 1 produk
    public function get_approved_by_product($id_product)
    {
        $this->db->select('trx_review.*, mst_user.i_username', false);
        $this->db->from('trx_review');
        $this->db->join('mst_user', 'mst_user.id_user = trx_review.id_user');
        $this->db->where('trx_review.id_product', $id_product);
        $this->db->where('trx_review.f_approved', 't');
        $this->db->order_by('trx_review.id_review', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_rating_summary($id_product)
    {
        $this->db->select('COUNT(*) AS n_review, AVG(n_rating) AS n_avg_rating', false);
        $this->db->from('trx_review');
        $this->db->where('id_product', $id_product);
        $this->db->where('f_approved', 't');
        return $this->db->get()->row_array();
    }

    public function insert($data)
    {
        return $this->db->insert('trx_review', $data);
    }

    public function set_approved($id, $approved)
    {
        $this->db->where('id_review', $id);
        return $this->db->update('trx_review', array('f_approved' => $approved ? 't' : 'f'));
    }

    public function delete($id)
    {
        $this->db->where('id_review', $id);
        return $this->db->delete('trx_review');
    }
}
