<?php
/**
 * Template Name: Plugin Documentation — SmartUpWorld Portfolio Showcase
 *
 * Hardcoded plugin doc page. Styles: assets/css/smartupworld-portfolio-showcase.css
 * (loaded automatically for the page with the slug "smartupworld-portfolio-showcase").
 *
 * @package Smartupworld
 */

$suwpf_wporg = 'https://wordpress.org/plugins/smartupworld-portfolio-showcase/';
$suwpf_shot  = get_theme_file_uri( 'assets/images/smartupworld-portfolio-showcase.webp' );

get_header(); ?>

<main id="main" class="site-main plugin-doc-main">
<div class="plugin-doc-wrap">

	<!-- Hero -->
	<div class="doc-hero">
		<div class="doc-hero-inner">
			<h1>SmartUpWorld Portfolio Showcase</h1>
			<p>A filterable portfolio grid for WordPress with browser-style project cards. Show client websites and demos with an Elementor widget or a simple shortcode — with one-click demo content.</p>
			<span>Version 1.0.0</span>
			<span>Free</span>
			<span>Elementor Ready</span><br><br>
			<a href="<?php echo esc_url( $suwpf_wporg ); ?>" target="_blank" rel="noopener noreferrer">Download from WordPress.org</a>
		</div>
	</div>

	<!-- Screenshot -->
	<figure class="doc-shot">
		<img src="<?php echo esc_url( $suwpf_shot ); ?>" width="1200" height="677" alt="SmartUpWorld Portfolio Showcase: filter tabs and three browser-style portfolio project cards" fetchpriority="high" decoding="async">
		<figcaption>The portfolio grid with filter tabs, counts and browser-style project cards.</figcaption>
	</figure>

	<!-- What is it -->
	<div class="doc-card">
		<h2>What Is SmartUpWorld Portfolio Showcase?</h2>
		<p>SmartUpWorld Portfolio Showcase turns your client websites, demos and case studies into a clean, filterable portfolio grid. Every project card looks like a small browser window with the site address, a screenshot, a category badge and a link to the live site — ideal for web design agencies, freelancers and developers.</p>
		<p><strong>Key features:</strong></p>
		<ul>
			<li>Elementor widget “Portfolio Showcase” with content, layout, colour, spacing and typography controls</li>
			<li>Shortcode <code>[suw_portfolio]</code> for the block editor, Classic editor, widgets and theme templates</li>
			<li>Portfolio section in the dashboard — add projects with a title, screenshot, website URL and project type</li>
			<li>Filter tabs built from your project types, with optional counts</li>
			<li>Responsive grid — 1 to 4 columns per device, full-width background with a centred container</li>
			<li>Three sample projects appear until you publish your first project, so a new portfolio is never empty</li>
			<li>One-click demo import — and one-click delete — using images bundled with the plugin</li>
			<li>Schema.org structured data, responsive images and accessible filter buttons</li>
			<li>Lightweight: one small CSS file and one deferred script, loaded only where a portfolio appears</li>
		</ul>
	</div>

	<!-- Installation -->
	<div class="doc-card">
		<h2>Installation</h2>
		<ol class="doc-steps">
			<li><span>Go to <strong>Plugins → Add New</strong> in your WordPress dashboard, search for <strong>SmartUpWorld Portfolio Showcase</strong> and click <strong>Install Now</strong>. Or <a href="<?php echo esc_url( $suwpf_wporg ); ?>" target="_blank" rel="noopener">download it from WordPress.org</a> and upload the zip.</span></li>
			<li><span>Click <strong>Activate</strong>. A new <strong>Portfolio</strong> menu appears in the dashboard.</span></li>
			<li><span>Add the <strong>Portfolio Showcase</strong> Elementor widget or the <code>[suw_portfolio]</code> shortcode to any page. Three sample projects show straight away.</span></li>
			<li><span>Add your own projects under <strong>Portfolio → Add New Project</strong>, or import the demo under <strong>Portfolio → Docs &amp; Demo</strong>.</span></li>
		</ol>
	</div>

	<!-- Elementor -->
	<div class="doc-card">
		<h2>Using the Elementor Widget</h2>
		<ol class="doc-steps">
			<li><span>Edit a page with Elementor.</span></li>
			<li><span>Search the widget panel for <strong>Portfolio Showcase</strong> (SmartUpWorld category) and drag it onto the page.</span></li>
			<li><span>The widget shows the projects from <strong>Portfolio</strong> in your dashboard automatically. To type projects straight into the widget instead, set <strong>Content → Projects → Source</strong> to <strong>Add projects here</strong>.</span></li>
		</ol>
		<h3>Widget Settings</h3>
		<table>
			<thead>
				<tr><th>Panel</th><th>Settings</th></tr>
			</thead>
			<tbody>
				<tr><td>Content → Header</td><td>Eyebrow label, heading and its HTML tag, description.</td></tr>
				<tr><td>Content → Projects</td><td>Source (Portfolio admin section or projects added in the widget), type filter, number of projects.</td></tr>
				<tr><td>Content → Layout &amp; Features</td><td>Columns per device, filter tabs, tab counts, “All” label, browser frame, full-width background, button text and link.</td></tr>
				<tr><td>Style → Colours</td><td>Accent, accent light, button &amp; badge, headings, body text, borders, background gradient.</td></tr>
				<tr><td>Style → Cards &amp; Spacing</td><td>Section padding, content width, card gap, corner radius, screenshot shape.</td></tr>
				<tr><td>Style → Typography</td><td>Heading and card title fonts.</td></tr>
			</tbody>
		</table>
	</div>

	<!-- Basic shortcode -->
	<div class="doc-card">
		<h2>Basic Shortcode</h2>
		<p>No Elementor? Paste this into any page, post or widget to show your portfolio with a heading:</p>
		<pre>[suw_portfolio heading="Our Work"]</pre>
	</div>

	<!-- Attributes -->
	<div class="doc-card">
		<h2>All Shortcode Attributes</h2>
		<table>
			<thead>
				<tr><th>Attribute</th><th>Default</th><th>Description</th></tr>
			</thead>
			<tbody>
				<tr><td><code>eyebrow</code></td><td>—</td><td>Small pill label above the heading. Omit to hide.</td></tr>
				<tr><td><code>heading</code></td><td>—</td><td>Section heading. Omit to hide.</td></tr>
				<tr><td><code>heading_tag</code></td><td>h2</td><td><code>h1</code>, <code>h2</code>, <code>h3</code>, <code>h4</code> or <code>div</code>. Card titles automatically use the next heading level down.</td></tr>
				<tr><td><code>subheading</code></td><td>—</td><td>Short description under the heading.</td></tr>
				<tr><td><code>type</code></td><td>—</td><td>Only show these project type slugs, comma-separated. Example: <code>type="blog,business"</code>.</td></tr>
				<tr><td><code>limit</code></td><td>0</td><td>Maximum number of projects. <code>0</code> shows all.</td></tr>
				<tr><td><code>filter</code></td><td>yes</td><td>Show the filter tabs. Hidden automatically when there is only one type.</td></tr>
				<tr><td><code>counts</code></td><td>yes</td><td>Show the number of projects on each tab.</td></tr>
				<tr><td><code>all_label</code></td><td>All</td><td>Text of the first tab.</td></tr>
				<tr><td><code>browser</code></td><td>yes</td><td>Browser-window frame above each screenshot.</td></tr>
				<tr><td><code>full_width</code></td><td>yes</td><td>Background runs edge to edge (<code>yes</code>) or stays inside the content width (<code>no</code>).</td></tr>
				<tr><td><code>cta_text</code></td><td>—</td><td>Button text under the grid. Needs <code>cta_url</code>.</td></tr>
				<tr><td><code>cta_url</code></td><td>—</td><td>Button link, for example <code>/contact/</code>.</td></tr>
			</tbody>
		</table>
	</div>

	<!-- Examples -->
	<div class="doc-card">
		<h2>Usage Examples</h2>
		<div class="doc-examples">
			<div><small>Grid only — no header</small><pre>[suw_portfolio]</pre></div>
			<div><small>Full header — eyebrow, heading, description</small><pre>[suw_portfolio eyebrow="Portfolio" heading="Our Clients / Demos" subheading="Explore the websites we have designed and built for our clients."]</pre></div>
			<div><small>Only some project types, six projects</small><pre>[suw_portfolio heading="Business Websites" type="business" limit="6"]</pre></div>
			<div><small>With a call-to-action button</small><pre>[suw_portfolio heading="Recent Work" cta_text="Start your project" cta_url="/contact/"]</pre></div>
			<div><small>Minimal — no tabs, no browser frame</small><pre>[suw_portfolio filter="no" browser="no"]</pre></div>
			<div><small>Boxed section inside the content width</small><pre>[suw_portfolio heading="Portfolio" full_width="no"]</pre></div>
		</div>
	</div>

	<!-- Projects -->
	<div class="doc-card">
		<h2>Adding Your Projects</h2>
		<ol class="doc-steps">
			<li><span>Go to <strong>Portfolio → Add New Project</strong> and enter the project name as the title.</span></li>
			<li><span>Paste the live site address into the <strong>Website URL</strong> box.</span></li>
			<li><span>Set a screenshot as the <strong>Project screenshot</strong> (featured image). About 1600 × 1000 px works best. Add descriptive alt text in the Media Library for better image SEO.</span></li>
			<li><span>Choose a <strong>Project Type</strong> such as Blog or Business. Each type becomes a filter tab.</span></li>
			<li><span>Use the <strong>Order</strong> field to control the order — lower numbers come first.</span></li>
		</ol>
	</div>

	<!-- Demo content -->
	<div class="doc-card">
		<h2>Sample Projects and Demo Content</h2>
		<h3>Sample projects on a new install</h3>
		<p>Until you publish your first project, the portfolio shows three built-in sample projects — Sample Blog Project, Sample Business Project and Sample Corporate Project — so you can style the section right away. They are not saved in your database and disappear automatically once a real project is published. Logged-in editors see a short note about this; visitors do not.</p>
		<h3>Import the demo</h3>
		<p>Go to <strong>Portfolio → Docs &amp; Demo → Demo content</strong> and click <strong>Import demo content</strong>. It creates the three sample projects as real, editable projects, adds their screenshots to the Media Library, creates the Blog and Business project types and a “Portfolio Demo” page. The screenshots ship with the plugin, so nothing is downloaded from other websites.</p>
		<h3>Delete the demo</h3>
		<p>When you are ready for your own work, open the same tab and click <strong>Delete demo content</strong>. It removes only what the import created — the projects, their images, the demo page and any project types left empty. Projects you added yourself are never touched.</p>
	</div>

	<!-- SEO -->
	<div class="doc-card">
		<h2>SEO and Performance</h2>
		<ul>
			<li>Real <code>&lt;img&gt;</code> screenshots with alt text, width and height (no layout shift) and responsive <code>srcset</code>.</li>
			<li>Image loading is handled by WordPress core: images likely to be in view load first, the rest are lazy loaded.</li>
			<li>Correct heading hierarchy — card titles sit one level below the section heading.</li>
			<li>Schema.org <code>ItemList</code> structured data (JSON-LD) describing your projects. Turn it off with the <code>suwpf_schema_enabled</code> filter if your SEO plugin already covers it.</li>
			<li>About 7 KB of CSS and 2 KB of deferred JavaScript with no jQuery, loaded only on pages that show a portfolio.</li>
			<li>Accessible filter buttons (<code>aria-pressed</code>), keyboard-focusable cards and support for “reduce motion”.</li>
		</ul>
	</div>

	<!-- FAQ -->
	<div class="doc-faq">
		<h2>Frequently Asked Questions</h2>
		<div>
			<strong>Do I need Elementor?</strong>
			<p>No. Elementor adds a drag-and-drop widget, but the <code>[suw_portfolio]</code> shortcode works in any editor.</p>
		</div>
		<div>
			<strong>Where do the three projects on a new portfolio come from?</strong>
			<p>They are built-in sample projects so a new portfolio is never blank. They disappear automatically once you publish your first project.</p>
		</div>
		<div>
			<strong>How do I remove the demo content?</strong>
			<p>Open <strong>Portfolio → Docs &amp; Demo → Demo content</strong> and click <strong>Delete demo content</strong>. Your own projects stay.</p>
		</div>
		<div>
			<strong>How do I add a new filter tab?</strong>
			<p>Create a project type under <strong>Portfolio → Project Types</strong> and assign it to at least one project.</p>
		</div>
		<div>
			<strong>Can I show only some project types?</strong>
			<p>Yes. In Elementor use <strong>Only these types</strong>; with the shortcode use <code>type="blog,business"</code>.</p>
		</div>
		<div>
			<strong>Why do project links use rel="nofollow"?</strong>
			<p>Cards link to external client sites, and <code>nofollow</code> is the safe default for outbound links.</p>
		</div>
		<div>
			<strong>What happens when I uninstall the plugin?</strong>
			<p>The demo content is removed. Projects you added yourself stay in the database, so reinstalling brings your portfolio back.</p>
		</div>
	</div>

	<!-- Changelog -->
	<div class="doc-changelog">
		<h2>Changelog</h2>
		<div>
			<span>1.0.0</span>
			<ul>
				<li>Initial release.</li>
				<li>Elementor widget and <code>[suw_portfolio]</code> shortcode.</li>
				<li>Portfolio admin section with project types and website URL.</li>
				<li>Built-in sample projects until the first project is published.</li>
				<li>One-click demo import and delete using bundled images.</li>
				<li>Schema.org ItemList structured data, responsive images and accessible filter tabs.</li>
			</ul>
		</div>
	</div>

	<!-- More plugins -->
	<div class="doc-card">
		<h2>More Free Plugins by SmartUpWorld</h2>
		<div class="doc-links">
			<a href="<?php echo esc_url( home_url( '/smartupworld-post-slider/' ) ); ?>"><strong>SmartUpWorld Post Slider</strong><span>Responsive, accessible post carousel with one shortcode.</span></a>
			<a href="<?php echo esc_url( home_url( '/smartupworld-feed-control/' ) ); ?>"><strong>SmartUpWorld Feed &amp; Author Control</strong><span>Disable, redirect or noindex RSS feeds and author archives.</span></a>
			<a href="<?php echo esc_url( home_url( '/quick-duplicate-post/' ) ); ?>"><strong>Quick Duplicate Post</strong><span>Duplicate posts and pages in one click.</span></a>
		</div>
	</div>

</div>
</main>

<?php
wp_print_inline_script_tag(
	wp_json_encode(
		array(
			'@context'            => 'https://schema.org',
			'@type'               => 'SoftwareApplication',
			'name'                => 'SmartUpWorld Portfolio Showcase',
			'description'         => 'Free WordPress plugin: a filterable portfolio grid with browser-style project cards, an Elementor widget and a shortcode, with one-click demo content.',
			'applicationCategory' => 'DesignApplication',
			'operatingSystem'     => 'WordPress 6.0 or later',
			'softwareVersion'     => '1.0.0',
			'url'                 => get_permalink(),
			'downloadUrl'         => $suwpf_wporg,
			'screenshot'          => $suwpf_shot,
			'license'             => 'https://www.gnu.org/licenses/gpl-2.0.html',
			'isAccessibleForFree' => true,
			'offers'              => array(
				'@type'         => 'Offer',
				'price'         => '0',
				'priceCurrency' => 'USD',
			),
			'author'              => array(
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
