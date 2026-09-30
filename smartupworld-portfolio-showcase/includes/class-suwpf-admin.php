<?php
/**
 * Dashboard page: Portfolio → Docs & Demo.
 *
 * @package SmartUpWorld_Portfolio_Showcase
 */

defined( 'ABSPATH' ) || exit;

/**
 * Documentation page, demo import/delete and plugin-screen links.
 */
class SUWPF_Admin {

	const SLUG = 'suwpf-docs';

	/**
	 * Hook suffix of the docs page.
	 *
	 * @var string
	 */
	private static $hook = '';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_post_suwpf_import_demo', array( __CLASS__, 'handle_import' ) );
		add_action( 'admin_post_suwpf_delete_demo', array( __CLASS__, 'handle_delete' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( SUWPF_FILE ), array( __CLASS__, 'action_links' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'row_meta' ), 10, 2 );
	}

	/**
	 * Docs page URL.
	 *
	 * @param string $tab Optional tab.
	 * @return string
	 */
	public static function url( $tab = '' ) {
		$url = admin_url( 'edit.php?post_type=' . SUWPF_Post_Type::POST_TYPE . '&page=' . self::SLUG );
		return $tab ? add_query_arg( 'tab', $tab, $url ) : $url;
	}

	/**
	 * Registers the submenu page.
	 */
	public static function menu() {
		self::$hook = (string) add_submenu_page(
			'edit.php?post_type=' . SUWPF_Post_Type::POST_TYPE,
			__( 'Portfolio Showcase — Docs & Demo', 'smartupworld-portfolio-showcase' ),
			__( 'Docs & Demo', 'smartupworld-portfolio-showcase' ),
			'edit_posts',
			self::SLUG,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Loads the page CSS/JS on the docs page only.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public static function assets( $hook ) {
		if ( ! self::$hook || $hook !== self::$hook ) {
			return;
		}
		wp_enqueue_style( 'suwpf-admin', SUWPF_URL . 'assets/css/suw-portfolio-admin.css', array(), SUWPF_VERSION );
		wp_enqueue_script(
			'suwpf-admin',
			SUWPF_URL . 'assets/js/suw-portfolio-admin.js',
			array(),
			SUWPF_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	/**
	 * "Docs & Demo" link on the Plugins screen.
	 *
	 * @param array $links Action links.
	 * @return array
	 */
	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( self::url() ) . '">' . esc_html__( 'Docs & Demo', 'smartupworld-portfolio-showcase' ) . '</a>' );
		return $links;
	}

	/**
	 * "Documentation" link under the plugin description.
	 *
	 * @param array  $meta Row meta links.
	 * @param string $file Plugin file.
	 * @return array
	 */
	public static function row_meta( $meta, $file ) {
		if ( plugin_basename( SUWPF_FILE ) === $file ) {
			$meta[] = '<a href="' . esc_url( SUWPF_DOCS_URL ) . '" target="_blank" rel="noopener">' . esc_html__( 'Online documentation', 'smartupworld-portfolio-showcase' ) . '</a>';
		}
		return $meta;
	}

	/**
	 * Handles the "Import demo content" form.
	 */
	public static function handle_import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to import demo content.', 'smartupworld-portfolio-showcase' ), 403 );
		}
		check_admin_referer( 'suwpf_import_demo' );

		$result           = SUWPF_Demo::import();
		$result['action'] = 'import';
		set_transient( 'suwpf_demo_result_' . get_current_user_id(), $result, 5 * MINUTE_IN_SECONDS );

		wp_safe_redirect( self::url( 'demo' ) );
		exit;
	}

