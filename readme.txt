=== SmartUpWorld Post Slider ===
Contributors: smartupworld
Tags: post slider, carousel, owl carousel, posts, shortcode
Requires at least: 5.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight, accessible Owl Carousel post slider. Display your latest posts anywhere using the [suwps_post_slider] shortcode.

== Description ==

SmartUpWorld Post Slider lets you display your WordPress posts in a beautiful, responsive carousel using a simple shortcode — no coding required.

**Features:**

* Simple shortcode — paste it anywhere: posts, pages, widgets, templates
* Responsive — 1 column on mobile, 2 on tablet, 3 on desktop
* Optional section header with eyebrow pill, title, gradient underline, and subtitle
* Filter by category slug or ID
* Show specific posts by ID
* Exclude the current post (perfect for single post templates)
* Navigation arrows with SVG icons
* Pagination dots
* Autoplay with hover-pause
* Lazy loading on images after the first slide (LCP-safe eager loading on slide 1)
* Full ARIA accessibility — labelled buttons, dots, keyboard support
* No configuration needed — works out of the box
* Bundled Owl Carousel — no CDN dependency
* Lightweight — assets only load where the shortcode is used

**Basic usage:**

`[suwps_post_slider]`

**With a header:**

`[suwps_post_slider eyebrow="From The Blog" title="Latest Insights" subtitle="Practical guides on website care, speed and security."]`

**For single post templates (exclude current, no arrows):**

`[suwps_post_slider title="Related Articles" count="6" skip="current" nav="false" underline="false"]`

Full documentation: [smartupworld.com/smartupworld-post-slider/](https://smartupworld.com/smartupworld-post-slider/)

== Installation ==

1. Upload the `smartupworld-post-slider` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Paste `[suwps_post_slider]` into any post, page, or widget.
4. Visit **Post Slider** in your WordPress admin menu for all shortcode options and examples.

== Frequently Asked Questions ==

= What shortcode do I use? =
Use `[suwps_post_slider]` anywhere in your site. The admin menu page lists every available attribute with copy-ready examples.

= Can I filter by category? =
Yes. Use `category="your-category-slug"` or `category="14"` (numeric ID). Example: `[suwps_post_slider category="tech" count="6"]`

= How do I show specific posts? =
Use the `ids` attribute with comma-separated post IDs: `[suwps_post_slider ids="12,45,67"]`. This overrides `count` and `category`.

= Can I hide the navigation arrows or dots? =
Yes. Set `nav="false"` to hide arrows or `dots="false"` to hide dots. Set both to `false` for a minimal auto-playing slider.

= Does it work on single post templates? =
Yes. Add `skip="current"` to remove the current post from the slider results, ideal for a "Related Articles" section.

= Does it conflict with my theme's Owl Carousel? =
The plugin registers its assets under unique handles (`suw-owl-carousel`, `suw-post-slider`). If your theme already enqueues Owl Carousel under the same handle, WordPress deduplicates automatically so the library only loads once.

= Where is the documentation? =
Full documentation is at [smartupworld.com/smartupworld-post-slider/](https://smartupworld.com/smartupworld-post-slider/). The admin **Post Slider** page also contains all attributes and copy-ready shortcode examples.

== Screenshots ==

1. Admin documentation page with shortcode reference and copy buttons.
2. Post slider on the front end — full header with eyebrow, title, underline and subtitle.
3. Slider on a single post page showing related articles without the section header.

== Changelog ==

= 1.0.1 =
* Renamed the shortcode tag to [suwps_post_slider] for a unique, conflict-free prefix.
* Moved admin page CSS and JavaScript into properly enqueued files.
* Renamed the "exclude" attribute to "skip".

= 1.0.0 =
* Initial release.
* Bundled Owl Carousel 2.3.4 — no CDN dependency.
* Shortcode attributes: eyebrow, title, subtitle, underline, count, category, ids, exclude, layout, nav, dots.
* ARIA accessibility improvements — labelled nav buttons and dots.
* LCP-safe eager loading on first slide image.
* Admin documentation page with copy-ready examples and SmartUpWorld branding.

== Upgrade Notice ==

= 1.0.1 =
The shortcode tag changed to [suwps_post_slider]. Update any existing usage.

= 1.0.0 =
Initial release.
