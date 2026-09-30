<?php
/**
 * [suw_portfolio] shortcode — renders projects from the Portfolio admin section.
 *
 * @package SmartUpWorld_Portfolio_Showcase
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode handler.
 */
class SUWPF_Shortcode {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_shortcode( 'suw_portfolio', array( __CLASS__, 'render' ) );
	}

	/**
	 * Shortcode output.
	 *
	 * Attributes: eyebrow, heading, subheading, type (slugs, comma-separated),
	 * limit, filter (yes|no), counts (yes|no), browser (yes|no), all_label,
	 * cta_text, cta_url, heading_tag (h1–h4|div), full_width (yes|no).
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'eyebrow'     => '',
				'heading'     => '',
				'subheading'  => '',
				'type'        => '',
				'limit'       => 0,
				'filter'      => 'yes',
				'counts'      => 'yes',
				'browser'     => 'yes',
				'all_label'   => __( 'All', 'smartupworld-portfolio-showcase' ),
				'cta_text'    => '',
				'cta_url'     => '',
				'heading_tag' => 'h2',
				'full_width'  => 'yes',
			),
			$atts,
			'suw_portfolio'
		);

		$items = SUWPF_Post_Type::get_items_or_samples(
			array(
				'limit' => absint( $atts['limit'] ),
				'type'  => $atts['type'],
			)
		);

		return SUWPF_Renderer::render(
			$items,
			array(
				'eyebrow'       => $atts['eyebrow'],
				'heading'       => $atts['heading'],
				'subheading'    => $atts['subheading'],
				'show_filter'   => $atts['filter'],
				'show_counts'   => $atts['counts'],
				'show_browser'  => $atts['browser'],
				'all_label'     => $atts['all_label'],
				'cta_text'      => $atts['cta_text'],
				'cta_url'       => $atts['cta_url'],
				'heading_tag'   => strtolower( (string) $atts['heading_tag'] ),
				'full_width_bg' => $atts['full_width'],
				'empty_text'    => __( 'No published projects match the "type" attribute of this portfolio shortcode.', 'smartupworld-portfolio-showcase' ),
			)
		);
	}
}
