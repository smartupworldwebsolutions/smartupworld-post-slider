<?php
/**
 * Sample projects and one-click demo content.
 *
 * The three sample projects ship with the plugin (images in assets/images),
 * so nothing is downloaded from other sites. Everything the importer creates
 * is flagged with META_FLAG so "Delete demo content" removes exactly that.
 *
 * @package SmartUpWorld_Portfolio_Showcase
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sample data, demo import and demo removal.
 */
class SUWPF_Demo {

	const META_FLAG  = '_suwpf_demo';
	const PAGE_TITLE = 'Portfolio Demo';

	/**
	 * The bundled sample projects.
	 *
	 * The addresses are IANA-reserved example domains, so they can never point
	 * at somebody's real site.
	 *
	 * @return array[] title, type, url, image (file name stem in assets/images).
	 */
	public static function projects() {
		return array(
			array(
				'title' => __( 'Sample Blog Project', 'smartupworld-portfolio-showcase' ),
				'type'  => __( 'Blog', 'smartupworld-portfolio-showcase' ),
				'url'   => 'https://www.example.com/',
				'image' => 'sample-blog',
			),
			array(
				'title' => __( 'Sample Business Project', 'smartupworld-portfolio-showcase' ),
				'type'  => __( 'Business', 'smartupworld-portfolio-showcase' ),
				'url'   => 'https://www.example.org/',
				'image' => 'sample-business',
			),
			array(
				'title' => __( 'Sample Corporate Project', 'smartupworld-portfolio-showcase' ),
				'type'  => __( 'Business', 'smartupworld-portfolio-showcase' ),
				'url'   => 'https://www.example.net/',
				'image' => 'sample-corporate',
			),
		);
	}

	/**
	 * Sample projects as renderer items (shown until the first real project is published).
	 *
	 * @return array[]
	 */
	public static function samples() {
		$items = array();
		foreach ( self::projects() as $p ) {
			$items[] = array(
				'title'  => $p['title'],
				'type'   => $p['type'],
				'url'    => $p['url'],
				'image'  => SUWPF_URL . 'assets/images/' . $p['image'] . '-1200.webp',
				'srcset' => SUWPF_URL . 'assets/images/' . $p['image'] . '-600.webp 600w, ' . SUWPF_URL . 'assets/images/' . $p['image'] . '-1200.webp 1200w',
				'width'  => 1200,
				'height' => 750,
				'sample' => true,
			);
		}
		return $items;
	}

	/**
	 * Whether any demo content is currently installed.
	 *
	 * @return bool
	 */
	public static function is_installed() {
		return (bool) self::flagged_ids( array( SUWPF_Post_Type::POST_TYPE, 'page' ), 1 );
	}

