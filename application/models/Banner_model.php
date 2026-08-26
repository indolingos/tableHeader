<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Banner_model extends CI_Model {

    public function get_all()
    {
        $this->db->order_by('n_sort_order', 'ASC');
        $this->db->order_by('id_banner', 'DESC');
        return $this->db->get('mst_banner')->result_array();
    }

    public function get_by_id($id)
    {
        $this->db->where('id_banner', $id);
        return $this->db->get('mst_banner')->row_array();
    }

    public function insert($data)
    {
        return $this->db->insert('mst_banner', $data);
    }

    public function update($id, $data)
    {
        $this->db->where('id_banner', $id);
        return $this->db->update('mst_banner', $data);
    }

    public function set_active($id, $active)
    {
        $this->db->where('id_banner', $id);
        return $this->db->update('mst_banner', array('f_active' => $active ? 't' : 'f'));
    }

    public function delete($id)
    {
        $this->db->where('id_banner', $id);
        return $this->db->delete('mst_banner');
    }
}
