<?php

class WCMp_Advance_Shipping_Ajax {

    public function __construct() {
        add_action('wp_ajax_delete_table_rate_shipping_row', array(&$this, 'delete_table_rate_shipping_row'));
        add_action('wp_ajax_nopriv_delete_table_rate_shipping_row', array(&$this, 'delete_table_rate_shipping_row'));
        
    }

    public function delete_table_rate_shipping_row() {
        if (is_array($_POST['rate_id'])) {
            $rate_ids = array_map('intval', $_POST['rate_id']);
        } else {
            $rate_ids = array(intval($_POST['rate_id']));
        }

        if (!empty($rate_ids)) {
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->prefix}woocommerce_shipping_table_rates WHERE rate_id IN (" . implode(',', $rate_ids) . ")");
        }
        die();
    }

}

