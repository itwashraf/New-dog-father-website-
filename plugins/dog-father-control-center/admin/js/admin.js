/**
 * Dog Father Control Center — admin scripts.
 */
( function ( $ ) {
	'use strict';

	$( function () {
		// Live theme preview ------------------------------------------------
		var $preview = $( '#dfcc-preview' );

		function pvVal( id, fallback ) {
			var $f = $( '#' + id );
			var v = $f.length ? $.trim( $f.val() || '' ) : '';
			return v || fallback;
		}

		function updatePreview() {
			if ( ! $preview.length ) { return; }

			var primary    = pvVal( 'color_primary', '#FFF10A' );
			var gold       = pvVal( 'color_gold', '#FEC208' );
			var headerBg   = pvVal( 'header_bg', '#0b0b0d' );
			var headerText = pvVal( 'header_text', '#ffffff' );
			var pageBg     = pvVal( 'page_bg', '#0a0a0b' );
			var bodyText   = pvVal( 'body_text', '#ffffff' );
			var muted      = pvVal( 'muted_text', '#9a9aa3' );
			var heading    = pvVal( 'heading_color', bodyText );
			var link       = pvVal( 'link_color', gold );
			var accent     = pvVal( 'accent', gold );
			var cardBg     = pvVal( 'card_bg', '#15151a' );
			var border     = pvVal( 'border_color', 'rgba(255,255,255,.14)' );
			var btnBg      = pvVal( 'btn_bg', '' );
			var btnText    = pvVal( 'btn_text', '#000000' );
			var footerBg   = pvVal( 'footer_bg', '#050506' );
			var footerText = pvVal( 'footer_text', muted );
			var btnBackground = btnBg || ( 'linear-gradient(135deg,' + primary + ',' + gold + ')' );

			$preview.find( '[data-pv-header]' ).css( { background: headerBg, borderColor: border } );
			$preview.find( '[data-pv-headertext]' ).css( 'color', headerText );
			$preview.find( '[data-pv-body]' ).css( 'background', pageBg );
			$preview.find( '[data-pv-text]' ).css( 'color', bodyText );
			$preview.find( '[data-pv-muted]' ).css( 'color', muted );
			$preview.find( '[data-pv-heading]' ).css( 'color', heading );
			$preview.find( '[data-pv-link]' ).css( 'color', link );
			$preview.find( '[data-pv-accent]' ).css( 'color', accent );
			$preview.find( '[data-pv-card]' ).css( { background: cardBg, borderColor: border } );
			$preview.find( '[data-pv-btn]' ).css( { background: btnBackground, color: btnText } );
			$preview.find( '[data-pv-footer]' ).css( 'background', footerBg );
			$preview.find( '[data-pv-footertext]' ).css( 'color', footerText );
			// Mobile burger lines follow the header text colour.
			$preview.find( '[data-pv-burger]' ).css( 'background', headerText );
		}

		// Reset every colour back to the original The Dog Father palette.
		$( document ).on( 'click', '.dfcc-reset-colors', function ( e ) {
			e.preventDefault();
			if ( ! window.confirm( 'Reset every colour to the original The Dog Father defaults? Your other settings are kept. Press Save afterwards to apply.' ) ) {
				return;
			}
			$( '.dfcc-color-field' ).each( function () {
				var $f    = $( this );
				var $wrap = $f.closest( '.wp-picker-container' );
				var def   = $f.data( 'default-color' );
				if ( def ) {
					// Brand / announcement colours have a defined default.
					var $db = $wrap.find( '.wp-picker-default' );
					if ( $db.length ) {
						$db.prop( 'disabled', false ).trigger( 'click' );
					} else if ( $f.wpColorPicker ) {
						$f.wpColorPicker( 'color', def );
					}
				} else {
					// Section colours: clearing them restores the theme's built-in
					// The Dog Father default for that area.
					var $cb = $wrap.find( '.wp-picker-clear' );
					if ( $cb.length ) {
						$cb.trigger( 'click' );
					} else {
						$f.val( '' ).trigger( 'change' );
					}
				}
			} );
			setTimeout( updatePreview, 80 );
		} );

		// Desktop / Mobile preview toggle.
		$( document ).on( 'click', '.dfcc-pv-tab', function () {
			var view = $( this ).data( 'pv-view' );
			$( '.dfcc-pv-tab' ).removeClass( 'is-active' );
			$( this ).addClass( 'is-active' );
			$preview.toggleClass( 'is-desktop', 'desktop' === view ).toggleClass( 'is-mobile', 'mobile' === view );
		} );

		// WordPress color pickers for any brand color field. Refresh the
		// preview whenever a colour changes or is cleared.
		if ( $.fn.wpColorPicker ) {
			$( '.dfcc-color-field' ).wpColorPicker( {
				change: function () { setTimeout( updatePreview, 30 ); },
				clear:  function () { setTimeout( updatePreview, 30 ); }
			} );
		}
		// Also catch manual typing into the hex field.
		$( document ).on( 'input change', '.dfcc-color-field', function () { setTimeout( updatePreview, 0 ); } );
		updatePreview();

		// Drag-to-reorder the homepage Section Layout list.
		if ( $.fn.sortable ) {
			$( '#dfcc-section-sort' ).sortable( {
				handle: '.dfcc-drag',
				placeholder: 'dfcc-sortable-placeholder',
				forcePlaceholderSize: true,
				axis: 'y'
			} );
		}

		// Dim a section row when its show/hide checkbox is unticked.
		$( document ).on( 'change', '.dfcc-sec-toggle', function () {
			$( this ).closest( '.dfcc-sec-row' ).toggleClass( 'dfcc-hidden', ! this.checked );
		} );

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
