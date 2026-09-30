<?php
/**
 * Plugin Name:       SmartUpWorld Portfolio Showcase
 * Plugin URI:        https://smartupworld.com/smartupworld-portfolio-showcase/
 * Description:       Filterable portfolio grid with browser-style project cards. Works as an Elementor widget or with the [suw_portfolio] shortcode, with one-click demo content.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            SmartUpWorld Websolutions
 * Author URI:        https://smartupworld.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       smartupworld-portfolio-showcase
 *
 * Elementor tested up to: 4.3.3
 *
 * @package SmartUpWorld_Portfolio_Showcase
 */

defined( 'ABSPATH' ) || exit;

define( 'SUWPF_VERSION', '1.0.0' );
define( 'SUWPF_FILE', __FILE__ );
define( 'SUWPF_DIR', plugin_dir_path( __FILE__ ) );
define( 'SUWPF_URL', plugin_dir_url( __FILE__ ) );
define( 'SUWPF_DOCS_URL', 'https://smartupworld.com/smartupworld-portfolio-showcase/' );

require_once SUWPF_DIR . 'includes/class-suwpf-post-type.php';
require_once SUWPF_DIR . 'includes/class-suwpf-renderer.php';
require_once SUWPF_DIR . 'includes/class-suwpf-shortcode.php';
require_once SUWPF_DIR . 'includes/class-suwpf-demo.php';

SUWPF_Post_Type::init();
SUWPF_Shortcode::init();

if ( is_admin() ) {
	require_once SUWPF_DIR . 'includes/class-suwpf-admin.php';
	SUWPF_Admin::init();
}

/**
 * Front-end assets. Registered everywhere, enqueued only by the shortcode or
 * the Elementor widget that actually renders a portfolio.
 */
function suwpf_register_assets() {
	if ( wp_style_is( 'suwpf', 'registered' ) ) {
		return;
	}
	wp_register_style( 'suwpf', SUWPF_URL . 'assets/css/suw-portfolio.css', array(), SUWPF_VERSION );
	wp_register_script(
		'suwpf',
		SUWPF_URL . 'assets/js/suw-portfolio.js',
		array(),
		SUWPF_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'suwpf_register_assets' );
add_action( 'elementor/frontend/after_register_styles', 'suwpf_register_assets' );
add_action( 'elementor/frontend/after_register_scripts', 'suwpf_register_assets' );

/**
 * Elementor integration (only runs when Elementor is active).
 *
 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
 */
function suwpf_register_elementor_widget( $widgets_manager ) {
	require_once SUWPF_DIR . 'includes/class-suwpf-elementor-widget.php';
	$widgets_manager->register( new SUWPF_Elementor_Widget() );
}
add_action( 'elementor/widgets/register', 'suwpf_register_elementor_widget' );

/**
 * Adds the "SmartUpWorld" category to the Elementor widget panel.
 *
 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
 */
function suwpf_register_elementor_category( $elements_manager ) {
	$elements_manager->add_category(
		'smartupworld',
		array(
			'title' => __( 'SmartUpWorld', 'smartupworld-portfolio-showcase' ),
			'icon'  => 'eicon-apps',
		)
	);
}
add_action( 'elementor/elements/categories_registered', 'suwpf_register_elementor_category' );
