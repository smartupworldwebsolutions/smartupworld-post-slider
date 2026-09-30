<?php
/**
 * Registers the Meme custom post type and Meme Category taxonomy.
 *
 * @package SmartUpWorld_Memes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Post type registration, template loading and asset enqueueing.
 */
class SUWM_Post_Type {

	const POST_TYPE = 'suw_meme';
	const TAXONOMY  = 'meme_category';

	/**
	 * Registers the post type and taxonomy.
	 */
	public static function register() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'               => __( 'Memes', 'smartupworld-memes' ),
					'singular_name'      => __( 'Meme', 'smartupworld-memes' ),
					'add_new'            => __( 'Add New Meme', 'smartupworld-memes' ),
					'add_new_item'       => __( 'Add New Meme', 'smartupworld-memes' ),
					'edit_item'          => __( 'Edit Meme', 'smartupworld-memes' ),
					'new_item'           => __( 'New Meme', 'smartupworld-memes' ),
					'view_item'          => __( 'View Meme', 'smartupworld-memes' ),
					'search_items'       => __( 'Search Memes', 'smartupworld-memes' ),
					'not_found'          => __( 'No memes found.', 'smartupworld-memes' ),
					'not_found_in_trash' => __( 'No memes found in trash.', 'smartupworld-memes' ),
					'all_items'          => __( 'All Memes', 'smartupworld-memes' ),
					'menu_name'          => __( 'Memes', 'smartupworld-memes' ),
				),
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => true,
				'query_var'           => true,
				'rewrite'             => array(
					'slug'       => 'memes',
					'with_front' => false,
				),
				'capability_type'     => 'post',
				'has_archive'         => 'memes',
				'hierarchical'        => false,
				'menu_position'       => 6,
				'menu_icon'           => 'dashicons-format-image',
				'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ),
			)
		);

		register_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'              => __( 'Meme Categories', 'smartupworld-memes' ),
					'singular_name'     => __( 'Meme Category', 'smartupworld-memes' ),
					'search_items'      => __( 'Search Meme Categories', 'smartupworld-memes' ),
					'all_items'         => __( 'All Meme Categories', 'smartupworld-memes' ),
					'parent_item'       => __( 'Parent Meme Category', 'smartupworld-memes' ),
					'parent_item_colon' => __( 'Parent Meme Category:', 'smartupworld-memes' ),
					'edit_item'         => __( 'Edit Meme Category', 'smartupworld-memes' ),
					'update_item'       => __( 'Update Meme Category', 'smartupworld-memes' ),
					'add_new_item'      => __( 'Add New Meme Category', 'smartupworld-memes' ),
					'new_item_name'     => __( 'New Meme Category Name', 'smartupworld-memes' ),
					'menu_name'         => __( 'Meme Categories', 'smartupworld-memes' ),
				),
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_in_menu'      => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'meme-category' ),
				'query_var'         => true,
			)
		);
	}

	/**
	 * Enqueues the meme stylesheet on meme pages.
	 */
	public static function enqueue() {
		if ( ! is_singular( self::POST_TYPE ) && ! is_post_type_archive( self::POST_TYPE ) && ! is_tax( self::TAXONOMY ) ) {
			return;
		}
		wp_enqueue_style(
			'suwm-memes',
			SUWM_URL . 'assets/css/memes.css',
			array(),
			SUWM_VERSION
		);
	}

	/**
	 * Points single-meme requests at the plugin's template if the theme doesn't have one.
	 *
	 * @param string $template Current template path.
	 * @return string
	 */
	public static function single_template( $template ) {
		if ( is_singular( self::POST_TYPE ) && ! locate_template( 'single-' . self::POST_TYPE . '.php' ) ) {
			$plugin_template = SUWM_DIR . 'templates/single-suw_meme.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}
		return $template;
	}

	/**
	 * Points archive/taxonomy requests at the plugin's template if the theme doesn't have one.
	 *
	 * @param string $template Current template path.
	 * @return string
	 */
	public static function archive_template( $template ) {
		$use_plugin = false;
		if ( is_post_type_archive( self::POST_TYPE ) && ! locate_template( 'archive-' . self::POST_TYPE . '.php' ) ) {
			$use_plugin = true;
		}
		if ( is_tax( self::TAXONOMY ) && ! locate_template( 'taxonomy-' . self::TAXONOMY . '.php' ) ) {
			$use_plugin = true;
		}
		if ( $use_plugin ) {
			$plugin_template = SUWM_DIR . 'templates/archive-suw_meme.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}
		return $template;
	}
}
