<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/wcmps-advance-shipping/wcmps-advance-shipping_template_table_rate.php
 *
 * @author 		WC Marketplace
 * @package 	wcmps-advance-shipping/Templates
 * @version     0.0.1
 */
global $WCMp_Advance_Shipping, $wpdb, $WCMp;
$vendor_user_id = get_current_user_id();
$shipping_class_id = get_user_meta($vendor_user_id, 'shipping_class_id', true);
if (!$shipping_class_id) {
    $shipping_term = get_term_by('slug', $vendor_data->user_data->user_login . '-' . $vendor_user_id, 'product_shipping_class', ARRAY_A);
    if (!$shipping_term) {
        $shipping_term = wp_insert_term($vendor_data->user_data->user_login . '-' . $vendor_user_id, 'product_shipping_class');
    }
    if (!is_wp_error($shipping_term)) {
        $shipping_term_id = $shipping_term['term_id'];
        update_user_meta($vendor_user_id, 'shipping_class_id', $shipping_term['term_id']);
        add_woocommerce_term_meta($shipping_term['term_id'], 'vendor_id', $vendor_user_id);
        add_woocommerce_term_meta($shipping_term['term_id'], 'vendor_shipping_origin', get_option('woocommerce_default_country'));
    }
}
$shipping_class_id = $shipping_term_id = get_user_meta($vendor_user_id, 'shipping_class_id', true);
$raw_zones = WC_Shipping_Zones::get_zones();
$raw_zones[] = array('id' => 0);
$zone_id = isset($postdata['zoneId']) ? absint($postdata['zoneId']) : 0;
$zone = new WC_Shipping_Zone($zone_id);
$raw_methods = $zone->get_shipping_methods();
foreach ($raw_methods as $raw_method) {
    if ($raw_method->id == 'table_rate') {
        $table_rates = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}woocommerce_shipping_table_rates WHERE `rate_class` = {$shipping_class_id} AND `shipping_method_id` = {$raw_method->instance_id} order by 'shipping_method_id' ", OBJECT);
        ?>
        <input type="hidden" name="shipping_method_id" value="<?php echo $raw_method->instance_id; ?>" />
        <input type="hidden" name="shipping_class_id" value="<?php echo $shipping_class_id; ?>" />
        <div class="panel panel-default pannel-outer-heading">
            <div class="panel-heading">
                <h3 class="wcmp_black_headding"><?php _e('Table Rates: ', 'wcmp-advance-shipping'); echo $zone->get_zone_name(); ?></h3>
            </div>
            <div class="wcmp_table_holder panel-body">
                <table class="table table-bordered responsive-table wcmp_table_rate_shipping widefat striped">
                    <thead>
                        <tr>
                            <th><?php _e('Select Shipping', 'wcmp-advance-shipping'); ?></th>
                            <th width="126"><?php _e('Condition', 'wcmp-advance-shipping'); ?></th>
                            <th><?php _e('Min', 'wcmp-advance-shipping'); ?></th>
                            <th><?php _e('Max', 'wcmp-advance-shipping'); ?></th>
                            <th><?php _e('Break', 'wcmp-advance-shipping'); ?></th>
                            <th><?php _e('Abort', 'wcmp-advance-shipping'); ?></th>
                            <th><?php _e('Row cost', 'wcmp-advance-shipping'); ?></th>
                            <th><?php _e('Item cost', 'wcmp-advance-shipping'); ?></th>
                            <th><?php _e('Kg cost', 'wcmp-advance-shipping'); ?></th>
                            <th><?php _e('% cost', 'wcmp-advance-shipping'); ?></th>
                            <th><?php _e('Label', 'wcmp-advance-shipping'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (count($table_rates) > 0) {
                            foreach ($table_rates as $table_rate) {
                                $WCMp_Advance_Shipping->template->get_template('wcmp_advance_shipping_template_table_rate_item.php', array('option' => $table_rate, 'shipping_method_id' => $raw_method->instance_id));
                            }
                        } else {
                            $option = new stdClass();
                            $option->rate_id = '';
                            $option->rate_class = $shipping_class_id;
                            $option->rate_condition = '';
                            $option->rate_min = '';
                            $option->rate_max = '';
                            $option->rate_priority = 0;
                            $option->rate_abort = 0;
                            $option->rate_cost = '';
                            $option->rate_cost_per_item = '';
                            $option->rate_cost_per_weight_unit = '';
                            $option->rate_cost_percent = '';
                            $option->rate_label = '';
                            $WCMp_Advance_Shipping->template->get_template('wcmp_advance_shipping_template_table_rate_item.php', array('option' => $option, 'shipping_method_id' => $raw_method->instance_id));

                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">
                                <button type="button" class="wcmp_add_tablerate_item btn btn-default"><?php _e('Add Shipping Rate', 'wcmp-advance-shipping') ?></button>
                            </td>
                            <td colspan="6">
                                <button style="float: right;" type="button" name="wcmp_remove_table_rate_item" class="wcmp_remove_table_rate_item btn btn-default"><?php _e('Delete selected rows', 'wcmp-advance-shipping') ?></button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <?php
    }
}
?>
