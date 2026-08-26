<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Brand_model extends CI_Model {

    public function get_all($include_inactive = true)
    {
        if (!$include_inactive) {
            $this->db->where('f_active', 't');
        }

        $this->db->order_by('e_brand', 'ASC');
        return $this->db->get('mst_brand')->result_array();
    }

    public function get_by_id($id)
    {
        $this->db->where('id_brand', $id);
        return $this->db->get('mst_brand')->row_array();
    }

    public function find_by_name($name, $exclude_id = null)
    {
        $this->db->where('LOWER(e_brand) =', strtolower(trim($name)), false);

        if ($exclude_id !== null && $exclude_id !== '') {
            $this->db->where('id_brand !=', $exclude_id);
        }

        return $this->db->get('mst_brand')->row_array();
    }

    public function insert($data)
    {
        return $this->db->insert('mst_brand', $data);
    }

    public function update($id, $data)
    {
        $this->db->where('id_brand', $id);
        return $this->db->update('mst_brand', $data);
    }

    public function set_active($id, $active)
    {
        $this->db->where('id_brand', $id);
        return $this->db->update('mst_brand', array(
            'f_active' => $active ? 't' : 'f'
        ));
    }
}
