<?php defined( 'WPINC' ) or die(); ?>

<!-- todo wctcSite could be dashes? has to match control name or setting name or something exactly i think -->

<div id="wctc-site-<?php echo esc_attr( $this->site_id ); ?>" class="wctcSite" data-preview-url="<?php echo esc_url( $preview_url ); ?>" data-theme-slug="<?php echo esc_attr( $this->theme_slug ); ?>" data-site-id="<?php echo esc_attr( $this->site_id ); ?>">
	<!-- todo don't need all the data- params above? -->

	<div class="wctc-site-screenshot">
		<img src="<?php echo esc_url( $this->screenshot_url ); ?>" alt="<?php echo esc_attr( $this->site_name ); ?>" />
	</div>

	<h3 class="wctc-site-name">
		<?php echo esc_attr( $this->site_name ); ?>
	</h3>

	<span id="live-preview-label-<?php echo esc_attr( $this->site_id ); ?>" class="wctc-live-preview-label">Live Preview</span>
</div>
