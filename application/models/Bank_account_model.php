<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bank_account_model extends CI_Model {

    public function get_user_summary()
    {
        $this->db->select('mst_user.id_user, mst_user.i_username,
                            COUNT(mst_bank_account.id_bank_account) AS n_account', false);
        $this->db->from('mst_user');
        $this->db->join('mst_bank_account', 'mst_bank_account.id_user = mst_user.id_user', 'inner');
        $this->db->group_by('mst_user.id_user, mst_user.i_username');
        $this->db->order_by('mst_user.i_username', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_by_user($id_user)
    {
        $this->db->where('id_user', $id_user);
        $this->db->order_by('f_primary', 'DESC');
        $this->db->order_by('id_bank_account', 'ASC');
        return $this->db->get('mst_bank_account')->result_array();
    }

    public function get_by_id($id)
    {
        $this->db->where('id_bank_account', $id);
        return $this->db->get('mst_bank_account')->row_array();
    }

    public function insert($data)
    {
        return $this->db->insert('mst_bank_account', $data);
    }

    public function update($id, $data)
    {
        $this->db->where('id_bank_account', $id);
        return $this->db->update('mst_bank_account', $data);
    }

    public function delete($id)
    {
        $this->db->where('id_bank_account', $id);
        return $this->db->delete('mst_bank_account');
    }
}
