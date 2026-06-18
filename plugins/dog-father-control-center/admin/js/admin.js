/**
 * Dog Father Control Center — admin scripts.
 */
( function ( $ ) {
	'use strict';

	$( function () {
		// WordPress color pickers for any brand color field.
		if ( $.fn.wpColorPicker ) {
			$( '.dfcc-color-field' ).wpColorPicker();
		}

		// Media uploader for image fields (Open Graph, gallery, etc.).
		$( document ).on( 'click', '.dfcc-media-upload', function ( e ) {
			e.preventDefault();
			var $button = $( this );
			var $target = $( '#' + $button.data( 'target' ) );
			var $preview = $( '#' + $button.data( 'preview' ) );

			var frame = wp.media( {
				title: $button.data( 'title' ) || 'Select image',
				multiple: false,
				library: { type: 'image' },
				button: { text: 'Use this image' }
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				$target.val( attachment.id );
				if ( $preview.length ) {
					$preview.attr( 'src', attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url ).show();
				}
			} );

			frame.open();
		} );

		// Clear a media field.
		$( document ).on( 'click', '.dfcc-media-clear', function ( e ) {
			e.preventDefault();
			var $button = $( this );
			$( '#' + $button.data( 'target' ) ).val( '' );
			$( '#' + $button.data( 'preview' ) ).hide();
		} );
	} );
}( jQuery ) );
