<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_type_model extends CI_Model {

    public function get_summary()
    {
        $this->db->select('mst_product_type.id_product_type, mst_product_type.e_product_type,
                            COUNT(DISTINCT mst_category.id_category) AS n_categories,
                            COUNT(mst_product.id_product) AS n_products,
                            SUM(CASE WHEN mst_product.f_active = \'t\' THEN 1 ELSE 0 END) AS n_active_products', false);
        $this->db->from('mst_product_type');
        $this->db->join('mst_category', 'mst_category.id_product_type = mst_product_type.id_product_type', 'left');
        $this->db->join('mst_product', 'mst_product.id_category = mst_category.id_category', 'left');
        $this->db->group_by('mst_product_type.id_product_type, mst_product_type.e_product_type');
        $this->db->order_by('mst_product_type.e_product_type', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_by_id($id_product_type)
    {
        $this->db->where('id_product_type', $id_product_type);
        return $this->db->get('mst_product_type')->row_array();
    }

    public function get_categories_by_type($id_product_type)
    {
        $this->db->where('id_product_type', $id_product_type);
        $this->db->order_by('e_category', 'ASC');
        return $this->db->get('mst_category')->result_array();
    }

    public function get_products_by_type($id_product_type)
    {
        $this->db->select('mst_product.*, mst_category.e_category');
        $this->db->from('mst_product');
        $this->db->join('mst_category', 'mst_category.id_category = mst_product.id_category');
        $this->db->where('mst_category.id_product_type', $id_product_type);
        $this->db->order_by('mst_category.e_category', 'ASC');
        $this->db->order_by('mst_product.id_product', 'ASC');
        return $this->db->get()->result_array();
    }
}
