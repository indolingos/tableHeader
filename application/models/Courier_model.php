<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Courier_model extends CI_Model {

    public function get_all($include_inactive = true)
    {
        if (!$include_inactive) {
            $this->db->where('f_active', 't');
        }
        $this->db->order_by('e_courier_name', 'ASC');
        return $this->db->get('mst_courier')->result_array();
    }

    public function get_summary()
    {
        $this->db->select('mst_courier.*,
                            COUNT(DISTINCT trx_shipment.id_shipment) AS n_shipments', false);
        $this->db->from('mst_courier');
        $this->db->join('trx_shipment', 'trx_shipment.id_courier = mst_courier.id_courier', 'left');
        $this->db->group_by('mst_courier.id_courier');
        $this->db->order_by('mst_courier.e_courier_name', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_by_id($id)
    {
        $this->db->where('id_courier', $id);
        return $this->db->get('mst_courier')->row_array();
    }

    public function get_shipments_by_courier($id_courier)
    {
        $this->db->select('trx_shipment.*, trx_transaction.i_invoice, mst_user.i_username');
        $this->db->from('trx_shipment');
        $this->db->join('trx_transaction', 'trx_transaction.id_transaction = trx_shipment.id_transaction');
        $this->db->join('mst_user', 'mst_user.id_user = trx_transaction.id_user');
        $this->db->where('trx_shipment.id_courier', $id_courier);
        $this->db->order_by('trx_shipment.id_shipment', 'DESC');
        return $this->db->get()->result_array();
    }

    public function insert($data)
    {
        return $this->db->insert('mst_courier', $data);
    }

    public function update($id, $data)
    {
        $this->db->where('id_courier', $id);
        return $this->db->update('mst_courier', $data);
    }

    public function set_active($id, $active)
    {
        $this->db->where('id_courier', $id);
        return $this->db->update('mst_courier', array('f_active' => $active ? 't' : 'f'));
    }
}
