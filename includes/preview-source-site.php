<?php

// todo remove this?

namespace WordCamp\Theme_Cloner;

defined( 'WPINC' ) or die();

add_action( 'customize_preview_wctc_source_site_id', __NAMESPACE__ . '\preview_source_site' );

/* todo how does the theme preview one work
	special case, don't try to immetate
	find canonical examploe of how a transport -> default one works, and do that
	have to refresh page anyway to change screen, so no point using postMessage. couldn't even if wanted to, prob
*/



// todo need a callback for customize_value_{id} ? if not here, maybe in import.php ?

// todo dequeue any current custom css?

// todo overwrite preview() instead of using filters?

/**
 * Register hook callback functions that only run in the Previewer
 *
 * @param \WP_Customize_Filter_Setting $setting
 */
function preview_source_site( $setting ) {

	/*var_dump(
		$setting->id,
		$setting->type,
		$setting->default

		$setting->site_id,
		$setting->name,
		$setting->theme_slug,
		$setting->screenshot_url

		// todo need to get this setting populated somehow? is that how it would normally work? or
	);
	*/
/*
	var_dump(
		//$setting->manager->get_setting( 'wctc_source_site_id' )
		//$setting->manager->_previewed_blog_id,
		$setting->manager->post_value( $setting )
	);
*/

}
