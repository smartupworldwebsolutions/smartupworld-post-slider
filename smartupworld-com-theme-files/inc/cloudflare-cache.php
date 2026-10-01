<?php
/**
 * Cloudflare cache purge helpers.
 *
 * Automatically purges the affected post URL from Cloudflare when:
 *  - A comment is approved / published
 *  - A post is published or updated
 *
 * Store your credentials in wp-config.php (never hard-code them here):
 *   define( 'SUW_CF_ZONE_ID',   'your_zone_id_here' );
 *   define( 'SUW_CF_API_TOKEN', 'your_api_token_here' );
 *
 * @package Smartupworld
 */

defined( 'ABSPATH' ) || exit;

// ── Purge on comment approval ─────────────────────────────────────────────────
add_action( 'transition_comment_status', 'suw_cf_purge_on_comment', 10, 3 );
function suw_cf_purge_on_comment( $new_status, $old_status, $comment ) {
	if ( 'approved' !== $new_status || 'approved' === $old_status ) {
		return;
	}
	$url = get_permalink( $comment->comment_post_ID );
	if ( $url ) {
		suw_cf_purge_urls( array( $url ) );
	}
}

// ── Purge on post publish / update ────────────────────────────────────────────
add_action( 'save_post', 'suw_cf_purge_on_save', 10, 2 );
function suw_cf_purge_on_save( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( 'publish' !== $post->post_status ) {
		return;
	}
	$url = get_permalink( $post_id );
	if ( $url ) {
		suw_cf_purge_urls( array( $url ) );
	}
}

// ── Core purge function ───────────────────────────────────────────────────────
function suw_cf_purge_urls( array $urls ) {
	$zone_id   = defined( 'SUW_CF_ZONE_ID' )   ? SUW_CF_ZONE_ID   : '';
	$api_token = defined( 'SUW_CF_API_TOKEN' ) ? SUW_CF_API_TOKEN : '';

	if ( ! $zone_id || ! $api_token || empty( $urls ) ) {
		return;
	}

	wp_remote_post(
		"https://api.cloudflare.com/client/v4/zones/{$zone_id}/purge_cache",
		array(
			'timeout' => 10,
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_token,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( array( 'files' => array_values( $urls ) ) ),
		)
	);
}
