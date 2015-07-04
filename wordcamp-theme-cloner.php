<?php

namespace WordCamp\Theme_Cloner;

defined( 'WPINC' ) or die();

/*
Plugin Name: WordCamp Theme Cloner
Description: Allows organizers to clone the theme and custom CSS, etc from other WordCamps as a starting point for their own site.
Version:     0.1
Author:      WordCamp.org
Author URI:  http://wordcamp.org
License:     GPLv2 or later
*/


/* todo
 *
 * i18n strings
 * phpdoc everything
 * change name to site cloner, update prefix
 * setup menus and widgets too, home page post vs page option
 */


/*
 * todo commit msg
 *
 * because such a fundamental change, removed 90% of code, so went ahead and rewrote from scratch rather than trying to integrate the two
 *
 * fixes 11, { 3 others }
 *
 * ---
 *
 * leave comment on #4 saying to migrate to backbone and _ templates instead of php, so that can filter on the fly
 */



add_action( 'plugins_loaded',        __NAMESPACE__ . '\get_wordcamp_sites' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\register_scripts' );
add_action( 'customize_register',    __NAMESPACE__ . '\register_customizer_components', 21 );  // todo 20 necessary?

function register_scripts() {
	wp_register_style(
		'wordcamp-theme-cloner',
		plugin_dir_url( __FILE__ ) . 'wordcamp-theme-cloner.css',
		array(),
		1
	);

	wp_register_script(
		'wordcamp-theme-cloner',
		plugin_dir_url( __FILE__ ) . 'wordcamp-theme-cloner.js',
		array( 'jquery', 'customize-controls' ),
		1,
		true

	// todo depends bakcbone/underscoire ?
	);
}

/**
 * todo
 *
 * @param \WP_Customize_Manager $wp_customize
 */
function register_customizer_components( $wp_customize ) {
	require_once( __DIR__ . '/includes/session-setting.php' );
	require_once( __DIR__ . '/includes/sites-section.php' );
	require_once( __DIR__ . '/includes/site-control.php' );

	$wp_customize->register_control_type( __NAMESPACE__ . '\Site_Control' );

	$wp_customize->add_setting( new Session_Setting(
		$wp_customize,
		'wctc_source_site_id',
		array(
			'default'           => 0,                                   // todo not needed?
			// 'type'              => 'wctc_filter_setting',   // todo better name
			'sanitize_callback' => 'absint',        // todo not needed, or need to actually use this instead of doing manually?
		)

	// todo capability should be array( 'switch_themes', 'edit_theme_options' ) ? same in normal, but good defence in depth. api allows array or just single cap?

	// todo add js sanitization callback?

	// pass in `transport` or hardcode in class? same for other settings?

	// todo but don't want permenant setting, just make it empty b/c need something for control to show up, but won't actually use it?
	// also don't want to create a setting for each control, just one setting. could maybe make it a multidemensional array if have to
	// todo create new setting type that doesn't save to db
	// written but test w/ storing it first to make sure preview/switch works first
	// use WP_Customize_Filter_Setting instead?
	) );

	$wp_customize->add_panel( 'wordcamp_theme_cloner', array(
		'title'       => 'Clone Another WordCamp',
		'description' => "Clone another WordCamp's theme and custom CSS as a starting point for your site.",
	) );

	$wp_customize->add_section( new Sites_Section(
		$wp_customize,
		'wctc_sites',
		array(
			'panel' => 'wordcamp_theme_cloner',
			'title' => 'WordCamp Sites',
		)
	) );

	foreach( get_wordcamp_sites() as $wordcamp ) {
		$wp_customize->add_control( new Site_Control(
			$wp_customize,
			'wctc_site_id_' . $wordcamp['site_id'],
			array(
				'site_id'        => $wordcamp['site_id'],
				'site_name'      => $wordcamp['name'],
				'theme_slug'     => $wordcamp['theme_slug'],
				'screenshot_url' => $wordcamp['screenshot_url'],
				'settings'       => 'wctc_source_site_id',          // todo assign in control class?
				'section'        => 'wctc_sites',
				'type'           => 'wctcSite'                      // todo should be able to set this in control instead of here, but if do that then breaks
			)
		) );

		// todo maybe make this same control for each b/c that's how `radio` works? but theme switcher uses diff

	}






	/*
	$wp_customize->add_control( 'wctc_site_id', array(  // todo name
		'label'      => __( 'WordCamp Site', 'themename' ),
		'section'    => 'wctc_sites',
		'settings'   => 'wctc_source_site_id',
		'type'       => 'radio',
		'choices'    => array(
			'14' => 'SF 2013',
			'71' => 'SF 2014',
			'13' => 'Atlanta 2014',
		),
	) );
*/







	/*
		class Test_Setting extends \WP_Customize_Setting {
			public function preview() {
				return;


				var_dump( $this->_original_value, $this->_previewed_blog_id );

				if ( ! isset( $this->_original_value ) ) {
					$this->_original_value = $this->value();
				}

				if ( ! isset( $this->_previewed_blog_id ) ) {
					$this->_previewed_blog_id = get_current_blog_id();
				}

				var_dump( $this->_original_value, $this->_previewed_blog_id );

				var_dump( $this->manager->post_value( $this ) );
			}
		}


		$wp_customize->add_section( 'themename_color_scheme', array(
			'title'          => __( 'Color Scheme', 'themename' ),
			'priority'       => 35,
		) );

		$wp_customize->add_setting( new Test_Setting(
			$wp_customize,
			'color_scheme2',
			array(
				'default'        => 'some-default-value',
				'type'           => 'theme_mod',
				'capability'     => 'edit_theme_options',
			)
		) );

		$wp_customize->add_control( 'themename_color_scheme', array(
			'label'      => __( 'Color Scheme', 'themename' ),
			'section'    => 'themename_color_scheme',
			'settings'   => 'color_scheme2',
			'type'       => 'radio',
			'choices'    => array(
				'value1' => 'Choice 1',
				'value2' => 'Choice 2',
				'value3' => 'Choice 3',
			),
		) );
	*/

}

/**
 * Get required data for relevant WordCamp sites
 *
 * todo explain why called at plugins_loaded, to prime the set transient before gets called at register_customizer_components
 *   has to be done before setup_theme, because customerizer fucks with current theme and need to use original theme
 *   more elegant to do it before it setup than mucking with it's internal shit to reverse and reapply filters
 *
 * is used during register_customizer_components, but that's too late to set, so have to call earlier to prime the transient cache
 *
 * @return array
 */
function get_wordcamp_sites() {
	// plugins_loaded runs on every screen, but we only need this when loading the Customizer and Previewer
	if ( 'customize.php' != basename( $_SERVER['SCRIPT_NAME'] ) && empty( $_REQUEST['wp_customize'] ) ) {
		return array();
	}

	$transient_key = 'wctc_sites';

	if ( $sites = get_site_transient( $transient_key ) ) {
		return $sites;
	}

	switch_to_blog( BLOG_ID_CURRENT_SITE ); // central.wordcamp.org

	$sites = array();
	$wordcamps = get_posts( array(
		'post_type'      => 'wordcamp',
		'post_status'    => 'publish',
		'posts_per_page' => 50,

		/*
		 * @todo
		 *
		 * exclude camps that aren't done building their theme yet
		 *  - those that have coming soon enabled
		 *  - those that are more the X months away from camp?
		 *  - what other criteria could be used to determine this?
		 *  - only ones with dates and urls -- already doing below, but can do in meta query instead? probably faster in meta query, even though meta queries are slow
		 *  - tickets open (before camp starts) or attendees (after camp closed) ?
		 *
		 * exclude camps older than 2-3 years? will probably be out of fashion and don't want to have to sort through bazillion choices, even with filters
		 *
		 * need to sort by most recent to get good results b/c of
		 *
		 * remove the posts_per_page limit when #4 is implemented
		 */
	) );

	foreach( $wordcamps as $wordcamp ) {
		$site_id  = get_wordcamp_site_id( $wordcamp );
		$site_url = get_post_meta( $wordcamp->ID, 'URL', true );

		if ( ! $site_id || ! $site_url ) {
			continue;
		}

		switch_to_blog( $site_id );

		$sites[] = array(
			'site_id'        => $site_id,
			'name'           => get_wordcamp_name(),
			'theme_slug'     => get_stylesheet(),
			'screenshot_url' => get_screenshot_url( $site_url ),
		);

		restore_current_blog();
	}

	restore_current_blog();

	set_site_transient( $transient_key, $sites, DAY_IN_SECONDS );
	// what's the size of the array when there are 1000 sites in it? bigger than 1MB ?

	return $sites;
}

/**
 * Get the mShot URL for the given site URL
 *
 * Allow it to be filtered so that production URLs can be changed to match development URLs in local environments.
 *
 * @param string $site_url
 *
 * @return string
 */
function get_screenshot_url( $site_url ) {
	$screenshot_url = "https://www.wordpress.com/mshots/v1/" . rawurlencode( $site_url );

	$screenshot_url = add_query_arg(
		array(
			'w' => 375,
			'h' => 250,
		),
		$screenshot_url
	);

	return apply_filters( 'wctc_site_screenshot_url', $screenshot_url );
}


/*
if ( true ) {
	// want the initial preview load to call print_source_site_css()

	// todo better way to do this?

	//$_REQUEST['theme']='twentythirteen';
get_option();
	add_filter( 'pre_option_stylesheet', function ( $default ) {
		var_dump( $default );

		return 'twentythirteen';

	}, 11 );  // after \WP_Customize_Manager::current_theme()  // todo too late to do it here

}
add_filter( 'template', __NAMESPACE__ . '\foo' );
add_filter( 'stylesheet', __NAMESPACE__ . '\foo' );
function foo( $old ) {
	return $old;
	return 'twentytwelve';
}


*/
