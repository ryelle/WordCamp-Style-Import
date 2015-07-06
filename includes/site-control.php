<?php

namespace WordCamp\Theme_Cloner;

defined( 'WPINC' ) or die();

/**
 * Custom Customizer Control for a WordCamp site
 */
class Site_Control extends \WP_Customize_Control {
	//public $type = 'wctcSite';    todo see add_control call for notes
	public $site_id, $site_name, $screenshot_url, $theme_slug;

	/**
	 * Enqueue scripts/styles
	 */
	public function enqueue() {
		wp_enqueue_style(  'wordcamp-theme-cloner' );
		wp_enqueue_script( 'wordcamp-theme-cloner' );

		// todo enqueue on section instead, b/c some js will run on those elements, or enqueue on plugin, b/c it acts on multiple components?
	}

	/**
	 * Don't render the control content from PHP, as it's rendered via JS on load.
	 */
	public function render_content() {
		$preview_url = add_query_arg(
			array(
				'theme' => $this->theme_slug,
				'wctc_source_site_id' => $this->site_id,        // todo don't need this anymore since using post_value()?   or still need it for theme switch
			),
			admin_url( 'customize.php' )
		);

		require( dirname( __DIR__ ) . '/templates/site-control.php' );
		// todo render js template instead b/c will have backbone collection so can filter on the fly. maybe not in v1 though?
	}
}
