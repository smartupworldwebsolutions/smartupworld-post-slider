<?php
/**
 * [suw_portfolio] shortcode — renders projects from the Portfolio admin section.
 *
 * Attributes: eyebrow, heading, subheading, type (slugs, comma-separated),
 * limit, filter (yes|no), counts (yes|no), browser (yes|no), all_label,
 * cta_text, cta_url, heading_tag (h2|h3…), full_width (yes|no).
 */

defined( 'ABSPATH' ) || exit;

class SUWPF_Shortcode {

	public static function init() {
		add_shortcode( 'suw_portfolio', array( __CLASS__, 'render' ) );
	}

	public static function render( $atts ) {
		$atts = shortcode_atts( array(
			'eyebrow'     => '',
			'heading'     => '',
			'subheading'  => '',
			'type'        => '',
			'limit'       => 0,
			'filter'      => 'yes',
			'counts'      => 'yes',
			'browser'     => 'yes',
			'all_label'   => __( 'All', 'smartupworld-portfolio' ),
			'cta_text'    => '',
			'cta_url'     => '',
			'heading_tag' => 'h2',
			'full_width'  => 'yes',
		), $atts, 'suw_portfolio' );

		$items = SUWPF_Post_Type::get_items( array(
			'limit' => absint( $atts['limit'] ),
			'type'  => $atts['type'],
		) );

		return SUWPF_Renderer::render( $items, array(
			'eyebrow'       => $atts['eyebrow'],
			'heading'       => $atts['heading'],
			'subheading'    => $atts['subheading'],
			'show_filter'   => $atts['filter'],
			'show_counts'   => $atts['counts'],
			'show_browser'  => $atts['browser'],
			'all_label'     => $atts['all_label'],
			'cta_text'      => $atts['cta_text'],
			'cta_url'       => $atts['cta_url'],
			'heading_tag'   => strtolower( $atts['heading_tag'] ),
			'full_width_bg' => $atts['full_width'],
		) );
	}
}
