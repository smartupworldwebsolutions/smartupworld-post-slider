<?php
/**
 * Comment section — modern form + threaded comment list.
 *
 * Swaps the theme's comments template for inc/comment-section-template.php.
 * Remove 'inc/comment-section.php' from the modules array to roll back.
 *
 * @package Smartupworld
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'comments_template', 'suw_comments_template', 20 );
function suw_comments_template( $template ) {
	$custom = __DIR__ . '/comment-section-template.php';
	return file_exists( $custom ) ? $custom : $template;
}

// Styles are inlined (no extra request) and only printed where comments render.
add_action( 'wp_enqueue_scripts', 'suw_comments_assets' );
function suw_comments_assets() {
	if ( ! is_singular() ) {
		return;
	}
	$post_id = get_queried_object_id();
	if ( post_password_required( $post_id ) || ( ! comments_open( $post_id ) && ! get_comments_number( $post_id ) ) ) {
		return;
	}

	wp_register_style( 'suw-comments', false, array(), null );
	wp_enqueue_style( 'suw-comments' );
	wp_add_inline_style( 'suw-comments', suw_comments_css() );

	if ( comments_open( $post_id ) && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

function suw_comment_item( $comment, $args, $depth ) {
	$tag     = ( 'div' === $args['style'] ) ? 'div' : 'li';
	$id      = (int) $comment->comment_ID;
	$classes = 'suwc-item' . ( empty( $args['has_children'] ) ? '' : ' parent' );

	if ( in_array( $comment->comment_type, array( 'pingback', 'trackback' ), true ) ) {
		printf(
			'<%1$s id="comment-%2$d" %3$s><div class="suwc-ping">%4$s %5$s</div>',
			$tag, // phpcs:ignore WordPress.Security.EscapeOutput -- fixed literal.
			$id,
			comment_class( $classes, $comment, null, false ), // phpcs:ignore WordPress.Security.EscapeOutput
			'trackback' === $comment->comment_type ? 'Trackback:' : 'Pingback:',
			get_comment_author_link( $comment ) // phpcs:ignore WordPress.Security.EscapeOutput
		);
		return;
	}

	$size   = $depth > 1 ? 36 : 44;
	$avatar = get_avatar(
		$comment,
		$size,
		'',
		'',
		array(
			'class'    => 'suwc-avatar',
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);
	if ( ! $avatar ) {
		$avatar = '<span class="suwc-avatar suwc-avatar--initial" aria-hidden="true">' . esc_html( mb_substr( get_comment_author( $comment ), 0, 1 ) ) . '</span>';
	}

	$post      = get_post( $comment->comment_post_ID );
	$is_author = $post && $comment->user_id && (int) $comment->user_id === (int) $post->post_author;
	?>
	<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput ?> id="comment-<?php echo $id; ?>" <?php comment_class( $classes, $comment ); ?>>
		<article id="div-comment-<?php echo $id; ?>" class="suwc-c">
			<div class="suwc-c__avatar"><?php echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<div class="suwc-c__body">
				<header class="suwc-c__meta">
					<span class="suwc-c__author"><?php echo get_comment_author_link( $comment ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<?php if ( $is_author ) : ?>
						<span class="suwc-badge">Author</span>
					<?php endif; ?>
					<a class="suwc-c__date" href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
						<time datetime="<?php echo esc_attr( get_comment_date( 'c', $comment ) ); ?>"><?php echo esc_html( get_comment_date( '', $comment ) ); ?></time>
					</a>
				</header>

				<?php if ( '0' === (string) $comment->comment_approved ) : ?>
					<p class="suwc-pending">Your comment is awaiting moderation.</p>
				<?php endif; ?>

				<div class="suwc-c__text"><?php comment_text( $comment ); ?></div>

				<div class="suwc-c__actions">
					<?php
					// WordPress rejects replies to unapproved comments, so don't offer the link.
					if ( '1' === (string) $comment->comment_approved ) {
						comment_reply_link(
							array_merge(
							$args,
								array(
									'add_below'  => 'div-comment',
									'depth'      => $depth,
									'max_depth'  => $args['max_depth'],
									'before'     => '',
									'after'      => '',
									'reply_text' => '<svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 17 4 12l5-5"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>Reply',
								)
							),
							$comment
						);
					}
					edit_comment_link( 'Edit', '', '' );
					?>
				</div>
			</div>
		</article>
	<?php
	// The walker closes the <li>.
}

function suw_comments_css() {
	return <<<'CSS'
.suwc{--suwc-accent:#2563eb;--suwc-accent-hover:#1d4ed8;--suwc-accent-soft:#eff6ff;--suwc-ring:rgba(37,99,235,.2);--suwc-text:#111827;--suwc-body:#374151;--suwc-muted:#6b7280;--suwc-border:#e5e7eb;--suwc-field:#d1d5db;--suwc-danger:#dc2626;margin:0 0 2.5rem;padding-top:2rem;border-top:1px solid var(--suwc-border);color:var(--suwc-text);line-height:1.6}
.suwc *,.suwc *::before,.suwc *::after{box-sizing:border-box}
.suwc .screen-reader-text{position:absolute!important;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);clip-path:inset(50%);white-space:nowrap;border:0;visibility:visible}
.suwc-title{display:flex;align-items:center;gap:.6rem;margin:0 0 1.25rem;font-size:1.5rem;font-weight:700;line-height:1.3;color:var(--suwc-text)}
.suwc-count{display:inline-flex;align-items:center;justify-content:center;min-width:2rem;height:2rem;padding:0 .55rem;border-radius:999px;background:var(--suwc-accent-soft);color:var(--suwc-accent);font-size:.9375rem;font-weight:700}
.suwc-list,.suwc-list .children{list-style:none;margin:0;padding:0}
.suwc-list .children{margin-left:1.4rem;padding-left:1.1rem;border-left:2px solid var(--suwc-border)}
.suwc .comment,.suwc .pingback,.suwc .trackback{margin:0;padding:0;background:none;border:0;border-radius:0;box-shadow:none}
.suwc-c{display:flex;gap:.875rem;padding:1.125rem 0;border-top:1px solid var(--suwc-border)}
.suwc-list>.suwc-item:first-child>.suwc-c{border-top:0;padding-top:0}
.suwc-c__avatar{flex:none}
.suwc-avatar{display:block;width:44px;height:44px;border-radius:50%;object-fit:cover;background:var(--suwc-accent-soft)}
.suwc .children .suwc-avatar{width:36px;height:36px}
.suwc-avatar--initial{display:flex;align-items:center;justify-content:center;color:var(--suwc-accent);font-size:1.05rem;font-weight:700;text-transform:uppercase}
.suwc-c__body{flex:1;min-width:0}
.suwc-c__meta{display:flex;flex-wrap:wrap;align-items:center;gap:.2rem .6rem;margin-bottom:.3rem;line-height:1.4}
.suwc-c__author{font-weight:700;color:var(--suwc-text)}
.suwc-c__author a{color:inherit}
.suwc-c__author a:hover{color:var(--suwc-accent)}
.suwc-badge{padding:.15rem .55rem;border-radius:999px;background:var(--suwc-accent-soft);color:var(--suwc-accent);font-size:.6875rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
.suwc-c__date{font-size:.8125rem;color:var(--suwc-muted)}
.suwc-c__date:hover{color:var(--suwc-accent)}
.suwc-pending{display:inline-block;margin:.15rem 0 .5rem;padding:.2rem .6rem;border-radius:6px;background:#fef3c7;color:#92400e;font-size:.8125rem}
.suwc-c__text{color:var(--suwc-body);overflow-wrap:anywhere}
.suwc-c__text p{margin:0 0 .75rem;font-size:1rem;line-height:1.65;color:inherit}
.suwc-c__text>:last-child{margin-bottom:0}
.suwc-c__text a{color:var(--suwc-accent);text-decoration:underline}
.suwc-c__actions{display:flex;flex-wrap:wrap;gap:1rem;margin-top:.5rem}
.suwc .comment-reply-link,.suwc .comment-edit-link{display:inline-flex;align-items:center;gap:.35rem;margin:0;padding:.25rem 0;background:none!important;border-radius:0;color:var(--suwc-accent)!important;font-size:.875rem;font-weight:600;letter-spacing:0;text-transform:none}
.suwc .comment-edit-link{color:var(--suwc-muted)!important}
.suwc .comment-reply-link:hover,.suwc .comment-edit-link:hover{color:var(--suwc-accent-hover)!important;text-decoration:underline}
.suwc-ping{padding:.75rem 0;border-top:1px solid var(--suwc-border);font-size:.875rem;color:var(--suwc-muted)}
.suwc .comments-pagination{margin:1.25rem 0 0}
.suwc .comments-pagination .nav-links{display:flex;flex-wrap:wrap;gap:.4rem}
.suwc .comments-pagination .page-numbers{display:inline-flex;align-items:center;justify-content:center;min-width:2.5rem;height:2.5rem;padding:0 .75rem;border:1px solid var(--suwc-border);border-radius:10px;color:var(--suwc-text);font-size:.9375rem;font-weight:600}
.suwc .comments-pagination .page-numbers.current,.suwc .comments-pagination a.page-numbers:hover{background:var(--suwc-accent);border-color:var(--suwc-accent);color:#fff}
.suwc .comments-pagination .page-numbers.dots{min-width:auto;border:0}
.suwc-closed{margin:1.5rem 0 0;padding:.875rem 1.125rem;border-radius:12px;background:#f8fafc;color:var(--suwc-muted);font-size:.9375rem}
.suwc-respond{margin-top:2rem;padding:1.75rem;background:#fff;border:1px solid var(--suwc-border);border-radius:var(--radius-card,16px);box-shadow:var(--shadow-soft,0 4px 24px rgba(21,101,192,.1))}
.suwc>.suwc-respond:first-child{margin-top:0}
.suwc-list .suwc-respond{margin:.25rem 0 1.25rem;padding:1.25rem;box-shadow:none}
.suwc-reply-title{display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;gap:.25rem 1rem;margin:0 0 .35rem;font-size:1.375rem;font-weight:700;line-height:1.3;color:var(--suwc-text)}
.suwc-reply-title small{font-size:.875rem;font-weight:600}
.suwc-reply-title small a{color:var(--suwc-danger)}
.suwc-reply-title small a:hover{text-decoration:underline}
.suwc-notes{margin:0 0 1.25rem;font-size:.875rem;line-height:1.5;color:var(--suwc-muted)}
.suwc-notes a{color:var(--suwc-accent);font-weight:600}
.suwc-req{color:var(--suwc-danger);font-weight:700}
.suwc-opt{font-weight:400;color:var(--suwc-muted)}
.suwc-form{container-type:inline-size;margin:0}
.suwc-field{margin:0 0 1rem}
.suwc .suwc-field label{display:block;margin:0 0 .4rem;font-size:.875rem;font-weight:600;line-height:1.4;letter-spacing:normal;color:var(--suwc-text)}
.suwc .suwc-input{display:block;width:100%;min-height:48px;margin:0;padding:.7rem .95rem;font-family:inherit;font-size:1rem;line-height:1.5;color:var(--suwc-text);background:#fff;border:1px solid var(--suwc-field);border-radius:10px;box-shadow:none;outline:2px solid transparent;outline-offset:2px;transition:border-color .15s ease,box-shadow .15s ease;-webkit-appearance:none;appearance:none}
.suwc .suwc-input::placeholder{color:#9ca3af;opacity:1}
.suwc .suwc-input:hover{border-color:#9ca3af}
.suwc .suwc-input:focus{border-color:var(--suwc-accent);box-shadow:0 0 0 4px var(--suwc-ring)}
.suwc .suwc-input:user-invalid{border-color:var(--suwc-danger)}
.suwc .suwc-input:user-invalid:focus{box-shadow:0 0 0 4px rgba(220,38,38,.15)}
.suwc textarea.suwc-input{min-height:9.5rem;max-height:30rem;resize:vertical;field-sizing:content}
.suwc-grid{display:grid;grid-template-columns:minmax(0,1fr);column-gap:1rem}
@container (min-width:480px){.suwc-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.suwc-field--url{grid-column:1/-1}}
@container (min-width:720px){.suwc-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.suwc-field--url{grid-column:auto}}
.suwc-consent{grid-column:1/-1;display:flex;align-items:flex-start;gap:.6rem;margin:0 0 1rem;font-size:.875rem;line-height:1.5;color:var(--suwc-muted)}
.suwc .suwc-consent input{flex:none;width:1.1rem;height:1.1rem;margin:.15rem 0 0;padding:0;accent-color:var(--suwc-accent)}
.suwc .suwc-consent label{display:inline;margin:0;font-size:inherit;font-weight:400;letter-spacing:normal;color:inherit}
.suwc-submit{display:flex;align-items:center;margin:.25rem 0 0}
.suwc .suwc-btn{display:inline-flex;align-items:center;justify-content:center;gap:.55rem;min-height:48px;margin:0;padding:.75rem 1.6rem;font-family:inherit;font-size:1rem;font-weight:700;line-height:1;color:#fff;background:var(--suwc-accent);border:0;border-radius:10px;box-shadow:0 6px 16px rgba(37,99,235,.25);cursor:pointer;transition:background-color .15s ease,box-shadow .15s ease,transform .15s ease}
.suwc .suwc-btn:hover{background:var(--suwc-accent-hover);box-shadow:0 8px 20px rgba(37,99,235,.32);transform:translateY(-1px)}
.suwc .suwc-btn:active{transform:none}
.suwc .suwc-btn:focus-visible{outline:2px solid var(--suwc-accent);outline-offset:3px}
.suwc .suwc-btn svg{flex:none}
@container (max-width:479px){.suwc .suwc-btn{width:100%}}
@media (max-width:575px){.suwc-respond{padding:1.25rem}.suwc-list .children{margin-left:.5rem;padding-left:.85rem}.suwc-c{gap:.7rem}}
@media (prefers-reduced-motion:reduce){.suwc *{transition:none!important}.suwc .suwc-btn:hover{transform:none}}
CSS;
}
