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

// ── Template swap ─────────────────────────────────────────────────────────────
add_filter( 'comments_template', 'suw_comments_template', 20 );
function suw_comments_template( $template ) {
	$custom = __DIR__ . '/comment-section-template.php';
	return file_exists( $custom ) ? $custom : $template;
}

// ── Assets ────────────────────────────────────────────────────────────────────
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

	// Always enqueue comment-reply so our inline script has a handle to attach to.
	wp_enqueue_script( 'comment-reply' );

	$per_page    = max( 1, (int) get_option( 'comments_per_page', 50 ) );
	$total       = (int) get_comments_number( $post_id );
	$total_pages = (int) ceil( $total / $per_page );

	wp_add_inline_script(
		'comment-reply',
		'var suwcData=' . wp_json_encode( array(
			'ajaxurl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'suw-comment-nonce' ),
			'postId'     => $post_id,
			'maxLen'     => 2000,
			'totalPages' => $total_pages,
			'i18n'       => array(
				'submitting'  => "Posting…",
				'success'     => 'Comment posted!',
				'pending'     => 'Your comment is awaiting moderation. Thank you!',
				'error'       => 'Something went wrong. Please try again.',
				'over'        => 'Your comment is too long (max 2 000 characters).',
				'loadMore'    => 'Load more comments',
				'loading'     => "Loading…",
				'noMore'      => 'All comments loaded.',
				'likeTitle'   => 'Like this comment',
				'unlikeTitle' => 'Unlike this comment',
			),
		) ) . ';',
		'before'
	);
	wp_add_inline_script( 'comment-reply', suw_comments_js() );
}

// ── Honeypot — blocks bots on both normal and AJAX paths ──────────────────────
add_filter( 'preprocess_comment', 'suw_honeypot_check', 1 );
function suw_honeypot_check( $commentdata ) {
	if ( ! is_admin() && isset( $_POST['suw_hp'] ) && '' !== $_POST['suw_hp'] ) {
		wp_die( 'Comment rejected.', 'Comment Rejected', array( 'response' => 403, 'back_link' => true ) );
	}
	return $commentdata;
}

// ── Add ugc to commenter URL rel (safety net — WP 5.3+ already does this) ────
add_filter( 'get_comment_author_link', 'suw_comment_author_ugc' );
function suw_comment_author_ugc( $return ) {
	if ( false !== strpos( $return, 'ugc' ) ) {
		return $return;
	}
	return str_replace( 'rel="nofollow"', 'rel="nofollow ugc"', $return );
}

// ── AJAX: submit comment ──────────────────────────────────────────────────────
add_action( 'wp_ajax_suw_post_comment',        'suw_ajax_post_comment' );
add_action( 'wp_ajax_nopriv_suw_post_comment', 'suw_ajax_post_comment' );
function suw_ajax_post_comment() {
	if ( ! check_ajax_referer( 'suw-comment-nonce', 'suw_nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed. Refresh the page and try again.' ) );
	}

	// Honeypot (belt-and-suspenders alongside the preprocess_comment filter).
	if ( ! empty( $_POST['suw_hp'] ) ) {
		wp_send_json_error( array( 'message' => 'Comment rejected.' ) );
	}

	// WordPress 4.4+: handles validation, spam checks, insertion, cookie.
	$comment = wp_handle_comment_submission( wp_unslash( $_POST ) );

	if ( is_wp_error( $comment ) ) {
		$map = array(
			'require_valid_comment'         => 'Please write something in your comment.',
			'require_name_email'            => 'Please enter your name and email address.',
			'invalid_author'                => 'Please enter a valid name.',
			'invalid_email'                 => 'Please enter a valid email address.',
			'invalid_url'                   => 'Please enter a valid website URL.',
			'comment_duplicate'             => 'It looks like you already submitted that comment.',
			'comment_flood'                 => "You're commenting too quickly — please wait a moment.",
			'comment_on_trash'              => 'This post is no longer available.',
			'comment_on_draft'              => 'This post is not published.',
			'comment_on_password_protected' => 'This post is password protected.',
			'comment_not_whitelisted'       => 'Your comment has been held for moderation.',
		);
		$code = $comment->get_error_code();
		$msg  = isset( $map[ $code ] ) ? $map[ $code ] : wp_strip_all_tags( $comment->get_error_message() );
		wp_send_json_error( array( 'message' => $msg ) );
	}

	$approved = '1' === (string) $comment->comment_approved;
	$html     = '';

	if ( $approved ) {
		ob_start();
		$args = array(
			'style'        => 'ol',
			'max_depth'    => (int) get_option( 'thread_comments_depth', 5 ),
			'has_children' => false,
			'format'       => 'html5',
			'avatar_size'  => 44,
		);
		suw_comment_item( $comment, $args, 1 );
		echo '</li>';
		$html = ob_get_clean();
	}

	wp_send_json_success( array(
		'html'    => $html,
		'pending' => ! $approved,
		'count'   => number_format_i18n( (int) get_comments_number( $comment->comment_post_ID ) ),
		'message' => $approved
			? 'Comment posted!'
			: 'Your comment is awaiting moderation. Thank you!',
	) );
}

