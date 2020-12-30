<?php

class WCMp_Advance_Shipping_Admin {

    public $settings;

    public function __construct() {
        add_action('admin_init', array(&$this, 'save_wcmp_table_rate_shipping'));
        add_action('admin_enqueue_scripts', array( $this, 'wcmp_table_rate_shipping_admin_enqueue_scripts'));
        add_action('wcmp_vendor_preview_tabs_post', array( $this, 'wcmp_vendor_policy'));
        add_action('wcmp_vendor_preview_tabs_form_post', array( $this, 'wcmp_vendor_policy_content'));
        add_action('wcmp_vendor_details_update', array( $this, 'save_wcmp_vendor_shipping_policy'), 10, 2);
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
        wp_enqueue_style('admin_css', $WCMp_Advance_Shipping->plugin_url . 'assets/admin/css/admin.css', array(), $WCMp_Advance_Shipping->version);
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
    public function wcmp_vendor_policy($is_approved_vendor){
        if($is_approved_vendor && get_wcmp_vendor_settings( 'is_policy_on', 'general' ) == 'Enable' ) {
            ?>
            <li> 
                <a href="#vendor-policy"><span class="dashicons dashicons-lock"></span> <?php echo __('Vendor Policy', 'wcmp-advance-shipping'); ?></a>
            </li>
            <?php
        }    
    }
    public function wcmp_vendor_policy_content($is_approved_vendor){
        if ($is_approved_vendor && get_wcmp_vendor_settings( 'is_policy_on', 'general' ) == 'Enable' ) {
        global $WCMp;
        $content = '';
            ?>
            <div id="vendor-policy">
                <?php
                $vendor = get_wcmp_vendor($_GET['ID']);
                $content .= '<h2>' . __('Policy Settings', 'wcmp-advance-shipping') . '</h2>';
                $content .= '<div class="wcmp-form-field">';
                $policy_tab_options = array(
                                    "vendor_shipping_policy" => array('label' => __('Shipping Policy', 'dc-woocommerce-multi-vendor'), 'type' => 'wpeditor', 'id' => 'vendor_shipping_policy', 'label_for' => 'vendor_shipping_policy', 'name' => 'vendor_shipping_policy', 'cols' => 50, 'rows' => 6, 'value' => $vendor->shipping_policy), // Textarea
                                    "vendor_refund_policy" => array('label' => __('Refund Policy', 'dc-woocommerce-multi-vendor'), 'type' => 'wpeditor', 'id' => 'vendor_refund_policy', 'label_for' => 'vendor_refund_policy', 'name' => 'vendor_refund_policy', 'cols' => 50, 'rows' => 6, 'value' => $vendor->refund_policy), // Textarea
                                    "vendor_cancellation_policy" => array('label' => __('Cancellation/Return/Exchange Policy', 'dc-woocommerce-multi-vendor'), 'type' => 'wpeditor', 'id' => 'vendor_cancellation_policy', 'label_for' => 'vendor_cancellation_policy', 'name' => 'vendor_cancellation_policy', 'cols' => 50, 'rows' => 6, 'value' => $vendor->cancellation_policy), // Textarea
                                );  
                $content .= '</div>';
                
                echo apply_filters('wcmp_vendor_policy_settings', $content);
                $WCMp->wcmp_wp_fields->dc_generate_form_field( $policy_tab_options );
                ?>
            </div>
            <?php
        }
    }

    public function save_wcmp_vendor_shipping_policy($post, $vendor){
    // Policy tab data save
        if ( get_wcmp_vendor_settings( 'is_policy_on', 'general' ) == 'Enable' ) {
            if ( isset( $post['vendor_shipping_policy'] ) ) {
                update_user_meta( $vendor->id, 'vendor_shipping_policy', stripslashes( html_entity_decode( $post['vendor_shipping_policy'], ENT_QUOTES, get_bloginfo( 'charset' ) ) ) );
            }
            if ( isset( $post['vendor_refund_policy'] ) ) {
                update_user_meta( $vendor->id, 'vendor_refund_policy', stripslashes( html_entity_decode( $post['vendor_refund_policy'], ENT_QUOTES, get_bloginfo( 'charset' ) ) ) );
            }
            if ( isset( $post['vendor_cancellation_policy'] ) ) {
                update_user_meta( $vendor->id, 'vendor_cancellation_policy', stripslashes( html_entity_decode( $post['vendor_cancellation_policy'], ENT_QUOTES, get_bloginfo( 'charset' ) ) ) );
            }
        }
    }
}
