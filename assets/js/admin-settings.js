/**
 * Admin settings script.
 *
 * { global, rs_featured_image_settings_data }
 *
 * @package ReallySimpleFeaturedImage
 */

( function( $, data ) {
	$(
		function() {
			// Edit prompt.
			$(
				function() {
					let changed = false;

					$( 'input, textarea, select, checkbox' ).change(
						function() {
							changed = true;
						}
					);

					$( '.rs_featured-image-nav-tab-wrapper a' ).click(
						function() {
							if ( changed ) {
								window.onbeforeunload = function() {
									return data.i18n_nav_warning;
								};
							} else {
								window.onbeforeunload = '';
							}
						}
					);

					$( '.submit :input' ).click(
						function() {
							window.onbeforeunload = '';
						}
					);
				}
			);

			// Select all/none.
			$( '.rs_featured_image' ).on(
				'click',
				'.select_all',
				function() {
					$( this )
					.closest( 'td' )
					.find( 'select option' )
					.attr( 'selected', 'selected' );
					$( this )
					.closest( 'td' )
					.find( 'select' )
					.trigger( 'change' );
					return false;
				}
			);

			$( '.rs_featured_image' ).on(
				'click',
				'.select_none',
				function() {
					$( this )
					.closest( 'td' )
					.find( 'select option' )
					.removeAttr( 'selected' );
					$( this )
					.closest( 'td' )
					.find( 'select' )
					.trigger( 'change' );
					return false;
				}
			);

			const collBtn      = document.getElementsByClassName( 'collapsible' );
			const collBtnCount = collBtn.length;
			let i;

			for ( i = 0; i < collBtnCount; i++ ) {
				collBtn[ i ].addEventListener(
					'click',
					function( e ) {
						e.preventDefault();
						this.classList.toggle( 'active' );
						const content = this.nextElementSibling;
						if ( content.style.maxHeight ) {
							content.style.maxHeight = null;
						} else {
							content.style.maxHeight = content.scrollHeight + 'px';
						}
					}
				);
				if ( i === 0 ) {
					$( collBtn[ i ] ).trigger( 'click' );
				}
			}

			// Initialize WP Color Picker.
			$( '.color-field' ).wpColorPicker();

			// Initialize Select2.
			$( '.rs_featured_image-multi-select' ).select2();

			// Initialize Copy to Clipboard.
			$( document ).on( 'click', '.copy-to-clipboard', function( e ) {
        e.preventDefault();

        // Find the target input/textarea
        const targetSel = $( this ).data( 'clipboard-target' );
        const $field    = $( targetSel );
        if ( ! $field.length ) {
            console.warn( 'Copy target not found:', targetSel );
            return;
        }

        const value = $field.val();

        // Use modern Clipboard API if supported
        if ( navigator.clipboard && navigator.clipboard.writeText ) {
            navigator.clipboard.writeText( value )
                .then( function() {
                    console.log( 'Copied to clipboard: ' + value );
                } )
                .catch( function( err ) {
                    console.error( 'Clipboard API failed, falling back', err );
                    fallbackCopy( value );
                } );
        } else {
            // fallback for older browsers
            fallbackCopy( value );
        }

        // ----------------------------------------
        // Fallback helper: hidden textarea + execCommand
        // ----------------------------------------
        function fallbackCopy( text ) {
						const $temp = $( '<textarea>' )
                .css({
                    position: 'fixed',
                    top:      0,
                    left:     0,
                    opacity:  0
                })
                .val( text )
                .appendTo( 'body' )
                .focus()
                .select();

            try {
                document.execCommand( 'copy' );
                console.log( 'Copied to clipboard: ' + text );
            } catch ( err ) {
                console.error( 'Fallback: unable to copy', err );
            }
            $temp.remove();
        }
    } );
		}
	);
}( jQuery, rs_featured_image_settings_data ) );
