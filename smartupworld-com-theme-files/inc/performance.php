<?php
/**
 * Performance optimisations — LCP, CLS, FCP, TTFB
 *
 * @package Smartupworld
 */

defined( 'ABSPATH' ) || exit;

// ── 1. Preconnect to Google Fonts & ad servers ──────────────────────────────
add_action( 'wp_head', 'suw_perf_preconnect', 1 );
function suw_perf_preconnect() {
	$hints = array(
		array( 'href' => 'https://fonts.googleapis.com',  'crossorigin' => false ),
		array( 'href' => 'https://fonts.gstatic.com',     'crossorigin' => true  ),
		array( 'href' => 'https://pagead2.googlesyndication.com', 'crossorigin' => false ),
		array( 'href' => 'https://www.google-analytics.com',      'crossorigin' => false ),
		array( 'href' => 'https://www.googletagmanager.com',       'crossorigin' => false ),
	);
	foreach ( $hints as $hint ) {
		$co = $hint['crossorigin'] ? ' crossorigin' : '';
		echo '<link rel="preconnect" href="' . esc_url( $hint['href'] ) . '"' . $co . '>' . "\n";
	}
}

// ── 2. Preload the hero / LCP image on the front page ──────────────────────
// Update the filename below to match your actual hero image.
add_action( 'wp_head', 'suw_perf_preload_hero', 2 );
function suw_perf_preload_hero() {
	if ( ! is_front_page() ) {
		return;
	}
	// Change this path to the hero image used in your theme's front-page template.
	$hero = get_template_directory_uri() . '/assets/images/primary.webp';
	echo '<link rel="preload" as="image" href="' . esc_url( $hero ) . '" fetchpriority="high">' . "\n";
}

// ── 3. Inline critical CSS — reserves ad slot space (CLS fix) ───────────────
add_action( 'wp_head', 'suw_perf_critical_css', 3 );
function suw_perf_critical_css() {
	?>
<style id="suw-perf-critical">
/* Reserve ad slot height so the page doesn't jump when ads load (CLS) */
.adsbygoogle{display:block;min-height:90px;}
ins.adsbygoogle[data-ad-format="auto"]{min-height:250px;}
/* Prevent layout shift from images that lack explicit dimensions */
img{height:auto;}
</style>
	<?php
}

// ── 4. Add font-display:swap to Google Fonts URLs ───────────────────────────
add_filter( 'style_loader_src', 'suw_perf_fonts_display_swap', 10, 2 );
function suw_perf_fonts_display_swap( $src ) {
	if ( false !== strpos( $src, 'fonts.googleapis.com' ) ) {
		if ( false === strpos( $src, 'display=' ) ) {
			$src = add_query_arg( 'display', 'swap', $src );
		}
	}
	return $src;
}

// ── 5. Defer non-critical scripts ───────────────────────────────────────────
// Scripts that must stay synchronous (render-blocking by design).
add_filter( 'script_loader_tag', 'suw_perf_defer_scripts', 10, 2 );
function suw_perf_defer_scripts( $tag, $handle ) {
	$keep_sync = array(
		'jquery',
		'jquery-core',
		'jquery-migrate',
	);
	if ( in_array( $handle, $keep_sync, true ) ) {
		return $tag;
	}
	// Already has defer or async — leave it alone.
	if ( false !== strpos( $tag, ' defer' ) || false !== strpos( $tag, ' async' ) ) {
		return $tag;
	}
	return str_replace( ' src=', ' defer src=', $tag );
}

// ── 6. Set explicit image dimensions on post thumbnails (CLS fix) ────────────
// Ensures WordPress outputs width/height attrs so the browser reserves space.
add_filter( 'wp_get_attachment_image_attributes', 'suw_perf_image_attrs', 10, 3 );
function suw_perf_image_attrs( $attr, $attachment, $size ) {
	// WordPress already adds width/height for registered sizes.
	// This filter is a safety net for any that slip through without them.
	if ( empty( $attr['width'] ) || empty( $attr['height'] ) ) {
		$meta = wp_get_attachment_metadata( $attachment->ID );
		if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
			$attr['width']  = $meta['width'];
			$attr['height'] = $meta['height'];
		}
	}
	return $attr;
}

// ── 7. DNS-prefetch for common third-party origins ───────────────────────────
add_filter( 'wp_resource_hints', 'suw_perf_dns_prefetch', 10, 2 );
function suw_perf_dns_prefetch( $hints, $relation_type ) {
	if ( 'dns-prefetch' !== $relation_type ) {
		return $hints;
	}
	$hints[] = '//www.google.com';
	$hints[] = '//www.gstatic.com';
	$hints[] = '//adservice.google.com';
	return $hints;
}
