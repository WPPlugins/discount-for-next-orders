<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('MA_Discount_Email_Class')) {

    class MA_Discount_Email_Class {

        public function ma_is_coupon_exists($coupon_code) {
            global $wpdb;
            $coupon_found = $wpdb->get_var($wpdb->prepare("
				SELECT $wpdb->posts.ID
				FROM $wpdb->posts
				WHERE $wpdb->posts.post_type = 'shop_coupon'
				AND $wpdb->posts.post_status = 'publish'
				AND $wpdb->posts.post_title = '%s'
				", $coupon_code));

            if ($coupon_found) {
                return true;
            }
            return false;
        }

        public function ma_expiry_date_calculator($expiry_date_cop) {

            if ($expiry_date_cop > 1) {
                $date_data = '+' . $expiry_date_cop . ' days';
            } else {
                $date_data = '+' . $expiry_date_cop . ' day';
            }

            return date('Y-m-d', strtotime($date_data));
        }

        public function ma_generate_coupon($coupon_code, $expiry_date, $email, $dis_type, $dis_per, $use_limit,$order_id) {

            $coupon = array(
                'post_title' => $coupon_code,
                'post_content' => '',
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
                'post_type' => 'shop_coupon'
            );

            $new_coupon_id = wp_insert_post($coupon);

            // Add meta
            update_post_meta($new_coupon_id, 'discount_type', $dis_type);
            update_post_meta($new_coupon_id, 'coupon_amount', $dis_per);
            update_post_meta($new_coupon_id, 'individual_use', 'yes');
            update_post_meta($new_coupon_id, 'product_ids', '');
            update_post_meta($new_coupon_id, 'exclude_product_ids', '');
            update_post_meta($new_coupon_id, 'usage_limit', $use_limit);
            update_post_meta($new_coupon_id, 'usage_limit_per_user', $use_limit);
            update_post_meta($new_coupon_id, 'expiry_date', $expiry_date);
            update_post_meta($order_id,'ma_coupon_status','generated');
            if ($email != 'none') {
                update_post_meta($new_coupon_id, 'customer_email', $email);
            }

            return $coupon;
        }

        public function ma_get_email_contents($coupon_code, $expiry_date, $email, $dis_per, $store_link) {
            $main_data = "
                            <center>
                            <div style='width:80%;font-family: Arial, Helvetica, sans-serif;
                                    background-color: white;
                                    border: 2px solid rgb(34, 209, 194);
                                    padding: 5px;'>
                                <table style='width:100%' >
                                <tr>
                                <td style='width:100%;height:60px;padding:10px;vertical-align: middle;text-align: center;background-color: rgb(34, 209, 194);border-radius: 10px;color:white;padding-top: 20px;'>
                                        <h2>Thank You for beign Our Customer...</h2>
                                </td>
                                </tr>
                                <tr>	
                                <td style='width:100%;height:45px;vertical-align: middle;text-align: center;'>
                                        <strong>GET [coupon percentage]% OFF</strong>
                                </td>
                                </tr>	
                                </table>
                                <div>
                                        <p>Here's your coupon code - but hurry! Ends <strong>[coupon expiry]</strong></p>
                                        <div style='width: 60%;box-shadow: 0;padding: 10px;background: border-box;opacity: 0.7;border-radius: 7px;color: rgb(34, 209, 194);'>
                                                <strong>[coupon code]</strong>
                                        </div>
                                        <p><h6>			Note: Coupon Code applicable for [coupon email]  </h6></p>

                                                <a href='[store link]' style='display: block;background: black;text-decoration: none;width:50%;padding: 10px;text-align: center;border-radius: 5px;color: white;
                                                    font-weight: bold;' target='_blank'>Visit the Store</a>
                                        </p>
                                </div>
                            </div>
                    </center>
                    ";

            $main_data = str_replace('[coupon percentage]', $dis_per, $main_data);
            $main_data = str_replace('[coupon expiry]', $expiry_date, $main_data);
            $main_data = str_replace('[coupon code]', $coupon_code, $main_data);
            $main_data = str_replace('[coupon email]', ($email != 'none') ? $email . ' only.' : 'all type of users.', $main_data);
            $main_data = str_replace('[store link]', $store_link, $main_data);

            return $main_data;
        }

    }

}
?>