	/**
	 * Handles the "Delete demo content" form.
	 */
	public static function handle_delete() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to delete demo content.', 'smartupworld-portfolio-showcase' ), 403 );
		}
		check_admin_referer( 'suwpf_delete_demo' );

		$result           = SUWPF_Demo::delete();
		$result['action'] = 'delete';
		set_transient( 'suwpf_demo_result_' . get_current_user_id(), $result, 5 * MINUTE_IN_SECONDS );

		wp_safe_redirect( self::url( 'demo' ) );
		exit;
	}

	/**
	 * Page output.
	 */
	public static function render() {
		$tabs = array(
			'start'     => __( 'Getting started', 'smartupworld-portfolio-showcase' ),
			'elementor' => __( 'Elementor widget', 'smartupworld-portfolio-showcase' ),
			'shortcode' => __( 'Shortcode', 'smartupworld-portfolio-showcase' ),
			'demo'      => __( 'Demo content', 'smartupworld-portfolio-showcase' ),
		);
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'start'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab switch.
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'start';
		}
		?>
		<div class="wrap suwpf-admin">
			<h1 class="screen-reader-text"><?php esc_html_e( 'SmartUpWorld Portfolio Showcase', 'smartupworld-portfolio-showcase' ); ?></h1>

			<div class="suwpf-hero">
				<div class="suwpf-hero__text">
					<p class="suwpf-hero__title"><?php esc_html_e( 'SmartUpWorld Portfolio Showcase', 'smartupworld-portfolio-showcase' ); ?> <span class="suwpf-hero__ver">v<?php echo esc_html( SUWPF_VERSION ); ?></span></p>
					<p><?php esc_html_e( 'A filterable portfolio grid with browser-style project cards. Use it as an Elementor widget or with the [suw_portfolio] shortcode.', 'smartupworld-portfolio-showcase' ); ?></p>
					<p class="suwpf-hero__links">
						<a class="button button-primary" href="<?php echo esc_url( SUWPF_DOCS_URL ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Online documentation', 'smartupworld-portfolio-showcase' ); ?></a>
						<a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . SUWPF_Post_Type::POST_TYPE ) ); ?>"><?php esc_html_e( 'Add a project', 'smartupworld-portfolio-showcase' ); ?></a>
					</p>
				</div>
				<img class="suwpf-hero__shot" src="<?php echo esc_url( SUWPF_URL . 'assets/images/preview.webp' ); ?>" width="1200" height="677" alt="<?php esc_attr_e( 'Portfolio Showcase preview: filter tabs and three project cards', 'smartupworld-portfolio-showcase' ); ?>">
			</div>

			<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Documentation sections', 'smartupworld-portfolio-showcase' ); ?>">
				<?php foreach ( $tabs as $key => $label ) : ?>
					<a href="<?php echo esc_url( self::url( $key ) ); ?>" class="nav-tab<?php echo $tab === $key ? ' nav-tab-active' : ''; ?>"<?php echo $tab === $key ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</nav>

			<div class="suwpf-tab">
				<?php
				switch ( $tab ) {
					case 'elementor':
						self::tab_elementor();
						break;
					case 'shortcode':
						self::tab_shortcode();
						break;
					case 'demo':
						self::tab_demo();
						break;
					default:
						self::tab_start();
				}
				?>
			</div>

			<p class="suwpf-footer">
				<?php
				printf(
					wp_kses(
						/* translators: %s: SmartUpWorld website URL */
						__( 'Made by <a href="%s" target="_blank" rel="noopener">SmartUpWorld Websolutions</a>.', 'smartupworld-portfolio-showcase' ),
						array(
							'a' => array(
								'href'   => array(),
								'target' => array(),
								'rel'    => array(),
							),
						)
					),
					esc_url( 'https://smartupworld.com/' )
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Getting started tab.
	 */
	private static function tab_start() {
		$link  = array( 'a' => array( 'href' => array() ) );
		$new   = admin_url( 'post-new.php?post_type=' . SUWPF_Post_Type::POST_TYPE );
		$types = admin_url( 'edit-tags.php?taxonomy=' . SUWPF_Post_Type::TAXONOMY . '&post_type=' . SUWPF_Post_Type::POST_TYPE );
		?>
		<div class="suwpf-card">
			<h2><?php esc_html_e( 'Works straight away', 'smartupworld-portfolio-showcase' ); ?></h2>
			<p><?php esc_html_e( 'Until you publish your first project, the portfolio shows three sample projects so you can see the layout. They are not saved anywhere and disappear automatically once a real project is published.', 'smartupworld-portfolio-showcase' ); ?></p>
		</div>
		<div class="suwpf-card">
			<h2><?php esc_html_e( 'Add your projects', 'smartupworld-portfolio-showcase' ); ?></h2>
			<ol>
				<?php /* translators: %s: "Add New Project" admin URL */ ?>
				<li><?php printf( wp_kses( __( 'Go to <a href="%s">Portfolio → Add New Project</a>.', 'smartupworld-portfolio-showcase' ), $link ), esc_url( $new ) ); ?></li>
				<li><?php esc_html_e( 'Enter the project name as the title.', 'smartupworld-portfolio-showcase' ); ?></li>
				<li><?php esc_html_e( 'Paste the live site address into the Website URL box.', 'smartupworld-portfolio-showcase' ); ?></li>
				<li><?php esc_html_e( 'Set a screenshot as the Project screenshot (featured image). About 1600 × 1000 px works best; add descriptive alt text in the Media Library for better SEO.', 'smartupworld-portfolio-showcase' ); ?></li>
				<?php /* translators: %s: Project Types admin URL */ ?>
				<li><?php printf( wp_kses( __( 'Pick a <a href="%s">Project Type</a> such as Blog or Business. Each type becomes a filter tab.', 'smartupworld-portfolio-showcase' ), $link ), esc_url( $types ) ); ?></li>
				<li><?php esc_html_e( 'Use the Order field (Page Attributes) to control the order; lower numbers come first.', 'smartupworld-portfolio-showcase' ); ?></li>
			</ol>
		</div>
		<div class="suwpf-card">
			<h2><?php esc_html_e( 'Show them on a page', 'smartupworld-portfolio-showcase' ); ?></h2>
			<ul>
				<li><strong><?php esc_html_e( 'Elementor:', 'smartupworld-portfolio-showcase' ); ?></strong> <?php esc_html_e( 'search for "Portfolio Showcase" in the widget panel (SmartUpWorld category) and drag it onto the page. It shows your Portfolio projects automatically.', 'smartupworld-portfolio-showcase' ); ?></li>
				<li><strong><?php esc_html_e( 'Any editor:', 'smartupworld-portfolio-showcase' ); ?></strong> <?php esc_html_e( 'add this shortcode:', 'smartupworld-portfolio-showcase' ); ?> <code>[suw_portfolio heading="Our Work"]</code></li>
			</ul>
			<?php /* translators: %s: demo tab URL */ ?>
			<p><?php printf( wp_kses( __( 'Want a fuller example? <a href="%s">Import the demo content</a> — six editable projects in three types. You can delete it again with one click.', 'smartupworld-portfolio-showcase' ), $link ), esc_url( self::url( 'demo' ) ) ); ?></p>
		</div>
		<?php
	}

	/**
	 * Shortcode tab.
	 */
	private static function tab_shortcode() {
		$rows = array(
			array( 'eyebrow', '', __( 'Small label above the heading.', 'smartupworld-portfolio-showcase' ) ),
			array( 'heading', '', __( 'Section heading.', 'smartupworld-portfolio-showcase' ) ),
			array( 'heading_tag', 'h2', __( 'HTML tag for the heading: h1, h2, h3, h4 or div. Card titles use the next level down.', 'smartupworld-portfolio-showcase' ) ),
			array( 'subheading', '', __( 'Short description under the heading.', 'smartupworld-portfolio-showcase' ) ),
			array( 'type', '', __( 'Only show these Project Type slugs, comma-separated, e.g. blog,business.', 'smartupworld-portfolio-showcase' ) ),
			array( 'limit', '0', __( 'Maximum number of projects. 0 = all (up to 100).', 'smartupworld-portfolio-showcase' ) ),
			array( 'filter', 'yes', __( 'Show the filter tabs (yes/no). Hidden automatically when there is only one type.', 'smartupworld-portfolio-showcase' ) ),
			array( 'counts', 'yes', __( 'Show the number of projects on each tab (yes/no).', 'smartupworld-portfolio-showcase' ) ),
			array( 'all_label', 'All', __( 'Text of the first tab.', 'smartupworld-portfolio-showcase' ) ),
			array( 'browser', 'yes', __( 'Browser-window frame above each screenshot (yes/no).', 'smartupworld-portfolio-showcase' ) ),
			array( 'full_width', 'yes', __( 'Background runs edge to edge (yes) or stays inside the content width (no).', 'smartupworld-portfolio-showcase' ) ),
			array( 'cta_text', '', __( 'Button text under the grid. Needs cta_url.', 'smartupworld-portfolio-showcase' ) ),
			array( 'cta_url', '', __( 'Button link, e.g. /contact/ or #contact.', 'smartupworld-portfolio-showcase' ) ),
		);
		?>
		<div class="suwpf-card">
			<h2><?php esc_html_e( 'Basic usage', 'smartupworld-portfolio-showcase' ); ?></h2>
			<pre>[suw_portfolio heading="Our Clients / Demos"]</pre>
			<h2><?php esc_html_e( 'Full example', 'smartupworld-portfolio-showcase' ); ?></h2>
			<pre>[suw_portfolio eyebrow="Portfolio" heading="Our Clients / Demos" subheading="Sites we have designed and built." type="blog,business" limit="6" cta_text="Start your project" cta_url="/contact/"]</pre>
		</div>
		<div class="suwpf-card">
			<h2><?php esc_html_e( 'Attributes', 'smartupworld-portfolio-showcase' ); ?></h2>
			<p><?php esc_html_e( 'All attributes are optional.', 'smartupworld-portfolio-showcase' ); ?></p>
			<table class="widefat striped">
				<thead><tr><th scope="col"><?php esc_html_e( 'Attribute', 'smartupworld-portfolio-showcase' ); ?></th><th scope="col"><?php esc_html_e( 'Default', 'smartupworld-portfolio-showcase' ); ?></th><th scope="col"><?php esc_html_e( 'What it does', 'smartupworld-portfolio-showcase' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $rows as $r ) : ?>
					<tr>
						<td><code><?php echo esc_html( $r[0] ); ?></code></td>
						<td>
							<?php if ( '' === $r[1] ) : ?>
								&mdash;
							<?php else : ?>
								<code><?php echo esc_html( $r[1] ); ?></code>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $r[2] ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Elementor tab.
	 */
	private static function tab_elementor() {
		$active = did_action( 'elementor/loaded' );
		?>
		<div class="suwpf-card">
			<h2><?php esc_html_e( 'Portfolio Showcase widget', 'smartupworld-portfolio-showcase' ); ?></h2>
			<p>
				<?php if ( $active ) : ?>
					<span class="dashicons dashicons-yes-alt suwpf-ok" aria-hidden="true"></span> <?php esc_html_e( 'Elementor is active — the widget is available.', 'smartupworld-portfolio-showcase' ); ?>
				<?php else : ?>
					<span class="dashicons dashicons-info suwpf-warn" aria-hidden="true"></span> <?php esc_html_e( 'Elementor is not active. The widget appears once Elementor is installed; the shortcode works without it.', 'smartupworld-portfolio-showcase' ); ?>
				<?php endif; ?>
			</p>
			<ol>
				<li><?php esc_html_e( 'Edit a page with Elementor.', 'smartupworld-portfolio-showcase' ); ?></li>
				<li><?php esc_html_e( 'Search the widget panel for "Portfolio Showcase" (SmartUpWorld category) and drag it onto the page.', 'smartupworld-portfolio-showcase' ); ?></li>
				<li><?php esc_html_e( 'By default it shows the projects from Portfolio in your dashboard. To type projects straight into the widget instead, set Content → Projects → Source to "Add projects here".', 'smartupworld-portfolio-showcase' ); ?></li>
			</ol>
		</div>
		<div class="suwpf-card">
			<h2><?php esc_html_e( 'Settings', 'smartupworld-portfolio-showcase' ); ?></h2>
			<table class="widefat striped">
				<tbody>
					<tr><th scope="row"><?php esc_html_e( 'Content → Header', 'smartupworld-portfolio-showcase' ); ?></th><td><?php esc_html_e( 'Eyebrow, heading (and its HTML tag), description.', 'smartupworld-portfolio-showcase' ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Content → Projects', 'smartupworld-portfolio-showcase' ); ?></th><td><?php esc_html_e( 'Source: the Portfolio admin section (default) with an optional type filter and limit, or projects added directly in the widget (name, type, screenshot, URL).', 'smartupworld-portfolio-showcase' ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Content → Layout & Features', 'smartupworld-portfolio-showcase' ); ?></th><td><?php esc_html_e( 'Columns per device, filter tabs, tab counts, "All" label, browser frame, full-width background, button text and link.', 'smartupworld-portfolio-showcase' ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Style → Colours', 'smartupworld-portfolio-showcase' ); ?></th><td><?php esc_html_e( 'Accent, accent light, button & badge, headings, body text, borders, background gradient.', 'smartupworld-portfolio-showcase' ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Style → Cards & Spacing', 'smartupworld-portfolio-showcase' ); ?></th><td><?php esc_html_e( 'Section padding, content width, card gap, corner radius, screenshot shape.', 'smartupworld-portfolio-showcase' ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Style → Typography', 'smartupworld-portfolio-showcase' ); ?></th><td><?php esc_html_e( 'Heading and card title fonts.', 'smartupworld-portfolio-showcase' ); ?></td></tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Demo content tab.
	 */
	private static function tab_demo() {
		$key    = 'suwpf_demo_result_' . get_current_user_id();
		$result = get_transient( $key );
		if ( is_array( $result ) ) {
			delete_transient( $key );
			self::result_notice( $result );
		}

		$installed = SUWPF_Demo::is_installed();
		$page_id   = SUWPF_Demo::page_id();
		$can       = current_user_can( 'manage_options' );
		?>
		<div class="suwpf-card">
			<?php if ( $installed ) : ?>
				<h2><?php esc_html_e( 'Demo content is installed', 'smartupworld-portfolio-showcase' ); ?></h2>
				<p><?php esc_html_e( 'The sample projects, their screenshots and the "Portfolio Demo" page are in your site. Edit them freely, or delete them all when you are ready to add your own projects.', 'smartupworld-portfolio-showcase' ); ?></p>
				<p>
					<?php if ( $page_id && 'publish' === get_post_status( $page_id ) ) : ?>
						<a class="button button-primary" href="<?php echo esc_url( get_permalink( $page_id ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View demo page', 'smartupworld-portfolio-showcase' ); ?></a>
					<?php endif; ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . SUWPF_Post_Type::POST_TYPE ) ); ?>"><?php esc_html_e( 'View projects', 'smartupworld-portfolio-showcase' ); ?></a>
				</p>
				<?php if ( $can ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="suwpf-delete-form" data-confirm="<?php esc_attr_e( 'Delete all demo projects, their screenshots and the Portfolio Demo page? Your own projects are not touched.', 'smartupworld-portfolio-showcase' ); ?>">
						<input type="hidden" name="action" value="suwpf_delete_demo">
						<?php wp_nonce_field( 'suwpf_delete_demo' ); ?>
						<?php submit_button( __( 'Delete demo content', 'smartupworld-portfolio-showcase' ), 'delete', 'submit', false ); ?>
						<span class="description"><?php esc_html_e( 'Removes only what the demo import created. Projects you added yourself stay.', 'smartupworld-portfolio-showcase' ); ?></span>
					</form>
				<?php endif; ?>
			<?php else : ?>
				<h2><?php esc_html_e( 'Import demo content', 'smartupworld-portfolio-showcase' ); ?></h2>
				<p><?php esc_html_e( 'Adds six editable demo projects with screenshots — two each for Blog, Business and E-commerce — plus those project types and a "Portfolio Demo" page that shows them. The screenshots are bundled with the plugin, so nothing is downloaded from other sites.', 'smartupworld-portfolio-showcase' ); ?></p>
				<?php if ( $can ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="suwpf_import_demo">
						<?php wp_nonce_field( 'suwpf_import_demo' ); ?>
						<?php submit_button( __( 'Import demo content', 'smartupworld-portfolio-showcase' ), 'primary', 'submit', false ); ?>
					</form>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( ! $can ) : ?>
				<p><em><?php esc_html_e( 'Only administrators can import or delete demo content.', 'smartupworld-portfolio-showcase' ); ?></em></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Notice after an import or delete.
	 *
	 * @param array $r Result from SUWPF_Demo::import() or ::delete().
	 */
	private static function result_notice( $r ) {
		if ( isset( $r['action'] ) && 'delete' === $r['action'] ) {
			$message = sprintf(
				/* translators: 1: projects, 2: images, 3: pages */
				__( 'Demo content deleted: %1$d projects, %2$d images and %3$d page removed.', 'smartupworld-portfolio-showcase' ),
				(int) $r['projects'],
				(int) $r['images'],
				(int) $r['pages']
			);
			$class = 'notice-success';
		} else {
			$message = sprintf(
				/* translators: 1: created, 2: skipped */
				__( 'Demo import finished: %1$d projects created, %2$d already existed.', 'smartupworld-portfolio-showcase' ),
				(int) $r['created'],
				(int) $r['skipped']
			);
			$class = 'notice-success';
			if ( ! empty( $r['images_failed'] ) ) {
				$message .= ' ' . sprintf(
					/* translators: %d: number of images */
					__( '%d screenshots could not be added to the Media Library — set them later as each project\'s screenshot.', 'smartupworld-portfolio-showcase' ),
					(int) $r['images_failed']
				);
				$class = 'notice-warning';
			}
		}
		printf( '<div class="notice %1$s inline"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}
}
