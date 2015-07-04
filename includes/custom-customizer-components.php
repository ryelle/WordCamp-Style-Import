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
 */
class Session_Setting extends \WP_Customize_Setting {       // todo better name than session? temporary? non-stored, etc
	public $type = 'wctc-session-setting';
	// todo add member for source_site_id? also for theme_slug?

	public function preview() {
		if ( ! $this->source_site_id = $this->manager->post_value( $this ) ) {
			return;
		}

		//$this->manager->theme();
		add_filter( 'pre_option_current_theme', array( $this, 'enable_source_site_theme' ), 11 );  // after \WP_Customize_Manager::current_theme()  // todo too late to do it here
		var_dump( current_filter() );

		// todo source site id is already sanitized, right?

		add_action( 'wp_head', array( $this, 'print_source_site_css' ), 99 );   // wp_print_styles is too early; the theme's stylesheet would get enqueued later and take precedence
			// todo might be more appropriate hook for this
	}

	/**
	 * Print the source site's custom CSS in an inline style block
	 *
	 * It can't be easily enqueued as an external stylesheet because Jetpack_Custom_CSS::link_tag() returns early
	 * in the Customizer if the theme being previewed is different from the live theme.
	 */
	public function print_source_site_css() {
		if ( method_exists( '\Jetpack', 'get_module_path' ) ) {
			require_once( \Jetpack::get_module_path( 'custom-css' ) );
		} else {
			return;
		}

		// todo this is getting called in <body> rather than <head>

		switch_to_blog( $this->source_site_id );
		printf( '<style id="custom-css-css">%s</style>', \Jetpack_Custom_CSS::get_css( true ) );    // todo might need to do diff ID to avoid conflicting w/ reg jetpack link tag. er, no, b/c jetpack returns false in customizer? maybe still good idfea for future proof, though
		restore_current_blog();
	}

	public function enable_source_site_theme() {
		var_dump($_REQUEST);
		//wp_die();
		echo 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
		return 'twentythirteen';
	}
}

/**
 * Custom Customizer Section for WordCamp sites
 */
class Sites_Section extends \WP_Customize_Section {
	public $type = 'wctc-sites';

	/**
	 * Render the sites section, which behaves like a panel.
	 */
	protected function render() {
		require_once( dirname( __DIR__ ) . '/templates/sites-section.php' );
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

		// todo enqueue on section instead, b/c some js will run on those elements, or enqueue on plugin, b/c it acts on multiple components?
	}

	/**
	 * Don't render the control content from PHP, as it's rendered via JS on load.
	 */
	public function render_content() {
		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$preview_url = add_query_arg(
			array(
				'theme' => $this->theme_slug,
				'wctc_source_site_id' => $this->site_id,        // todo don't need this anymore since using post_value()?   or still need it for theme switch
			),
			$current_url
		);

		require( dirname( __DIR__ ) . '/templates/site-control.php' );
		// todo render js template instead b/c will have backbone collection so can filter on the fly. maybe not in v1 though?
	}
}
