/**
 * SmartUpWorld Post Slider — Admin Documentation Page
 * Copy-to-clipboard for shortcode examples.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var buttons = document.querySelectorAll( '.suw-copy-btn' );

		Array.prototype.forEach.call( buttons, function ( btn ) {
			btn.addEventListener( 'click', function () {
				var text = btn.getAttribute( 'data-clipboard' ) || '';
				var original = btn.textContent;

				function done() {
					btn.textContent = ( window.suwPsAdmin && window.suwPsAdmin.copied ) || 'Copied!';
					window.setTimeout( function () {
						btn.textContent = original;
					}, 1500 );
				}

				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					navigator.clipboard.writeText( text ).then( done ).catch( function () {
						fallbackCopy( text, done );
					} );
				} else {
					fallbackCopy( text, done );
				}
			} );
		} );

		function fallbackCopy( text, callback ) {
			var area = document.createElement( 'textarea' );
			area.value = text;
			area.setAttribute( 'readonly', '' );
			area.style.position = 'absolute';
			area.style.left = '-9999px';
			document.body.appendChild( area );
			area.select();
			try {
				document.execCommand( 'copy' );
				callback();
			} catch ( e ) {
				// Clipboard unavailable — leave the button label unchanged.
			}
			document.body.removeChild( area );
		}
	} );
} )();