	/**
	 * Creates the sample projects, their types and a "Portfolio Demo" page.
	 * Safe to run more than once: anything that already exists is skipped.
	 *
	 * @return array{created:int, skipped:int, images_failed:int, page_id:int}
	 */
	public static function import() {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$result = array(
			'created'       => 0,
			'skipped'       => 0,
			'images_failed' => 0,
			'page_id'       => 0,
		);

		foreach ( self::projects() as $order => $p ) {
			$existing = self::find_demo_post( SUWPF_Post_Type::POST_TYPE, $p['title'] );
			if ( $existing ) {
				++$result['skipped'];
				if ( ! has_post_thumbnail( $existing ) && ! self::attach_image( $existing, $p['image'], $p['title'] ) ) {
					++$result['images_failed'];
				}
				continue;
			}

			$post_id = wp_insert_post(
				array(
					'post_type'   => SUWPF_Post_Type::POST_TYPE,
					'post_status' => 'publish',
					'post_title'  => $p['title'],
					'menu_order'  => $order,
				),
				true
			);
			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			update_post_meta( $post_id, SUWPF_Post_Type::META_URL, esc_url_raw( $p['url'] ) );
			update_post_meta( $post_id, self::META_FLAG, 1 );

			$term_id = self::term_id( $p['type'] );
			if ( $term_id ) {
				wp_set_object_terms( $post_id, array( $term_id ), SUWPF_Post_Type::TAXONOMY );
			}

			if ( ! self::attach_image( $post_id, $p['image'], $p['title'] ) ) {
				++$result['images_failed'];
			}

			++$result['created'];
		}

		$page = self::find_demo_post( 'page', self::PAGE_TITLE );
		if ( ! $page ) {
			$page = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => self::PAGE_TITLE,
					'post_content' => '<!-- wp:shortcode -->[suw_portfolio eyebrow="Portfolio" heading="Our Clients / Demos" subheading="Explore our portfolio to see the innovative projects and solutions we have crafted for our clients."]<!-- /wp:shortcode -->',
				)
			);
			if ( $page && ! is_wp_error( $page ) ) {
				update_post_meta( $page, self::META_FLAG, 1 );
			}
		}
		$result['page_id'] = is_wp_error( $page ) ? 0 : (int) $page;

		return $result;
	}

	/**
	 * Removes everything the importer created: projects, their screenshots,
	 * the demo page and any project types that are left empty.
	 *
	 * @return array{projects:int, images:int, pages:int}
	 */
	public static function delete() {
		$result = array(
			'projects' => 0,
			'images'   => 0,
			'pages'    => 0,
		);

		foreach ( self::flagged_ids( array( 'attachment' ) ) as $id ) {
			if ( wp_delete_attachment( $id, true ) ) {
				++$result['images'];
			}
		}
		foreach ( self::flagged_ids( array( SUWPF_Post_Type::POST_TYPE ) ) as $id ) {
			if ( wp_delete_post( $id, true ) ) {
				++$result['projects'];
			}
		}
		foreach ( self::flagged_ids( array( 'page' ) ) as $id ) {
			if ( wp_delete_post( $id, true ) ) {
				++$result['pages'];
			}
		}

		$terms = get_terms(
			array(
				'taxonomy'   => SUWPF_Post_Type::TAXONOMY,
				'hide_empty' => false,
				'meta_key'   => self::META_FLAG, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- small, admin-only lookup.
			)
		);
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$fresh = get_term( $term->term_id, SUWPF_Post_Type::TAXONOMY );
				if ( $fresh && ! is_wp_error( $fresh ) && 0 === (int) $fresh->count ) {
					wp_delete_term( $term->term_id, SUWPF_Post_Type::TAXONOMY );
				}
			}
		}

		return $result;
	}

	/**
	 * Demo page ID, or 0 if the demo hasn't been imported.
	 *
	 * @return int
	 */
	public static function page_id() {
		return self::find_demo_post( 'page', self::PAGE_TITLE );
	}

	/**
	 * Copies a bundled screenshot into the Media Library and sets it as the featured image.
	 *
	 * @param int    $post_id Project ID.
	 * @param string $image   File name stem in assets/images.
	 * @param string $title   Project title.
	 * @return bool
	 */
	private static function attach_image( $post_id, $image, $title ) {
		$file = $image . '-1200.webp';
		$src  = SUWPF_DIR . 'assets/images/' . $file;
		$tmp  = wp_tempnam( $file );
		if ( ! $tmp || ! is_readable( $src ) || ! copy( $src, $tmp ) ) {
			if ( $tmp ) {
				wp_delete_file( $tmp );
			}
			return false;
		}

		/* translators: %s: project name */
		$alt           = sprintf( __( '%s website screenshot', 'smartupworld-portfolio-showcase' ), $title );
		$attachment_id = media_handle_sideload(
			array(
				'name'     => $file,
				'tmp_name' => $tmp,
			),
			$post_id,
			$alt
		);
		if ( is_wp_error( $attachment_id ) ) {
			wp_delete_file( $tmp );
			return false;
		}

		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
		update_post_meta( $attachment_id, self::META_FLAG, 1 );
		set_post_thumbnail( $post_id, $attachment_id );
		return true;
	}

	/**
	 * Project type term ID, creating (and flagging) the term if needed.
	 *
	 * @param string $name Type name.
	 * @return int
	 */
	private static function term_id( $name ) {
		$term = term_exists( $name, SUWPF_Post_Type::TAXONOMY );
		if ( $term ) {
			return (int) $term['term_id'];
		}
		$term = wp_insert_term( $name, SUWPF_Post_Type::TAXONOMY );
		if ( is_wp_error( $term ) ) {
			return 0;
		}
		add_term_meta( $term['term_id'], self::META_FLAG, 1, true );
		return (int) $term['term_id'];
	}

	/**
	 * IDs of flagged demo posts.
	 *
	 * @param string[] $post_types Post types.
	 * @param int      $limit      Max results, -1 for all.
	 * @return int[]
	 */
	private static function flagged_ids( $post_types, $limit = -1 ) {
		return array_map(
			'intval',
			get_posts(
				array(
					'post_type'      => $post_types,
					'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash', 'inherit' ),
					'meta_key'       => self::META_FLAG, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- small, admin-only lookup.
					'fields'         => 'ids',
					'posts_per_page' => $limit,
					'no_found_rows'  => true,
				)
			)
		);
	}

	/**
	 * Finds a flagged demo post by title.
	 *
	 * @param string $post_type Post type.
	 * @param string $title     Title.
	 * @return int
	 */
	private static function find_demo_post( $post_type, $title ) {
		foreach ( self::flagged_ids( array( $post_type ) ) as $id ) {
			if ( get_post_field( 'post_title', $id ) === $title ) {
				return $id;
			}
		}
		return 0;
	}
}
