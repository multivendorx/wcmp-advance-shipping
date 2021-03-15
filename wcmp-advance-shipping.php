<?php

/**
 * Plugin Name: WCMp Advance Shipping
 * Plugin URI: https://wc-marketplace.com/
 * Description: A free Addon Bridging WC Marketplace and WC Table Rate Shipping.
 * Author: WC Marketplace
 * Version: 1.1.3
 * Author URI: https://wc-marketplace.com/
 * 
 * Text Domain: wcmp-advance-shipping
 * Domain Path: /languages/
 */
if (!class_exists('WCMps_Dependencies')) {
    require_once trailingslashit(dirname(__FILE__)) . 'includes/class-wcmp-dependencies.php';
}
require_once trailingslashit(dirname(__FILE__)) . 'includes/wcmp-advance-shipping-core-functions.php';
require_once trailingslashit(dirname(__FILE__)) . 'wcmp-advance-shipping-config.php';
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
if (!defined('WCMPS_ADVANCE_SHIPPING_PLUGIN_TOKEN'))
    exit;
if (!defined('WCMPS_ADVANCE_SHIPPING_TEXT_DOMAIN'))
    exit;

if (!WCMps_Dependencies::woocommerce_plugin_active_check()) {
    add_action('admin_notices', 'wcmps_advance_shipping_woocommerce_inactive_notice');
} elseif (!WCMps_Dependencies::wc_marketplace_plugin_active_check()) {
    add_action('admin_notices', 'wcmps_advance_shipping_wcmp_inactive_notice');
} elseif (!WCMps_Dependencies::table_rate_shipping_plugin_active_check()) {
    add_action('admin_notices', 'wcmps_advance_shipping_table_rate_shipping_inactive_notice');
} else {
    if (!class_exists('WCMp_Advance_Shipping')) {
        require_once( trailingslashit(dirname(__FILE__)) . 'classes/class-wcmp-advance-shipping.php' );
        global $WCMp_Advance_Shipping;
        $WCMp_Advance_Shipping = new WCMp_Advance_Shipping(__FILE__);
        $GLOBALS['WCMp_Advance_Shipping'] = $WCMp_Advance_Shipping;
    }
}
?>
