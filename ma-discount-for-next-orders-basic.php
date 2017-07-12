<?php

/*
  Plugin Name: WooCommerce Discount for Next Orders
  Plugin URI: https://www.moreaddons.com/
  Description: Pull your customers back for the next order.
  Version: 1.1.2
  Author: MoreAddons
  Author URI: https://www.moreaddons.com/
  domain : ma-dis-order-coupons
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
require_once(ABSPATH . "wp-admin/includes/plugin.php");
function ma_dis_order_coupons_activation_hook() {
    //check if basic version is there
    if (is_plugin_active('ma-discount-for-next-orders/ma-discount-for-next-orders.php')) {
        add_action('admin_notices','ma_offer_pro_admin_notices', 99);
        deactivate_plugins(plugin_basename(__FILE__));
        function ma_offer_pro_admin_notices()
        {
            is_admin() && add_filter('gettext', function($translated_text, $untranslated_text, $domain)
            {
                $old = array(
                    "Plugin <strong>activated</strong>.",
                    "Selected plugins <strong>activated</strong>."
                );
                $new = "<span style='color:red'>Discount for Next Orders - Pro Version Installed</span>";
                if (in_array($untranslated_text, $old, true)) {
                    $translated_text = $new;
                }
                return $translated_text;
            }, 99, 3);
        }
        return;
    }
}

register_activation_hook(__FILE__, 'ma_dis_order_coupons_activation_hook');

// =========== Define Section Start =====================//

define('MA_DISCOUNTS_ORDERS_COUPONS_MAIN_DIR', plugin_dir_url(__FILE__));
define('MA_DISCOUNT_SETTINGS_KEY', 'ma_discount_settings_key');

// =========== Define Section End =====================//
//check woocommerce is active or !

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    if (!class_exists('MA_Discounts_Coupons')) {

        class MA_Discounts_Coupons {

            public function __construct() {
                add_action('init', array($this, 'ma_load_plugin_textdomain'));
                add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'ma_discount_order_action_links'));
                add_action('woocommerce_order_status_completed', array($this, 'ma_discount_order_completed_callback'), 10, 1);
                add_action('admin_menu', array($this, 'ma_discount_order_menu_page'));
            }

            public function ma_load_plugin_textdomain() {

                load_plugin_textdomain('ma-dis-order-coupons', false, dirname(plugin_basename(__FILE__)) . '/lang');
            }

            public function ma_discount_order_completed_callback($order_id) {
                $output_array = (get_option('woocommerce_' . MA_DISCOUNT_SETTINGS_KEY) != false) ? get_option('woocommerce_' . MA_DISCOUNT_SETTINGS_KEY) : array();
                if (!empty($output_array)) 
                {
                    if( (isset($output_array['enable_disable']))  && $output_array['enable_disable'] === 'yes')
                    {
                        if ($output_array['code_follow'] === 'yes') {
                            $order = new WC_Order($order_id);
                            $order_num = $order->get_order_number();
                            $coupon_code = (isset($output_array['coupoun_data']) ? $output_array['coupoun_data'] : 'DIS') . $order_num . (isset($output_array['ma_coupoun_data_suffix']) ? $output_array['ma_coupoun_data_suffix'] : 'OFFER') ;
                        } else {
                            $order = new WC_Order($order_id);
                            $order_num = $order->get_order_number();
                            $coupon_code = (isset($output_array['coupoun_data']) ? $output_array['coupoun_data'] : 'DIS') . rtrim(strtr(base64_encode($order_num), '+/', '-_'), '=') . (isset($output_array['ma_coupoun_data_suffix']) ? $output_array['ma_coupoun_data_suffix'] : 'OFFER') ;
                        }

                        include_once('includes/ma-discount-email-class.php');
                        $email_class_obj = new MA_Discount_Email_Class();
                        //!$email_class_obj->ma_is_coupon_exists($coupon_code)
                        if (!get_post_meta($order_id,'ma_coupon_status') || get_post_meta($order_id,'ma_coupon_status') !=  'generated') {
                            $expiry_date = $email_class_obj->ma_expiry_date_calculator($output_array['coupon_expiry']);
                            if ($output_array['discount_type'] === 'per_dis') {
                                $dis_type = 'percent';
                            } else {
                                $dis_type = 'fixed_cart';
                            }
                            $use_limit = $output_array['coupon_limit'];
                            if ($output_array['coupon_res'] === 'yes') {
                                $email = (WC()->version < '2.7.0') ? $order->billing_email : $order->get_billing_email();
                            } else {
                                $email = 'none';
                            }
                            $dis_per = $output_array['coupon_amount'];
                            $store_link = !empty($output_array['store_link']) ? $output_array['store_link'] : '';
                            $new_coupon = $email_class_obj->ma_generate_coupon($coupon_code, $expiry_date, $email, $dis_type, $dis_per, $use_limit,$order_id);
                            $email_contents = $email_class_obj->ma_get_email_contents($coupon_code, $expiry_date, $email, $dis_per, $store_link);
                            if(isset($output_array['ma_email_sender_name']))
                            {
                                $sender_name = $output_array['ma_email_sender_name'];
                            }else{
                                $sender_name = 'WordPress';
                            }
                            $headers = array(
                                'From: '.$sender_name.'  <' . $output_array['email_from'] . '>',
                                'Reply-To: '.$sender_name.' <' . $output_array['email_from'] . '>',
                                'Content-Type: text/html; charset=UTF-8',
                                );
                        // have to handle sending failed case.(if fails remove coupon also).
                            $email_id = (WC()->version < '2.7.0') ? $order->billing_email : $order->get_billing_email();
                            

                            
                            wp_mail($email_id, $output_array['email_sub'], $email_contents, $headers);
                            
                             if(get_option('ma_discount_for_next_ord_err_log') !='')
                            {
                                $data = get_option('ma_discount_for_next_ord_err_log');
                                $new_data = '#'.$order_id.' -> '.$coupon_code.' Generated.&#13;&#10;'.'#'.$order_id.' -> Coupon Sent to '.$email_id.'  &#13;&#10;';
                                $new_data .= $data;
                            update_option('ma_discount_for_next_ord_err_log', $new_data );
                            }else{
                                $data ='#'.$order_id.' -> '.$coupon_code.' Generated.&#13;&#10;'.'#'.$order_id.' -> Coupon Sent to '.$email_id.'  &#13;&#10;';
                                update_option('ma_discount_for_next_ord_err_log', $data);
                            }
                        }
                    }else{

                        if(get_option('ma_discount_for_next_ord_err_log') !='')
                        {
                            $data = get_option('ma_discount_for_next_ord_err_log');
                            $new_data = 'Discount for Next Orders Plugin Not Enabled &#13;&#10;'; 
                            $new_data .= $data;
                            update_option('ma_discount_for_next_ord_err_log', $new_data );
                        }else{
                            $data = 'Discount for Next Orders Plugin Not Enabled &#13;&#10;';
                            update_option('ma_discount_for_next_ord_err_log', $data);   
                        }
                        
                    }
                }
                else{
                    if(get_option('ma_discount_for_next_ord_err_log') !='')
                        {
                            $data = get_option('ma_discount_for_next_ord_err_log');
                            $new_data = 'Discount for Next Orders Plugin Not Configured &#13;&#10;'; 
                            $new_data .= $data;
                            update_option('ma_discount_for_next_ord_err_log', $new_data );
                        }else{
                            $data = 'Discount for Next Orders Plugin Not Configured &#13;&#10;';
                            update_option('ma_discount_for_next_ord_err_log', $data);   
                        }
                }
            }

            public function ma_discount_order_init() {

                require_once('includes/ma-discount-for-next-day-main-class.php');
            }

            public function ma_discount_order_action_links($links) {
                $plugin_links = array(
                    '<a href="' . admin_url('admin.php?page=ma_discount_order_settings') . '">' . __('Settings', 'ma-dis-order-coupons') . '</a>',
                    //'<a href="#">' . __('Plugin Guide', 'ma-dis-order-coupons') . '</a>',
                    '<a href="https://wordpress.org/support/plugin/discount-for-next-orders">' . __('Support', 'ma-dis-order-coupons') . '</a>',
                );
                return array_merge($plugin_links, $links);
            }

            public function ma_discount_order_menu_page() {
                add_submenu_page('woocommerce', __('Discount for Next Orders (Basic)', 'ma-dis-order-coupons'), __('Discount for Next Orders (Basic)', 'ma-dis-order-coupons'), 'manage_woocommerce', 'ma_discount_order_settings', array($this, 'ma_discount_order_init'));
            }

        }

    }
    new MA_Discounts_Coupons();
} else {
    add_action('admin_notices','ma_offer_wc_admin_notices', 99);
    deactivate_plugins(plugin_basename(__FILE__));
    function ma_offer_wc_admin_notices()
    {
        is_admin() && add_filter('gettext', function($translated_text, $untranslated_text, $domain)
        {
            $old = array(
                "Plugin <strong>activated</strong>.",
                "Selected plugins <strong>activated</strong>."
            );
            $new = "<span style='color:red'>Discount for Next Orders - WooCommerce is not Installed</span>";
            if (in_array($untranslated_text, $old, true)) {
                $translated_text = $new;
            }
            return $translated_text;
        }, 99, 3);
    }
    return;
}



