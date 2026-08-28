<?php



// Add meta box to the side bar on edit page/post screens

add_action( 'add_meta_boxes', 'hewe_add_meta_box' );



// Add the box

function hewe_add_meta_box(){

	// Only show if they can edit WordPress options eg. administrator

	if( current_user_can( 'manage_options', $post_id ) ) {

		// Add meta box on all type of post to hide Elementor - specifying a post type where the '' is below would limit it

		add_meta_box( 'hewe_settings', 'Hide Edit With Elementor', 'hewe_meta_box_callback', '', 'side' );

	}

}



// HTML code of the meta box

function hewe_meta_box_callback( $post, $meta ){	

	// Use nonce for verification

	wp_nonce_field( plugin_basename(__FILE__), 'hewe_noncename' );

	// field value

	$value = get_post_meta( $post->ID, 'hewe_switch', 1 ); ?>

	<p><input type="checkbox" id="hewe_switch" name="hewe_switch" value="true" <?php if ($value == 'true') { ?> checked<?php } ?>> <label for="hewe_switch">Hide</label></p>

	<?php

}



// Save data when the post is saved

add_action( 'save_post', 'hewe_save_postdata' );



function hewe_save_postdata( $post_id ) {

	// check the nonce of our page, because save_post can be called from another location.

	if ( ! wp_verify_nonce( $_POST['hewe_noncename'], plugin_basename(__FILE__) ) )

		return;

	// if this is autosave do nothing

	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 

		return;

	// check user permission

	if( ! current_user_can( 'manage_options', $post_id ) )

		return;

	// clear the value of the input field.

	$hewe_data = sanitize_text_field( $_POST['hewe_switch'] );

	// Update data in the database.

	update_post_meta( $post_id, 'hewe_switch', $hewe_data );

}



// Load JS and give it access to the PHP variable



function hewe_script() {

	if (is_user_logged_in()) {

		// Compile a list of all posts / pages etc that have 'Hide Edit With Elementor' switched on and place in an array

		global $wpdb;    

		$hewe_query_raw = $wpdb->get_results("SELECT post_id FROM ".$wpdb->prefix."postmeta WHERE meta_key = 'hewe_switch' AND meta_value = 'true'");

		foreach($hewe_query_raw as $hewe_post): $hewe_query[] = $hewe_post->post_id; endforeach;

		// Enqueue scripts

		wp_enqueue_script( 'hewe_script', HEWE_PLUGINURL.'assets/js/script.js', array(), date('YmdHi', filemtime(plugin_dir_path( __FILE__ ).'../assets/js/script.js')), true);	

		// Expose the array to the script so it can be used in the jQuery

		$hewevar = array(

			'pagelist' => $hewe_query

		);

		wp_localize_script( 'hewe_script', 'hewevar', $hewevar );

	}

}



add_action( 'admin_enqueue_scripts', 'hewe_script', 2000 );

add_action( 'wp_enqueue_scripts', 'hewe_script', 2000 );

add_action('admin_head', 'hewe_css');

function hewe_css() {
    global $wpdb;    
	$hewe_query_raw = $wpdb->get_results("SELECT post_id FROM ".$wpdb->prefix."postmeta WHERE meta_key = 'hewe_switch' AND meta_value = 'true'");
	foreach($hewe_query_raw as $hewe_post):
	    if ($hewe_post->post_id == get_the_ID()):
	        echo '<style>
            #elementor-switch-mode-button, #elementor-editor, #wp-admin-bar-elementor_edit_page {
                display:none;
            } 
            </style>';
	    endif;
	endforeach;
}