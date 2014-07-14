<?php
/*
Plugin Name: Trigger Warning Deluxe
Plugin URI: http://portfolio.planetjon.ca/projects/trigger-warning-deluxe/
Description: Warn your readers of potentially traumatic content.
Version: 1.0.2
Requires at least: 3.5.0
Tested up to: 3.9.1
Author: Jonathan Weatherhead
Author URI: http://jonathanweatherhead.com
Text Domain: trigger-warning-deluxe
Domain Path: /languages/
*/

if( ! twd_meets_requirements() ) {
	add_action( 'admin_notices', 'twd_requirements_notice' );
	return;
}

include_once plugin_dir_path( __file__ ) . 'core-plugin.php';
include_once plugin_dir_path( __file__ ) . 'wordpress-plugin.php';

if( is_admin() && ! defined( 'DOING_AJAX' ) )
	include_once plugin_dir_path( __file__ ) . 'admin-plugin.php';

register_activation_hook( __FILE__, 'twd_activate' );
register_deactivation_hook( __FILE__, 'twd_deactivate' );
register_uninstall_hook( __FILE__, 'twd_uninstall' );

function twd_activate() {
	TWD_WordPressIntegration::instance()->activate();
}

function twd_deactivate() {
	TWD_WordPressIntegration::instance()->deactivate();
}

function twd_uninstall() {
	TWD_WordPressIntegration::instance()->uninstall();
}

function twd_requirements_notice() {
	printf( '<div class="error"><p>%s</p></div>', __( 'Trigger Warning Deluxe requires PHP 5.3 or later to run. Please update your server.', 'trigger-warning-deluxe' ) );
}

function twd_meets_requirements() {
	return true; //version_compare( PHP_VERSION, '5.3.0' ) >= 0;
}

?>