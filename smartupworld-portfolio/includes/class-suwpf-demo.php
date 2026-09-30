<?php
/**
 * One-click demo content: 7 projects with screenshots, the Blog/Business
 * types and a "Portfolio Demo" page. Safe to run more than once — items that
 * already exist are skipped.
 */

defined( 'ABSPATH' ) || exit;

class SUWPF_Demo {

	const META_FLAG = '_suwpf_demo';

	public static function projects() {
		return array(
			array( 'Weekleaks Blog', 'Blog', 'https://weekleaks.org', 'https://smartupworld.com/wp-content/uploads/2025/08/weekleaks.org-min-1.jpg' ),
			array( 'Digital Marketing Agency', 'Business', 'https://digitalmarketbooster.com/', 'https://smartupworld.com/wp-content/uploads/2025/08/agency.jpg' ),
			array( 'Bicchuron Blog', 'Blog', 'https://bicchuron.com', 'https://smartupworld.com/wp-content/uploads/2025/08/bicchuron-min.jpg' ),
			array( 'Daily Rumblings', 'Blog', 'https://dailyrumblings.com', 'https://smartupworld.com/wp-content/uploads/2025/08/dailyrumblings.webp' ),
			array( 'Grdruk', 'Business', 'https://grdruk.nl', 'https://smartupworld.com/wp-content/uploads/2026/04/grdruk.webp' ),
			array( 'Restaurant', 'Business', 'https://restaurant.upspire.blog/', 'https://smartupworld.com/wp-content/uploads/2026/06/restaurant.jpg' ),
			array( 'Nidohome', 'Business', 'https://nidohome.eu/', 'https://smartupworld.com/wp-content/uploads/2026/09/nidohome.eu_.jpg' ),
		);
	}

	/**
	 * @return array{created:int, skipped:int, images_failed:int, page_id:int}
	 */
	public static function import() {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 300 ); // image downloads can be slow
		}

		$result = array( 'created' => 0, 'skipped' => 0, 'images_failed' => 0, 'page_id' => 0 );

		foreach ( self::projects() as $order => $p ) {
			list( $title, $type, $url, $image ) = $p;

			$existing = self::find_demo_post( SUWPF_Post_Type::POST_TYPE, $title );
			if ( $existing ) {
				$result['skipped']++;
				// Re-running retries screenshots that failed to download last time.
				if ( ! has_post_thumbnail( $existing ) && ! self::attach_image( $existing, $image, $title ) ) {
					$result['images_failed']++;
				}
				continue;
			}

			$post_id = wp_insert_post( array(
				'post_type'   => SUWPF_Post_Type::POST_TYPE,
				'post_status' => 'publish',
				'post_title'  => $title,
				'menu_order'  => $order,
			), true );
			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			update_post_meta( $post_id, SUWPF_Post_Type::META_URL, esc_url_raw( $url ) );
			update_post_meta( $post_id, self::META_FLAG, 1 );
			wp_set_object_terms( $post_id, $type, SUWPF_Post_Type::TAXONOMY );

			if ( ! self::attach_image( $post_id, $image, $title ) ) {
				$result['images_failed']++;
			}

			$result['created']++;
		}

		$page = self::find_demo_post( 'page', 'Portfolio Demo' );
		if ( ! $page ) {
			$page = wp_insert_post( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Portfolio Demo',
				'post_content' => '[suw_portfolio eyebrow="Portfolio" heading="Our Clients / Demos" subheading="Explore our portfolio to see the innovative projects and solutions we have crafted for our clients."]',
			) );
			if ( $page && ! is_wp_error( $page ) ) {
				update_post_meta( $page, self::META_FLAG, 1 );
			}
		}
		$result['page_id'] = is_wp_error( $page ) ? 0 : (int) $page;

		return $result;
	}

	/** Downloads the screenshot into the Media Library and sets it as the featured image. */
	private static function attach_image( $post_id, $image, $title ) {
		$attachment_id = media_sideload_image( $image, $post_id, $title . ' website screenshot', 'id' );
		if ( is_wp_error( $attachment_id ) ) {
			return false;
		}
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title . ' website screenshot' );
		update_post_meta( $attachment_id, self::META_FLAG, 1 );
		set_post_thumbnail( $post_id, $attachment_id );
		return true;
	}

	/** Demo page ID, or 0 if the demo hasn't been imported. */
	public static function page_id() {
		return self::find_demo_post( 'page', 'Portfolio Demo' );
	}

	private static function find_demo_post( $post_type, $title ) {
		$ids = get_posts( array(
			'post_type'      => $post_type,
			'post_status'    => 'any',
			'title'          => $title,
			'meta_key'       => self::META_FLAG,
			'fields'         => 'ids',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
		) );
		return $ids ? (int) $ids[0] : 0;
	}
}
