<?php

class WCMp_Advance_Shipping_Template {

    public $template_url;

    public function __construct() {
        $this->template_url = 'wcmps-advance-shipping/';
        add_filter('wcmp_vendor_backend_shipping_methods_edit_form_fields', array(&$this, 'wcmps_advance_shipping_table_rate'), 10, 4);
       //add_action('wcmp_before_shipping_form_end_vendor_dashboard', array(&$this, 'output_wcmps_advance_shipping_table_rate'));
        add_filter('wcmp_vendor_shipping_methods', array(&$this, 'add_fields_wcmp_vendor_shipping_methods'));
        add_action('wcmp_vendor_shipping_table_rate_configure_form_fields', array(&$this, 'output_wcmps_advance_shipping_table_rate'), 10, 2);
    }

    public function add_fields_wcmp_vendor_shipping_methods($vendor_shippings) {
        $vendor_shippings['table_rate'] = __('Table Rates', 'wcmp-advance-shipping');
        return $vendor_shippings;
    }

    public function output_wcmps_advance_shipping_table_rate($shipping_method, $postdata) { 
        ?> 
        <div id="wrapper-<?php echo $shipping_method['id'] ?>">
            <div class="form-group">
                <label for="" class="control-label col-sm-3 col-md-3"><?php _e( 'Method Title', 'dc-woocommerce-multi-vendor' ); ?></label>
                <div class="col-md-9 col-sm-9">
                    <input id="method_title_lp" class="form-control" type="text" name="title" value="<?php echo isset($shipping_method['title']) ? $shipping_method['title'] : ''; ?>" placholder="<?php _e( 'Enter method title', 'dc-woocommerce-multi-vendor' ); ?>">
                </div>
            </div>
             
              <input type="hidden" id="method_description_lp" name="description" value="<?php echo isset($shipping_method['settings']['description']) ? $shipping_method['settings']['description'] : ''; ?>" />
             </div> 
         <?php
         $this->get_template('wcmp_advance_shipping_template_table_rate.php', array('shipping_method' => $shipping_method, 'postdata' => $postdata));
     }

