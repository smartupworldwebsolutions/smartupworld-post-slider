<?php
/**
 * Runs when the plugin is uninstalled via "Delete" in the WordPress dashboard.
 * Removes plugin options. Meme posts and categories are kept — reinstalling
 * brings the site back to the same state.
 *
 * @package SmartUpWorld_Memes
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Nothing stored yet — placeholder for future options cleanup.
