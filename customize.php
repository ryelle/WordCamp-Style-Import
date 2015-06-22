<?php

/**
 * Display a list of other WordCamps with buttons for Import and Preview.
 * Use the Customizer to show a live preview, and import the custom CSS on activation.
 */
class WordCamp_StyleImport_Customize {

	function __construct() {
		add_action( 'init', array( $this, 'customizer_buffer' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'customize_preview_init', array( $this, 'add_link_tag' ) );
		add_action( 'customize_register', array( $this, 'customize_register' ) );
		add_action( 'customize_save_wcsi_show_preview', array( $this, 'save_imported_style' ) );
	}

	/**
	 * Add the style selector to the Theme menu
	 */
	function add_menu_page() {
		add_theme_page( __( 'Import Style', 'wordcamporg' ), __( 'Import Style', 'wordcamporg' ), 'edit_theme_options', 'wcsi-sources', array( $this, 'display_page' ) );
	}

	/**
	 * Display all WordCamps on WordCamp Central for organizers to choose from.
	 */
	function display_page() {
		global $wpdb;
		?>
		<div class="wrap">
			<h2><?php _e( "Import Style from Another WordCamp", 'wordcamporg' ); ?></h2>

			<?php
				$home_url = home_url();
				$theme_url = admin_url( 'themes.php' );
				$customize_url = admin_url( 'customize.php' );
				$current_theme = get_stylesheet();

				switch_to_blog( 2 );
				$wordcamps = new WP_Query( array(
					'post_type'      => 'wordcamp',
					'posts_per_page' => -1,
					'meta_key'       => 'Start Date (YYYY-mm-dd)',
					'orderby'        => 'meta_value',
					'order'          => 'DESC'
				) );
			?>

			<div class="theme-browser">
			<?php while ( $wordcamps->have_posts() ): $wordcamps->the_post(); ?>
				<?php
					$url = parse_url( trailingslashit( get_post_meta( get_the_ID(), 'URL', true ) ) );
					$blog_details = false;

					if ( isset( $url['host'] ) && isset( $url['path'] ) ) {
						$blog_details = get_blog_details( array( 'domain' => $url['host'], 'path' => $url['path'] ), true );
					}

					if ( ! $blog_details ) {
						continue;
					}

					$theme = $wpdb->get_var(
						$sql = sprintf( "SELECT option_value FROM %s%d_options WHERE option_name = 'stylesheet';",
							$wpdb->base_prefix,
							$blog_details->blog_id
						)
					);

					$import_url = add_query_arg( array(
						'page'        => 'editcss',
						'source-site' => $blog_details->blog_id,
					), $theme_url );

					$preview_url = add_query_arg( array(
						'url'         => $home_url,
						'source-site' => $blog_details->blog_id,
						'theme'       => $theme,
					), $customize_url );

					$mshots = "http://s.wordpress.com/mshots/v1/";
					$mshots .= urlencode( str_replace( '.dev', '.org', get_post_meta( get_the_ID(), 'URL', true ) ) );
					$mshots = add_query_arg( array(
						'w' => 375,
						'h' => 250,
					), $mshots );
				?>

				<div class="theme">
					<div class="theme-screenshot">
						<img src="<?php echo $mshots; ?>" />
					</div>

					<h3 class="theme-name"><?php the_title(); ?></h3>

					<div class="theme-actions">
						<?php if ( $theme == $current_theme ) : ?>
							<a class="button button-primary activate" href="<?php echo $import_url; ?>">Import</a>
						<?php else : ?>
							<a class="button button-primary activate disabled" href="#">Import</a>
						<?php endif; ?>
						<a class="button button-secondary customize load-customize hide-if-no-customize" href="<?php echo $preview_url; ?>">Live Preview</a>
					</div><!-- /.theme-actions -->
				</div>

			<?php endwhile; ?>
			</div>

		</div>
		<?php
		restore_current_blog();
	}

	/**
	 * Add a section & control for our style preview.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	function customize_register( $wp_customize ) {
		$source_site = isset( $_GET['source-site'] ) ? absint( $_GET['source-site'] ) : 0;

		if ( $source_site ) {
			switch_to_blog( $source_site );
			$label = sprintf( __( 'Previewing styles from %s', 'wordcamporg' ), get_bloginfo( 'name' ) );
			$url = $this->link_tag();
			restore_current_blog();
		} else {
			// Doesn't really matter, we just need to keep the setting registered.
			$label = __( 'Not previewing another WordCamp\'s site.', 'wordcamporg' );
			$url = '';
		}

		$wp_customize->add_section( 'wcsi_preview', array(
			'title'    => __( 'WordCamp Style Import', 'wordcamporg' ),
			'priority' => 10,
		) );

		$wp_customize->add_setting( 'wcsi_show_preview', array(
			'default' => $url,
		) );

		$wp_customize->add_control( 'wcsi_show_preview', array(
			'label'       => $label,
			'section'     => 'wcsi_preview',
			'type'        => 'hidden',
			'description' => __( 'Placeholder text that could be a description.', 'wordcamporg' ),
		) );
	}

	function add_link_tag() {
		add_action( 'wp_head', array( $this, 'print_link_tag' ) );
	}

	function print_link_tag() {
		if ( ! $href = get_theme_mod( 'wcsi_show_preview', false ) ) {
			$href = $this->link_tag();
		}
		echo '<link rel="stylesheet" id="custom-css-css" type="text/css" href="' . esc_url( $href ) . '" />';
	}

	/**
	 * Called on customize initialization, add the previewing CSS.
	 */
	function link_tag() {
		$source_site = isset( $_GET['source-site'] ) ? absint( $_GET['source-site'] ) : 0;
		if ( ! $source_site ) {
			return;
		}

		if ( ! class_exists( 'Jetpack_Custom_CSS' ) ) {
			require Jetpack::get_module_path( 'custom-css' );
		}

		switch_to_blog( $source_site );
		$css = '';
		$safecss_post = Jetpack_Custom_CSS::get_current_revision();

		if ( ! empty( $safecss_post['post_content'] ) ) {
			$css = $safecss_post['post_content'];
		}


		$css = str_replace( array( '\\\00BB \\\0020', '\0BB \020', '0BB 020' ), '\00BB \0020', $css );

		if ( $css == '' ) {
			return;
		}

		$href = home_url( '/' );
		$href = add_query_arg( 'custom-css', 1, $href );
		$href = add_query_arg( 'csblog', $blog_id, $href );
		$href = add_query_arg( 'cscache', 6, $href );
		$href = add_query_arg( 'csrev', (int) get_option( $option . '_rev' ), $href );

		$href = apply_filters( 'safecss_href', $href, $blog_id );
		restore_current_blog();

		return $href;
	}

	/**
	 * Import the selected site's CSS after "Save & Activate" in the customizer.
	 *
	 * @param WP_Customize_Setting $setting Setting object for the setting we're saving.
	 *
	 * @return void
	 */
	function save_imported_style( $setting ) {
		$css = Jetpack_Custom_CSS::get_css();
		$current_post = Jetpack_Custom_CSS::get_current_revision();
		$current_preprocessor = get_post_meta( $current_post['ID'], 'custom_css_preprocessor', true );
		$current_theme = get_stylesheet();

		// Get our source blog and CSS
		$url = parse_url( $setting->post_value() );
		$blog_details = get_blog_details( array( 'domain' => $url['host'], 'path' => $url['path'] ), true );
		switch_to_blog( $blog_details->blog_id );

		$imported_css = Jetpack_Custom_CSS::get_css();
		$imported_post = Jetpack_Custom_CSS::get_current_revision();
		$imported_preprocessor = get_post_meta( $imported_post['ID'], 'custom_css_preprocessor', true );
		$imported_theme = wp_get_theme();

		if ( $imported_preprocessor && ( $current_preprocessor != $imported_preprocessor ) ) {
			$preprocessors = apply_filters( 'jetpack_custom_css_preprocessors', array() );

			if ( isset( $preprocessors[ $imported_preprocessor ] ) ) {
				$imported_css = call_user_func( $preprocessors[ $imported_preprocessor ]['callback'], $imported_css );
			}
		}
		restore_current_blog();

		// Save
		if ( $imported_theme->stylesheet != $current_theme ) {
			$css = $imported_css;
		} else {
			$css = $css . "\n" . $imported_css;
		}
		Jetpack_Custom_CSS::save( array(
			'css'          => $css,
			'preprocessor' => $current_preprocessor,
		) );
	}

	function customizer_buffer() {
		$source_site = isset( $_GET['source-site'] ) ? absint( $_GET['source-site'] ) : 0;
		if ( $source_site ) {
			ob_start( array( $this, 'buffer' ) );
		}
	}

	function buffer( $html ) {
		$replace = "<script type='text/javascript'>_wpCustomizeSettings.theme.active = false;</script>\n</body>";
		$html = str_replace( '</body>', $replace, $html );
		return $html;
	}

}

new WordCamp_StyleImport_Customize;
