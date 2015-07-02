
// todo strict

// todo convert to QNI style js

( function( wp, $ ) {
	if ( ! wp || ! wp.customize ) {
		return;
	}

	api = wp.customize;

	api.wctcSiteControl = api.Control.extend( {
		ready : function() {
			this.container.on( 'click', '.wctc-site', this.previewSite );

			// todo register search. requires backbone models etc, can probably copy a lot from QNI

		},

		previewSite : function() {
			var previewUrl = $( this ).data( 'previewUrl' );

			$( '.wp-full-overlay' ).addClass( 'customize-loading' );

			alert( previewUrl );
			//window.parent.location = previewUrl;

			// change theme
			// add css

			// have to refresh to change theme? look at how theme switcher does it. add ?theme= to url
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
