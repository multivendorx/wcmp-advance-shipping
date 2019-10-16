<?php
/**
 * Error notices for woocommerce plugin not found
 */
if (!function_exists('wcmps_advance_shipping_woocommerce_inactive_notice')) {

    function wcmps_advance_shipping_woocommerce_inactive_notice() {
        ?>
        <div id="message" class="error">
            <p><?php printf(__('%sWCMp Advance Shipping is inactive.%s The %sWooCommerce plugin%s must be active for the WCMp Advance Shipping to work. Please %sinstall & activate WooCommerce%s', WCMPS_ADVANCE_SHIPPING_TEXT_DOMAIN), '<strong>', '</strong>', '<a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . admin_url('plugin-install.php?tab=search&s=woocommerce') . '">', '&nbsp;&raquo;</a>'); ?></p>
        </div>
        <?php
    }

}
/**
 * Error notices for wc-marketplace plugin not found
 */
if (!function_exists('wcmps_advance_shipping_wcmp_inactive_notice')) {

    function wcmps_advance_shipping_wcmp_inactive_notice() {
        ?>
        <div id="message" class="error">
            <p><?php printf(__('%sWCMp Advance Shipping is inactive.%s The %sWC Marketplace%s must be active for the WCMp Advance Shipping to work. Please %sinstall & activate WC Marketplace%s', WCMPS_ADVANCE_SHIPPING_TEXT_DOMAIN), '<strong>', '</strong>', '<a target="_blank" href="https://wordpress.org/plugins/dc-woocommerce-multi-vendor/">', '</a>', '<a href="' . admin_url('plugin-install.php?tab=search&s=wc+marketplace') . '">', '&nbsp;&raquo;</a>'); ?></p>
        </div>
        <?php
    }

}
/**
 * Error notices for table rate shipping plugin not found
 */
if (!function_exists('wcmps_advance_shipping_table_rate_shipping_inactive_notice')) {

    function wcmps_advance_shipping_table_rate_shipping_inactive_notice() {
        ?>
        <div id="message" class="error">
            <p><?php printf(__('%sWCMp Advance Shipping is inactive.%s The %sTable Rate Shipping%s must be active for the WCMp Advance Shipping to work. Please %sinstall & activate Table Rate Shipping%s', WCMPS_ADVANCE_SHIPPING_TEXT_DOMAIN), '<strong>', '</strong>', '<a target="_blank" href="https://www.woothemes.com/products/table-rate-shipping/">', '</a>', '<a href="' . admin_url('plugins.php?tab=search&s=table+rate+shipping') . '">', '&nbsp;&raquo;</a>'); ?></p>
        </div>
        <?php
    }

}


function serializeToArray($data){
    foreach ($data as $d) {
        if( substr($d["name"], -1) == "]" ){
            $d["name"] = explode("[", str_replace("]", "", $d["name"]));
            switch (sizeof($d["name"])) {
                case 2:
                    $a[$d["name"][0]][$d["name"][1]] = $d["value"];
                break;

                case 3:
                    $a[$d["name"][0]][$d["name"][1]][$d["name"][2]] = $d["value"];
                break;

                case 4:
                    $a[$d["name"][0]][$d["name"][1]][$d["name"][2]][$d["name"][3]] = $d["value"];
                break;
            }
        }else{
            $a[$d["name"]] = $d["value"];
        } // if
    } // foreach

    return $a;
}