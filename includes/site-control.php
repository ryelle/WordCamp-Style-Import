<?php

namespace WordCamp\Theme_Cloner;

defined( 'WPINC' ) or die();

/**
 * Custom Customizer Control for a WordCamp site
 */
class Site_Control extends \WP_Customize_Control {
	public $site_id, $site_name, $screenshot_url, $theme_slug;
	public $settings = 'wctc_source_site_id';
	public $section  = 'wctc_sites';

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue() {
		wp_enqueue_style(  'wordcamp-theme-cloner' );
		wp_enqueue_script( 'wordcamp-theme-cloner' );
	}

	/**
	 * Render the control's content
	 */
	public function render_content() {
		$preview_url = add_query_arg(
			array(
				'theme'               => rawurlencode( $this->theme_slug ),
				'wctc_source_site_id' => rawurlencode( $this->site_id ),
			),
			admin_url( 'customize.php' )
		);

		require( dirname( __DIR__ ) . '/templates/site-control.php' );
	}
}
