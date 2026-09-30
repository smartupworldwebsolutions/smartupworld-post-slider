<?php
/**
 * Plugin Name:       SmartUpWorld Memes
 * Plugin URI:        https://smartupworld.com/smartupworld-memes/
 * Description:       A dedicated Memes section for SmartUpWorld — separate Custom Post Type so memes never mix with blog articles.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            SmartUpWorld Websolutions
 * Author URI:        https://smartupworld.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       smartupworld-memes
 * Domain Path:       /languages
 *
 * @package SmartUpWorld_Memes
 */

defined( 'ABSPATH' ) || exit;

define( 'SUWM_VERSION', '1.0.0' );
define( 'SUWM_FILE', __FILE__ );
define( 'SUWM_DIR', plugin_dir_path( __FILE__ ) );
define( 'SUWM_URL', plugin_dir_url( __FILE__ ) );

require_once SUWM_DIR . 'includes/class-suwm-post-type.php';

add_action( 'init', array( 'SUWM_Post_Type', 'register' ) );
add_action( 'wp_enqueue_scripts', array( 'SUWM_Post_Type', 'enqueue' ) );
add_filter( 'single_template', array( 'SUWM_Post_Type', 'single_template' ) );
add_filter( 'archive_template', array( 'SUWM_Post_Type', 'archive_template' ) );
