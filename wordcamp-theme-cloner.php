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
 * change name to site cloner, update prefix
 * setup menus and widgets too, home page post vs page option
 */


/*
 * todo commit msg
 *
 * because such a fundamental change, removed 90% of code, easier to rewrite from scratch rather than trying to integrate the two
 *
 * fixes 11, { several others }
 */



add_action( 'plugins_loaded',        __NAMESPACE__ . '\get_wordcamp_sites' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\register_scripts' );
add_action( 'customize_register',    __NAMESPACE__ . '\register_customizer_components' );

/**
 * Register scripts and styles
 */
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
	);
}

/**
 * Register our Customizer settings, panels, sections, and controls
 *
 * @param \WP_Customize_Manager $wp_customize
 */
function register_customizer_components( $wp_customize ) {
	require_once( __DIR__ . '/includes/source-site-id-setting.php' );
	require_once( __DIR__ . '/includes/sites-section.php' );
	require_once( __DIR__ . '/includes/site-control.php' );

	$wp_customize->register_control_type( __NAMESPACE__ . '\Site_Control' );

	$wp_customize->add_setting( new Source_Site_ID_Setting(
		$wp_customize,
		'wctc_source_site_id',
		array()
	) );

	$wp_customize->add_panel(
		'wordcamp_theme_cloner',
		array(
			'type'        => 'wctcPanel',
			'title'       => 'Clone Another WordCamp',
			'description' => "Clone another WordCamp's theme and custom CSS as a starting point for your site.",
		)
	);

	$wp_customize->add_section( new Sites_Section(
		$wp_customize,
		'wctc_sites',
		array(
			'panel' => 'wordcamp_theme_cloner',
			'title' => 'WordCamp Sites',
		)
	) );

	foreach( get_wordcamp_sites() as $wordcamp ) {
		if ( get_current_blog_id() == $wordcamp['site_id'] ) {
			continue;
		}

		$wp_customize->add_control( new Site_Control(
			$wp_customize,
			'wctc_site_id_' . $wordcamp['site_id'],
			array(
				'type'           => 'wctcSite',                      // todo should be able to set this in control instead of here, but if do that then control contents aren't populated
				'site_id'        => $wordcamp['site_id'],
				'site_name'      => $wordcamp['name'],
				'theme_slug'     => $wordcamp['theme_slug'],
				'screenshot_url' => $wordcamp['screenshot_url'],
			)
		) );
	}
}

/**
 * Get required data for relevant WordCamp sites
 *
 * This isn't actually used until register_customizer_components(), but it's called during `plugins_loaded` in
 * order to prime the cache. That has to be done before `setup_theme`, because the Theme Switcher will override
 * the current theme when `?theme=` is present in the URL parameters, and it's safer to just avoid that than to
 * muck with the internals and try to reverse it on the fly.
 *
 * @return array
 */
function get_wordcamp_sites() {
	// plugins_loaded is runs on every screen, but we only need this when loading the Customizer and Previewer
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
		'posts_per_page' => 50, // todo temporary workaround until #4 is in place
		'meta_key'       => 'Start Date (YYYY-mm-dd)',
		'orderby'        => 'meta_value_num',

		'meta_query' => array(
			array(
				'key'     => 'Start Date (YYYY-mm-dd)',
				'value'   => strtotime( 'now - 1 month' ),
				'compare' => '<'
			),

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
			 */
		),
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
	$screenshot_url = add_query_arg( 'w', 275, 'https://www.wordpress.com/mshots/v1/' . rawurlencode( $site_url ) );

	return apply_filters( 'wctc_site_screenshot_url', $screenshot_url );
}
