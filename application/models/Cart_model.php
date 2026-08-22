<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart_model extends CI_Model {

    public function get_by_user($id_user)
    {
        $this->db->select('trx_cart.id_cart, trx_cart.id_product, trx_cart.n_qty, trx_cart.dt_updated,
                            mst_product.i_product, mst_product.e_product, mst_product.v_price, mst_product.n_stock,
                            mst_category.e_category');
        $this->db->from('trx_cart');
        $this->db->join('mst_product', 'mst_product.id_product = trx_cart.id_product');
        $this->db->join('mst_category', 'mst_category.id_category = mst_product.id_category', 'left');
        $this->db->where('trx_cart.id_user', $id_user);
        $this->db->order_by('trx_cart.id_cart', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_user_summary()
    {
        $this->db->select('mst_user.id_user, mst_user.i_username,
                            COUNT(trx_cart.id_cart) AS n_items,
                            SUM(trx_cart.n_qty) AS n_total_qty,
                            SUM(trx_cart.n_qty * mst_product.v_price) AS v_total,
                            MAX(trx_cart.dt_updated) AS dt_last_updated', false);
        $this->db->from('trx_cart');
        $this->db->join('mst_user', 'mst_user.id_user = trx_cart.id_user');
        $this->db->join('mst_product', 'mst_product.id_product = trx_cart.id_product');
        $this->db->group_by('mst_user.id_user, mst_user.i_username');
        $this->db->order_by('mst_user.i_username', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_all_grouped()
    {
        $this->db->select('trx_cart.id_cart, trx_cart.id_product, trx_cart.n_qty, trx_cart.dt_updated,
                            mst_user.id_user, mst_user.i_username,
                            mst_product.i_product, mst_product.e_product, mst_product.v_price, mst_product.n_stock,
                            mst_category.e_category');
        $this->db->from('trx_cart');
        $this->db->join('mst_user', 'mst_user.id_user = trx_cart.id_user');
        $this->db->join('mst_product', 'mst_product.id_product = trx_cart.id_product');
        $this->db->join('mst_category', 'mst_category.id_category = mst_product.id_category', 'left');
        $this->db->order_by('mst_user.i_username', 'ASC');
        $this->db->order_by('trx_cart.id_cart', 'ASC');
        return $this->db->get()->result_array();
    }

    public function upsert($id_user, $id_product, $qty)
    {
        $this->db->where('id_user', $id_user);
        $this->db->where('id_product', $id_product);
        $existing = $this->db->get('trx_cart')->row_array();

        if ($existing) {
            $this->db->where('id_cart', $existing['id_cart']);
            return $this->db->update('trx_cart', array(
                'n_qty'      => $qty,
                'dt_updated' => date('Y-m-d H:i:s'),
            ));
        }

        return $this->db->insert('trx_cart', array(
            'id_user'    => $id_user,
            'id_product' => $id_product,
            'n_qty'      => $qty,
            'dt_created' => date('Y-m-d H:i:s'),
            'dt_updated' => date('Y-m-d H:i:s'),
        ));
    }

    public function remove($id_user, $id_product)
    {
        $this->db->where('id_user', $id_user);
        $this->db->where('id_product', $id_product);
        return $this->db->delete('trx_cart');
    }
}
