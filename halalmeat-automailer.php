<?php
ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );
/**
 * Plugin Name
 *
 * @package           Halalmeat-Automailer
 * @author            Sarmad Sohail
 * @copyright         2019 Linknbit
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Halalmeat-Automailer
 * Description:       To send order mails automatically to butcher and logsitic members
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Sarmad Sohail
 * Author URI:        https://github.com/srmd-tl
 * Text Domain:       halalmeat-automailer
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

//wp-admin/tools.php?page=crontrol_admin_manage_page&action=new-cron
define( 'BASE_PATH', plugin_dir_path( __FILE__ ) );
/**
 * Register a custom menu page.
 */

function halalmeat_automailer_register_my_custom_menu_page() {
	add_menu_page( 'Halal meat automailer', 'Test Halal Meat Automailer Now', 'manage_options', 'halalmeat-automailer-tester', 'halalmeat_automailer_test_mail' );
	add_submenu_page( 'halalmeat-automailer-tester', 'Settings', 'Settings',
		'manage_options', 'admin?page=halalmeat-automailer-settings' );

}

//halalmeat-automailer_test_mail to test mail
function halalmeat_automailer_test_mail() {
	lets_do_magic();
}

//halalmeat_automailer_settings
function halalmeat_automailer_settings() {
	require_once BASE_PATH . '/templates/settings.php';
	die();
}

//crontrol hooks
add_action( 'halalmeat_automailer_new_cron', 'halalmeat_automailer_cron_callback' );
function halalmeat_automailer_cron_callback() {
	lets_do_magic();
}

//on init hoook
add_action( 'init', 'on_halalmeat_automailer_init' );
function on_halalmeat_automailer_init() {
	if ( current_user_can('administrator') ) {
		halalmeat_automailer_register_my_custom_menu_page();
		if ( ! empty( $_GET ) && array_key_exists( 'page', $_GET ) && $_GET['page'] == 'halalmeat-automailer-settings' ) {
			halalmeat_automailer_settings();
		}
		if ( is_plugin_active( 'wp-crontrol/wp-crontrol.php' ) ) {
			//plugin enabled ,set cron accordingly from setting
		} else {
			echo '<div class="notice notice-error" style="padding:12px 12px">
                        <strong>Halalmeat Automailer</strong> wp-cron tab is disabled,pls activate it to set cron</div>';
		}
	}

}

//Our main function to perform auto mailing and all the dependant stuff
function lets_do_magic() {
	require_once( BASE_PATH . 'DbQuery.php' );
	require_once( BASE_PATH . 'Helper.php' );
	executeMainProcess('pre_order');

	//send order details to logistics members
//	$db->sendToLogistics( $orders );


//	$data=apply_filters( 'wc_customer_order_export_xml_order_note', array(
//    'Date'    => $order->get_date_created(),
//    'Author'  => $order->get_user(),
//    'Content' => str_replace( array( "\r", "\n" ), ' ', $order->get_customer_note()),
//), $order->get_customer_note(), $order );

}
function executeMainProcess(string $type)
{
	$orders            = [];
	$wooOrdersObjArray = [];
	$db                = new DbQuery();
	$db->getAllOrders( $wooOrdersObjArray, $orders ,$type);
	if ( $wooOrdersObjArray ) {
		//Get orders array for butcher
		$butcherOrders = $db->getOrderProductsForButcher( $wooOrdersObjArray );
		if ( $butcherOrders ) {
			//generate dynamic html string for butcher order
			$html = require_once( BASE_PATH . 'templates/card/bussniesscard.php' );
			echo $html;
			//Generate Pdf for butcher
			Helper::generatePdf( $html, 'butcher' );
			//Send mail to butcher
			$db->sendToButcher( $butcherOrders );
		}
		//generate dynamic html string for logistic order

		$html = require_once( BASE_PATH . 'templates/invoice/invoice.php' );
		echo $html;
		//Generate Pdf for butcher
		Helper::generatePdf( $html, 'logistics' );
		if ( $orders ) {
			//Send mail to logistics
			$db->sendToLogistics( $orders );
		}

	}
}


