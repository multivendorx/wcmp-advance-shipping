<?php

class WCMp_Advance_Shipping {

    public $plugin_url;
    public $plugin_path;
    public $version;
    public $token;
    public $text_domain;
    public $library;
    public $shortcode;
    public $admin;
    public $frontend;
    public $template;
    public $ajax;
    private $file;
    public $settings;
    public $dc_wp_fields;
    public $wcmps_table_rate;

    public function __construct($file) {
        $this->file = $file;
        $this->plugin_url = trailingslashit(plugins_url('', $plugin = $file));
        $this->plugin_path = trailingslashit(dirname($file));
        $this->token = WCMPS_ADVANCE_SHIPPING_PLUGIN_TOKEN;
        $this->text_domain = WCMPS_ADVANCE_SHIPPING_TEXT_DOMAIN;
        $this->version = WCMPS_ADVANCE_SHIPPING_PLUGIN_VERSION;
        add_action('init', array(&$this, 'init'), 15);
        add_filter('is_wcmp_vendor_shipping_tab_enable', array(&$this, 'is_wcmp_table_rate_shipping_enable'), 10, 2);
    }

    /**
     * initilize plugin on WP init
     */
    function init() {
        // Init Text Domain
        $this->load_plugin_textdomain();

        if (is_admin()) {
            $this->load_class('admin');
            $this->admin = new WCMp_Advance_Shipping_Admin();
        }

        // Init ajax
        if (defined('DOING_AJAX')) {
            $this->load_class('ajax');
            $this->ajax = new WCMp_Advance_Shipping_Ajax();
        }

        if (!is_admin() || defined('DOING_AJAX')) {
            $this->load_class('frontend');
            $this->frontend = new WCMp_Advance_Shipping_Frontend();
        }
        // init templates
        $this->load_class('template');
        $this->template = new WCMp_Advance_Shipping_Template();
    }
    
    public function is_wcmp_table_rate_shipping_enable($is_shipping_enable, $is_enable){
        $is_enable_table_rate = false;
        $raw_zones = WC_Shipping_Zones::get_zones();
        $raw_zones[] = array('id' => 0);
        foreach ($raw_zones as $raw_zone) {
            $zone = new WC_Shipping_Zone($raw_zone['id']);
            $raw_methods = $zone->get_shipping_methods();
            foreach ($raw_methods as $raw_method) {
                if ($raw_method->id == 'table_rate'){
                    $is_enable_table_rate = true;
                }
            }
        }
        if($is_enable && $is_enable_table_rate){
            $is_shipping_enable = true;
        }
        return $is_shipping_enable;
    }

    /**
     * Load Localisation files.
     *
     * Note: the first-loaded translation file overrides any following ones if the same translation is present
     *
     * @access public
     * @return void
     */
    public function load_plugin_textdomain() {
        $locale = is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
        $locale = apply_filters('plugin_locale', $locale, 'wcmp-advance-shipping');
        load_textdomain('wcmp-advance-shipping', WP_LANG_DIR . '/wcmp-advance-shipping/wcmp-advance-shipping-' . $locale . '.mo');
        load_plugin_textdomain('wcmp-advance-shipping', false, plugin_basename(dirname(dirname(__FILE__))) . '/languages');
    }

    public function load_class($class_name = '') {
        if ('' != $class_name && '' != $this->token) {
            require_once ('class-' . esc_attr($this->token) . '-' . esc_attr($class_name) . '.php');
        } // End If Statement
    }

}
