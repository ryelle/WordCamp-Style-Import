<?php defined( 'WPINC' ) or die(); ?>

<li id="section-<?php echo esc_attr( $this->id ); ?>" class="accordion-section control-section control-section-<?php echo esc_attr( $this->type ); ?>">
	<h3>
		<?php esc_html_e( 'WordCamp Sites' ); ?>
		<span class="title-count wctc-sites-count"><?php echo count( $this->controls ); ?></span>
	</h3>

	<div class="wctc-sites-section-content">
		<div class="wctc-site-browser rendered">    <!-- todo need this extra wrapper? -->
			<ul id="wctc-sites"></ul>
		</div>
	</div>
</li>