// ── AJAX: load more comments ──────────────────────────────────────────────────
add_action( 'wp_ajax_suw_load_more_comments',        'suw_ajax_load_more_comments' );
add_action( 'wp_ajax_nopriv_suw_load_more_comments', 'suw_ajax_load_more_comments' );
function suw_ajax_load_more_comments() {
	check_ajax_referer( 'suw-comment-nonce', 'nonce' );

	$post_id  = absint( isset( $_GET['post_id'] ) ? $_GET['post_id'] : 0 );
	$page     = max( 1, absint( isset( $_GET['page'] ) ? $_GET['page'] : 1 ) );
	$per_page = max( 1, (int) get_option( 'comments_per_page', 50 ) );

	if ( ! $post_id || ! get_post( $post_id ) ) {
		wp_send_json_error();
	}

	$top_level = get_comments( array(
		'post_id' => $post_id,
		'status'  => 'approve',
		'type'    => 'comment',
		'parent'  => 0,
		'number'  => $per_page,
		'offset'  => ( $page - 1 ) * $per_page,
		'orderby' => 'comment_date_gmt',
		'order'   => 'ASC',
	) );

	if ( empty( $top_level ) ) {
		wp_send_json_success( array( 'html' => '', 'has_more' => false ) );
	}

	$total_top = (int) get_comments( array(
		'post_id' => $post_id,
		'status'  => 'approve',
		'type'    => 'comment',
		'parent'  => 0,
		'count'   => true,
	) );

	$base_args = array(
		'style'       => 'ol',
		'max_depth'   => (int) get_option( 'thread_comments_depth', 5 ),
		'format'      => 'html5',
		'avatar_size' => 44,
	);

	ob_start();
	foreach ( $top_level as $cmt ) {
		suw_render_comment_tree( $cmt, $post_id, $base_args, 1 );
	}
	$html = ob_get_clean();

	wp_send_json_success( array(
		'html'     => $html,
		'has_more' => ( $page * $per_page ) < $total_top,
	) );
}

function suw_render_comment_tree( $comment, $post_id, $base_args, $depth ) {
	$max      = isset( $base_args['max_depth'] ) ? (int) $base_args['max_depth'] : 5;
	$children = ( $depth < $max ) ? get_comments( array(
		'post_id' => $post_id,
		'status'  => 'approve',
		'parent'  => $comment->comment_ID,
		'orderby' => 'comment_date_gmt',
		'order'   => 'ASC',
	) ) : array();

	$args = array_merge( $base_args, array( 'has_children' => ! empty( $children ) ) );
	suw_comment_item( $comment, $args, $depth );

	if ( ! empty( $children ) ) {
		echo '<ul class="children">';
		foreach ( $children as $child ) {
			suw_render_comment_tree( $child, $post_id, $base_args, $depth + 1 );
		}
		echo '</ul>';
	}
	echo '</li>';
}

