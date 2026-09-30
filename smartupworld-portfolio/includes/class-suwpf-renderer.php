<?php
/**
 * Shared markup for the shortcode and the Elementor widget.
 */

defined( 'ABSPATH' ) || exit;

class SUWPF_Renderer {

	/**
	 * @param array $items    Each: title, image, image_id (optional), type, url.
	 * @param array $settings eyebrow, heading, subheading, show_filter, show_counts,
	 *                        show_browser, all_label, cta_text, cta_url, heading_tag,
	 *                        full_width_bg.
	 * @return string
	 */
	public static function render( array $items, array $settings = array() ) {
		$s = wp_parse_args( $settings, array(
			'eyebrow'       => '',
			'heading'       => '',
			'subheading'    => '',
			'show_filter'   => true,
			'show_counts'   => true,
			'show_browser'  => true,
			'all_label'     => __( 'All', 'smartupworld-portfolio' ),
			'cta_text'      => '',
			'cta_url'       => '',
			'heading_tag'   => 'h2',
			'full_width_bg' => true,
		) );

		$items = array_values( array_filter( $items, function ( $item ) {
			return ! empty( $item['title'] );
		} ) );
		if ( ! $items ) {
			return current_user_can( 'edit_posts' )
				? '<p class="suwpf-empty">' . esc_html__( 'No portfolio projects yet. Add some under Portfolio → Add New Project.', 'smartupworld-portfolio' ) . '</p>'
				: '';
		}

		wp_enqueue_style( 'suwpf' );
		wp_enqueue_script( 'suwpf' );

		$tag         = in_array( $s['heading_tag'], array( 'h1', 'h2', 'h3', 'h4', 'div' ), true ) ? $s['heading_tag'] : 'h2';
		$uid         = wp_unique_id( 'suwpf-' );
		$types       = array_values( array_unique( array_filter( array_column( $items, 'type' ) ) ) );
		$type_counts = array_count_values( array_filter( array_column( $items, 'type' ) ) );
		$show_filter = self::truthy( $s['show_filter'] ) && count( $types ) > 1;
		$show_counts = self::truthy( $s['show_counts'] );
		$card_title  = $s['heading'] ? 'h3' : 'h2';

		ob_start();
		?>
		<section class="suwpf<?php echo self::truthy( $s['full_width_bg'] ) ? '' : ' suwpf--boxed'; ?>" id="<?php echo esc_attr( $uid ); ?>"<?php echo $s['heading'] ? ' aria-labelledby="' . esc_attr( $uid ) . '-h"' : ''; ?>>
			<div class="suwpf__container">

				<?php if ( $s['eyebrow'] || $s['heading'] || $s['subheading'] ) : ?>
					<div class="suwpf__head">
						<?php if ( $s['eyebrow'] ) : ?>
							<span class="suwpf__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></span>
						<?php endif; ?>
						<?php if ( $s['heading'] ) : ?>
							<<?php echo $tag; ?> class="suwpf__heading" id="<?php echo esc_attr( $uid ); ?>-h"><?php echo esc_html( $s['heading'] ); ?></<?php echo $tag; ?>>
							<span class="suwpf__underline" aria-hidden="true"></span>
						<?php endif; ?>
						<?php if ( $s['subheading'] ) : ?>
							<p class="suwpf__sub"><?php echo esc_html( $s['subheading'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $show_filter ) : ?>
					<div class="suwpf__tabs" role="group" aria-label="<?php esc_attr_e( 'Filter projects', 'smartupworld-portfolio' ); ?>">
						<button type="button" class="suwpf__tab" data-filter="*" aria-pressed="true">
							<?php echo esc_html( $s['all_label'] ); ?>
							<?php if ( $show_counts ) : ?><span class="suwpf__count"><?php echo (int) count( $items ); ?></span><?php endif; ?>
						</button>
						<?php foreach ( $types as $type ) : ?>
							<button type="button" class="suwpf__tab" data-filter="<?php echo esc_attr( $type ); ?>" aria-pressed="false">
								<?php echo esc_html( $type ); ?>
								<?php if ( $show_counts ) : ?><span class="suwpf__count"><?php echo (int) $type_counts[ $type ]; ?></span><?php endif; ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<ul class="suwpf__grid">
					<?php foreach ( $items as $item ) :
						$url    = isset( $item['url'] ) ? (string) $item['url'] : '';
						$domain = self::domain( $url );
						$type   = isset( $item['type'] ) ? (string) $item['type'] : '';
						$alt    = sprintf( /* translators: %s: project name */ __( '%s website screenshot', 'smartupworld-portfolio' ), $item['title'] );
					?>
						<li class="suwpf__card" data-type="<?php echo esc_attr( $type ); ?>">
							<?php if ( self::truthy( $s['show_browser'] ) ) : ?>
								<div class="suwpf__browser" aria-hidden="true">
									<i></i><i></i><i></i>
									<?php if ( $domain ) : ?><span class="suwpf__domain"><?php echo esc_html( $domain ); ?></span><?php endif; ?>
								</div>
							<?php endif; ?>

							<div class="suwpf__media">
								<?php
								if ( ! empty( $item['image_id'] ) ) {
									echo wp_get_attachment_image( (int) $item['image_id'], 'large', false, array(
										'alt'      => $alt,
										'loading'  => 'lazy',
										'decoding' => 'async',
										'sizes'    => '(max-width: 600px) 100vw, (max-width: 960px) 50vw, 400px',
									) );
								} elseif ( ! empty( $item['image'] ) ) {
									printf(
										'<img src="%1$s" alt="%2$s" width="800" height="500" loading="lazy" decoding="async">',
										esc_url( $item['image'] ),
										esc_attr( $alt )
									);
								}
								?>
							</div>

							<div class="suwpf__body">
								<div class="suwpf__text">
									<?php if ( $type ) : ?><span class="suwpf__badge"><?php echo esc_html( $type ); ?></span><?php endif; ?>
									<<?php echo $card_title; ?> class="suwpf__title">
										<?php if ( $url ) : ?>
											<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="nofollow noopener"><?php echo esc_html( $item['title'] ); ?><span class="suwpf-sr"> <?php esc_html_e( '(opens in a new tab)', 'smartupworld-portfolio' ); ?></span></a>
										<?php else : ?>
											<?php echo esc_html( $item['title'] ); ?>
										<?php endif; ?>
									</<?php echo $card_title; ?>>
								</div>
								<?php if ( $url ) : ?>
									<span class="suwpf__go" aria-hidden="true"><?php echo self::arrow(); ?></span>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>

				<?php if ( $s['cta_text'] && $s['cta_url'] ) : ?>
					<div class="suwpf__cta">
						<a href="<?php echo esc_url( $s['cta_url'] ); ?>"><?php echo esc_html( $s['cta_text'] ); ?> <?php echo self::arrow( 16 ); ?></a>
					</div>
				<?php endif; ?>

			</div>
		</section>
		<?php
		return ob_get_clean();
	}

	public static function domain( $url ) {
		$host = (string) wp_parse_url( (string) $url, PHP_URL_HOST );
		return preg_replace( '#^www\.#', '', $host );
	}

	private static function truthy( $value ) {
		return in_array( $value, array( true, 1, '1', 'yes', 'true', 'on' ), true );
	}

	private static function arrow( $size = 18 ) {
		return '<svg width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';
	}
}
