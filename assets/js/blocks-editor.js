/**
 * Metamint server-rendered blocks: client-side registration for the block editor.
 * No build step — plain JS using the `wp` globals.
 *
 * Each block mirrors the metadata in the matching `blocks/<name>/block.json`
 * and relies on ServerSideRender (the REST block-renderer endpoint) to paint
 * the PHP-rendered markup while editing.
 */

( function () {
	const el  = window.wp.element.createElement;
	const SSR = window.wp.serverSideRender
		? ( window.wp.serverSideRender.default || window.wp.serverSideRender )
		: null;

	const blocks = {
		'metamint/home-hero':          { title: 'Home: Hero',                        icon: 'cover-image' },
		'metamint/home-marquee':       { title: 'Home: Marquee',                     icon: 'superhero-alt' },
		'metamint/home-products':      { title: 'Home: Products',                    icon: 'products' },
		'metamint/home-features':      { title: 'Home: Features',                    icon: 'star-half' },
		'metamint/home-manifesto':     { title: 'Home: Manifesto',                   icon: 'format-quote' },
		'metamint/home-teamnote':      { title: 'Home: Team Note + Stats',           icon: 'groups' },
		'metamint/home-testimonials':  { title: 'Home: Testimonials',                icon: 'format-status' },
		'metamint/home-cta':           { title: 'Home: CTA',                         icon: 'megaphone' },
		'metamint/product-hero':       { title: 'Product: Hero',                     icon: 'cover-image' },
		'metamint/product-features':   { title: 'Product: Feature Matrix',           icon: 'star-half' },
		'metamint/product-steps':      { title: 'Product: How It Works',             icon: 'moves' },
		'metamint/product-demo':       { title: 'Product: Live Demo',                icon: 'desktop' },
		'metamint/product-pricing':    { title: 'Product: Pricing',                  icon: 'money-alt' },
		'metamint/product-faq':        { title: 'Product: FAQ',                      icon: 'editor-help' },
		'metamint/about-page':         { title: 'About Page (full)',                 icon: 'admin-users' },
		'metamint/contact-page':       { title: 'Contact Page (full)',               icon: 'email-alt' },
		'metamint/journal-archive':    { title: 'Journal Archive (full)',            icon: 'rss' },
	};

	Object.entries( blocks ).forEach( ( [ name, meta ] ) => {
		window.wp.blocks.registerBlockType( name, {
			apiVersion: 3,
			title: meta.title,
			category: 'design',
			icon: meta.icon,
			description: meta.title + ' (server-rendered by the Metamint theme)',
			supports: { html: false },
			edit( props ) {
				const fallback = el(
					'div',
					{ className: 'mm-editor-note' },
					meta.title
				);
				if ( ! SSR ) {
					return fallback;
				}
				return el( SSR, {
					key: name,
					block: name,
					attributes: props.attributes,
				} );
			},
			save() {
				return null;
			},
		} );
	} );
} )();