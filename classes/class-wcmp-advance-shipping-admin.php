<?php

class WCMp_Advance_Shipping_Admin {

    public $settings;

    public function __construct() {
        add_action('admin_init', array(&$this, 'save_wcmp_table_rate_shipping'));
        add_action('admin_enqueue_scripts', array( $this, 'wcmp_table_rate_shipping_admin_enqueue_scripts'));
    }

    function load_class($class_name = '') {
        global $WCMp_Advance_Shipping;
        if ('' != $class_name) {
            require_once ($WCMp_Advance_Shipping->plugin_path . '/admin/class-' . esc_attr($WCMp_Advance_Shipping->token) . '-' . esc_attr($class_name) . '.php');
        } // End If Statement
    }
    
    /**
	 * Admin styles + scripts
	 */
    public function wcmp_table_rate_shipping_admin_enqueue_scripts() {
        global $WCMp_Advance_Shipping, $WCMp;
        $frontend_style_path = $WCMp_Advance_Shipping->plugin_url . 'assets/frontend/';
        $frontend_style_path = str_replace(array('http:', 'https:'), '', $frontend_style_path);
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_script( 'wcmp_advanced_shipping_frontend', $frontend_style_path . 'js/frontend' . $suffix . '.js', array( 'jquery' ), $WCMp_Advance_Shipping->version);
        $screen = get_current_screen();
        $wcmp_shipping_screen = apply_filters( 'wcmp_table_rate_js_inclide_pages', array('wcmp_page_vendors', 'toplevel_page_dc-vendor-shipping'));
        if (in_array($screen->id, $wcmp_shipping_screen)) {
            wp_enqueue_script( 'wcmp_advanced_shipping', $WCMp_Advance_Shipping->plugin_url . 'assets/global/js/advance-shipping.js', array( 'jquery' ), $WCMp_Advance_Shipping->version);
            $WCMp->localize_script('wcmp_advanced_shipping');
        }
    }

    public function save_wcmp_table_rate_shipping() {
        global $wpdb;
        if (isset($_POST['wcmp_table_rate']) && isset($_POST['shipping_class_id'])) {
            $table_rate_datas = $_POST['wcmp_table_rate'];
            $shipping_class_id = $_POST['shipping_class_id'];
            if (!empty($table_rate_datas) && is_array($table_rate_datas)) {
            	// Clear cache
				$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_wc_ship_%')" );
				$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_shipping-transient-version')" );

                foreach ($table_rate_datas as $shipping_method_id => $table_rate_data) {
					foreach ($table_rate_data as $data) {
                        $rate_id = $data['rate_id'];
                        $rate_class = $shipping_class_id;
                        $rate_condition = $data['rate_condition'];
                        $rate_min = isset($data['rate_min']) ? $data['rate_min'] : '';
                        $rate_max = isset($data['rate_max']) ? $data['rate_max'] : '';
                        $rate_priority = isset($data['rate_priority']) ? $data['rate_priority'] : 0;
						$rate_abort = isset($data['rate_abort']) ? $data['rate_abort'] : 0;
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
                            add_action('admin_notices', array(&$this, 'add_shipping_updated_notice'));
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
                            add_action('admin_notices', array(&$this, 'add_shipping_updated_notice'));
                        }
                    }
                }
            }
        }
    }

    public function add_shipping_updated_notice() {
        ?>
        <div id="message" class="updated settings-error notice is-dismissible">
            <p><strong><?php _e('Table rates Updated', 'wcmp-advance-shipping') ?></strong></p>
        </div>
        <?php
    }

}
