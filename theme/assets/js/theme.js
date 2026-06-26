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
			burger.addEventListener( 'click', function () {
				var open = document.body.classList.toggle( 'df-nav-open' );
				burger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );
			// Close when a link is tapped.
			nav.addEventListener( 'click', function ( e ) {
				if ( e.target.closest( 'a' ) && document.body.classList.contains( 'df-nav-open' ) ) {
					document.body.classList.remove( 'df-nav-open' );
					burger.setAttribute( 'aria-expanded', 'false' );
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
