<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<center>
    <h1> <?php echo __('Discount for Next Orders'); ?> </h1>
</center>
	<hr>
<?php
if(isset($_POST['ma_submit'])){	
	$settings_array = array();
	$settings_array['discount_type']	= $_POST['ma_coupon_discount_type'];
	$settings_array['coupon_amount']	= !empty($_POST['ma_coupoun_amount']) ? $_POST['ma_coupoun_amount'] : '0';
	$settings_array['coupon_expiry']	= !empty($_POST['ma_coupoun_expiry']) ? $_POST['ma_coupoun_expiry'] : '365';
	$settings_array['coupon_res']		= $_POST['ma_coupon_res'];
	$settings_array['coupon_limit']		= !empty($_POST['ma_coupoun_limit']) ? $_POST['ma_coupoun_limit'] : '1';
	$settings_array['coupoun_data']		= !empty($_POST['ma_coupoun_data']) ? $_POST['ma_coupoun_data'] : 'DES';
	$settings_array['code_follow']		= $_POST['ma_coupon_code_follow'];
	$settings_array['email_from']		= !empty($_POST['ma_email_sent_from']) ? $_POST['ma_email_sent_from'] : '';
	$settings_array['email_sub']		= !empty($_POST['ma_email_sub']) ? $_POST['ma_email_sub'] : 'Discount Email';
	$settings_array['store_link']		= !empty($_POST['ma_store_link']) ? $_POST['ma_store_link'] : '';
    $settings_array['enable_disable']   = isset($_POST['ma_plugin_enable_disable']) ? 'yes' : 'no';
    $settings_array['ma_email_sender_name']   = !empty($_POST['ma_email_sender_name']) ? $_POST['ma_email_sender_name'] : 'WordPress';
    $settings_array['ma_coupoun_data_suffix']   = !empty($_POST['ma_coupoun_data_suffix']) ? $_POST['ma_coupoun_data_suffix'] : 'OFFER';

	update_option( 'woocommerce_'.MA_DISCOUNT_SETTINGS_KEY, $settings_array );
	echo '<div class="notice notice-success is-dismissible"> 
	<p><strong>'. __('Settings saved','ma-dis-order-coupons') .'</strong></p>
</div>';

}
if(isset($_POST['ma_clear_log']))
{
    update_option("ma_discount_for_next_ord_err_log","");
}
$output_array = (get_option('woocommerce_'.MA_DISCOUNT_SETTINGS_KEY) != false) ? get_option('woocommerce_'.MA_DISCOUNT_SETTINGS_KEY) : array();

