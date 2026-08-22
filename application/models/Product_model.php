<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

    public function get_all($search = null, $include_inactive = false)
    {
        $this->db->distinct();
        $this->db->select('mst_product.*, mst_category.e_category');
        $this->db->from('mst_product');
        $this->db->join('mst_category', 'mst_category.id_category = mst_product.id_category', 'left');

        if (!$include_inactive) {
            $this->db->where('mst_product.f_active', 't');
        }

        if ($search !== null && $search !== '') {
            $search = strtolower(trim($search)); 

            $this->db->group_start();
            $this->db->like('LOWER(mst_product.i_product)', $search);
            $this->db->or_like('LOWER(mst_product.e_product)', $search);
            $this->db->group_end();
        }

        $this->db->order_by('mst_product.id_product', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_all_with_inactive()
    {
        $this->db->distinct();
        $this->db->select('mst_product.*, mst_category.e_category');
        $this->db->from('mst_product');
        $this->db->join('mst_category', 'mst_category.id_category = mst_product.id_category', 'left');
        $this->db->order_by('mst_product.id_product', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_categories()
    {
        $this->db->order_by('e_category', 'ASC');
        return $this->db->get('mst_category')->result_array();
    }

    public function insert($data)
    {
        return $this->db->insert('mst_product', $data);
    }

    public function get_by_id($id)
    {
        $this->db->select('mst_product.*, mst_category.e_category');
        $this->db->from('mst_product');
        $this->db->join('mst_category', 'mst_category.id_category = mst_product.id_category', 'left');
        $this->db->where('mst_product.id_product', $id);
        return $this->db->get()->row_array();
    }

    public function update($id, $data)
    {
        $this->db->where('id_product', $id);
        return $this->db->update('mst_product', $data);
    }

    public function delete($id)
    {
        $this->db->where('id_product', $id);
        return $this->db->update('mst_product', array('f_active' => 'f'));
    }

    public function restore($id)
    {
        $this->db->where('id_product', $id);
        return $this->db->update('mst_product', array('f_active' => 't'));
    }

    public function is_code_exists($i_product, $exclude_id = null)
    {
        $this->db->where('i_product', $i_product);
        if ($exclude_id !== null && $exclude_id !== '') {
            $this->db->where('id_product !=', $exclude_id);
        }
        return $this->db->get('mst_product')->num_rows() > 0;
    }

    public function find_by_code($i_product, $exclude_id = null)
    {
        $this->db->where('i_product', $i_product);
        if ($exclude_id !== null && $exclude_id !== '') {
            $this->db->where('id_product !=', $exclude_id);
        }
        return $this->db->get('mst_product')->row_array();
    }

    public function get_by_kode($i_product)
    {
        $this->db->select('mst_product.*, mst_category.e_category');
        $this->db->from('mst_product');
        $this->db->join('mst_category', 'mst_category.id_category = mst_product.id_category', 'left');
        $this->db->where('mst_product.i_product', $i_product);
        $this->db->where('mst_product.f_active', 't');
        return $this->db->get()->row_array();
    }
}