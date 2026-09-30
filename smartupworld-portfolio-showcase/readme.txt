=== SmartUpWorld Portfolio Showcase ===
Contributors: smartupworld
Tags: portfolio, filterable portfolio, portfolio grid, elementor, showcase
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Filterable portfolio grid with browser-style project cards. Elementor widget and shortcode, one-click demo, fast and SEO friendly.

== Description ==

**SmartUpWorld Portfolio Showcase** turns your client websites, demos and case studies into a clean, filterable portfolio grid. Every project card looks like a small browser window with the site's address, a screenshot, a category badge and a link to the live site — ideal for web design agencies, freelancers, developers and theme shops.

It works as an **Elementor widget** and as a **shortcode**, so you can use it with Elementor, the block editor, the Classic editor or directly in a theme template.

= Works the moment you activate it =

Drop the widget or shortcode on a page and three sample projects appear straight away, so you can style the section before you add real work. The samples vanish automatically as soon as you publish your first project. No setup screens, no settings to save.

= Features =

* **Elementor widget** "Portfolio Showcase" with content, layout, colour, spacing and typography controls.
* **Shortcode** `[suw_portfolio]` for any editor, widget area or theme.
* **Portfolio admin section** — add projects with a title, screenshot, website URL and project type.
* **Filter tabs** built from your project types, with optional counts.
* **Responsive grid** — 1 to 4 columns per device, full-width background with a centred container.
* **One-click demo content** — import six editable projects in three types (Blog, Business, E-commerce) plus a demo page, and delete them again with one click.
* **Bundled sample images** — nothing is downloaded from third-party sites.
* **Colours via CSS custom properties** — match any brand from the Elementor Style tab.
* Card order controlled by the standard Order field.

= SEO and performance =

* Real `<img>` screenshots with `alt` text, `width`/`height` (no layout shift) and responsive `srcset`. Loading is left to WordPress core, which eager-loads images likely to be in view and lazy-loads the rest — good for Core Web Vitals.
* Correct heading hierarchy — card titles automatically sit one level below the section heading.
* Schema.org `ItemList` structured data (JSON-LD) for your projects. Disable it with the `suwpf_schema_enabled` filter if your SEO plugin already covers it.
* One small stylesheet and one tiny deferred script with no jQuery dependency, loaded only on pages that show a portfolio.
* Accessible: filter buttons use `aria-pressed`, cards are keyboard focusable, external links announce that they open in a new tab, and animations respect "reduce motion".

= Documentation =

Everything is explained inside WordPress under **Portfolio → Docs & Demo**. The full online guide is at [smartupworld.com/smartupworld-portfolio-showcase](https://smartupworld.com/smartupworld-portfolio-showcase/).

= Shortcode example =

`[suw_portfolio eyebrow="Portfolio" heading="Our Clients / Demos" subheading="Sites we have designed and built." type="blog,business" limit="6" cta_text="Start your project" cta_url="/contact/"]`

All attributes are optional: `eyebrow`, `heading`, `heading_tag`, `subheading`, `type`, `limit`, `filter`, `counts`, `all_label`, `browser`, `full_width`, `cta_text`, `cta_url`.

== Installation ==

1. In your dashboard go to **Plugins → Add New**, search for "SmartUpWorld Portfolio Showcase" and click **Install Now**, or upload the zip under **Plugins → Add New → Upload Plugin**.
2. Click **Activate**.
3. **Elementor:** edit a page, search the widget panel for "Portfolio Showcase" and drag it onto the page.
4. **Any other editor:** add `[suw_portfolio heading="Our Work"]` to a page.
5. Add your projects under **Portfolio → Add New Project**, or import the demo content under **Portfolio → Docs & Demo → Demo content**.

== Frequently Asked Questions ==

= Do I need Elementor? =

No. Elementor adds a drag-and-drop widget, but the `[suw_portfolio]` shortcode works in any editor.

= Where do the three projects on a new portfolio come from? =

They are built-in sample projects so a new portfolio is never empty. They are not stored in your database and disappear automatically once you publish your first project. Logged-in editors see a small note explaining this; visitors do not.

= How do I remove the demo content? =

Go to **Portfolio → Docs & Demo → Demo content** and click **Delete demo content**. Only the projects, images, project types and page that the demo import created are removed — your own projects are never touched.

= Does the demo import download anything? =

No. The sample screenshots ship with the plugin and are copied into your Media Library, so the import works offline and on firewalled servers.

= How do I add a project type (filter tab)? =

Go to **Portfolio → Project Types**, or create one directly in the project editor. Each type with at least one project becomes a filter tab.

= Can I show only some project types? =

Yes. In Elementor use **Content → Projects → Only these types**. With the shortcode use the `type` attribute with comma-separated slugs, for example `type="blog,business"`.

= Why do project links use rel="nofollow"? =

Portfolio cards link to external client sites. `nofollow` keeps your page's link equity on your own site and is the safe default for outbound links.

= Will it slow down my site? =

No. The CSS (about 7 KB) and the script (about 2 KB, deferred) load only on pages that contain a portfolio, and screenshots below the fold are lazy loaded.

= What happens when I uninstall the plugin? =

Uninstalling deletes the demo content. Projects you added yourself stay in the database, so reinstalling brings your portfolio back.

== Screenshots ==

1. The portfolio grid with filter tabs and browser-style project cards.
2. The Docs & Demo page in the WordPress dashboard, with one-click demo import and delete.
3. The Portfolio Showcase widget in the Elementor editor.

== Changelog ==

= 1.0.0 =
* Initial release.
* Elementor widget and `[suw_portfolio]` shortcode.
* Portfolio admin section with project types and website URL.
* Built-in sample projects until the first project is published.
* One-click demo import and delete using bundled images.
* Schema.org ItemList structured data, responsive lazy-loaded images and accessible filter tabs.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
