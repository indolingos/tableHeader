<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tax_model extends CI_Model {

    public function get_all()
    {
        $this->db->order_by('id_tax', 'ASC');
        return $this->db->get('mst_tax')->result_array();
    }

    public function get_by_id($id)
    {
        $this->db->where('id_tax', $id);
        return $this->db->get('mst_tax')->row_array();
    }

    public function insert($data)
    {
        return $this->db->insert('mst_tax', $data);
    }

    public function update($id, $data)
    {
        $this->db->where('id_tax', $id);
        return $this->db->update('mst_tax', $data);
    }

    public function set_active($id, $active)
    {
        $this->db->where('id_tax', $id);
        return $this->db->update('mst_tax', array('f_active' => $active ? 't' : 'f'));
    }
}
