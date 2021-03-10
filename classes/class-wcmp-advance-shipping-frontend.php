<?php

class WCMp_Advance_Shipping_Frontend {

    public function __construct() {
        global $WCMp
        add_action('wcmp_before_update_shipping_method', array(&$this, 'save_wcmp_table_rate_shipping'));
        add_action('wcmp_frontend_enqueue_scripts', array(&$this, 'frontend_styles'));
        add_filter( 'woocommerce_package_rates', array(&$this, 'wcmp_hide_table_rate_when_disabled' ), 99, 2 );
        remove_action('wp_ajax_wcmp-toggle-shipping-method', array($WCMp->ajax, 'wcmp_toggle_shipping_method'));
        add_action('wp_ajax_wcmp-toggle-shipping-method', array(&$this, 'wcmp_table_rate_toggle_shipping_method'));
    }

    public function save_wcmp_table_rate_shipping($postedData) {
        global $wpdb, $WCMp, $WCMp_Advance_Shipping;
        
        if(isset($postedData['settings'])){
            $wcmp_table_rate = array();
            $new_index = 0;
            $struc_arr =array();
            $shipping_class_id = $shipping_method_id = 0;
            foreach ($postedData['settings'] as $key => $value) {
                if (strpos($key, 'wcmp_table_rate') !== false) {
                    $key_arr = explode("[",$key);
                    if (count($key_arr) > 2) {
                        foreach ($key_arr as $index => $struc) {
                            $subkey = preg_replace('/[^a-zA-Z0-9_]/', '', $struc);
                            if($index == 0) {
                                continue;
                            }elseif($index == 1){
                                $new_index = $subkey;
                            }else{
                                $struc_arr[$subkey] = $value;
                            }
                            $wcmp_table_rate[$new_index] = $struc_arr;
                        }
                    } else {
                        foreach ($value as $index => $struc) {
                            $subkey = preg_replace('/[^a-zA-Z0-9_]/', '', $index);
                            $struc_arr[$subkey] = $struc;
                            $wcmp_table_rate[$key_arr[1]] = $struc_arr;
                        }
                    }
                }elseif( $key == 'shipping_method_id') {
                    $shipping_method_id = $value;
               }elseif( $key == 'shipping_class_id') {
                    $shipping_class_id = $value;
                }
            }
            if($wcmp_table_rate){
                // Clear cache
                $wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_wc_ship_%')" );
                $wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_shipping-transient-version')" );

                foreach ($wcmp_table_rate as $data) {
                    $rate_id = $data['rate_id'];
                    $rate_class = $shipping_class_id;
                    $rate_condition = $data['rate_condition'];
                    $rate_min = isset($data['rate_min']) ? $data['rate_min'] : '';
                    $rate_max = isset($data['rate_max']) ? $data['rate_max'] : '';
                    $rate_priority = isset($data['rate_priority']) ? 1 : 0;
                    $rate_abort = isset($data['rate_abort']) ? 1 : 0;
                    $rate_cost = isset($data['rate_cost']) ? rtrim(rtrim(number_format((double) $data['rate_cost'], 4, '.', ''), '0'), '.') : '';
                    $rate_cost_per_item = isset($data['rate_cost_per_item']) ? rtrim(rtrim(number_format((double) $data['rate_cost_per_item'], 4, '.', ''), '0'), '.') : '';
                    $rate_cost_per_weight_unit = isset($data['rate_cost_per_weight_unit']) ? rtrim(rtrim(number_format((double) $data['rate_cost_per_weight_unit'], 4, '.', ''), '0'), '.') : '';
                    $rate_cost_percent = isset($data['rate_cost_percent']) ? rtrim(rtrim(number_format((double) str_replace('%', '', $data['rate_cost_percent']), 2, '.', ''), '0'), '.') : '';
                    $rate_label = isset($data['rate_label']) ? $data['rate_label'] : '';
                    if ($rate_id > 0) {
                        $wpdb->update(
                                $wpdb->prefix . 'woocommerce_shipping_table_rates', array(
                            'rate_condition' => sanitize_title($rate_condition),
                            'rate_min' => $rate_min,
                            'rate_max' => $rate_max,
                            'rate_cost' => $rate_cost,
                            'rate_cost_per_item' => $rate_cost_per_item,
                            'rate_cost_per_weight_unit' => $rate_cost_per_weight_unit,
                            'rate_cost_percent' => $rate_cost_percent,
                            'rate_label' => $rate_label,
                            'rate_priority' => $rate_priority,
                            'rate_abort' => $rate_abort,
                                ), array(
                            'rate_id' => $rate_id
                                ), array(
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%d',
                            '%d'
                                ), array(
                            '%d'
                                )
                        );
                    } else {
                        $wpdb->insert("{$wpdb->prefix}woocommerce_shipping_table_rates", array(
                            'rate_class' => $rate_class,
                            'rate_condition' => sanitize_title($rate_condition),
                            'rate_min' => $rate_min,
                            'rate_max' => $rate_max,
                            'rate_priority' => $rate_priority,
                            'rate_abort' => $rate_abort,
                            'rate_cost' => $rate_cost,
                            'rate_cost_per_item' => $rate_cost_per_item,
                            'rate_cost_per_weight_unit' => $rate_cost_per_weight_unit,
                            'rate_cost_percent' => $rate_cost_percent,
                            'shipping_method_id' => $shipping_method_id,
                            'rate_label' => $rate_label
                                ), array(
                            '%d',
                            '%s',
                            '%d',
                            '%d',
                            '%d',
                            '%d',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%d',
                            '%s'
                                )
                        );
                    }
                }
                wc_add_notice(__('Table rates Updated', 'wcmp-advance-shipping'), 'success');
            }
        }
    }
    
