<?php

/**
 * Plugin Name: Hide Edit With Elementor
 * Description: Hide the Edit With Elementor button and links for pages and posts that you do not want it used for eg. a front page with custom fields.
 * Plugin URI:  https://www.engaghewe.co.uk/web-services/wordpress-plugin-development
 * Version:     1.2.0
 * Author:      Engage Web, Nick Arkell
 * Author URI:  https://www.engageweb.co.uk
 * Text Domain: hide-edit-with-elementor
 * License: GPL2
 */

// Define plugin paths

define("HEWE_PLUGINURL",plugin_dir_url( __FILE__ ));
define("HEWE_PATH", plugin_dir_path(__FILE__));

function hewe_dependencies() {

	// Only load plugin files if Elementor is active but display a warning and deactivate the plugin if not

	if (is_plugin_active( 'elementor/elementor.php' )) {

		// Load plugin files		

		require_once( HEWE_PATH . 'includes/plugin.php' );

	} else {		

		// Display warning
		
		function hewe_general_admin_notice(){
			echo '
			<div class="notice notice-warning is-dismissible">
				<p>Hide Edit With Elementor is only useful if Elementor has been activated.</p>
			</div>';
		}
		add_action('admin_notices', 'hewe_general_admin_notice');

		// Deactivate plugin		

		deactivate_plugins( HEWE_PATH . 'edit-with-elementor.php' );
	}
}

add_action('admin_init', 'hewe_dependencies');