// ── AJAX: like / unlike ───────────────────────────────────────────────────────
add_action( 'wp_ajax_suw_like_comment',        'suw_ajax_like_comment' );
add_action( 'wp_ajax_nopriv_suw_like_comment', 'suw_ajax_like_comment' );
function suw_ajax_like_comment() {
	check_ajax_referer( 'suw-comment-nonce', 'nonce' );

	$comment_id = absint( isset( $_POST['comment_id'] ) ? $_POST['comment_id'] : 0 );
	$unlike     = isset( $_POST['unlike'] ) && '1' === $_POST['unlike'];

	$comment = get_comment( $comment_id );
	if ( ! $comment || '1' !== (string) $comment->comment_approved ) {
		wp_send_json_error();
	}

	$current = max( 0, (int) get_comment_meta( $comment_id, 'suw_likes', true ) );
	$new     = $unlike ? max( 0, $current - 1 ) : $current + 1;
	update_comment_meta( $comment_id, 'suw_likes', $new );

	wp_send_json_success( array( 'count' => $new ) );
}

// ── Comment item callback ─────────────────────────────────────────────────────
function suw_comment_item( $comment, $args, $depth ) {
	$tag     = ( 'div' === $args['style'] ) ? 'div' : 'li';
	$id      = (int) $comment->comment_ID;
	$classes = 'suwc-item' . ( empty( $args['has_children'] ) ? '' : ' parent' );

	if ( in_array( $comment->comment_type, array( 'pingback', 'trackback' ), true ) ) {
		printf(
			'<%1$s id="comment-%2$d" %3$s><div class="suwc-ping">%4$s %5$s</div>',
			$tag, // phpcs:ignore WordPress.Security.EscapeOutput
			$id,
			comment_class( $classes, $comment, null, false ), // phpcs:ignore
			'trackback' === $comment->comment_type ? 'Trackback:' : 'Pingback:',
			get_comment_author_link( $comment ) // phpcs:ignore
		);
		return;
	}

	$size   = $depth > 1 ? 36 : 44;
	$avatar = get_avatar(
		$comment, $size, '', '',
		array( 'class' => 'suwc-avatar', 'loading' => 'lazy', 'decoding' => 'async' )
	);
	if ( ! $avatar ) {
		$avatar = '<span class="suwc-avatar suwc-avatar--initial" aria-hidden="true">'
			. esc_html( mb_substr( get_comment_author( $comment ), 0, 1 ) )
			. '</span>';
	}

	$post      = get_post( $comment->comment_post_ID );
	$is_author = $post && $comment->user_id && (int) $comment->user_id === (int) $post->post_author;
	$likes     = max( 0, (int) get_comment_meta( $id, 'suw_likes', true ) );
	?>
	<<?php echo $tag; // phpcs:ignore ?> id="comment-<?php echo $id; ?>" <?php comment_class( $classes, $comment ); ?>>
		<article id="div-comment-<?php echo $id; ?>" class="suwc-c">
			<div class="suwc-c__avatar"><?php echo $avatar; // phpcs:ignore ?></div>
			<div class="suwc-c__body">
				<header class="suwc-c__meta">
					<span class="suwc-c__author"><?php echo get_comment_author_link( $comment ); // phpcs:ignore ?></span>
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
					<button
						type="button"
						class="suwc-like-btn"
						data-id="<?php echo $id; ?>"
						title="<?php echo $likes > 0
							? esc_attr( $likes . ' like' . ( 1 === $likes ? '' : 's' ) )
							: 'Like this comment'; ?>"
					>
						<svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
						<span class="suwc-like-count"><?php echo $likes > 0 ? esc_html( (string) $likes ) : ''; ?></span>
					</button>
					<?php
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
	// The walker / suw_render_comment_tree closes the tag.
}

