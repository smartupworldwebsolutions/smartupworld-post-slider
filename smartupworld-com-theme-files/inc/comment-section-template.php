<?php
/**
 * Comment section template — loaded by inc/comment-section.php through the
 * comments_template filter. The theme's own comments.php is left untouched.
 *
 * @package Smartupworld
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() || ( ! have_comments() && ! comments_open() ) ) {
	return;
}

$suwc_req       = (bool) get_option( 'require_name_email' );
$suwc_commenter = wp_get_current_commenter();
$suwc_star      = ' <span class="suwc-req" aria-hidden="true">*</span>';
$suwc_mark      = $suwc_req ? $suwc_star : '';
$suwc_required  = $suwc_req ? ' required' : '';

$suwc_fields = array(
	// Honeypot: real users never see/fill this; bots that auto-fill forms are caught.
	'suw_hp' => '<div class="suwc-hp" aria-hidden="true" tabindex="-1"><label for="suw_hp">Leave this field empty</label><input type="text" id="suw_hp" name="suw_hp" value="" autocomplete="off" tabindex="-1"></div>',
	'author' => sprintf(
		'<div class="suwc-field suwc-field--author"><label for="author">Name%1$s</label><input id="author" class="suwc-input" name="author" type="text" value="%2$s" maxlength="245" autocomplete="name" placeholder="Your name"%3$s></div>',
		$suwc_mark,
		esc_attr( $suwc_commenter['comment_author'] ),
		$suwc_required
	),
	'email'  => sprintf(
		'<div class="suwc-field suwc-field--email"><label for="email">Email%1$s</label><input id="email" class="suwc-input" name="email" type="email" inputmode="email" value="%2$s" maxlength="100" autocomplete="email" placeholder="you@example.com" aria-describedby="email-notes"%3$s></div>',
		$suwc_mark,
		esc_attr( $suwc_commenter['comment_author_email'] ),
		$suwc_required
	),
	// type="text" so "mysite.com" is accepted; WordPress adds the scheme on save.
	'url'    => sprintf(
		'<div class="suwc-field suwc-field--url"><label for="url">Website <span class="suwc-opt">(optional)</span></label><input id="url" class="suwc-input" name="url" type="text" inputmode="url" value="%1$s" maxlength="200" autocomplete="url" placeholder="yoursite.com"></div>',
		esc_attr( $suwc_commenter['comment_author_url'] )
	),
);

if ( has_action( 'set_comment_cookies', 'wp_set_comment_cookies' ) && get_option( 'show_comments_cookies_opt_in' ) ) {
	$suwc_fields['cookies'] = sprintf(
		'<p class="suwc-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"%s><label for="wp-comment-cookies-consent">Save my name, email, and website in this browser for the next time I comment.</label></p>',
		empty( $suwc_commenter['comment_author_email'] ) ? '' : ' checked'
	);
}

$suwc_user      = wp_get_current_user();
$suwc_heading   = have_comments() ? 'h3' : 'h2';
$suwc_grid_open = static function () {
	echo '<div class="suwc-grid">';
};
$suwc_grid_end  = static function () {
	echo '</div>';
};
?>
<section id="comments" class="suwc">

	<?php if ( have_comments() ) : ?>
		<h2 class="suwc-title">
			Comments
			<span class="suwc-count"><?php echo esc_html( number_format_i18n( get_comments_number() ) ); ?></span>
		</h2>

		<ol class="suwc-list">
			<?php
			wp_list_comments(
				array(
					'style'    => 'ol',
					'callback' => 'suw_comment_item',
				)
			);
			?>
		</ol>

		<?php the_comments_pagination(); ?>

		<?php if ( ! comments_open() ) : ?>
			<p class="suwc-closed">Comments are closed.</p>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	add_action( 'comment_form_before_fields', $suwc_grid_open );
	add_action( 'comment_form_after_fields', $suwc_grid_end );

	comment_form(
		array(
			'fields'               => apply_filters( 'comment_form_default_fields', $suwc_fields ),
			'comment_field'        => '<div class="suwc-field suwc-field--comment"><label for="comment">Comment' . $suwc_star . '</label><textarea id="comment" class="suwc-input" name="comment" rows="5" maxlength="65525" placeholder="Share your thoughts, questions or experience…" required></textarea></div>',
			'comment_notes_before' => '<p class="suwc-notes"><span id="email-notes">Your email address will not be published.</span> Required fields are marked <span class="suwc-req">*</span></p>',
			'comment_notes_after'  => '',
			'logged_in_as'         => sprintf(
				'<p class="suwc-notes">Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s">Log out?</a></p>',
				esc_url( get_edit_user_link() ),
				esc_html( $suwc_user->display_name ),
				esc_url( wp_logout_url( get_permalink() ) )
			),
			'class_container'      => 'suwc-respond',
			'class_form'           => 'suwc-form',
			'title_reply'          => 'Leave a Reply',
			'title_reply_to'       => 'Reply to %s',
			'title_reply_before'   => '<' . $suwc_heading . ' id="reply-title" class="suwc-reply-title">',
			'title_reply_after'    => '</' . $suwc_heading . '>',
			'cancel_reply_before'  => ' <small>',
			'cancel_reply_after'   => '</small>',
			'cancel_reply_link'    => 'Cancel reply',
			'label_submit'         => 'Post Comment',
			'class_submit'         => 'suwc-btn',
			'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s<svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4z"/></svg></button>',
			'submit_field'         => '<p class="suwc-submit">%1$s %2$s</p>',
			'format'               => 'xhtml',
		)
	);

	remove_action( 'comment_form_before_fields', $suwc_grid_open );
	remove_action( 'comment_form_after_fields', $suwc_grid_end );
	?>

</section>
