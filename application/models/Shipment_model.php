<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shipment_model extends CI_Model {

    public function get_all()
    {
        $this->db->select('trx_shipment.*, trx_transaction.i_invoice, mst_user.i_username,
                            mst_courier.e_courier_name, mst_courier.e_courier_code');
        $this->db->from('trx_shipment');
        $this->db->join('trx_transaction', 'trx_transaction.id_transaction = trx_shipment.id_transaction');
        $this->db->join('mst_user', 'mst_user.id_user = trx_transaction.id_user');
        $this->db->join('mst_courier', 'mst_courier.id_courier = trx_shipment.id_courier');
        $this->db->order_by('trx_shipment.id_shipment', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_by_id($id)
    {
        $this->db->select('trx_shipment.*, trx_transaction.i_invoice, trx_transaction.id_user, mst_user.i_username,
                            mst_courier.e_courier_name, mst_courier.e_courier_code, mst_courier.i_phone AS courier_phone');
        $this->db->from('trx_shipment');
        $this->db->join('trx_transaction', 'trx_transaction.id_transaction = trx_shipment.id_transaction');
        $this->db->join('mst_user', 'mst_user.id_user = trx_transaction.id_user');
        $this->db->join('mst_courier', 'mst_courier.id_courier = trx_shipment.id_courier');
        $this->db->where('trx_shipment.id_shipment', $id);
        return $this->db->get()->row_array();
    }

    // Nested table: barang apa saja & qty yang ada dalam pengiriman ini
    public function get_detail($id_shipment)
    {
        $this->db->select('trx_shipment_detail.*, mst_product.i_product, mst_product.e_product');
        $this->db->from('trx_shipment_detail');
        $this->db->join('mst_product', 'mst_product.id_product = trx_shipment_detail.id_product');
        $this->db->where('trx_shipment_detail.id_shipment', $id_shipment);
        return $this->db->get()->result_array();
    }

    public function insert_header($data)
    {
        $this->db->insert('trx_shipment', $data);
        return $this->db->insert_id();
    }

    public function insert_detail($data)
    {
        return $this->db->insert('trx_shipment_detail', $data);
    }

    public function update_status($id, $data)
    {
        $this->db->where('id_shipment', $id);
        return $this->db->update('trx_shipment', $data);
    }

    public function get_couriers()
    {
        $this->db->where('f_active', 't');
        $this->db->order_by('e_courier_name', 'ASC');
        return $this->db->get('mst_courier')->result_array();
    }
}
