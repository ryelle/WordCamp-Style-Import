<?php defined( 'WPINC' ) or die(); ?>

<!--<div class="theme" tabindex="0" data-preview-url="<?php echo esc_attr( $preview_url ); ?>" aria-describedby="{{ data.theme.id }}-action {{ data.theme.id }}-name"> -->

<div id="wctc-site-<?php echo esc_attr( $this->site_id ); ?>" class="wctcSite" data-preview-url="<?php echo esc_url( $preview_url ); ?>" data-theme-slug="<?php echo esc_attr( $this->theme_slug ); ?>" data-site-id="<?php echo esc_attr( $this->site_id ); ?>">
	<div class="wctc-site-screenshot">
		<img src="<?php echo esc_url( $this->screenshot_url ); ?>" alt="<?php echo esc_attr( $this->site_name ); ?>" />
	</div>

	<h3 class="wctc-site-name">
		<?php echo esc_attr( $this->site_name ); ?>
	</h3>

	<span id="live-preview-label-<?php echo esc_attr( $this->site_id ); ?>" class="wctc-live-preview-label">Live Preview</span>

	<div class="wctc-actions">
		<!-- todo don't need anymore?
		<button type="button" class="button wctc-preview-site" data-wctc-site-id="<?php echo esc_attr( $this->site_id ); ?>">
			<?php _e( 'Preview', 'wordcamporg' ); ?>
		</button>
		-->
	</div>
</div>
