jQuery(function ($) {
	// Define the function
	function removeElementor(item) {
		// Check if user is on the post - either viewing or editing
		if ($('#_acf_post_id').val() == item || $('#post_ID').val() == item || $('body').hasClass('page-id-' + item)) {
			$('#elementor-switch-mode-button').hide();
			$('#elementor-editor').hide();
			$('#wp-admin-bar-elementor_edit_page').hide();
		}
		// Also remove the Edit With Elementor link in the WordPress admin post list
		$('tr#post-' + item + ' .edit_with_elementor').hide();
	}
	// Run the function for all IDs provided in the array
	if (hewevar.pagelist) {
		const idList = hewevar.pagelist;
		idList.forEach(removeElementor);
	}
}); // jQuery End