    public function frontend_styles($is_vendor_dashboard) {
        global $WCMp_Advance_Shipping, $WCMp;
        $frontend_style_path = $WCMp_Advance_Shipping->plugin_url . 'assets/frontend/';
        $frontend_style_path = str_replace(array('http:', 'https:'), '', $frontend_style_path);
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_script( 'wcmp_advanced_shipping_frontend', $frontend_style_path . 'js/frontend' . $suffix . '.js', array( 'jquery' ), $WCMp_Advance_Shipping->version);
	wp_enqueue_script( 'wcmp_advanced_shipping', $WCMp_Advance_Shipping->plugin_url . 'assets/global/js/advance-shipping.js', array( 'jquery' ), $WCMp_Advance_Shipping->version);
        $WCMp->localize_script('wcmp_advanced_shipping');
        wp_register_style('wcmp_as_frontend', $frontend_style_path . 'css/frontend' . $suffix . '.css', array(), $WCMp_Advance_Shipping->version);
        wp_enqueue_style('wcmp_as_frontend');
       
    }
    // Hide table rate when no rates are found
    public function wcmp_hide_table_rate_when_disabled( $rates, $package ) {
        $table_rate = array();
        foreach ( $rates as $rate_id => $rate ) {
            if ( 'wcmp_vendor_shipping' === $rate->method_id  && strpos($rate->id, "table_rate") !== false ) {
                unset($rates);
            } else {
                $table_rate[ $rate_id ] = $rate;
            }
        }
        return !empty( $table_rate ) ? $table_rate : $rates;
    }

    public function wcmp_table_rate_toggle_shipping_method() {
        global $WCMp, $wpdb;
        $instance_id = isset($_POST['instance_id']) ? wc_clean($_POST['instance_id']) : 0;
        $zone_id = isset($_POST['zoneID']) ? wc_clean($_POST['zoneID']) : 0;
        $checked_data = isset($_POST['checked']) ? wc_clean($_POST['checked']) : '';
        $find_method_id_by_instance = $wpdb->get_results($wpdb->prepare("SELECT method_id FROM {$wpdb->prefix}wcmp_shipping_zone_methods WHERE `instance_id` = %d", $instance_id) );
        if ( !empty($find_method_id_by_instance) && $find_method_id_by_instance[0]->method_id == 'table_rate' ) {
            $shipping_class_id = get_user_meta(get_current_vendor_id(), 'shipping_class_id', true);
            $table_rates = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}woocommerce_shipping_table_rates WHERE `rate_class` = {$shipping_class_id} order by 'shipping_method_id' ", OBJECT);
            if (!empty($table_rates)) {
                foreach ($table_rates as $key => $value) {
                    $checked = ( $_POST['checked'] == 'true' ) ? 0 : 1;
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}woocommerce_shipping_table_rates SET rate_abort=%d WHERE rate_class=%d", $checked, $value->rate_class ) );
                }
            }
        }
        $data = array(
            'instance_id' => $instance_id,
            'zone_id' => $zone_id,
            'checked' => ( $checked_data == 'true' ) ? 1 : 0
        );
        if( !class_exists( 'WCMP_Shipping_Zone' ) ) {
            $WCMp->load_vendor_shipping();
        }
        $result = WCMP_Shipping_Zone::toggle_shipping_method($data);
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        $message = $data['checked'] ? __('Shipping method enabled successfully', 'dc-woocommerce-multi-vendor') : __('Shipping method disabled successfully', 'dc-woocommerce-multi-vendor');
        wp_send_json_success($message);
    }

}
