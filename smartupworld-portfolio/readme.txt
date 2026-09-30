=== SmartUpWorld Portfolio Showcase ===
Contributors: smartupworld
Tags: portfolio, elementor, showcase, projects, filter
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modern, filterable portfolio grid with browser-style project cards. Elementor widget and shortcode.

== Description ==

Show client websites and demos in a clean, filterable grid. Each card looks like a small browser window with the site's address, a screenshot, a type badge and a link to the live site.

* **Elementor widget** "Portfolio Showcase" (category: SmartUpWorld) with content, layout, colour, spacing and typography controls.
* **Shortcode** `[suw_portfolio]` for any editor or theme.
* **Portfolio admin section** — add projects with a title, screenshot (featured image), website URL and type.
* Filter tabs with counts, full-width background with a centred container, responsive 1–4 columns.
* Real `<img>` screenshots with alt text and lazy loading (SEO friendly); accessible filter buttons; honours "reduce motion".
* Lightweight: one small CSS file and one tiny script with no jQuery dependency, loaded only where a portfolio appears.

== Installation ==

1. Upload the `smartupworld-portfolio` folder to `/wp-content/plugins/`, or install the zip via Plugins → Add New → Upload.
2. Activate the plugin.
3. Elementor: drag **Portfolio Showcase** onto a page. Add projects right in the widget, or switch *Source* to *Portfolio admin section*.
4. Without Elementor: add projects under **Portfolio → Add New Project**, then place `[suw_portfolio heading="Our Work"]` on any page.

== Demo content ==

Go to **Portfolio → Docs & Demo → Demo content** and click **Import demo content**. It adds 7 sample projects with screenshots, the Blog and Business project types, and a "Portfolio Demo" page. Running it again skips anything already imported and retries screenshots that failed to download.

The same content is also included as `demo/portfolio-demo.xml` for Tools → Import → WordPress.

== Documentation ==

Full documentation is inside WordPress under **Portfolio → Docs & Demo** (also linked from the Plugins screen).

== Shortcode ==

`[suw_portfolio eyebrow="Portfolio" heading="Our Clients / Demos" subheading="…" type="blog,business" limit="6" filter="yes" counts="yes" browser="yes" all_label="All" cta_text="Start your project" cta_url="#contact" heading_tag="h2" full_width="yes"]`

All attributes are optional. `type` takes Project Type slugs.

== Changelog ==

= 1.0.0 =
* Initial release.
