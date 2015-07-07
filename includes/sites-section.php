<?php

namespace WordCamp\Theme_Cloner;

defined( 'WPINC' ) or die();

/**
 * Custom Customizer Section for WordCamp sites
 */
class Sites_Section extends \WP_Customize_Section {
	public $type = 'wctc-sites';

	/**
	 * Render the section's content
	 */
	protected function render() {
		require_once( dirname( __DIR__ ) . '/templates/sites-section.php' );
	}
}
