
// todo strict

// todo convert to QNI style js

( function( wp, $ ) {
	if ( ! wp || ! wp.customize ) {
		return;
	}

	api = wp.customize;

	api.wctcSiteControl = api.Control.extend( {
		ready : function() {
			this.container.on( 'click', '.wctcSite', this.previewSite );    // todo need to specify class? doesn't this.container already mean that?

			if ( true ) {
				//api( 'wctc_source_site_id' ).set( 71 );
				wp.customize.panel( 'wordcamp_theme_cloner' ).expand();
			}

			// need to bind to the setting being changed instead of making our own click event?

			// todo register search. requires backbone models etc, can probably copy a lot from QNI
				// api might handle all that for you
		},

		previewSite : function() {
			var previewUrl = $( this ).data( 'preview-url' ),    // todo should be preview-url ?
				theme      = $( this ).data( 'theme-slug' ),    // extract from previewurl, or append to previewurl? don't want duplicated in both
				site_id    = $( this ).data( 'site-id' );
			// can just do data() to get all at once

			//$( '.wp-full-overlay' ).addClass( 'customize-loading' );    // todo is this doing anything if we're just redirectiong? maybe good if redirect takes a long time?



			// set theme value and submit the form instead?

			// set customized values

			//console.log( previewUrl );
			//api.previewer.set( previewUrl );



/*
			if ( api.settings.theme.stylesheet === theme ) {
				api( 'wctc_source_site_id' ).set( site_id );
			} else {
				window.parent.location = previewUrl;    // todo wtf, just make it a fucking link to begin with. remove cursor: pointer toop. or is this needed to trigger the setting?
				// but also have to set site id, maybe do that on php side or something?
				// also have to open the fucking panel
			}
*/



/*
would rather do it this way
			if ( api.settings.theme.stylesheet !== theme ) {
				// switch the theme
			}

			api( 'wctc_source_site_id' ).set( site_id );
			*/


			api( 'wctc_source_site_id' ).set( site_id );
		},

		/**
		 * todo
		 * Show or hide the theme based on the presence of the term in the title, description, and author.
		 */
		filter: function( term ) {
			// todo this is just copy/paste, need to rework
			// might need to be using underscore templates in order for this to work? or maybe not
			// maybe don't even need to setup backone models/controllers etc b/c customizer already does that?

			// todo this really belongs as part of section,not control, right?
				// or this part is here, but controller part is in section?


			// This probably isn't the best way to do this, but it works for searches like an author, a name, a tag (ex. one column), and certain keywords.
			var control = this,
				haystack = control.params.theme.name + ' '
					+ control.params.theme.description + ' '
					+ control.params.theme.tags + ' '
					+ control.params.theme.author;
			haystack = haystack.toLowerCase().replace( '-', ' ' );
			if ( -1 !== haystack.search( term ) ) {
				control.activate();
			} else {
				control.deactivate();
			}
		}
	} );

	$.extend( api.controlConstructor, {
		wctcSite: api.wctcSiteControl
	} );
} )( window.wp, jQuery );