     public function wcmps_advance_shipping_table_rate($settings_html, $user_id, $zone_id, $vendor_shipping_method){ 
        global $wpdb, $WCMp_Advance_Shipping, $WCMp;
        
        $shipping_class_id = get_user_meta($user_id, 'shipping_class_id', true);
        $zone = new WC_Shipping_Zone($zone_id);
        $raw_methods = $zone->get_shipping_methods();
        foreach ($raw_methods as $raw_method) {
            if ($raw_method->id == 'table_rate') {
                   
                $settings_html = '<!-- Table Rates -->'
                            . '<div class="shipping_form" id="'.$vendor_shipping_method['id'].'">'  
                            .'<input type="hidden" id="method_description_lp" name="description" value="'.$vendor_shipping_method['settings']['description'].'" />'
                             . '<div class="form-group">'
                            . '<label for="" class="control-label col-sm-3 col-md-3">'.__( 'Method Title', 'dc-woocommerce-multi-vendor' ).'</label>'
                            . '<div class="col-md-9 col-sm-9">'
                            . '<input id="method_title_fs" class="form-control" type="text" name="title" value="'.$vendor_shipping_method['title'].'" placholder="'.__( 'Enter method title', 'dc-woocommerce-multi-vendor' ).'">'
                            . '</div></div>'
                            . '<!--div class="form-group">'
                            . '<label for="" class="control-label col-sm-3 col-md-3">'.__( 'Description', 'dc-woocommerce-multi-vendor' ).'</label>'
                            . '<div class="col-md-9 col-sm-9">'
                            . '<textarea id="method_description_lp" class="form-control" name="method_description">'.$vendor_shipping_method['settings']['description'].'</textarea>'
                            . '</div></div--></div>'
                             .'<input type="hidden" name="shipping_method_id" value="'.$raw_method->instance_id.'" />'
                           .' <input type="hidden" name="shipping_class_id" value="'.$shipping_class_id.'" />'
                            .'<div class="panel panel-default pannel-outer-heading">'
                            .' <div class="panel-heading">'
                            .'<h3 class="wcmp_black_headding">'. __('Table Rates: ', 'wcmp-advance-shipping') . $zone->get_zone_name() .'</h3>'
                            .'</div>'
                            . '<div class="wcmp_table_holder panel-body">'
                            . '<table class="table table-bordered responsive-table wcmp_table_rate_shipping widefat striped">'
                            .'<thead><tr>'.'<th>'
                            . __('Select Shipping', 'wcmp-advance-shipping') .'</th>'
                            .'<th width="126">'. __('Condition', 'wcmp-advance-shipping').'</th>'
                            .'<th>'. __('Min', 'wcmp-advance-shipping') .'</th>'
                                .'<th>'. __('Max', 'wcmp-advance-shipping') .'</th>'
                                .'<th>'. __('Break', 'wcmp-advance-shipping') .'</th>'
                                .'<th>'. __('Abort', 'wcmp-advance-shipping') .'</th>'
                                .'<th>'. __('Row cost', 'wcmp-advance-shipping').'</th>'
                                .'<th>'.__('Item cost', 'wcmp-advance-shipping') .'</th>'
                                .'<th>'.__('Kg cost', 'wcmp-advance-shipping') .'</th>'
                                .'<th>'. __('% cost', 'wcmp-advance-shipping') .'</th>'
                                .'<th>'. __('Label', 'wcmp-advance-shipping') .'</th>'
                            .'</tr></thead>'
                            .'<tbody>';
                $table_rates = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}woocommerce_shipping_table_rates WHERE `rate_class` = {$shipping_class_id} AND `shipping_method_id` = {$raw_method->instance_id} order by 'shipping_method_id' ", OBJECT);
                if( $table_rates ) {
                    foreach ($table_rates as $table_rate) {
                        ob_start();
                        $WCMp_Advance_Shipping->template->get_template('wcmp_advance_shipping_template_table_rate_item.php', array('option' => $table_rate, 'shipping_method_id' => $raw_method->instance_id));
                        $item_row = ob_get_clean();
                        $settings_html .= $item_row;
                    }
                } else {
                    ob_start();
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
                    $item_row = ob_get_clean();
                    $settings_html .= $item_row;
                }
                
                $settings_html .= '</tbody>' 
                        .'<tfoot><tr>'
                       .'<td colspan="5"> 
                                <button type="button" class="wcmp_add_tablerate_item btn btn-default">'. __('Add Shipping Rate', 'wcmp-advance-shipping') .'</button>
                            </td>'
                           .'<td colspan="6">
                                <button style="float: right;" type="button" name="wcmp_remove_table_rate_item" class="wcmp_remove_table_rate_item btn btn-default">'. __('Delete selected rows', 'wcmp-advance-shipping') .'</button>
                            </td>'
                       .' </tr> </tfoot>'         
                    .'</table>'
                    .'</div></div>';

                return $settings_html;
            }
        }
    }
       

    /**
     * Get other templates (e.g. product attributes) passing attributes and including the file.
     *
     * @access public
     * @param mixed $template_name
     * @param array $args (default: array())
     * @param string $template_path (default: '')
     * @param string $default_path (default: '')
     * @return void
     */
    public function get_template($template_name, $args = array(), $template_path = '', $default_path = '') {

        if ($args && is_array($args))
            extract($args);

        $located = $this->locate_template($template_name, $template_path, $default_path);

        include ($located);
    }

    /**
     * Locate a template and return the path for inclusion.
     *
     * This is the load order:
     *
     * 		yourtheme		/	$template_path	/	$template_name
     * 		yourtheme		/	$template_name
     * 		$default_path	/	$template_name
     *
     * @access public
     * @param mixed $template_name
     * @param string $template_path (default: '')
     * @param string $default_path (default: '')
     * @return string
     */
    public function locate_template($template_name, $template_path = '', $default_path = '') {
        global $woocommerce, $WCMp_Advance_Shipping;

        if (!$template_path)
            $template_path = $this->template_url;
        if (!$default_path)
            $default_path = $WCMp_Advance_Shipping->plugin_path . '/templates/';

        // Look within passed path within the theme - this is priority
        $template = locate_template(array(trailingslashit($template_path) . $template_name, $template_name));

        // Get default template
        if (!$template)
            $template = $default_path . $template_name;

        // Return what we found
        return $template;
    }

    /**
     * Get template part (for templates like the shop-loop).
     *
     * @access public
     * @param mixed $slug
     * @param string $name (default: '')
     * @return void
     */
    public function get_template_part($slug, $name = '') {
        global $WCMp_Advance_Shipping;
        $template = '';

        // Look in yourtheme/slug-name.php and yourtheme/woocommerce/slug-name.php
        if ($name)
            $template = $this->locate_template(array("{$slug}-{$name}.php", "{$this->template_url}{$slug}-{$name}.php"));

        // Get default slug-name.php
        if (!$template && $name && file_exists($WCMp_Advance_Shipping->plugin_path . "templates/{$slug}-{$name}.php"))
            $template = $WCMp_Advance_Shipping->plugin_path . "templates/{$slug}-{$name}.php";

        // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/woocommerce/slug.php
        if (!$template)
            $template = $this->locate_template(array("{$slug}.php", "{$this->template_url}{$slug}.php"));

        echo $template;

        if ($template)
            load_template($template, false);
    }

}
