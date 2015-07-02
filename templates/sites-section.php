<?php defined( 'WPINC' ) or die(); ?>

<li id="section-<?php echo esc_attr( $this->id ); ?>" class="control-section control-section-<?php echo esc_attr( $this->type ); ?>">
	<h3><?php esc_html_e( 'WordCamp Sites' ); ?>
		<span class="title-count wctc-sites-count"><?php echo count( $this->controls ); ?></span>
	</h3>

	<div class="wctc-sites-section-content">
		<p>
			<label for="wctc-sites-filter">
				<span class="screen-reader-text"><?php _e( 'Search sites...' ); ?></span>
				<input type="search" id="wctc-sites-filter" placeholder="<?php esc_attr_e( 'Search sites...' ); ?>" />
			</label>
		</p>

		<div class="wctc-site-browser rendered">
			<ul id="wctc-sites"></ul>
		</div>
	</div>
</li>
