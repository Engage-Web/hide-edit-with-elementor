<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add meta box to the side bar on edit page/post screens
add_action( 'add_meta_boxes', 'hewe_add_meta_box' );

// Add the box
function hewe_add_meta_box() {
	// Only show if they can edit WordPress options eg. administrator
	if ( current_user_can( 'manage_options' ) ) {
		// Add meta box on all type of post to hide Elementor - specifying a post type where the '' is below would limit it
		add_meta_box( 'hewe_settings', 'Hide Edit With Elementor', 'hewe_meta_box_callback', '', 'side' );
	}
}

// HTML code of the meta box
function hewe_meta_box_callback( $post, $meta ) {
	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'hewe_noncename' );

	// field value
	$value = get_post_meta( $post->ID, 'hewe_switch', true );
	?>
	<p><input type="checkbox" id="hewe_switch" name="hewe_switch" value="true" <?php checked( $value, 'true' ); ?>> <label for="hewe_switch">Hide</label></p>
	<?php
}

// Save data when the post is saved
add_action( 'save_post', 'hewe_save_postdata' );

function hewe_save_postdata( $post_id ) {
	// Check the nonce of our page, because save_post can be called from another location.
	if ( ! isset( $_POST['hewe_noncename'] ) ) {
		return;
	}

	$hewe_nonce = sanitize_text_field( wp_unslash( $_POST['hewe_noncename'] ) );
	if ( ! wp_verify_nonce( $hewe_nonce, plugin_basename( __FILE__ ) ) ) {
		return;
	}

	// If this is autosave do nothing.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check user permission.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Store a normalized checkbox value.
	$hewe_data = isset( $_POST['hewe_switch'] ) && 'true' === sanitize_text_field( wp_unslash( $_POST['hewe_switch'] ) ) ? 'true' : '';
	update_post_meta( $post_id, 'hewe_switch', $hewe_data );
}

/**
 * Get IDs for posts where Elementor editing is hidden.
 *
 * @return int[] Post IDs.
 */
function hewe_get_hidden_post_ids() {
	return get_posts(
		array(
			'post_type'      => 'any',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => 'hewe_switch',
			'meta_value'     => 'true',
		)
	);
}

// Load JS and give it access to the PHP variable
function hewe_script() {
	if ( is_user_logged_in() ) {
		$hewe_query = hewe_get_hidden_post_ids();
		$script_path = plugin_dir_path( __FILE__ ) . '../assets/js/script.js';
		$script_version = file_exists( $script_path ) ? (string) filemtime( $script_path ) : null;

		// Enqueue scripts.
		wp_enqueue_script( 'hewe_script', HEWE_PLUGINURL . 'assets/js/script.js', array(), $script_version, true );

		// Expose the array to the script so it can be used in the jQuery.
		wp_localize_script(
			'hewe_script',
			'hewevar',
			array(
				'pagelist' => $hewe_query,
			)
		);
	}
}

add_action( 'admin_enqueue_scripts', 'hewe_script', 2000 );
add_action( 'wp_enqueue_scripts', 'hewe_script', 2000 );
add_action( 'admin_head', 'hewe_css' );

function hewe_css() {
	$hewe_query = hewe_get_hidden_post_ids();
	$current_id = get_the_ID();

	if ( $current_id && in_array( $current_id, $hewe_query, true ) ) {
		echo '<style>
			#elementor-switch-mode-button, #elementor-editor, #wp-admin-bar-elementor_edit_page {
				display:none;
			}
			</style>';
	}
}
