<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function get_by_username($username)
    {
        $this->db->where('i_username', $username);
        return $this->db->get('mst_user')->row_array();
    }

    public function get_by_id($id)
    {
        $this->db->where('id_user', $id);
        return $this->db->get('mst_user')->row_array();
    }

    public function get_download_settings($user_id)
    {
        $this->db->select('t_download_settings');
        $this->db->where('id_user', $user_id);
        $row = $this->db->get('mst_user')->row_array();
        return $row ? $row['t_download_settings'] : null;
    }

    public function save_download_settings($user_id, $json)
    {
        $this->db->where('id_user', $user_id);
        return $this->db->update('mst_user', array('t_download_settings' => $json));
    }

    public function username_exists($username)
    {
        $this->db->where('i_username', $username);
        return $this->db->get('mst_user')->num_rows() > 0;
    }

    public function create($username, $hashed_password)
    {
        $data = array(
            'i_username' => $username,
            'c_password' => $hashed_password,
            'f_active'   => 't',
        );
        return $this->db->insert('mst_user', $data);
    }
}
