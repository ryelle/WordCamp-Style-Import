( function( wp, $ ) {
	'use strict';

	if ( ! wp || ! wp.customize ) {
		return;
	}

	var api = wp.customize;

	api.wctcPanel = api.Panel.extend( {
		/**
		 * todo
		 */
		ready : function() {
			var urlParams = this.getUrlParams( window.location );
			
			if ( urlParams.hasOwnProperty( 'wctc_source_site_id' ) ) {
				this.expand();
				api( 'wctc_source_site_id' ).set( urlParams.wctc_source_site_id );
					// todo explain why have to do this, b/c it's inefficient. have to reload entire theme taking page refresh, but can't import styles then b/c {forget why}
			}
		},

		/**
		 * Parse the URL parameters
		 *
		 * @see https://stackoverflow.com/a/2880929/450127
		 *
		 * @param {string} url
		 *
		 * @returns {object}
		 */
		getUrlParams : function( url ) {
			var match,
				urlParams = {},
				pl        = /\+/g,  // Regex for replacing addition symbol with a space
				search    = /([^&=]+)=?([^&]*)/g,
				query     = url.search.substring( 1 ),
				decode    = function ( s ) {
					return decodeURIComponent( s.replace( pl, " " ) );
				};

			while ( match = search.exec( query ) ) {
				urlParams[ decode( match[ 1 ] ) ] = decode( match[ 2 ] );
			}

			return urlParams;
		}
	} );

	api.wctcSiteControl = api.Control.extend( {
		/**
		 * todo
		 */
		ready : function() {
			//console.log( 'control ready()' );   // todo ready() gets called once for every control 5x? only want to do this once



			this.container.on( 'click', '.wctcSite', this.previewSite );    // todo need to specify class? doesn't this.container already mean that?
			//console.log( this.container );



			// need to bind to the setting being changed instead of making our own click event?
		},

		/**
		 * Preview the selected site
		 *
		 * If the site is using a different theme, then reload the entire Customizer with the theme URL parameter
		 * set, so that the Theme Switcher will do that for us. Otherwise just set the ID to refresh the Previewer.
		 *
		 * todo explain reloading page has to be done in js rather than a normal link, because otherwise they'd get a confirm() dialog when navigating away from page where had changed input fields
		 *      er, wait, that's being caused by something else and still happens when doing it here. wtf. there was a comment in some core code somewhere about that
		 *
		 * @param {object} event
		 */
		previewSite : function( event ) {
			var previewUrl         = $( this ).data( 'preview-url' ),
				//previewUrlParams   = api.wctcSiteControl.getUrlParams( previewUrl ),
				requestedSiteTheme = $( this ).data( 'theme-slug' ),    // extract from previewurl, or append to previewurl? don't want duplicated in both
				requestedSiteID    = $( this ).data( 'site-id' );                   // same duplication here

			//console.log( previewUrlParams );

			// can just do data() to get all at once
			// not all of that is needed? if remove, remove from markup too

			if ( api( 'wctc_source_site_id' ).get() == requestedSiteID ) {
				return;
			}

			if ( api.settings.theme.stylesheet === requestedSiteTheme ) {
				api( 'wctc_source_site_id' ).set( requestedSiteID );

				// todo might want to update URL with new ID anyway, just to be proper, but that would involve pushstate to avoid refreshing page? dunno, probably not worth it at this fucking point
			} else {
				window.parent.location = previewUrl;

			    // todo wtf, just make it a fucking link to begin with. remove cursor: pointer toop. or is this needed to trigger the setting?
					// but also have to set site id, maybe do that on php side or something?
					// tried and caused problem w/ confirm() dialog b/c leaving edited page, but could have been unrelated?
			}
		}
	} );

	$.extend( api.panelConstructor,   { wctcPanel : api.wctcPanel       } );
	$.extend( api.controlConstructor, { wctcSite  : api.wctcSiteControl } );
} )( window.wp, jQuery );
