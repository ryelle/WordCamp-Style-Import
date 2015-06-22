<?php
/*
Plugin Name: WordCamp Style Import
Version: 0.1.0
*/

/**
 * Import a style from another WordCamp
 */
class WordCamp_StyleImport {
	private $messages = array();

	function __construct() {
		add_action( 'admin_enqueue_scripts',  array( $this, 'scripts_styles' ) );
		add_filter( 'safecss_css',            array( $this, 'clone_css' ) );
		add_action( 'admin_footer',           array( $this, 'display_messages' ) );
	}

	/**
	 * Add our custom styles.
	 */
	function scripts_styles() {
		wp_enqueue_style( 'wc-style-import', plugins_url( 'style.css', __FILE__ ) );
	}

	/**
	 * Get the selected site's CSS and append it to the current CSS.
	 *
	 * @todo Mode, Content Width?
	 */
	function clone_css( $css ) {
		if ( ! is_admin() ) {
			return $css;
		}

		$current_screen = get_current_screen();
		if ( 'appearance_page_editcss' !== $current_screen->id ) {
			return $css;
		}

		// `action` is set when the CSS is saved. We don't want to double-append CSS.
		if ( isset( $_POST['action'] ) ) {
			return $css;
		}

		$source_site = isset( $_GET['source-site'] ) ? absint( $_GET['source-site'] ) : 0;
		if ( ! $source_site ) {
			return $css;
		}

		$current_post = Jetpack_Custom_CSS::get_current_revision();
		$current_preprocessor = get_post_meta( $current_post['ID'], 'custom_css_preprocessor', true );
		$current_theme = get_stylesheet();

		switch_to_blog( $source_site );
		remove_filter( 'safecss_css', array( $this, 'clone_css' ) );
		/* Now working in the source site. */

		$source_name = str_replace( array( 'http://', 'https://' ), '', home_url() );
		if ( false !== strpos( $css, "/*==Imported from {$source_name}==*/" ) ) {
			$this->add_message( __( "It looks like you've already imported this site.", 'wordcamp-style-import' ) );
			return $css;
		}

		/* Check that we're using the same theme. If not, suggest changing. */
		$source_theme = wp_get_theme();
		if ( $source_theme->stylesheet != $current_theme ) {
			restore_current_blog();

			$message = __( "The site you're importing from is using a different base theme. ", 'wordcamp-style-import' );
			$message .= '<a href="' . add_query_arg( 'theme', $source_theme->stylesheet, admin_url( 'themes.php' ) ) . '">';
			$message .= sprintf( __( "Switch to %s." ), $source_theme );
			$message .= '</a>';
			$this->add_message( $message );
			return $css;
		}

		/* If our site is currently using vanilla CSS, convert the import and use that. */
		$imported_css = Jetpack_Custom_CSS::get_css();
		$imported_post = Jetpack_Custom_CSS::get_current_revision();
		$imported_preprocessor = get_post_meta( $imported_post['ID'], 'custom_css_preprocessor', true );
		if ( $imported_preprocessor && ( $current_preprocessor != $imported_preprocessor ) ) {
			$preprocessors = apply_filters( 'jetpack_custom_css_preprocessors', array() );

			if ( isset( $preprocessors[ $imported_preprocessor ] ) ) {
				$imported_css = call_user_func( $preprocessors[ $imported_preprocessor ]['callback'], $imported_css );
			}
		}

		/* Back to our site. */
		add_filter( 'safecss_css', array( $this, 'clone_css' ) );
		restore_current_blog();

		$this->add_message( __( "The selected site's CSS has been added below, but has not been saved yet.", 'wordcamp-style-import' ), 'warning' );

		return $css . "\n" . $imported_css . "\n/*==Imported from {$source_name}==*/";
	}

	/**
	 * Add a message (error or success) to our message queue.
	 * Note that anything added after admin_footer won't display.
	 */
	function add_message( $message = '', $type = 'error' ) {
		if ( ! $message ) {
			return;
		}
		$this->messages[] = sprintf( '<div class="updated %s"><p>%s</p></div>', $type, $message );
	}

	/**
	 * Display any messages we might have after importing the new CSS.
	 *
	 * These are technically displayed in the footer, but wp-admin uses
	 * JS to pull notices up to the correct location.
	 */
	function display_messages() {
		foreach ( $this->messages as $message ) {
			echo $message;
		}
	}

}

new WordCamp_StyleImport;

require_once __DIR__ . '/customize.php';
