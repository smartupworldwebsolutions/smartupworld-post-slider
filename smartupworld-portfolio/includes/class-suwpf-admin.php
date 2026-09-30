<?php
/**
 * Dashboard page: Portfolio → Docs & Demo.
 */

defined( 'ABSPATH' ) || exit;

class SUWPF_Admin {

	const SLUG = 'suwpf-docs';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_suwpf_import_demo', array( __CLASS__, 'handle_import' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( SUWPF_FILE ), array( __CLASS__, 'action_links' ) );
	}

	public static function url() {
		return admin_url( 'edit.php?post_type=' . SUWPF_Post_Type::POST_TYPE . '&page=' . self::SLUG );
	}

	public static function menu() {
		add_submenu_page(
			'edit.php?post_type=' . SUWPF_Post_Type::POST_TYPE,
			__( 'Portfolio Showcase — Docs & Demo', 'smartupworld-portfolio' ),
			__( 'Docs & Demo', 'smartupworld-portfolio' ),
			'edit_posts',
			self::SLUG,
			array( __CLASS__, 'render' )
		);
	}

	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( self::url() ) . '">' . esc_html__( 'Docs & Demo', 'smartupworld-portfolio' ) . '</a>' );
		return $links;
	}

	public static function handle_import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to import demo content.', 'smartupworld-portfolio' ), 403 );
		}
		check_admin_referer( 'suwpf_import_demo' );

		$r = SUWPF_Demo::import();
		set_transient( 'suwpf_import_result_' . get_current_user_id(), $r, 5 * MINUTE_IN_SECONDS );

		wp_safe_redirect( add_query_arg( 'tab', 'demo', self::url() ) );
		exit;
	}

	public static function render() {
		$tabs = array(
			'start'     => __( 'Getting started', 'smartupworld-portfolio' ),
			'shortcode' => __( 'Shortcode', 'smartupworld-portfolio' ),
			'elementor' => __( 'Elementor widget', 'smartupworld-portfolio' ),
			'demo'      => __( 'Demo content', 'smartupworld-portfolio' ),
		);
		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'start'; // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'start';
		}
		?>
		<div class="wrap suwpf-admin">
			<style>
				.suwpf-admin .suwpf-hero{background:linear-gradient(135deg,#0D1B2A,#1565C0);color:#fff;border-radius:10px;padding:28px 32px;margin:16px 0 20px}
				.suwpf-admin .suwpf-hero h1{color:#fff;font-size:24px;margin:0 0 6px;padding:0}
				.suwpf-admin .suwpf-hero p{color:#dbe7f5;font-size:14px;margin:0;max-width:720px}
				.suwpf-admin .suwpf-hero .ver{display:inline-block;margin-left:8px;font-size:12px;background:rgba(255,255,255,.18);border-radius:99px;padding:2px 10px;vertical-align:middle}
				.suwpf-admin .suwpf-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:20px 24px;margin:0 0 16px;max-width:960px}
				.suwpf-admin .suwpf-card h2{margin-top:0}
				.suwpf-admin .suwpf-card code{font-size:13px}
				.suwpf-admin pre{background:#f6f7f7;border:1px solid #dcdcde;border-radius:6px;padding:12px 14px;white-space:pre-wrap;word-break:break-word;margin:8px 0 12px}
				.suwpf-admin table.widefat td,.suwpf-admin table.widefat th{vertical-align:top}
				.suwpf-admin ol li,.suwpf-admin ul li{margin-bottom:6px}
				.suwpf-admin .nav-tab-wrapper{max-width:960px}
			</style>

			<div class="suwpf-hero">
				<h1><?php esc_html_e( 'SmartUpWorld Portfolio Showcase', 'smartupworld-portfolio' ); ?><span class="ver">v<?php echo esc_html( SUWPF_VERSION ); ?></span></h1>
				<p><?php esc_html_e( 'A filterable portfolio grid with browser-style project cards. Use it as an Elementor widget or with the [suw_portfolio] shortcode.', 'smartupworld-portfolio' ); ?></p>
			</div>

			<nav class="nav-tab-wrapper">
				<?php foreach ( $tabs as $key => $label ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'tab', $key, self::url() ) ); ?>" class="nav-tab<?php echo $tab === $key ? ' nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</nav>
			<br>

			<?php call_user_func( array( __CLASS__, 'tab_' . $tab ) ); ?>
		</div>
		<?php
	}

	private static function tab_start() {
		$new = admin_url( 'post-new.php?post_type=' . SUWPF_Post_Type::POST_TYPE );
		$types = admin_url( 'edit-tags.php?taxonomy=' . SUWPF_Post_Type::TAXONOMY . '&post_type=' . SUWPF_Post_Type::POST_TYPE );
		?>
		<div class="suwpf-card">
			<h2><?php esc_html_e( 'Add your projects', 'smartupworld-portfolio' ); ?></h2>
			<ol>
				<li><?php printf( wp_kses( __( 'Go to <a href="%s">Portfolio → Add New Project</a>.', 'smartupworld-portfolio' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( $new ) ); ?></li>
				<li><?php esc_html_e( 'Enter the project name as the title.', 'smartupworld-portfolio' ); ?></li>
				<li><?php esc_html_e( 'Paste the live site address into the Website URL box.', 'smartupworld-portfolio' ); ?></li>
				<li><?php esc_html_e( 'Set a screenshot as the Featured Image (about 1600 × 1000 px works best).', 'smartupworld-portfolio' ); ?></li>
				<li><?php printf( wp_kses( __( 'Pick a <a href="%s">Project Type</a> such as Blog or Business. Each type becomes a filter tab.', 'smartupworld-portfolio' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( $types ) ); ?></li>
				<li><?php esc_html_e( 'Use the Order field (Page Attributes) to control the order; lower numbers come first.', 'smartupworld-portfolio' ); ?></li>
			</ol>
		</div>
		<div class="suwpf-card">
			<h2><?php esc_html_e( 'Show them on a page', 'smartupworld-portfolio' ); ?></h2>
			<ul>
				<li><strong><?php esc_html_e( 'Elementor:', 'smartupworld-portfolio' ); ?></strong> <?php esc_html_e( 'search for "Portfolio Showcase" in the widget panel (SmartUpWorld category) and set Source to "Portfolio admin section".', 'smartupworld-portfolio' ); ?></li>
				<li><strong><?php esc_html_e( 'Any editor:', 'smartupworld-portfolio' ); ?></strong> <?php esc_html_e( 'add this shortcode:', 'smartupworld-portfolio' ); ?> <code>[suw_portfolio heading="Our Work"]</code></li>
			</ul>
			<p><?php printf( wp_kses( __( 'Want to see it first? <a href="%s">Import the demo content</a>.', 'smartupworld-portfolio' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( add_query_arg( 'tab', 'demo', self::url() ) ) ); ?></p>
		</div>
		<?php
	}

	private static function tab_shortcode() {
		$rows = array(
			array( 'eyebrow', '', __( 'Small label above the heading.', 'smartupworld-portfolio' ) ),
			array( 'heading', '', __( 'Section heading.', 'smartupworld-portfolio' ) ),
			array( 'heading_tag', 'h2', __( 'HTML tag for the heading: h1, h2, h3, h4 or div.', 'smartupworld-portfolio' ) ),
			array( 'subheading', '', __( 'Short description under the heading.', 'smartupworld-portfolio' ) ),
			array( 'type', '', __( 'Only show these Project Type slugs, comma-separated, e.g. blog,business.', 'smartupworld-portfolio' ) ),
			array( 'limit', '0', __( 'Maximum number of projects. 0 = all.', 'smartupworld-portfolio' ) ),
			array( 'filter', 'yes', __( 'Show the filter tabs (yes/no). Hidden automatically when there is only one type.', 'smartupworld-portfolio' ) ),
			array( 'counts', 'yes', __( 'Show the number of projects on each tab (yes/no).', 'smartupworld-portfolio' ) ),
			array( 'all_label', 'All', __( 'Text of the first tab.', 'smartupworld-portfolio' ) ),
			array( 'browser', 'yes', __( 'Browser-window frame above each screenshot (yes/no).', 'smartupworld-portfolio' ) ),
			array( 'full_width', 'yes', __( 'Background runs edge to edge (yes) or stays inside the content width (no).', 'smartupworld-portfolio' ) ),
			array( 'cta_text', '', __( 'Button text under the grid. Needs cta_url.', 'smartupworld-portfolio' ) ),
			array( 'cta_url', '', __( 'Button link, e.g. /contact/ or #contact.', 'smartupworld-portfolio' ) ),
		);
		?>
		<div class="suwpf-card">
			<h2><?php esc_html_e( 'Basic usage', 'smartupworld-portfolio' ); ?></h2>
			<pre>[suw_portfolio heading="Our Clients / Demos"]</pre>
			<h2><?php esc_html_e( 'Full example', 'smartupworld-portfolio' ); ?></h2>
			<pre>[suw_portfolio eyebrow="Portfolio" heading="Our Clients / Demos" subheading="Sites we have designed and built." type="blog,business" limit="6" cta_text="Start your project" cta_url="/contact/"]</pre>
		</div>
		<div class="suwpf-card">
			<h2><?php esc_html_e( 'Attributes', 'smartupworld-portfolio' ); ?></h2>
			<p><?php esc_html_e( 'All attributes are optional.', 'smartupworld-portfolio' ); ?></p>
			<table class="widefat striped">
				<thead><tr><th><?php esc_html_e( 'Attribute', 'smartupworld-portfolio' ); ?></th><th><?php esc_html_e( 'Default', 'smartupworld-portfolio' ); ?></th><th><?php esc_html_e( 'What it does', 'smartupworld-portfolio' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $rows as $r ) : ?>
					<tr><td><code><?php echo esc_html( $r[0] ); ?></code></td><td><?php echo '' === $r[1] ? '—' : '<code>' . esc_html( $r[1] ) . '</code>'; ?></td><td><?php echo esc_html( $r[2] ); ?></td></tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	private static function tab_elementor() {
		$active = did_action( 'elementor/loaded' );
		?>
		<div class="suwpf-card">
			<h2><?php esc_html_e( 'Portfolio Showcase widget', 'smartupworld-portfolio' ); ?></h2>
			<p>
				<?php if ( $active ) : ?>
					<span class="dashicons dashicons-yes-alt" style="color:#00a32a"></span> <?php esc_html_e( 'Elementor is active — the widget is available.', 'smartupworld-portfolio' ); ?>
				<?php else : ?>
					<span class="dashicons dashicons-info" style="color:#dba617"></span> <?php esc_html_e( 'Elementor is not active. The widget appears once Elementor is installed; the shortcode works without it.', 'smartupworld-portfolio' ); ?>
				<?php endif; ?>
			</p>
			<ol>
				<li><?php esc_html_e( 'Edit a page with Elementor.', 'smartupworld-portfolio' ); ?></li>
				<li><?php esc_html_e( 'Search the widget panel for "Portfolio Showcase" (SmartUpWorld category) and drag it onto the page.', 'smartupworld-portfolio' ); ?></li>
				<li><?php esc_html_e( 'Choose where projects come from under Content → Projects → Source.', 'smartupworld-portfolio' ); ?></li>
			</ol>
		</div>
		<div class="suwpf-card">
			<h2><?php esc_html_e( 'Settings', 'smartupworld-portfolio' ); ?></h2>
			<table class="widefat striped">
				<tbody>
					<tr><th><?php esc_html_e( 'Content → Header', 'smartupworld-portfolio' ); ?></th><td><?php esc_html_e( 'Eyebrow, heading (and its HTML tag), description.', 'smartupworld-portfolio' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Content → Projects', 'smartupworld-portfolio' ); ?></th><td><?php esc_html_e( 'Source: add projects directly in the widget (name, type, screenshot, URL), or use the Portfolio admin section with optional type filter and limit.', 'smartupworld-portfolio' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Content → Layout & Features', 'smartupworld-portfolio' ); ?></th><td><?php esc_html_e( 'Columns per device, filter tabs, tab counts, "All" label, browser frame, full-width background, button text and link.', 'smartupworld-portfolio' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Style → Colours', 'smartupworld-portfolio' ); ?></th><td><?php esc_html_e( 'Accent, accent light, button & badge, headings, body text, borders, background gradient.', 'smartupworld-portfolio' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Style → Cards & Spacing', 'smartupworld-portfolio' ); ?></th><td><?php esc_html_e( 'Section padding, content width, card gap, corner radius, screenshot shape.', 'smartupworld-portfolio' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Style → Typography', 'smartupworld-portfolio' ); ?></th><td><?php esc_html_e( 'Heading and card title fonts.', 'smartupworld-portfolio' ); ?></td></tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	private static function tab_demo() {
		$result  = get_transient( 'suwpf_import_result_' . get_current_user_id() );
		$page_id = SUWPF_Demo::page_id();

		if ( $result ) {
			delete_transient( 'suwpf_import_result_' . get_current_user_id() );
			$class = $result['images_failed'] ? 'notice-warning' : 'notice-success';
			echo '<div class="notice ' . esc_attr( $class ) . ' inline"><p>';
			printf(
				/* translators: 1: created, 2: skipped */
				esc_html__( 'Demo import finished: %1$d projects created, %2$d already existed.', 'smartupworld-portfolio' ),
				(int) $result['created'],
				(int) $result['skipped']
			);
			if ( $result['images_failed'] ) {
				echo ' ' . sprintf(
					/* translators: %d: number of images */
					esc_html__( '%d screenshots could not be downloaded — add them later as Featured Images.', 'smartupworld-portfolio' ),
					(int) $result['images_failed']
				);
			}
			echo '</p></div>';
		}
		?>
		<div class="suwpf-card">
			<h2><?php esc_html_e( 'Import demo content', 'smartupworld-portfolio' ); ?></h2>
			<p><?php esc_html_e( 'Adds 7 sample projects with screenshots, the Blog and Business project types, and a "Portfolio Demo" page that shows them. Running it again skips anything already imported.', 'smartupworld-portfolio' ); ?></p>

			<?php if ( $page_id ) : ?>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( get_permalink( $page_id ) ); ?>" target="_blank"><?php esc_html_e( 'View demo page', 'smartupworld-portfolio' ); ?></a>
					<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . SUWPF_Post_Type::POST_TYPE ) ); ?>"><?php esc_html_e( 'View projects', 'smartupworld-portfolio' ); ?></a>
				</p>
			<?php endif; ?>

			<?php if ( current_user_can( 'manage_options' ) ) : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="suwpf_import_demo">
					<?php wp_nonce_field( 'suwpf_import_demo' ); ?>
					<?php submit_button( $page_id ? __( 'Import again (skips existing)', 'smartupworld-portfolio' ) : __( 'Import demo content', 'smartupworld-portfolio' ), $page_id ? 'secondary' : 'primary', 'submit', false ); ?>
					<span class="description" style="margin-left:8px"><?php esc_html_e( 'Downloading the screenshots can take up to a minute.', 'smartupworld-portfolio' ); ?></span>
				</form>
			<?php else : ?>
				<p><em><?php esc_html_e( 'Only administrators can import demo content.', 'smartupworld-portfolio' ); ?></em></p>
			<?php endif; ?>
		</div>
		<?php
	}
}
