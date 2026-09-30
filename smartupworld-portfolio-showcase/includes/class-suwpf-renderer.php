<?php
/**
 * Shared markup for the shortcode and the Elementor widget.
 *
 * @package SmartUpWorld_Portfolio_Showcase
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the portfolio section.
 */
class SUWPF_Renderer {

	/**
	 * Returns the portfolio section markup (for the shortcode).
	 *
	 * @param array $items    See display().
	 * @param array $settings See display().
	 * @return string
	 */
	public static function render( array $items, array $settings = array() ) {
		ob_start();
		self::display( $items, $settings );
		return (string) ob_get_clean();
	}

	/**
	 * Prints the portfolio section.
	 *
	 * @param array $items    Each: title, type, url, and image (URL) or image_id.
	 *                        Optional: srcset, width, height, sample (bool).
	 * @param array $settings eyebrow, heading, subheading, show_filter, show_counts,
	 *                        show_browser, all_label, cta_text, cta_url, heading_tag,
	 *                        full_width_bg, empty_text.
	 */
	public static function display( array $items, array $settings = array() ) {
		$s = wp_parse_args(
			$settings,
			array(
				'eyebrow'       => '',
				'heading'       => '',
				'subheading'    => '',
				'show_filter'   => true,
				'show_counts'   => true,
				'show_browser'  => true,
				'all_label'     => __( 'All', 'smartupworld-portfolio-showcase' ),
				'cta_text'      => '',
				'cta_url'       => '',
				'heading_tag'   => 'h2',
				'full_width_bg' => true,
				'empty_text'    => __( 'No portfolio projects match these settings yet.', 'smartupworld-portfolio-showcase' ),
			)
		);

		$items = array_values(
			array_filter(
				$items,
				function ( $item ) {
					return ! empty( $item['title'] );
				}
			)
		);
		if ( ! $items ) {
			if ( current_user_can( 'edit_posts' ) ) {
				echo '<p class="suwpf-empty">' . esc_html( $s['empty_text'] ) . '</p>';
			}
			return;
		}

		wp_enqueue_style( 'suwpf' );
		wp_enqueue_script( 'suwpf' );

		$tag         = in_array( $s['heading_tag'], array( 'h1', 'h2', 'h3', 'h4', 'div' ), true ) ? $s['heading_tag'] : 'h2';
		$uid         = wp_unique_id( 'suwpf-' );
		$types       = array_values( array_unique( array_filter( array_column( $items, 'type' ) ) ) );
		$type_counts = array_count_values( array_filter( array_column( $items, 'type' ) ) );
		$show_filter = self::truthy( $s['show_filter'] ) && count( $types ) > 1;
		$show_counts = self::truthy( $s['show_counts'] );
		$levels      = array(
			'h1' => 'h2',
			'h2' => 'h3',
			'h3' => 'h4',
			'h4' => 'h5',
		);
		$card_tag    = ( $s['heading'] && isset( $levels[ $tag ] ) ) ? $levels[ $tag ] : 'h2';
		$is_sample   = ! empty( $items[0]['sample'] );

		?>
		<section class="suwpf<?php echo self::truthy( $s['full_width_bg'] ) ? '' : ' suwpf--boxed'; ?>" id="<?php echo esc_attr( $uid ); ?>"<?php echo $s['heading'] ? ' aria-labelledby="' . esc_attr( $uid ) . '-h"' : ''; ?>>
			<div class="suwpf__container">

				<?php if ( $s['eyebrow'] || $s['heading'] || $s['subheading'] ) : ?>
					<div class="suwpf__head">
						<?php if ( $s['eyebrow'] ) : ?>
							<span class="suwpf__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></span>
						<?php endif; ?>
						<?php if ( $s['heading'] ) : ?>
							<<?php echo tag_escape( $tag ); ?> class="suwpf__heading" id="<?php echo esc_attr( $uid ); ?>-h"><?php echo esc_html( $s['heading'] ); ?></<?php echo tag_escape( $tag ); ?>>
							<span class="suwpf__underline" aria-hidden="true"></span>
						<?php endif; ?>
						<?php if ( $s['subheading'] ) : ?>
							<p class="suwpf__sub"><?php echo esc_html( $s['subheading'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $is_sample && current_user_can( 'edit_posts' ) ) : ?>
					<p class="suwpf__notice">
						<?php
						printf(
							wp_kses(
								/* translators: 1: "Add New Project" admin URL, 2: demo content admin URL */
								__( 'You are seeing sample projects (only logged-in editors see this note). <a href="%1$s">Add your first project</a> or <a href="%2$s">import the demo content</a> — the samples disappear as soon as a project is published.', 'smartupworld-portfolio-showcase' ),
								array( 'a' => array( 'href' => array() ) )
							),
							esc_url( admin_url( 'post-new.php?post_type=' . SUWPF_Post_Type::POST_TYPE ) ),
							esc_url( admin_url( 'edit.php?post_type=' . SUWPF_Post_Type::POST_TYPE . '&page=suwpf-docs&tab=demo' ) )
						);
						?>
					</p>
				<?php endif; ?>

				<?php if ( $show_filter ) : ?>
					<div class="suwpf__tabs" role="group" aria-label="<?php esc_attr_e( 'Filter projects', 'smartupworld-portfolio-showcase' ); ?>">
						<button type="button" class="suwpf__tab" data-filter="*" aria-pressed="true">
							<?php echo esc_html( $s['all_label'] ); ?>
							<?php if ( $show_counts ) : ?>
								<span class="suwpf__count"><?php echo (int) count( $items ); ?></span>
							<?php endif; ?>
						</button>
						<?php foreach ( $types as $type ) : ?>
							<button type="button" class="suwpf__tab" data-filter="<?php echo esc_attr( $type ); ?>" aria-pressed="false">
								<?php echo esc_html( $type ); ?>
								<?php if ( $show_counts ) : ?>
									<span class="suwpf__count"><?php echo (int) $type_counts[ $type ]; ?></span>
								<?php endif; ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<ul class="suwpf__grid">
					<?php
					foreach ( $items as $item ) :
						$url    = isset( $item['url'] ) ? (string) $item['url'] : '';
						$domain = self::domain( $url );
						$type   = isset( $item['type'] ) ? (string) $item['type'] : '';
						?>
						<li class="suwpf__card" data-type="<?php echo esc_attr( $type ); ?>">
							<?php if ( self::truthy( $s['show_browser'] ) ) : ?>
								<div class="suwpf__browser" aria-hidden="true">
									<i></i><i></i><i></i>
									<?php if ( $domain ) : ?>
										<span class="suwpf__domain"><?php echo esc_html( $domain ); ?></span>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<div class="suwpf__media">
								<?php self::image( $item ); ?>
							</div>

							<div class="suwpf__body">
								<div class="suwpf__text">
									<?php if ( $type ) : ?>
										<span class="suwpf__badge"><?php echo esc_html( $type ); ?></span>
									<?php endif; ?>
									<<?php echo tag_escape( $card_tag ); ?> class="suwpf__title">
										<?php if ( $url ) : ?>
											<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="nofollow noopener"><?php echo esc_html( $item['title'] ); ?><span class="suwpf-sr"> <?php esc_html_e( '(opens in a new tab)', 'smartupworld-portfolio-showcase' ); ?></span></a>
										<?php else : ?>
											<?php echo esc_html( $item['title'] ); ?>
										<?php endif; ?>
									</<?php echo tag_escape( $card_tag ); ?>>
								</div>
								<?php if ( $url ) : ?>
									<span class="suwpf__go" aria-hidden="true"><?php self::arrow(); ?></span>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>

				<?php if ( $s['cta_text'] && $s['cta_url'] ) : ?>
					<div class="suwpf__cta">
						<a href="<?php echo esc_url( $s['cta_url'] ); ?>"><?php echo esc_html( $s['cta_text'] ); ?> <?php self::arrow( 16 ); ?></a>
					</div>
				<?php endif; ?>

			</div>
			<?php
			if ( ! $is_sample ) {
				self::schema( $items, $s['heading'] );
			}
			?>
		</section>
		<?php
	}

	/**
	 * Host name of a URL without "www.".
	 *
	 * @param string $url URL.
	 * @return string
	 */
	public static function domain( $url ) {
		$host = (string) wp_parse_url( (string) $url, PHP_URL_HOST );
		return preg_replace( '#^www\.#', '', $host );
	}

	/**
	 * Prints the card screenshot: a Media Library image (with srcset) or an image URL.
	 * Media Library images get their loading/fetchpriority/decoding attributes from
	 * WordPress core, which eager-loads images likely to be in view and lazy-loads the rest.
	 *
	 * @param array $item Renderer item.
	 */
	private static function image( $item ) {
		$alt = '';
		if ( ! empty( $item['image_id'] ) ) {
			$alt = (string) get_post_meta( (int) $item['image_id'], '_wp_attachment_image_alt', true );
		}
		if ( '' === trim( $alt ) ) {
			/* translators: %s: project name */
			$alt = sprintf( __( '%s website screenshot', 'smartupworld-portfolio-showcase' ), $item['title'] );
		}

		if ( ! empty( $item['image_id'] ) && wp_attachment_is_image( (int) $item['image_id'] ) ) {
			echo wp_get_attachment_image(
				(int) $item['image_id'],
				'large',
				false,
				array(
					'alt'   => $alt,
					'sizes' => '(max-width: 600px) 100vw, (max-width: 960px) 50vw, 400px',
				)
			);
		} elseif ( ! empty( $item['image'] ) ) {
			printf(
				'<img src="%1$s"%2$s alt="%3$s" width="%4$d" height="%5$d" loading="lazy" decoding="async">',
				esc_url( $item['image'] ),
				! empty( $item['srcset'] ) ? ' srcset="' . esc_attr( $item['srcset'] ) . '" sizes="(max-width: 600px) 100vw, (max-width: 960px) 50vw, 400px"' : '',
				esc_attr( $alt ),
				! empty( $item['width'] ) ? (int) $item['width'] : 1200,
				! empty( $item['height'] ) ? (int) $item['height'] : 750
			);
		}
	}

	/**
	 * Schema.org ItemList (JSON-LD) describing the projects, so search engines
	 * understand the section. Not printed for the sample projects.
	 *
	 * @param array  $items   Renderer items.
	 * @param string $heading Section heading.
	 */
	private static function schema( $items, $heading ) {
		/**
		 * Filters whether the portfolio prints its JSON-LD structured data.
		 *
		 * @param bool  $enabled Default true.
		 * @param array $items   Projects being shown.
		 */
		if ( ! apply_filters( 'suwpf_schema_enabled', true, $items ) ) {
			return;
		}

		$list = array();
		foreach ( $items as $i => $item ) {
			$work = array(
				'@type' => 'CreativeWork',
				'name'  => wp_strip_all_tags( $item['title'] ),
			);
			if ( ! empty( $item['url'] ) ) {
				$work['url'] = esc_url_raw( $item['url'] );
			}
			$image = ! empty( $item['image_id'] ) ? wp_get_attachment_image_url( (int) $item['image_id'], 'large' ) : ( $item['image'] ?? '' );
			if ( $image ) {
				$work['image'] = esc_url_raw( $image );
			}
			if ( ! empty( $item['type'] ) ) {
				$work['genre'] = wp_strip_all_tags( $item['type'] );
			}
			$list[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'item'     => $work,
			);
		}

		$data = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'ItemList',
			'name'            => $heading ? wp_strip_all_tags( $heading ) : __( 'Portfolio', 'smartupworld-portfolio-showcase' ),
			'numberOfItems'   => count( $list ),
			'itemListElement' => $list,
		);

		wp_print_inline_script_tag(
			(string) wp_json_encode( $data, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
			array( 'type' => 'application/ld+json' )
		);
	}

	/**
	 * Loose boolean for shortcode / Elementor switcher values.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	private static function truthy( $value ) {
		return in_array( $value, array( true, 1, '1', 'yes', 'true', 'on' ), true );
	}

	/**
	 * Prints the arrow icon.
	 *
	 * @param int $size Pixel size.
	 */
	private static function arrow( $size = 18 ) {
		printf(
			'<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
			(int) $size
		);
	}
}
