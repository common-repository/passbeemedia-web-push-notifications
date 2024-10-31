<?php
/**
 * Plugin Name: Pbm Safari Push
 * Plugin URI: http://passbeemedia.com/
 * Description: Drive traffic to your website with Safari Mavericks push notifications and Passbeemedia.
 * Version: 1.0.0
 * Author: Passbeemedia
 * Author URI: http://passbeemedia.com/
 * License: GPLv2 or later
 */
	
define( 'PBM_URL', plugin_dir_url( __FILE__ ) );
define( 'PBM_PS_URL', 'http://webpush.passbeemedia.com/' );

require_once( plugin_dir_path( __FILE__ ) . 'includes/class-pbm-core.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-pbm-api.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-pbm-bbpress.php' );

$pbm = new Pbm();
$bbPress_active = Pbm_bbPress::bbPress_active();

if( !empty( $bbPress_active['present'] ) && !empty( $bbPress_active['enabled'] ) ) {
    $roost_bbp = new Pbm_bbPress();
}

register_activation_hook( __FILE__, array( 'PBM', 'init' ) );
register_uninstall_hook( __FILE__, array( 'PBM', 'uninstall' ) );
