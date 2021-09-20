<?php
ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );
date_default_timezone_set( "Europe/Amsterdam" );
//date_default_timezone_set( "Asia/Karachi" );

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
require_once( ABSPATH . 'wp-admin/includes/post.php' );
if ( ! is_admin() ) {
	require_once( ABSPATH . 'wp-admin/includes/post.php' );
}
/**
 * Register a custom menu page.
 */
function halalmeat_automailer_register_my_custom_menu_page() {
	add_menu_page( 'Halal meat automailer', 'Test Halal Meat Automailer Now', 'manage_options', 'halalmeat-automailer-tester', 'halalmeat_automailer_test_mail' );
	add_submenu_page( 'halalmeat-automailer-tester', 'Settings', 'Settings',
		'manage_options', 'admin?page=halalmeat-automailer-settings' );
	add_submenu_page( 'halalmeat-automailer-tester', 'Send Mail', 'Send Mail',
		'manage_options', 'admin.php?page=halalmeat-automailer-manual-email' );
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
	if ( current_user_can( 'administrator' ) ) {
		halalmeat_automailer_register_my_custom_menu_page();
		if ( ! empty( $_GET ) && array_key_exists( 'page', $_GET ) && $_GET['page'] == 'halalmeat-automailer-settings' ) {
			halalmeat_automailer_settings();
		}
		if ( ! empty( $_GET ) && array_key_exists( 'page', $_GET ) && $_GET['page'] == 'halalmeat-automailer-manual-email' ) {
			require_once BASE_PATH . 'Helper.php';
			require_once BASE_PATH . 'DbQuery.php';
			$db = new DbQuery();
			if ( in_array( strtolower( Helper::getCurrentDay() ), [ 'fri', 'tue' ] ) ) {
				 lets_do_magic();
			}
			echo "Job done";
			die();
		}

		if ( is_plugin_active( 'wp-crontrol/wp-crontrol.php' ) ) {
			//plugin enabled ,set cron accordingly from setting
		} else {
			if ( is_admin() ) {
				echo '<div class="notice notice-error" style="padding:12px 12px">
                        <strong>Halalmeat Automailer</strong> wp-cron tab is disabled,pls activate it to set cron</div>';
			}
		}
	}
}

//Our main function to perform auto mailing and all the dependant stuff
function lets_do_magic() {
	require_once( BASE_PATH . 'DbQuery.php' );
	require_once( BASE_PATH . 'Helper.php' );
	$db = new DbQuery();
	if ( current( $db->getSetting()['test_mode'] ) ) {
		executeMainProcess( 'pre_order' );
	} else {
		$currentTime =strtotime(Helper::getCurrentTime()) ;

		if (
			( $currentTime > strtotime(current( $db->getSetting()['preorder_time'] ).':00' ))
			&& $currentTime < strtotime(current( $db->getSetting()['order_time'] ).':00' )) {
			echo "pre time";
			executeMainProcess( 'pre_order' );
		} else if ( ( $currentTime < strtotime(current( $db->getSetting()['preorder_time'] ).':00' ))
			        && $currentTime > strtotime(current( $db->getSetting()['order_time'] ).':00' )) {
			echo "post time";
			$mainProcessOrders=executeMainProcess( 'order' );
			if($mainProcessOrders)
			{
				$db->markComplete();
			}
		}

	}

	//send order details to logistics members
//	$db->sendToLogistics( $orders );
}

function executeMainProcess( string $type ) {
	$orders            = [];
	$wooOrdersObjArray = [];
	$db                = new DbQuery();
	$db->getAllOrders( $wooOrdersObjArray, $orders, $type );
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
		if($type=='order')
		{
			$db->sendToLogistics( $orders );
		}
		return $wooOrdersObjArray;
	}
}

// Add your custom order status action button (for orders with "processing" status)
add_filter( 'woocommerce_admin_order_actions', 'add_custom_order_status_actions_button', 100, 2 );
function add_custom_order_status_actions_button( $actions, $order ) {
	require_once BASE_PATH . 'Helper.php';
	require_once BASE_PATH . 'DbQuery.php';
	$db = new DbQuery();

	// Display the button for all orders that have a 'processing' status
	if ( $order->has_status( array( 'processing' ) ) ) {
		// Get Order ID (compatibility all WC versions)
		$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
		// Set the action button
		$orderTime = current( $db->getSetting()['order_time'] );
		if ( in_array( strtolower( Helper::getCurrentDay() ), [
				'fri',
				'tue'
			] ) && Helper::getCurrentTime() <= $orderTime ) {
			$actions['send_manual_mail'] = array(
				'url'    => admin_url( 'admin.php?action=send_manual_mail&order_id=' . $order_id ),
				'name'   => __( 'Send Mail', 'woocommerce' ),
				'action' => "send_manual_mail", // keep "view" class for a clean button CSS
			);
		}
	}

	return $actions;
}

// Set Here the WooCommerce icon for your action button
add_action( 'admin_head', 'add_custom_order_status_actions_button_css' );
function add_custom_order_status_actions_button_css() {
	$action_slug = "send_manual_mail"; // The key slug defined for your action button

	echo '<style>.wc-action-button-' . $action_slug . '::after { font-family: woocommerce !important; content: "\e029" !important; }</style>';
}
