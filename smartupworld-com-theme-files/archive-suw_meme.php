<?php
/**
 * SmartUpWorld theme — Meme archive / category template.
 * Place this file in the theme root to override the plugin's fallback template.
 *
 * @package Smartupworld
 */

get_header();

$current_term = is_tax( 'meme_category' ) ? get_queried_object() : null;
$categories   = get_terms(
	array(
		'taxonomy'   => 'meme_category',
		'hide_empty' => true,
	)
);
?>

<main id="main" class="site-main suwm-archive">
	<div class="suwm-wrap">

		<!-- Header -->
		<div class="suwm-head">
			<?php if ( $current_term ) : ?>
				<span class="suwm-eyebrow"><?php esc_html_e( 'Category', 'smartupworld-memes' ); ?></span>
				<h1 class="suwm-heading"><?php echo esc_html( $current_term->name ); ?></h1>
				<?php if ( $current_term->description ) : ?>
					<p class="suwm-sub"><?php echo esc_html( $current_term->description ); ?></p>
				<?php endif; ?>
			<?php else : ?>
				<span class="suwm-eyebrow">SmartUpWorld</span>
				<h1 class="suwm-heading">Memes</h1>
				<p class="suwm-sub">Relatable web dev &amp; WordPress humour — one meme at a time.</p>
			<?php endif; ?>
		</div>

		<!-- Category filter tabs -->
		<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
			<nav class="suwm-cats" aria-label="Meme categories">
				<a class="suwm-cat-link<?php echo ! $current_term ? ' is-active' : ''; ?>"
				   href="<?php echo esc_url( get_post_type_archive_link( 'suw_meme' ) ); ?>">All</a>
				<?php foreach ( $categories as $cat ) : ?>
					<a class="suwm-cat-link<?php echo ( $current_term && (int) $current_term->term_id === (int) $cat->term_id ) ? ' is-active' : ''; ?>"
					   href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
						<?php echo esc_html( $cat->name ); ?>
						<span class="suwm-cat-count"><?php echo (int) $cat->count; ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<!-- Meme grid -->
		<?php if ( have_posts() ) : ?>
			<ul class="suwm-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					$cats = get_the_terms( get_the_ID(), 'meme_category' );
					?>
					<li class="suwm-card">
						<a class="suwm-card-link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="suwm-card-img">
									<?php the_post_thumbnail( 'large', array( 'alt' => esc_attr( get_the_title() ), 'sizes' => '(max-width:600px) 100vw,(max-width:960px) 50vw,380px' ) ); ?>
								</div>
							<?php endif; ?>
							<div class="suwm-card-body">
								<?php if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) : ?>
									<span class="suwm-badge"><?php echo esc_html( $cats[0]->name ); ?></span>
								<?php endif; ?>
								<h2 class="suwm-card-title"><?php the_title(); ?></h2>
							</div>
						</a>
					</li>
				<?php endwhile; ?>
			</ul>

			<div class="suwm-pagination">
				<?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => '&larr; Previous', 'next_text' => 'Next &rarr;' ) ); ?>
			</div>

		<?php else : ?>
			<p class="suwm-empty">No memes yet — check back soon!</p>
		<?php endif; ?>

	</div>
</main>

<?php get_footer();