// ── CSS ───────────────────────────────────────────────────────────────────────
function suw_comments_css() {
	return '.suwc{--suwc-accent:#2563eb;--suwc-accent-hover:#1d4ed8;--suwc-accent-soft:#eff6ff;--suwc-ring:rgba(37,99,235,.2);--suwc-text:#111827;--suwc-body:#374151;--suwc-muted:#6b7280;--suwc-border:#e5e7eb;--suwc-field:#d1d5db;--suwc-danger:#dc2626;margin:0 0 2.5rem;padding-top:2rem;border-top:1px solid var(--suwc-border);color:var(--suwc-text);line-height:1.6}
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
.suwc-c__text p{margin:0 0 .75rem;font-size:1rem;line-height:1.65;color:inherit;white-space:pre-line}
.suwc-c__text>:last-child{margin-bottom:0}
.suwc-c__text a{color:var(--suwc-accent);text-decoration:underline}
.suwc-c__actions{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem 1rem;margin-top:.5rem}
.suwc .comment-reply-link,.suwc .comment-edit-link{display:inline-flex;align-items:center;gap:.35rem;margin:0;padding:.25rem 0;background:none!important;border-radius:0;color:var(--suwc-accent)!important;font-size:.875rem;font-weight:600;letter-spacing:0;text-transform:none}
.suwc .comment-edit-link{color:var(--suwc-muted)!important}
.suwc .comment-reply-link:hover,.suwc .comment-edit-link:hover{color:var(--suwc-accent-hover)!important;text-decoration:underline}
.suwc-like-btn{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem 0;background:none;border:0;color:var(--suwc-muted);font-size:.875rem;font-weight:600;cursor:pointer;transition:color .15s}
.suwc-like-btn:hover,.suwc-like-btn.is-liked{color:var(--suwc-danger)}
.suwc-like-btn.is-liked svg{fill:var(--suwc-danger);stroke:var(--suwc-danger)}
.suwc-like-btn:disabled{opacity:.5;cursor:default}
.suwc-like-count{min-width:.75ch}
.suwc-ping{padding:.75rem 0;border-top:1px solid var(--suwc-border);font-size:.875rem;color:var(--suwc-muted)}
.suwc .comments-pagination{margin:1.25rem 0 0}
.suwc .comments-pagination .nav-links{display:flex;flex-wrap:wrap;gap:.4rem}
.suwc .comments-pagination .page-numbers{display:inline-flex;align-items:center;justify-content:center;min-width:2.5rem;height:2.5rem;padding:0 .75rem;border:1px solid var(--suwc-border);border-radius:10px;color:var(--suwc-text);font-size:.9375rem;font-weight:600}
.suwc .comments-pagination .page-numbers.current,.suwc .comments-pagination a.page-numbers:hover{background:var(--suwc-accent);border-color:var(--suwc-accent);color:#fff}
.suwc .comments-pagination .page-numbers.dots{min-width:auto;border:0}
.suwc-load-more{display:block;width:100%;margin:1.25rem 0 0;padding:.75rem 1.5rem;background:var(--suwc-accent-soft);border:1px solid var(--suwc-border);border-radius:10px;color:var(--suwc-accent);font-family:inherit;font-size:.9375rem;font-weight:700;cursor:pointer;transition:background .15s,border-color .15s}
.suwc-load-more:hover{background:#dbeafe;border-color:#93c5fd}
.suwc-load-more:disabled{opacity:.6;cursor:default}
.suwc-item--new{animation:suwcFadeIn .4s ease}
@keyframes suwcFadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
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
.suwc-hp{position:absolute;left:-9999px;width:0;height:0;overflow:hidden;visibility:hidden}
.suwc-msg{margin:.75rem 0 0;padding:.65rem .9rem;border-radius:8px;font-size:.9rem;font-weight:600}
.suwc-msg--ok{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}
.suwc-msg--error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.suwc-form{container-type:inline-size;margin:0}
.suwc-field{margin:0 0 1rem;position:relative}
.suwc .suwc-field label{display:block;margin:0 0 .4rem;font-size:.875rem;font-weight:600;line-height:1.4;letter-spacing:normal;color:var(--suwc-text)}
.suwc .suwc-input{display:block;width:100%;min-height:48px;margin:0;padding:.7rem .95rem;font-family:inherit;font-size:1rem;line-height:1.5;color:var(--suwc-text);background:#fff;border:1px solid var(--suwc-field);border-radius:10px;box-shadow:none;outline:2px solid transparent;outline-offset:2px;transition:border-color .15s ease,box-shadow .15s ease;-webkit-appearance:none;appearance:none}
.suwc .suwc-input::placeholder{color:#9ca3af;opacity:1}
.suwc .suwc-input:hover{border-color:#9ca3af}
.suwc .suwc-input:focus{border-color:var(--suwc-accent);box-shadow:0 0 0 4px var(--suwc-ring)}
.suwc .suwc-input:user-invalid{border-color:var(--suwc-danger)}
.suwc .suwc-input:user-invalid:focus{box-shadow:0 0 0 4px rgba(220,38,38,.15)}
.suwc textarea.suwc-input{min-height:9.5rem;max-height:30rem;resize:vertical;field-sizing:content}
.suwc-char-count{display:block;margin:.35rem 0 0;font-size:.8125rem;color:var(--suwc-muted);text-align:right;transition:color .15s}
.suwc-char-count--warn{color:#d97706}
.suwc-char-count--over{color:var(--suwc-danger);font-weight:700}
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
.suwc .suwc-btn:disabled{opacity:.65;cursor:default;transform:none;box-shadow:none}
.suwc .suwc-btn svg{flex:none}
@container (max-width:479px){.suwc .suwc-btn{width:100%}}
@media (max-width:575px){.suwc-respond{padding:1.25rem}.suwc-list .children{margin-left:.5rem;padding-left:.85rem}.suwc-c{gap:.7rem}}
@media (prefers-reduced-motion:reduce){.suwc *{transition:none!important;animation:none!important}.suwc .suwc-btn:hover{transform:none}}';
}

// ── JavaScript ────────────────────────────────────────────────────────────────
function suw_comments_js() {
	return '(function(){
"use strict";
if(!window.suwcData)return;
var d=suwcData;
function qs(s,c){return(c||document).querySelector(s);}
function qsa(s,c){return(c||document).querySelectorAll(s);}

function showMsg(form,text,isError){
  var el=qs(".suwc-msg",form);
  if(!el){
    el=document.createElement("p");
    el.className="suwc-msg";
    var sub=qs(".suwc-submit",form);
    if(sub)sub.insertAdjacentElement("afterend",el);
    else form.appendChild(el);
  }
  el.textContent=text;
  el.className="suwc-msg "+(isError?"suwc-msg--error":"suwc-msg--ok");
  el.removeAttribute("hidden");
  el.scrollIntoView({behavior:"smooth",block:"nearest"});
  if(!isError)setTimeout(function(){el.hidden=true;},7000);
}

function initCounter(form){
  var ta=qs("#comment",form);
  if(!ta)return;
  var ctr=document.createElement("span");
  ctr.className="suwc-char-count";
  ta.parentNode.appendChild(ctr);
  function update(){
    var n=ta.value.length;
    ctr.textContent=n.toLocaleString()+" / "+d.maxLen.toLocaleString();
    ctr.classList.toggle("suwc-char-count--warn",n>=Math.floor(d.maxLen*0.85));
    ctr.classList.toggle("suwc-char-count--over",n>d.maxLen);
  }
  ta.addEventListener("input",update);
  update();
}

function initForm(form){
  initCounter(form);
  form.addEventListener("submit",function(e){
    e.preventDefault();
    var ta=qs("#comment",form);
    if(ta&&ta.value.length>d.maxLen){showMsg(form,d.i18n.over,true);return;}
    var btn=qs(".suwc-btn",form);
    var orig=btn?btn.innerHTML:"";
    if(btn){btn.disabled=true;btn.textContent=d.i18n.submitting;}
    var body=new FormData(form);
    body.append("action","suw_post_comment");
    body.append("suw_nonce",d.nonce);
    fetch(d.ajaxurl,{method:"POST",body:body,credentials:"same-origin"})
      .then(function(r){return r.json();})
      .then(function(res){
        if(btn){btn.disabled=false;btn.innerHTML=orig;}
        if(res.success){
          var data=res.data;
          if(data.html&&!data.pending){
            var pi=qs("[name=comment_parent]",form);
            var pid=pi?parseInt(pi.value,10):0;
            var list=qs(".suwc-list");
            if(pid>0&&list){
              var pli=document.getElementById("comment-"+pid);
              if(pli){
                var kids=qs(".children",pli);
                if(!kids){kids=document.createElement("ul");kids.className="children";pli.appendChild(kids);}
                kids.insertAdjacentHTML("beforeend",data.html);
                var nl=kids.lastElementChild;
                if(nl)nl.classList.add("suwc-item--new");
              }
            }else if(list){
              list.insertAdjacentHTML("beforeend",data.html);
              var ne=list.lastElementChild;
              if(ne)ne.classList.add("suwc-item--new");
            }
            var badge=qs(".suwc-count");
            if(badge&&data.count)badge.textContent=data.count;
            initLikes();
          }
          showMsg(form,data.message||(data.pending?d.i18n.pending:d.i18n.success),false);
          form.reset();
          var ctr=qs(".suwc-char-count",form);
          if(ctr)ctr.textContent="0 / "+d.maxLen.toLocaleString();
          var cancel=document.getElementById("cancel-comment-reply-link");
          if(cancel)cancel.click();
        }else{
          showMsg(form,(res.data&&res.data.message)||d.i18n.error,true);
        }
      })
      .catch(function(){
        if(btn){btn.disabled=false;btn.innerHTML=orig;}
        showMsg(form,d.i18n.error,true);
      });
  });
}

function initLoadMore(){
  if(d.totalPages<=1)return;
  var section=document.getElementById("comments");
  var pagination=section&&qs(".comments-pagination",section);
  var list=section&&qs(".suwc-list",section);
  if(!pagination||!list)return;
  var cur=qs(".page-numbers.current",pagination);
  var currentPage=cur?(parseInt(cur.textContent,10)||1):1;
  if(currentPage>=d.totalPages)return;
  pagination.hidden=true;
  var btn=document.createElement("button");
  btn.type="button";
  btn.className="suwc-load-more";
  btn.textContent=d.i18n.loadMore;
  pagination.insertAdjacentElement("afterend",btn);
  var nextPage=currentPage+1;
  btn.addEventListener("click",function(){
    btn.disabled=true;
    btn.textContent=d.i18n.loading;
    fetch(d.ajaxurl+"?action=suw_load_more_comments&nonce="+encodeURIComponent(d.nonce)+"&post_id="+d.postId+"&page="+nextPage,{credentials:"same-origin"})
      .then(function(r){return r.json();})
      .then(function(res){
        if(res.success&&res.data.html){
          list.insertAdjacentHTML("beforeend",res.data.html);
          nextPage++;
          initLikes();
          if(res.data.has_more){btn.disabled=false;btn.textContent=d.i18n.loadMore;}
          else{btn.textContent=d.i18n.noMore;btn.disabled=true;}
        }else{btn.disabled=false;btn.textContent=d.i18n.loadMore;}
      })
      .catch(function(){btn.disabled=false;btn.textContent=d.i18n.loadMore;});
  });
}

function getLiked(){try{return JSON.parse(localStorage.getItem("suwc_liked")||"{}");}catch(e){return{};}}
function saveLiked(o){try{localStorage.setItem("suwc_liked",JSON.stringify(o));}catch(e){}}

function initLikes(){
  var liked=getLiked();
  qsa(".suwc-like-btn").forEach(function(btn){
    if(btn._suwInit)return;
    btn._suwInit=true;
    var cid=btn.dataset.id;
    if(liked[cid]){btn.classList.add("is-liked");btn.title=d.i18n.unlikeTitle;}
    btn.addEventListener("click",function(){
      var was=!!liked[cid];
      btn.disabled=true;
      var body=new FormData();
      body.append("action","suw_like_comment");
      body.append("nonce",d.nonce);
      body.append("comment_id",cid);
      body.append("unlike",was?"1":"0");
      fetch(d.ajaxurl,{method:"POST",body:body,credentials:"same-origin"})
        .then(function(r){return r.json();})
        .then(function(res){
          if(res.success){
            var cel=qs(".suwc-like-count",btn);
            var n=res.data.count;
            if(cel)cel.textContent=n>0?n:"";
            if(was){delete liked[cid];btn.classList.remove("is-liked");btn.title=d.i18n.likeTitle;}
            else{liked[cid]=1;btn.classList.add("is-liked");btn.title=d.i18n.unlikeTitle;}
            saveLiked(liked);
          }
          btn.disabled=false;
        })
        .catch(function(){btn.disabled=false;});
    });
  });
}

qsa(".suwc-form").forEach(initForm);
initLoadMore();
initLikes();
}());';
}
