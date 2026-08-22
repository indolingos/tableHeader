<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Address_model extends CI_Model {

    // Ringkasan jumlah alamat per konsumen (master)
    public function get_user_summary()
    {
        $this->db->select('mst_user.id_user, mst_user.i_username,
                            COUNT(mst_address.id_address) AS n_address', false);
        $this->db->from('mst_user');
        $this->db->join('mst_address', 'mst_address.id_user = mst_user.id_user', 'inner');
        $this->db->group_by('mst_user.id_user, mst_user.i_username');
        $this->db->order_by('mst_user.i_username', 'ASC');
        return $this->db->get()->result_array();
    }

    // Semua alamat (dipakai untuk detail per user - nested table)
    public function get_by_user($id_user)
    {
        $this->db->where('id_user', $id_user);
        $this->db->order_by('f_primary', 'DESC');
        $this->db->order_by('id_address', 'ASC');
        return $this->db->get('mst_address')->result_array();
    }

    public function get_by_id($id)
    {
        $this->db->where('id_address', $id);
        return $this->db->get('mst_address')->row_array();
    }

    public function insert($data)
    {
        return $this->db->insert('mst_address', $data);
    }

    public function update($id, $data)
    {
        $this->db->where('id_address', $id);
        return $this->db->update('mst_address', $data);
    }

    public function delete($id)
    {
        $this->db->where('id_address', $id);
        return $this->db->delete('mst_address');
    }
}
