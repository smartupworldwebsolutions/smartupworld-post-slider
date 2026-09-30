<?php
/**
 * "Portfolio" admin section: projects (title, featured image, website URL)
 * grouped by a "Project type" taxonomy.
 */

defined( 'ABSPATH' ) || exit;

class SUWPF_Post_Type {

	const POST_TYPE = 'suwpf_project';
	const TAXONOMY  = 'suwpf_type';
	const META_URL  = '_suwpf_url';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
	}

	public static function register() {
		register_post_type( self::POST_TYPE, array(
			'labels'        => array(
				'name'          => __( 'Portfolio', 'smartupworld-portfolio' ),
				'singular_name' => __( 'Project', 'smartupworld-portfolio' ),
				'add_new'       => __( 'Add New Project', 'smartupworld-portfolio' ),
				'add_new_item'  => __( 'Add New Project', 'smartupworld-portfolio' ),
				'edit_item'     => __( 'Edit Project', 'smartupworld-portfolio' ),
				'all_items'     => __( 'All Projects', 'smartupworld-portfolio' ),
				'menu_name'     => __( 'Portfolio', 'smartupworld-portfolio' ),
			),
			'public'        => false, // projects link out to client sites; no single pages needed
			'show_ui'       => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-portfolio',
			'menu_position' => 21,
			'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
		) );

		register_taxonomy( self::TAXONOMY, self::POST_TYPE, array(
			'labels'            => array(
				'name'          => __( 'Project Types', 'smartupworld-portfolio' ),
				'singular_name' => __( 'Project Type', 'smartupworld-portfolio' ),
				'add_new_item'  => __( 'Add New Type', 'smartupworld-portfolio' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
		) );

		register_post_meta( self::POST_TYPE, self::META_URL, array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'esc_url_raw',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}

	public static function add_meta_box() {
		add_meta_box( 'suwpf_url', __( 'Website URL', 'smartupworld-portfolio' ), array( __CLASS__, 'meta_box' ), self::POST_TYPE, 'normal', 'high' );
	}

	public static function meta_box( $post ) {
		wp_nonce_field( 'suwpf_save', 'suwpf_nonce' );
		printf(
			'<p><input type="url" name="suwpf_url" value="%s" class="widefat" placeholder="https://example.com"></p><p class="description">%s</p>',
			esc_attr( get_post_meta( $post->ID, self::META_URL, true ) ),
			esc_html__( 'The live site the card links to. Set the screenshot as the Featured Image, and the category under Project Types.', 'smartupworld-portfolio' )
		);
	}

	public static function save( $post_id ) {
		if ( ! isset( $_POST['suwpf_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['suwpf_nonce'] ), 'suwpf_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$url = isset( $_POST['suwpf_url'] ) ? esc_url_raw( wp_unslash( $_POST['suwpf_url'] ) ) : '';
		update_post_meta( $post_id, self::META_URL, $url );
	}

	public static function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new['suwpf_thumb'] = __( 'Image', 'smartupworld-portfolio' );
			}
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['suwpf_url'] = __( 'Website', 'smartupworld-portfolio' );
			}
		}
		return $new;
	}

	public static function column_content( $column, $post_id ) {
		if ( 'suwpf_thumb' === $column ) {
			echo get_the_post_thumbnail( $post_id, array( 80, 50 ), array( 'style' => 'width:80px;height:50px;object-fit:cover;border-radius:4px' ) );
		} elseif ( 'suwpf_url' === $column ) {
			$url = get_post_meta( $post_id, self::META_URL, true );
			if ( $url ) {
				printf( '<a href="%1$s" target="_blank" rel="noopener">%2$s</a>', esc_url( $url ), esc_html( SUWPF_Renderer::domain( $url ) ) );
			}
		}
	}

	/**
	 * Projects as renderer items.
	 *
	 * @param array $args limit (int), type (comma-separated type slugs).
	 * @return array[]
	 */
	public static function get_items( $args = array() ) {
		$args  = wp_parse_args( $args, array( 'limit' => -1, 'type' => '' ) );
		$query = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => (int) $args['limit'] ? (int) $args['limit'] : -1,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			'no_found_rows'  => true,
		);
		if ( '' !== trim( (string) $args['type'] ) ) {
			$query['tax_query'] = array( array(
				'taxonomy' => self::TAXONOMY,
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_title', explode( ',', $args['type'] ) ),
			) );
		}

		$items = array();
		foreach ( get_posts( $query ) as $post ) {
			$terms   = get_the_terms( $post, self::TAXONOMY );
			$items[] = array(
				'title'    => get_the_title( $post ),
				'image'    => (string) get_the_post_thumbnail_url( $post, 'large' ),
				'image_id' => (int) get_post_thumbnail_id( $post ),
				'type'     => ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '',
				'url'      => (string) get_post_meta( $post->ID, self::META_URL, true ),
			);
		}
		return $items;
	}
}
