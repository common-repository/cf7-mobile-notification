<?php
/**
 * Plugin Name:     Contact Form 7 Database & Mobile App - CF7 DB & App
 * Plugin URI:      https://wordpress.org/plugins/cf7-mobile-notification
 * Description:     This plugin allows you to store and receive via the App "CF7 Database & Contact Manager for Wordpress" Contact Form 7 form submissions.
 * Author:          Daniele Callegaro
 * Author URI:      mailto:daniele.callegaro.90@gmail.com
 * Text Domain:     cf7_mobile_notification
 * Domain Path:     /cf7_mobile_notification
 * Version:         1.0.0
 *
 * @package         Cf7MobileNotification
 */

use Cf7MobileNotification\Constants\Constants;
use Cf7MobileNotification\Hooks\Hooks;
use Cf7MobileNotification\Updater\Installer;

require __DIR__ . "/vendor/autoload.php";


function cf7_mobile_notification_on_activate( $network_wide ){
	global $wpdb;
	if ( is_multisite() && $network_wide ) {
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			Installer::getInstance()->install();
			restore_current_blog();
		}
	} else {
		add_option(Constants::OPTION_ACTIVATION_REDIRECT, true);
		Installer::getInstance()->install();
	}
}

register_activation_hook( __FILE__, 'cf7_mobile_notification_on_activate' );
Hooks::getInstance()->loadHook();
