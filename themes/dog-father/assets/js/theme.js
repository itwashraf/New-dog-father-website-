/**
 * The Dog Father — theme interactions. Vanilla JS, no dependencies.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {

		/* Sticky header shadow on scroll. */
		var header = document.getElementById( 'df-header' );
		if ( header ) {
			var onScroll = function () {
				header.classList.toggle( 'is-scrolled', window.scrollY > 20 );
			};
			onScroll();
			window.addEventListener( 'scroll', onScroll, { passive: true } );
		}

		/* Mobile navigation. */
		var burger = document.querySelector( '.df-burger' );
		var nav    = document.getElementById( 'df-nav' );
		if ( burger && nav ) {
			// Dim backdrop behind the drawer so tapping outside closes the menu.
			var scrim = document.createElement( 'div' );
			scrim.className = 'df-nav-scrim';
			document.body.appendChild( scrim );

			var closeNav = function () {
				document.body.classList.remove( 'df-nav-open' );
				burger.setAttribute( 'aria-expanded', 'false' );
			};
			var openNav = function () {
				document.body.classList.add( 'df-nav-open' );
				burger.setAttribute( 'aria-expanded', 'true' );
			};

			burger.addEventListener( 'click', function () {
				if ( document.body.classList.contains( 'df-nav-open' ) ) {
					closeNav();
				} else {
					openNav();
				}
			} );
			// Close when a link is tapped.
			nav.addEventListener( 'click', function ( e ) {
				if ( e.target.closest( 'a' ) && document.body.classList.contains( 'df-nav-open' ) ) {
					closeNav();
				}
			} );
			// Close when tapping the backdrop.
			scrim.addEventListener( 'click', closeNav );
			// Close on Escape.
			document.addEventListener( 'keydown', function ( e ) {
				if ( ( 'Escape' === e.key || 'Esc' === e.key ) && document.body.classList.contains( 'df-nav-open' ) ) {
					closeNav();
				}
			} );
		}

		/* FAQ accordion. */
		document.querySelectorAll( '.df-faq__q' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var item = btn.closest( '.df-faq__item' );
				if ( ! item ) { return; }
				var answer = item.querySelector( '.df-faq__a' );
				var open = item.classList.toggle( 'is-open' );
				if ( answer ) {
					answer.style.maxHeight = open ? ( answer.scrollHeight + 40 ) + 'px' : null;
				}
			} );
		} );

		/* Reveal on scroll. */
		var reveals = document.querySelectorAll( '.df-reveal:not(.is-visible)' );
		if ( 'IntersectionObserver' in window && reveals.length ) {
			var io = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-visible' );
						io.unobserve( entry.target );
					}
				} );
			}, { threshold: 0.12 } );
			reveals.forEach( function ( el ) { io.observe( el ); } );
		} else {
			reveals.forEach( function ( el ) { el.classList.add( 'is-visible' ); } );
		}

	} );
}() );