//// Get Order ID and Key
//$order->get_id();
//$order->get_order_key();
//
//// Get Order Totals $0.00
//$order->get_formatted_order_total();
//$order->get_cart_tax();
//$order->get_currency();
//$order->get_discount_tax();
//$order->get_discount_to_display();
//$order->get_discount_total();
//$order->get_fees();
//$order->get_formatted_line_subtotal();
//$order->get_shipping_tax();
//$order->get_shipping_total();
//$order->get_subtotal();
//$order->get_subtotal_to_display();
//$order->get_tax_location();
//$order->get_tax_totals();
//$order->get_taxes();
//$order->get_total();
//$order->get_total_discount();
//$order->get_total_tax();
//$order->get_total_refunded();
//$order->get_total_tax_refunded();
//$order->get_total_shipping_refunded();
//$order->get_item_count_refunded();
//$order->get_total_qty_refunded();
//$order->get_qty_refunded_for_item();
//$order->get_total_refunded_for_item();
//$order->get_tax_refunded_for_item();
//$order->get_total_tax_refunded_by_rate_id();
//$order->get_remaining_refund_amount();
//
//// Get and Loop Over Order Items
//foreach ( $order->get_items() as $item_id => $item ) {
//   $product_id = $item->get_product_id();
//   $variation_id = $item->get_variation_id();
//   $product = $item->get_product();
//   $name = $item->get_name();
//   $quantity = $item->get_quantity();
//   $subtotal = $item->get_subtotal();
//   $total = $item->get_total();
//   $tax = $item->get_subtotal_tax();
//   $taxclass = $item->get_tax_class();
//   $taxstat = $item->get_tax_status();
//   $allmeta = $item->get_meta_data();
//   $somemeta = $item->get_meta( '_whatever', true );
//   $type = $item->get_type();
//}
//
//// Other Secondary Items Stuff
//$order->get_items_key();
//$order->get_items_tax_classes();
//$order->get_item_count();
//$order->get_item_total();
//$order->get_downloadable_items();
//
//// Get Order Lines
//$order->get_line_subtotal();
//$order->get_line_tax();
//$order->get_line_total();
//
//// Get Order Shipping
//$order->get_shipping_method();
//$order->get_shipping_methods();
//$order->get_shipping_to_display();
//
//// Get Order Dates
//$order->get_date_created();
//$order->get_date_modified();
//$order->get_date_completed();
//$order->get_date_paid();
//
//// Get Order User, Billing & Shipping Addresses
//$order->get_customer_id();
//$order->get_user_id();
//$order->get_user();
//$order->get_customer_ip_address();
//$order->get_customer_user_agent();
//$order->get_created_via();
//$order->get_customer_note();
//$order->get_address_prop();
//$order->get_billing_first_name();
//$order->get_billing_last_name();
//$order->get_billing_company();
//$order->get_billing_address_1();
//$order->get_billing_address_2();
//$order->get_billing_city();
//$order->get_billing_state();
//$order->get_billing_postcode();
//$order->get_billing_country();
//$order->get_billing_email();
//$order->get_billing_phone();
//$order->get_shipping_first_name();
//$order->get_shipping_last_name();
//$order->get_shipping_company();
//$order->get_shipping_address_1();
//$order->get_shipping_address_2();
//$order->get_shipping_city();
//$order->get_shipping_state();
//$order->get_shipping_postcode();
//$order->get_shipping_country();
//$order->get_address();
//$order->get_shipping_address_map_url();
//$order->get_formatted_billing_full_name();
//$order->get_formatted_shipping_full_name();
//$order->get_formatted_billing_address();
//$order->get_formatted_shipping_address();
//
//// Get Order Payment Details
//$order->get_payment_method();
//$order->get_payment_method_title();
//$order->get_transaction_id();
//
//// Get Order URLs
//$order->get_checkout_payment_url();
//$order->get_checkout_order_received_url();
//$order->get_cancel_order_url();
//$order->get_cancel_order_url_raw();
//$order->get_cancel_endpoint();
//$order->get_view_order_url();
//$order->get_edit_order_url();
//
//// Get Order Status
//$order->get_status();
