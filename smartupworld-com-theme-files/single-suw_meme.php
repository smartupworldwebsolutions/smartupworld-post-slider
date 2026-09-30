<?php
/**
 * SmartUpWorld theme — single meme template.
 * Place this file in the theme root to override the plugin's fallback template.
 *
 * @package Smartupworld
 */

get_header();

$cats = get_the_terms( get_the_ID(), 'meme_category' );
?>

<main id="main" class="site-main suwm-single">
	<div class="suwm-wrap">

		<?php if ( have_posts() ) : the_post(); ?>

		<!-- Breadcrumb -->
		<nav class="suwm-breadcrumb" aria-label="Breadcrumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
			<span aria-hidden="true"> / </span>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'suw_meme' ) ); ?>">Memes</a>
			<?php if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) : ?>
				<span aria-hidden="true"> / </span>
				<a href="<?php echo esc_url( get_term_link( $cats[0] ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a>
			<?php endif; ?>
			<span aria-hidden="true"> / </span>
			<span><?php the_title(); ?></span>
		</nav>

		<article class="suwm-article" itemscope itemtype="https://schema.org/ImageObject">

			<header class="suwm-article-header">
				<?php if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) : ?>
					<div class="suwm-article-cats">
						<?php foreach ( $cats as $cat ) : ?>
							<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="suwm-badge"><?php echo esc_html( $cat->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<h1 class="suwm-article-title" itemprop="name"><?php the_title(); ?></h1>
				<p class="suwm-article-meta">By <?php echo esc_html( get_the_author() ); ?> &middot; <?php echo esc_html( get_the_date() ); ?></p>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="suwm-article-img" itemprop="contentUrl">
					<?php the_post_thumbnail( 'full', array( 'alt' => esc_attr( get_the_title() ), 'sizes' => '(max-width:760px) 100vw,760px' ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<div class="suwm-article-content">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

		</article>

		<!-- Prev / Next -->
		<nav class="suwm-post-nav" aria-label="Meme navigation">
			<?php
			$prev = get_previous_post( false, '', 'meme_category' );
			$next = get_next_post( false, '', 'meme_category' );
			if ( $prev ) :
				?>
				<a class="suwm-post-nav-link suwm-post-nav-prev" href="<?php echo esc_url( get_permalink( $prev ) ); ?>">
					<span class="suwm-post-nav-label">&larr; Previous meme</span>
					<span class="suwm-post-nav-title"><?php echo esc_html( get_the_title( $prev ) ); ?></span>
				</a>
			<?php endif; ?>
			<?php if ( $next ) : ?>
				<a class="suwm-post-nav-link suwm-post-nav-next" href="<?php echo esc_url( get_permalink( $next ) ); ?>">
					<span class="suwm-post-nav-label">Next meme &rarr;</span>
					<span class="suwm-post-nav-title"><?php echo esc_html( get_the_title( $next ) ); ?></span>
				</a>
			<?php endif; ?>
		</nav>

		<?php endif; ?>

	</div>
</main>

<?php
wp_print_inline_script_tag(
	(string) wp_json_encode(
		array(
			'@context'      => 'https://schema.org',
			'@type'         => 'ImageObject',
			'name'          => wp_strip_all_tags( get_the_title() ),
			'description'   => wp_strip_all_tags( get_the_excerpt() ),
			'contentUrl'    => has_post_thumbnail() ? esc_url_raw( get_the_post_thumbnail_url( get_the_ID(), 'full' ) ) : '',
			'url'           => esc_url_raw( get_permalink() ),
			'datePublished' => esc_attr( get_the_date( 'c' ) ),
			'dateModified'  => esc_attr( get_the_modified_date( 'c' ) ),
			'author'        => array(
				'@type' => 'Organization',
				'name'  => 'SmartUpWorld Websolutions',
				'url'   => 'https://smartupworld.com/',
			),
		),
		JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	),
	array( 'type' => 'application/ld+json' )
);

get_footer();