?>
<div style="width:100%;">
	<br>
	<style type="">
	.input_box_css{
		line-height: 22px;
		padding-right: 24px;
		width:30%;;
	}
    .input_box_adavanced_css{
        line-height: 22px;
        padding-right: 24px;
        width:70%;;
    }
	</style>
	
	<h2> <?php echo __('Generic Settings:'); ?> </h2>
	<form method="post">
            <table class="form-table">
             <tr>
                            <th><?php echo __('Enable Discount for Next Orders ','ma-dis-order-coupons'); ?></th>

                            <td>
                                    <input type="checkbox" name="ma_plugin_enable_disable" id="ma_plugin_enable_disable" class="input_box_css" <?php echo ((!empty($output_array) && (isset($output_array['enable_disable']))  && $output_array['enable_disable'] === 'yes' ) ? 'checked' : ''); ?>
                            </td>
            </tr>
                    <tr>
                            <th><?php echo __('Discount Type ','ma-dis-order-coupons'); ?></th>

                            <td>
                                    <select name="ma_coupon_discount_type" id="ma_coupon_discount_type" class="wc-enhanced-select" style='width:30%;'>
                                            <?php if(!empty($output_array)) 
                                                    {
                                                            if((isset($output_array['discount_type'])) && $output_array['discount_type'] === 'per_dis')
                                                            { 
                                                                    echo "<option value='per_dis' selected='true'>". __('Percentage Discount','ma-dis-order-coupons') ."</option>";
                                                                    echo "<option value='fix_dis'>".__('Fixed Cart Discount ','ma-dis-order-coupons')."</option>";
                                                            }else{
                                                                    echo "<option value='per_dis' >". __('Percentage Discount','ma-dis-order-coupons') ."</option>";
                                                                    echo "<option value='fix_dis' selected='true'>".__('Fixed Cart Discount ','ma-dis-order-coupons')."</option>";
                                                            }
                                                    }else{

                                                                    echo "<option value='per_dis' selected='true'>". __('Percentage Discount','ma-dis-order-coupons') ."</option>";
                                                                    echo "<option value='fix_dis'>".__('Fixed Cart Discount ','ma-dis-order-coupons')."</option>";
                                                    }
                                            ?>

                                    </select>
                            </td>
                           
                    </tr>
                    <tr>
                            <th><?php echo __('Discount Amount (%) ','ma-dis-order-coupons'); ?></th>

                            <td>
                                    <input type="text" name="ma_coupoun_amount" id="ma_coupoun_amount" class="input_box_css" value="<?php echo (!empty($output_array) && (isset($output_array['coupon_amount']))) ? $output_array['coupon_amount'] : ''; ?>" placeholder="<?php  _e('Discount Amount ( ex: 20 )','ma-dis-order-coupons'); ?>" required >(%)
                            </td>
                    </tr>
                    <tr>
                            <th><?php echo __('Discount expiry date (In days)','ma-dis-order-coupons'); ?></th>

                            <td>
                                    <input type="text" name="ma_coupoun_expiry" id="ma_coupoun_expiry" value="<?php echo (!empty($output_array) && (isset($output_array['coupon_expiry']))) ? $output_array['coupon_expiry'] : ''; ?>" class="input_box_css" placeholder="<?php _e('Default: 365 Days','ma-dis-order-coupons'); ?>">
                            </td>
                    </tr>
                    <tr>
                            <th><?php echo __('Discount Email Restrictions ','ma-dis-order-coupons'); ?></th>

                            <td>
                                    <select name="ma_coupon_res" id="ma_coupon_res" class="wc-enhanced-select" style='width:30%;'>
                                    <?php if(!empty($output_array)) 
                                                    {
                                                            if((isset($output_array['coupon_res'])) && $output_array['coupon_res'] === 'yes')
                                                            {
                                                                    echo "<option value='yes' selected='true'>". __('Restricted with Order Email','ma-dis-order-coupons') . "</option>";
                                                                    echo "<option value='no'>" . __('Everyone ','ma-dis-order-coupons') . "</option>";
                                                            }else{
                                                                    echo "<option value='yes' >". __('Restricted with Order Email','ma-dis-order-coupons') . "</option>";
                                                                    echo "<option value='no' selected='true'>" . __('Everyone','ma-dis-order-coupons') . "</option>";
                                                            }
                                                    }else{

                                                                    echo "<option value='yes' selected='true'>". __('Restricted with Order Email','ma-dis-order-coupons') . "</option>";
                                                                    echo "<option value='no'>" . __('Everyone','ma-dis-order-coupons') . "</option>";
                                                    }
                                    ?>

                                    </select>
                            </td>
                    </tr>
                    <tr>
                            <th><?php echo __('Discount Apply Limit ','ma-dis-order-coupons'); ?></th>

                            <td>
                                    <input type="text" name="ma_coupoun_limit" id="ma_coupoun_limit" class="input_box_css" value="<?php echo (!empty($output_array) && (isset($output_array['coupon_limit']))) ? $output_array['coupon_limit'] : ''; ?>" placeholder="<?php _e('Default: 1 (one time use)','ma-dis-order-coupons'); ?>">
                            </td>
                    </tr>

            </table>
            <hr>
            <h2> <?php echo __('Advanced Settings:','ma-dis-order-coupons'); ?> </h2>
            <table class="form-table">

                    <tr>
                            <th><?php echo __('Discount Code Prefix ','ma-dis-order-coupons'); ?></th>

                            <td>
                                    <input type="text" name="ma_coupoun_data" id="ma_coupoun_data" class="input_box_adavanced_css" required value="<?php echo (!empty($output_array) && (isset($output_array['coupoun_data']))) ? $output_array['coupoun_data'] : 'DIS'; ?>" >
                            </td>
                             <td rowspan="5">
                             Discount for Next Orders Log: <br>
                                <textarea style="width:95%;height:240px;overflow:scroll;" disabled="true"><?php echo ((get_option('ma_discount_for_next_ord_err_log') !='') ? get_option('ma_discount_for_next_ord_err_log') : '') ;?></textarea>
                            <form method="post">
                                <button class="button button-secondary" type="submit" style="float: right;margin-right: 5%;" name="ma_clear_log" id="ma_clear_log">Clear Log</button>
                            </form>
                            </td>
                    </tr>
                     <tr>
                            <th><?php echo __('Discount Code Suffix ','ma-dis-order-coupons'); ?></th>

                            <td>
                                    <input type="text" name="ma_coupoun_data_suffix" id="ma_coupoun_data_suffix" class="input_box_adavanced_css" required value="<?php echo (!empty($output_array) && (isset($output_array['ma_coupoun_data_suffix']))) ? $output_array['ma_coupoun_data_suffix'] : 'OFFER'; ?>" >
                            </td>
                    </tr>
                    <tr>
                            <th><?php echo __('Discount Code Following','ma-dis-order-coupons'); ?></th>

                            <td>
                                    <select name="ma_coupon_code_follow" id="ma_coupon_code_follow" class="wc-enhanced-select" style='width:70%;'>
                                    <?php if(!empty($output_array)) 
                                                    {
                                                            if((isset($output_array['code_follow'])) && $output_array['code_follow'] === 'yes')
                                                            {
                                                                    echo "<option value='yes' selected='true'>" . __('Order Number ','ma-dis-order-coupons') . "</option>";
                                                                    echo "<option value='no'>" . __('Random Code ','ma-dis-order-coupons') . "</option>";
                                                            }else{
                                                                    echo "<option value='yes' >" . __('Order Number ','ma-dis-order-coupons') . "</option>";
                                                                    echo "<option value='no' selected='true'>" . __('Random Code ','ma-dis-order-coupons') . "</option>";
                                                            }
                                                    }else{

                                                                    echo "<option value='yes' selected='true'>" . __('Order Number','ma-dis-order-coupons') . "</option>";
                                                                    echo "<option value='no'>" . __('Random Code ','ma-dis-order-coupons') . "</option>";
                                                    }
                                    ?>


                                    </select>
                            </td>
                    </tr>
                    <tr>
                            <th><?php echo __('Email Send From','ma-dis-order-coupons'); ?></th>

                            <td>
                                    <input type="text" name="ma_email_sent_from" id="ma_email_sent_from" class="input_box_adavanced_css" placeholder="<?php _e('info@example.com','ma-dis-order-coupons'); ?>" required value="<?php echo (!empty($output_array) && (isset($output_array['email_from']))) ? $output_array['email_from'] : ''; ?>" >
                            </td>
                    </tr>
                    <tr>
                            <th><?php echo __('Email Sender Name','ma-dis-order-coupons'); ?></th>

                            <td>
                                    <input type="text" name="ma_email_sender_name" id="ma_email_sender_name" class="input_box_adavanced_css" placeholder="<?php _e('Default: WordPress','ma-dis-order-coupons'); ?>" value="<?php echo (!empty($output_array) && (isset($output_array['ma_email_sender_name']))) ? $output_array['ma_email_sender_name'] : ''; ?>" >
                            </td>
                    </tr>
                    <tr>
                            <th><?php echo __('Email Subject','ma-dis-order-coupons'); ?></th>

                            <td>
                                    <input type="text" name="ma_email_sub" id="ma_email_sub" class="input_box_adavanced_css" placeholder="<?php _e('Discount Email','ma-dis-order-coupons'); ?>" required value="<?php echo (!empty($output_array) && (isset($output_array['email_sub']))) ? $output_array['email_sub'] : ''; ?>" >
                            </td>
                    </tr>
                    <tr>
                            <th><?php echo __('Store/Shop Link','ma-dis-order-coupons'); ?></th>

                            <td>
                                    <input type="text" name="ma_store_link" id="ma_store_link" class="input_box_adavanced_css" placeholder="<?php _e('Store Link for Email Button','ma-dis-order-coupons'); ?>" required value="<?php echo (!empty($output_array) && (isset($output_array['store_link']))) ? $output_array['store_link'] : ''; ?>" ><br>
                                   
                            </td>
                    </tr>
            </table>
            <hr>
            <br>
            <button type="submit" class="button button-primary" name="ma_submit" id="ma_submit"><?php echo __("Save Changes",'ma-dis-order-coupons'); ?></button>
            </form>
</div> 