<?php
ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );
//date_default_timezone_set( "Europe/Amsterdam" );
date_default_timezone_set( "Asia/Karachi" );
# No need for the template engine
define( 'WP_USE_THEMES', false );
# Load WordPress Core
// Assuming we're in a subdir: "~/wp-content/plugins/current_dir"
require_once( '../../../wp-load.php' );
defined( 'BASE_PATH' ) or define( 'BASE_PATH', plugin_dir_path( __FILE__ ) );
require_once( BASE_PATH . 'halalmeat-automailer.php' );
lets_do_magic();
//executeMainProcess('order');
//print_r(get_post(1));