/**
 * Dog Father Control Center — public front-end script.
 *
 * Handles (all vanilla JS, no dependencies):
 *   - Gallery album filters
 *   - Gallery lightbox (image / video, no external library)
 *   - Testimonial slider navigation
 *   - Booking form submission via fetch to the dfcc/v1/bookings REST endpoint
 *
 * Config is injected via wp_localize_script as window.dfccFrontend.
 */
( function () {
	'use strict';

	var config = window.dfccFrontend || {};
	var i18n = config.i18n || {};

	document.addEventListener( 'DOMContentLoaded', function () {
		initGalleryFilters();
		initLightbox();
		initSliders();
		initBookingForms();
	} );

	/* -----------------------------------------------------------------
	 * Gallery filters
	 * --------------------------------------------------------------- */
	function initGalleryFilters() {
		var wraps = document.querySelectorAll( '.dfcc-gallery-wrap' );
		wraps.forEach( function ( wrap ) {
			var buttons = wrap.querySelectorAll( '.dfcc-filter' );
			var items = wrap.querySelectorAll( '.dfcc-gallery-item' );
			if ( ! buttons.length ) {
				return;
			}

			buttons.forEach( function ( btn ) {
				btn.addEventListener( 'click', function () {
					var filter = btn.getAttribute( 'data-filter' );

					buttons.forEach( function ( b ) {
						b.classList.toggle( 'is-active', b === btn );
					} );

					items.forEach( function ( item ) {
						var terms = ( item.getAttribute( 'data-terms' ) || '' ).split( /\s+/ );
						var show = '*' === filter || terms.indexOf( filter ) !== -1;
						item.classList.toggle( 'is-hidden', ! show );
					} );
				} );
			} );
		} );
	}

	/* -----------------------------------------------------------------
	 * Lightbox
	 * --------------------------------------------------------------- */
	function initLightbox() {
		var triggers = document.querySelectorAll( '.dfcc-gallery-wrap[data-lightbox="1"] .dfcc-gallery-trigger' );
		if ( ! triggers.length ) {
			return;
		}

		var overlay = buildLightbox();
		var contentEl = overlay.querySelector( '.dfcc-lightbox-content' );
		var lastFocus = null;

		triggers.forEach( function ( trigger ) {
			trigger.addEventListener( 'click', function () {
				lastFocus = trigger;
				openLightbox(
					overlay,
					contentEl,
					trigger.getAttribute( 'data-type' ),
					trigger.getAttribute( 'data-src' ),
					trigger.getAttribute( 'aria-label' ) || ''
				);
			} );
		} );

		overlay.addEventListener( 'click', function ( e ) {
			if ( e.target === overlay || e.target.classList.contains( 'dfcc-lightbox-close' ) ) {
				closeLightbox( overlay, contentEl, lastFocus );
			}
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && overlay.classList.contains( 'is-open' ) ) {
				closeLightbox( overlay, contentEl, lastFocus );
			}
		} );
	}

	function buildLightbox() {
		var overlay = document.createElement( 'div' );
		overlay.className = 'dfcc-lightbox';
		overlay.setAttribute( 'role', 'dialog' );
		overlay.setAttribute( 'aria-modal', 'true' );
		overlay.innerHTML =
			'<div class="dfcc-lightbox-content">' +
			'<button type="button" class="dfcc-lightbox-close" aria-label="Close">&times;</button>' +
			'<div class="dfcc-lightbox-media"></div>' +
			'</div>';
		document.body.appendChild( overlay );
		return overlay;
	}

	function openLightbox( overlay, contentEl, type, src, label ) {
		var media = contentEl.querySelector( '.dfcc-lightbox-media' );
		media.innerHTML = '';

		if ( ! src ) {
			return;
		}

		if ( 'video' === type ) {
			var embed = videoEmbed( src );
			media.innerHTML = embed;
		} else {
			var img = document.createElement( 'img' );
			img.src = src;
			img.alt = label;
			media.appendChild( img );
		}

		overlay.classList.add( 'is-open' );
		document.body.style.overflow = 'hidden';
		var closeBtn = overlay.querySelector( '.dfcc-lightbox-close' );
		if ( closeBtn ) {
			closeBtn.focus();
		}
	}

	function closeLightbox( overlay, contentEl, lastFocus ) {
		overlay.classList.remove( 'is-open' );
		document.body.style.overflow = '';
		var media = contentEl.querySelector( '.dfcc-lightbox-media' );
		if ( media ) {
			media.innerHTML = '';
		}
		if ( lastFocus && typeof lastFocus.focus === 'function' ) {
			lastFocus.focus();
		}
	}

	function videoEmbed( url ) {
		var yt = url.match( /(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{11})/ );
		if ( yt ) {
			return '<iframe src="https://www.youtube.com/embed/' + encodeURIComponent( yt[ 1 ] ) +
				'?autoplay=1&rel=0" title="Video" frameborder="0" allow="autoplay; encrypted-media; fullscreen" allowfullscreen></iframe>';
		}

		var vimeo = url.match( /vimeo\.com\/(?:video\/)?(\d+)/ );
		if ( vimeo ) {
			return '<iframe src="https://player.vimeo.com/video/' + encodeURIComponent( vimeo[ 1 ] ) +
				'?autoplay=1" title="Video" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>';
		}

		// Direct file fallback.
		var safe = url.replace( /"/g, '%22' );
		return '<video src="' + safe + '" controls autoplay playsinline></video>';
	}

	/* -----------------------------------------------------------------
	 * Testimonial slider
	 * --------------------------------------------------------------- */
	function initSliders() {
		var sliders = document.querySelectorAll( '.dfcc-testimonials[data-slider="1"]' );
		sliders.forEach( function ( slider ) {
			var track = slider.querySelector( '.dfcc-testimonials-track' );
			var prev = slider.querySelector( '.dfcc-slider-prev' );
			var next = slider.querySelector( '.dfcc-slider-next' );
			if ( ! track ) {
				return;
			}

			function step() {
				var card = track.querySelector( '.dfcc-testimonial-card' );
				return card ? card.getBoundingClientRect().width + 24 : track.clientWidth * 0.8;
			}

			if ( prev ) {
				prev.addEventListener( 'click', function () {
					track.scrollBy( { left: -step(), behavior: 'smooth' } );
				} );
			}
			if ( next ) {
				next.addEventListener( 'click', function () {
					track.scrollBy( { left: step(), behavior: 'smooth' } );
				} );
			}
		} );
	}

	/* -----------------------------------------------------------------
	 * Booking form
	 * --------------------------------------------------------------- */
	function initBookingForms() {
		var forms = document.querySelectorAll( '.dfcc-booking-form' );
		forms.forEach( function ( form ) {
			form.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				submitBooking( form );
			} );
		} );
	}

	function submitBooking( form ) {
		var messageEl = form.querySelector( '.dfcc-form-message' );
		var submitBtn = form.querySelector( '.dfcc-submit' );

		setMessage( messageEl, '', '' );

		// Honeypot: silently succeed-looking but do nothing.
		var hp = form.querySelector( '[name="dfcc_hp_website"]' );
		if ( hp && hp.value ) {
			setMessage( messageEl, i18n.success || 'Thank you!', 'success' );
			form.reset();
			return;
		}

		// Client-side validation of required fields.
		var invalid = [];
		form.querySelectorAll( '[required]' ).forEach( function ( field ) {
			var ok = !! String( field.value ).trim();
			field.classList.toggle( 'dfcc-invalid', ! ok );
			if ( ! ok ) {
				invalid.push( field );
			}
		} );

		if ( invalid.length ) {
			setMessage( messageEl, i18n.required || 'Please fill in all required fields.', 'error' );
			invalid[ 0 ].focus();
			return;
		}

		var payload = {};
		new FormData( form ).forEach( function ( value, key ) {
			if ( 'dfcc_hp_website' !== key ) {
				payload[ key ] = value;
			}
		} );

		if ( ! config.restUrl ) {
			setMessage( messageEl, i18n.error || 'Something went wrong.', 'error' );
			return;
		}

		if ( submitBtn ) {
			submitBtn.disabled = true;
			submitBtn.dataset.label = submitBtn.textContent;
			submitBtn.textContent = i18n.sending || 'Sending…';
		}

		fetch( config.restUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': config.nonce || ''
			},
			body: JSON.stringify( payload )
		} )
			.then( function ( response ) {
				return response.json().then( function ( data ) {
					return { ok: response.ok, data: data };
				} );
			} )
			.then( function ( result ) {
				if ( result.ok && result.data && result.data.success ) {
					setMessage( messageEl, i18n.success || 'Thank you!', 'success' );
					form.reset();
				} else {
					var msg = ( result.data && result.data.message ) ? result.data.message : ( i18n.error || 'Something went wrong.' );
					setMessage( messageEl, msg, 'error' );
				}
			} )
			.catch( function () {
				setMessage( messageEl, i18n.error || 'Something went wrong.', 'error' );
			} )
			.finally( function () {
				if ( submitBtn ) {
					submitBtn.disabled = false;
					if ( submitBtn.dataset.label ) {
						submitBtn.textContent = submitBtn.dataset.label;
					}
				}
			} );
	}

	function setMessage( el, text, type ) {
		if ( ! el ) {
			return;
		}
		el.textContent = text;
		el.classList.remove( 'is-error', 'is-success' );
		if ( 'error' === type ) {
			el.classList.add( 'is-error' );
		} else if ( 'success' === type ) {
			el.classList.add( 'is-success' );
		}
	}
}() );
