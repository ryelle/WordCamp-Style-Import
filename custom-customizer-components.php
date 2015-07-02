<?php

namespace WordCamp\Theme_Cloner;

defined( 'WPINC' ) or die();

/**
 * Custom Customizer Setting for a temporary session setting
 *
 * because `type` is not handled, `update()` will just fire action and not save anywhere
 *
 * todo explain why needed, don't want to store anything in db
 * not tied to db setting like most, just need it temporary then will copy stuff to new site
 *
 * todo just use WP_Customize_Filter_Setting instead?
class Session_Setting extends \WP_Customize_Setting {
	public $type = 'wctc-session-setting';
}
 *
 */

/**
 * Custom Customizer Section for WordCamp sites
 */
class Sites_Section extends \WP_Customize_Section {
	public $type = 'wctc-sites';

	/**
	 * Render the sites section, which behaves like a panel.
	 */
	protected function render() {
		require_once( __DIR__ . '/templates/sites-section.php' );
	}
}

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

		// todo not working?
	}

	/**
	 * Don't render the control content from PHP, as it's rendered via JS on load.
	 */
	public function render_content() {
		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$preview_url = add_query_arg(
			array(
				'site_id' => $this->site_id,    // todo need to be wctc_site_id so php handler can recognize it uniquely?
				'theme'   => $this->theme_slug,
			),
			$current_url
		);

		require( __DIR__ . '/templates/site-control.php' );
		// todo render js template instead b/c will have backbone collection so can filter on the fly. maybe not in v1 though?
	}
}