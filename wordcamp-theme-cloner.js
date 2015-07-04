
// todo strict

// todo convert to QNI style js

( function( wp, $ ) {
	if ( ! wp || ! wp.customize ) {
		return;
	}

	api = wp.customize;

	api.wctcSiteControl = api.Control.extend( {
		ready : function() {
			var urlParams = this.getUrlParams();

			this.container.on( 'click', '.wctcSite', this.previewSite );    // todo need to specify class? doesn't this.container already mean that?

			if ( urlParams.hasOwnProperty( 'wctc_source_site_id' ) ) {
				wp.customize.panel( 'wordcamp_theme_cloner' ).expand();

				api( 'wctc_source_site_id' ).set( urlParams.wctc_source_site_id );
				// todo explain why have to do this, b/c it's inefficient. have to reload entire theme taking page refresh, but can't import styles then b/c {forget why}

				// todo ready() gets called 5x? only want to do this once
			}

			// need to bind to the setting being changed instead of making our own click event?

			// todo register search. requires backbone models etc, can probably copy a lot from QNI
				// api might handle all that for you, setting control.deactivatte like filter() does below
		},

		// todo https://stackoverflow.com/a/2880929/450127
		getUrlParams : function() {
			var match,
				urlParams = {},
				pl     = /\+/g,  // Regex for replacing addition symbol with a space
				search = /([^&=]+)=?([^&]*)/g,
				decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
				query  = window.location.search.substring(1);

			while (match = search.exec(query))
				urlParams[decode(match[1])] = decode(match[2]);

			return urlParams;
		},

		// todo explain reloading page has to be done in js rather than a normal link, because otherwise they'd get a confirm() dialog when navigating away from page where had changed input fields
			// er, wait, that's being caused by something else and still happens when doing it here. wtf. there was a comment in some core code somewhere about that
		previewSite : function( event ) {
			var previewUrl = $( this ).data( 'preview-url' ),
				theme_being_previewed = $( this ).data( 'theme-slug' ),    // extract from previewurl, or append to previewurl? don't want duplicated in both
				site_id    = $( this ).data( 'site-id' );                   // same duplication here

			// can just do data() to get all at once
			// not all of that is needed? if remove, remove from markup too

			//$( '.wp-full-overlay' ).addClass( 'customize-loading' );    // todo is this doing anything if we're just redirectiong? maybe good if redirect takes a long time?

			// todo don't preview if already on site that was clicked on


			// set theme value and submit the form instead?

			// set customized values

			//console.log( previewUrl );
			//api.previewer.set( previewUrl );



			if ( api.settings.theme.stylesheet === theme_being_previewed ) {
				api( 'wctc_source_site_id' ).set( site_id );
				// todo need to update ID in URL param so that when clicking save that gets the right one?
					// doesn't look like it. the ajax request to save()/update() contains the active one.
					// might want to do thsi anyway just to be proper, but that would involve pushstate to avoid refreshing page? dunno, probably not worth it at this fucking point
			} else {
				window.parent.location = previewUrl;    // todo wtf, just make it a fucking link to begin with. remove cursor: pointer toop. or is this needed to trigger the setting?
				// but also have to set site id, maybe do that on php side or something?
				// also have to open the fucking panel


			}




/*
would rather do it this way
			if ( api.settings.theme.stylesheet !== theme ) {
				// switch the theme
			}

			api( 'wctc_source_site_id' ).set( site_id );
			*/


			//api( 'wctc_source_site_id' ).set( site_id );
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
