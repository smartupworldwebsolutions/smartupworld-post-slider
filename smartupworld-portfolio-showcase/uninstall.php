<?php
/**
 * Uninstall: removes the demo content the plugin created. Projects the site
 * owner added themselves are left in place so no work is lost.
 *
 * @package SmartUpWorld_Portfolio_Showcase
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once __DIR__ . '/includes/class-suwpf-post-type.php';
require_once __DIR__ . '/includes/class-suwpf-demo.php';

// The taxonomy must exist for the empty demo project types to be deleted.
SUWPF_Post_Type::register();
SUWPF_Demo::delete();
