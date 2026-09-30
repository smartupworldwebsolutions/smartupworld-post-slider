<?php
/**
 * Plugin Name:       SmartUpWorld Portfolio Showcase
 * Plugin URI:        https://smartupworld.com/
 * Description:       A modern, filterable portfolio grid with browser-style project cards. Works as an Elementor widget or with the [suw_portfolio] shortcode.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Smartupworld Websolutions
 * Author URI:        https://smartupworld.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       smartupworld-portfolio
 */

defined( 'ABSPATH' ) || exit;

define( 'SUWPF_VERSION', '1.0.0' );
define( 'SUWPF_FILE', __FILE__ );
define( 'SUWPF_DIR', plugin_dir_path( __FILE__ ) );
define( 'SUWPF_URL', plugin_dir_url( __FILE__ ) );

require_once SUWPF_DIR . 'includes/class-suwpf-post-type.php';
require_once SUWPF_DIR . 'includes/class-suwpf-renderer.php';
require_once SUWPF_DIR . 'includes/class-suwpf-shortcode.php';
require_once SUWPF_DIR . 'includes/class-suwpf-demo.php';

if ( is_admin() ) {
	require_once SUWPF_DIR . 'includes/class-suwpf-admin.php';
	SUWPF_Admin::init();
}

SUWPF_Post_Type::init();
SUWPF_Shortcode::init();

/**
 * Front-end assets. Registered everywhere, enqueued only by the shortcode or
 * the Elementor widget that actually renders a portfolio.
 */
add_action( 'wp_enqueue_scripts', 'suwpf_register_assets' );
add_action( 'elementor/frontend/after_register_styles', 'suwpf_register_assets' );
add_action( 'elementor/frontend/after_register_scripts', 'suwpf_register_assets' );
function suwpf_register_assets() {
	if ( wp_style_is( 'suwpf', 'registered' ) ) {
		return;
	}
	wp_register_style( 'suwpf', SUWPF_URL . 'assets/css/suw-portfolio.css', array(), SUWPF_VERSION );
	wp_register_script( 'suwpf', SUWPF_URL . 'assets/js/suw-portfolio.js', array(), SUWPF_VERSION, true );
}

/**
 * Elementor integration (only when Elementor is active).
 */
add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
	require_once SUWPF_DIR . 'includes/class-suwpf-elementor-widget.php';
	$widgets_manager->register( new SUWPF_Elementor_Widget() );
} );

add_action( 'elementor/elements/categories_registered', function ( $elements_manager ) {
	$elements_manager->add_category( 'smartupworld', array(
		'title' => __( 'SmartUpWorld', 'smartupworld-portfolio' ),
		'icon'  => 'fa fa-plug',
	) );
} );

register_activation_hook( __FILE__, function () {
	SUWPF_Post_Type::register();
	flush_rewrite_rules();
} );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
