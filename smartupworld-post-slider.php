<?php
/**
 * Plugin Name:       SmartUpWorld Post Slider
 * Plugin URI:        https://smartupworld.com/smartupworld-post-slider/
 * Description:       A lightweight, accessible Owl Carousel post slider. Use the shortcode [suwps_post_slider] anywhere in your site.
 * Version:           1.0.1
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Smartupworld Websolutions
 * Author URI:        https://smartupworld.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       smartupworld-post-slider
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Constants ──────────────────────────────────────────────────────────────

define( 'SUW_PS_VERSION',  '1.0.1' );
define( 'SUW_PS_DIR',      plugin_dir_path( __FILE__ ) );
define( 'SUW_PS_URL',      plugins_url( '/', __FILE__ ) );

// ── Bootstrap ──────────────────────────────────────────────────────────────

add_action( 'wp_enqueue_scripts', 'suw_ps_register_assets' );
add_action( 'admin_menu',            'suw_ps_admin_menu' );
add_action( 'admin_enqueue_scripts', 'suw_ps_admin_assets' );
add_shortcode( 'suwps_post_slider', 'suw_ps_shortcode' );

// ── Register Assets (front-end) ────────────────────────────────────────────

function suw_ps_register_assets() {
	wp_register_style(
		'suw-owl-carousel',
		SUW_PS_URL . 'assets/css/owl.carousel.min.css',
		array(),
		'2.3.4'
	);

	wp_register_style(
		'suw-owl-theme',
		SUW_PS_URL . 'assets/css/owl.theme.default.min.css',
		array( 'suw-owl-carousel' ),
		'2.3.4'
	);

	wp_register_style(
		'suw-post-slider',
		SUW_PS_URL . 'assets/css/suw-post-slider.css',
		array( 'suw-owl-carousel', 'suw-owl-theme' ),
		SUW_PS_VERSION
	);

	wp_register_script(
		'suw-owl-carousel',
		SUW_PS_URL . 'assets/js/owl.carousel.min.js',
		array( 'jquery' ),
		'2.3.4',
		true
	);
}

// ── Inline Init Script (added once per page) ───────────────────────────────

function suw_ps_maybe_add_inline_script() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	$inline = '
jQuery( document ).ready( function( $ ) {
	$( ".suw-post-slider .owl-carousel" ).each( function () {
		var $carousel = $( this );
		var $slider   = $carousel.closest( ".suw-post-slider" );
		var navAttr   = $slider.attr( "data-nav" );
		var dotsAttr  = $slider.attr( "data-dots" );
		var showNav   = navAttr  !== "false";
		var showDots  = dotsAttr !== "false";

		$carousel.owlCarousel( {
			margin:             20,
			loop:               false,
			autoplay:           true,
			autoplayTimeout:    5000,
			autoplayHoverPause: true,
			smartSpeed:         700,
			nav:                showNav,
			navText: [
				\'<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>\',
				\'<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>\'
			],
			dots: showDots,
			responsive: {
				0:   { items: 1 },
				600: { items: 2 },
				900: { items: 3 }
			}
		} );

		function suwOwlA11y() {
			$carousel.find( ".owl-prev" ).removeAttr( "role" ).attr( "aria-label", "Previous slide" );
			$carousel.find( ".owl-next" ).removeAttr( "role" ).attr( "aria-label", "Next slide" );
			$carousel.find( ".owl-dot" ).each( function ( i ) {
				$( this ).attr( "role", "button" ).attr( "aria-label", "Go to slide " + ( i + 1 ) );
			} );
			$carousel.find( ".owl-dot" ).removeAttr( "aria-current" );
			$carousel.find( ".owl-dot.active" ).attr( "aria-current", "true" );
		}

		$carousel.on( "initialized.owl.carousel changed.owl.carousel", suwOwlA11y );
		setTimeout( suwOwlA11y, 300 );
	} );
} );
';

	wp_add_inline_script( 'suw-owl-carousel', $inline );
}

// ── Shortcode ──────────────────────────────────────────────────────────────

function suw_ps_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'eyebrow'   => '',
			'title'     => '',
			'subtitle'  => '',
			'underline' => 'true',
			'count'     => 8,
			'ids'       => '',
			'category'  => '',
			'skip'      => '',
			'layout'    => 'contained',
			'nav'       => 'true',
			'dots'      => 'true',
		),
		$atts,
		'suwps_post_slider'
	);

	// Enqueue assets when shortcode is actually used.
	wp_enqueue_style( 'suw-post-slider' );
	wp_enqueue_script( 'suw-owl-carousel' );
	suw_ps_maybe_add_inline_script();

	// ── Resolve the current post ID for PHP-side exclusion ──
	$exclude_current = ( 'current' === $atts['skip'] && is_singular( 'post' ) );
	$current_post_id = $exclude_current ? get_the_ID() : 0;
	$requested_count = intval( $atts['count'] );

	// ── Fetch posts — no post__not_in / post__in used (avoids VIP sniff) ──
	if ( ! empty( $atts['ids'] ) ) {
		// Fetch each requested ID individually — avoids post__in / include sniff entirely.
		$requested_ids   = array_map( 'intval', explode( ',', $atts['ids'] ) );
		$posts_to_render = array();
		foreach ( $requested_ids as $pid ) {
			$p = get_post( $pid );
			if ( $p instanceof WP_Post && 'publish' === $p->post_status ) {
				$posts_to_render[] = $p;
			}
		}
	} else {
		// Fetch one extra when excluding, then slice in PHP.
		$fetch_count = $exclude_current ? $requested_count + 1 : $requested_count;
		$args        = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $fetch_count,
		);

		if ( ! empty( $atts['category'] ) ) {
			$cat_value = trim( $atts['category'] );
			if ( is_numeric( $cat_value ) ) {
				$args['cat'] = intval( $cat_value );
			} else {
				$args['category_name'] = sanitize_title( $cat_value );
			}
		}

		$q       = new WP_Query( $args );
		$fetched = $q->posts;
		wp_reset_postdata();

		// Exclude current post in PHP — no post__not_in needed.
		if ( $exclude_current && $current_post_id ) {
			$fetched = array_filter( $fetched, function( $p ) use ( $current_post_id ) {
				return (int) $p->ID !== (int) $current_post_id;
			} );
		}

		$posts_to_render = array_slice( array_values( $fetched ), 0, $requested_count );
	}

	if ( empty( $posts_to_render ) ) {
		return '<p style="color:#888;font-size:14px;text-align:center;">'
			. esc_html__( 'No posts found.', 'smartupworld-post-slider' )
			. '</p>';
	}

	// ── Layout ──
	$is_contained = ( 'fullwidth' !== sanitize_text_field( $atts['layout'] ) );
	$nav_attr     = ( 'false' === $atts['nav'] )  ? 'false' : 'true';
	$dots_attr    = ( 'false' === $atts['dots'] ) ? 'false' : 'true';

	$output  = '<div class="suw-post-slider" data-nav="' . esc_attr( $nav_attr ) . '" data-dots="' . esc_attr( $dots_attr ) . '">';

	if ( $is_contained ) {
		$output .= '<div class="container">';
	}

	// ── Section header ──
	if ( ! empty( $atts['eyebrow'] ) || ! empty( $atts['title'] ) || ! empty( $atts['subtitle'] ) ) {
		$output .= '<div class="suw-slider-header">';

		if ( ! empty( $atts['eyebrow'] ) ) {
			$output .= '<div class="swb-eyebrow">' . esc_html( $atts['eyebrow'] ) . '</div>';
		}

		if ( ! empty( $atts['title'] ) ) {
			$output .= '<h2 class="suw-slider-title">' . esc_html( $atts['title'] ) . '</h2>';

			if ( 'false' !== $atts['underline'] ) {
				$output .= '<div class="swb-underline"></div>';
			}
		}

		if ( ! empty( $atts['subtitle'] ) ) {
			$output .= '<p class="suw-slider-subtitle">' . esc_html( $atts['subtitle'] ) . '</p>';
		}

		$output .= '</div>';
	}

	$output .= '<div class="owl-carousel owl-theme">';

	$slide_index = 0;

	foreach ( $posts_to_render as $post_obj ) {
		$post_id   = (int) $post_obj->ID;
		$title     = get_the_title( $post_id );
		$author    = get_the_author_meta( 'display_name', (int) $post_obj->post_author );
		$permalink = get_permalink( $post_id );
		$cats      = get_the_category( $post_id );
		$cat_name  = ( $cats && ! is_wp_error( $cats ) ) ? esc_html( $cats[0]->name ) : '';
		$thumb_url = get_the_post_thumbnail_url( $post_id, 'large' );

		if ( $thumb_url ) {
			$loading_attr = ( 0 === $slide_index )
				? 'loading="eager" fetchpriority="high" decoding="async"'
				: 'loading="lazy" decoding="async"';

			$img_html = '<img class="suw-slide-img" src="' . esc_url( $thumb_url ) . '" alt="' . esc_attr( $title ) . '" ' . $loading_attr . '>';
		} else {
			$img_html  = '<div class="suw-slide-no-img">';
			$img_html .= '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
			$img_html .= '</div>';
		}

		$output .= '<div class="item">';
		$output .= '<a href="' . esc_url( $permalink ) . '" class="suw-slide-card" title="' . esc_attr( $title ) . '">';
		$output .= $img_html;
		$output .= '<div class="suw-slide-overlay">';
		$output .= '<h3 class="suw-slide-title">' . esc_html( $title ) . '</h3>';
		$output .= '<div class="suw-slide-meta">';
		$output .= '<span class="suw-slide-author">';
		$output .= '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
		$output .= esc_html( $author );
		$output .= '</span>';
		$output .= '</div>';
		$output .= '<span class="suw-slide-read">';
		$output .= esc_html__( 'Read More', 'smartupworld-post-slider' );
		$output .= ' <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';
		$output .= '</span>';
		$output .= '</div>';
		$output .= '</a>';
		$output .= '</div>';

		$slide_index++;
	} // end foreach

	$output .= '</div>'; // .owl-carousel

	if ( $is_contained ) {
		$output .= '</div>'; // .container
	}

	$output .= '</div>'; // .suw-post-slider

	wp_reset_postdata();

	return $output;
}

// ── Admin Menu ─────────────────────────────────────────────────────────────

function suw_ps_admin_menu() {
	add_menu_page(
		__( 'Post Slider', 'smartupworld-post-slider' ),
		__( 'Post Slider', 'smartupworld-post-slider' ),
		'manage_options',
		'suw-post-slider',
		'suw_ps_admin_page',
		'dashicons-slides',
		80
	);
}

// ── Admin Assets ───────────────────────────────────────────────────────────

function suw_ps_admin_assets( $hook_suffix ) {
	if ( 'toplevel_page_suw-post-slider' !== $hook_suffix ) {
		return;
	}

	wp_enqueue_style(
		'suw-ps-admin',
		SUW_PS_URL . 'assets/css/suw-ps-admin.css',
		array(),
		SUW_PS_VERSION
	);

	wp_enqueue_script(
		'suw-ps-admin',
		SUW_PS_URL . 'assets/js/suw-ps-admin.js',
		array(),
		SUW_PS_VERSION,
		true
	);

	wp_localize_script(
		'suw-ps-admin',
		'suwPsAdmin',
		array(
			'copied' => __( 'Copied!', 'smartupworld-post-slider' ),
		)
	);
}

// ── Admin Page ─────────────────────────────────────────────────────────────

function suw_ps_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$doc_url = 'https://smartupworld.com/smartupworld-post-slider/';

	$examples = array(
		array(
			'label'     => __( 'Slider only — no header', 'smartupworld-post-slider' ),
			'shortcode' => '[suwps_post_slider]',
		),
		array(
			'label'     => __( 'With title only', 'smartupworld-post-slider' ),
			'shortcode' => '[suwps_post_slider title="Latest Insights"]',
		),
		array(
			'label'     => __( 'Full header — eyebrow, title, subtitle', 'smartupworld-post-slider' ),
			'shortcode' => '[suwps_post_slider eyebrow="From The Blog" title="Latest Insights" subtitle="Practical guides on website care, speed and security."]',
		),
		array(
			'label'     => __( 'Related posts on single posts — skip current', 'smartupworld-post-slider' ),
			'shortcode' => '[suwps_post_slider title="Related Articles" underline="false" count="6" skip="current" nav="false"]',
		),
		array(
			'label'     => __( 'Filter by category', 'smartupworld-post-slider' ),
			'shortcode' => '[suwps_post_slider title="Tech Articles" category="tech" count="6"]',
		),
		array(
			'label'     => __( 'Specific post IDs', 'smartupworld-post-slider' ),
			'shortcode' => '[suwps_post_slider title="Featured Posts" ids="12,45,67,89"]',
		),
		array(
			'label'     => __( 'Minimal — no arrows, no dots', 'smartupworld-post-slider' ),
			'shortcode' => '[suwps_post_slider nav="false" dots="false"]',
		),
	);

	$attributes = array(
		array( 'eyebrow',   '—',           __( 'Small pill label above the section title. Omit to hide.', 'smartupworld-post-slider' ) ),
		array( 'title',     '—',           __( 'Section heading rendered as an H2. Omit to hide the header entirely.', 'smartupworld-post-slider' ) ),
		array( 'subtitle',  '—',           __( 'Subheading text below the title. Omit to hide.', 'smartupworld-post-slider' ) ),
		array( 'underline', 'true',        __( 'Show a gradient bar under the title. Set to false to remove it.', 'smartupworld-post-slider' ) ),
		array( 'count',     '8',           __( 'Number of posts to display.', 'smartupworld-post-slider' ) ),
		array( 'category',  '—',           __( 'Filter by category slug or numeric ID.', 'smartupworld-post-slider' ) ),
		array( 'ids',       '—',           __( 'Comma-separated post IDs. Overrides count and category.', 'smartupworld-post-slider' ) ),
		array( 'skip',      '—',           __( 'Set to "current" to skip the current post. Useful on single post templates.', 'smartupworld-post-slider' ) ),
		array( 'layout',    'contained',   __( 'Use "contained" for a centred max-width wrapper, or "fullwidth" to stretch edge to edge.', 'smartupworld-post-slider' ) ),
		array( 'nav',       'true',        __( 'Show previous/next arrow buttons. Set to false to hide.', 'smartupworld-post-slider' ) ),
		array( 'dots',      'true',        __( 'Show pagination dots. Set to false to hide.', 'smartupworld-post-slider' ) ),
	);
	?>
	<div class="suw-admin-wrap">

		<div class="suw-admin-header">
			<img src="<?php echo esc_url( SUW_PS_URL . 'assets/images/smartupworld.svg' ); ?>" alt="SmartUpWorld">
			<div class="suw-admin-header-text">
				<h1>
					<?php esc_html_e( 'SmartUpWorld Post Slider', 'smartupworld-post-slider' ); ?>
					<span class="suw-badge">v<?php echo esc_html( SUW_PS_VERSION ); ?></span>
				</h1>
				<p>
					<?php esc_html_e( 'A lightweight, accessible Owl Carousel post slider — no configuration needed.', 'smartupworld-post-slider' ); ?><br>
					<a href="<?php echo esc_url( $doc_url ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Full Documentation', 'smartupworld-post-slider' ); ?>
					</a>
				</p>
			</div>
		</div>

		<div class="suw-section">
			<h2><?php esc_html_e( 'Basic Shortcode', 'smartupworld-post-slider' ); ?></h2>
			<div class="suw-shortcode-box">
				<?php echo esc_html( '[suwps_post_slider]' ); ?>
				<button type="button" class="suw-copy-btn" data-clipboard="<?php echo esc_attr( '[suwps_post_slider]' ); ?>">
					<?php esc_html_e( 'Copy', 'smartupworld-post-slider' ); ?>
				</button>
			</div>
			<p><?php esc_html_e( 'Paste this shortcode into any post, page, or widget. It displays your 8 latest published posts in a responsive carousel.', 'smartupworld-post-slider' ); ?></p>
		</div>

		<div class="suw-section">
			<h2><?php esc_html_e( 'All Shortcode Attributes', 'smartupworld-post-slider' ); ?></h2>
			<table class="suw-attr-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Attribute', 'smartupworld-post-slider' ); ?></th>
						<th><?php esc_html_e( 'Default', 'smartupworld-post-slider' ); ?></th>
						<th><?php esc_html_e( 'Description', 'smartupworld-post-slider' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $attributes as $attribute ) : ?>
						<tr>
							<td><code><?php echo esc_html( $attribute[0] ); ?></code></td>
							<td><?php echo esc_html( $attribute[1] ); ?></td>
							<td><?php echo esc_html( $attribute[2] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<div class="suw-section">
			<h2><?php esc_html_e( 'Usage Examples', 'smartupworld-post-slider' ); ?></h2>
			<div class="suw-examples">
				<?php foreach ( $examples as $example ) : ?>
					<div>
						<div class="suw-example-label"><?php echo esc_html( $example['label'] ); ?></div>
						<div class="suw-shortcode-box">
							<?php echo esc_html( $example['shortcode'] ); ?>
							<button type="button" class="suw-copy-btn" data-clipboard="<?php echo esc_attr( $example['shortcode'] ); ?>">
								<?php esc_html_e( 'Copy', 'smartupworld-post-slider' ); ?>
							</button>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<p class="suw-footer">
			<a href="https://smartupworld.com/" target="_blank" rel="noopener noreferrer">SmartUpWorld Websolutions</a>
			&nbsp;&middot;&nbsp;
			<a href="<?php echo esc_url( $doc_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'smartupworld-post-slider' ); ?></a>
		</p>

	</div>
	<?php
}
