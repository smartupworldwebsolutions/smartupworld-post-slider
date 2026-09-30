=== SmartUpWorld Memes ===
Contributors: smartupworld
Tags: memes, custom post type, humor, web design, wordpress
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A dedicated Memes section for your WordPress site — separate from blog posts, with category filtering and SEO-friendly templates.

== Description ==

**SmartUpWorld Memes** adds a Memes custom post type so your meme content stays completely separate from your blog articles. Publish memes, organise them by category and let your visitors browse or filter them — all without touching your existing posts.

= Features =

* **Meme post type** — its own admin menu, URLs at `/memes/` and full block editor support.
* **Meme Categories** taxonomy — hierarchical, with its own `/meme-category/` slugs.
* **Archive page** (`/memes/`) — responsive grid with live category filter tabs.
* **Single meme page** — large image, breadcrumb, prev/next navigation and Schema.org `ImageObject` structured data.
* **Plugin templates** — fallback templates are built in, so the pages work on any theme straight away. Override them by placing `archive-suw_meme.php` or `single-suw_meme.php` in your theme root.
* **Lightweight** — one CSS file (~4 KB), loaded only on meme pages.

= SEO =

* Schema.org `ImageObject` JSON-LD on every single meme page.
* Correct heading hierarchy and breadcrumb nav.
* Responsive `srcset` images with proper `alt` text.
* Accessible category tabs.

== Installation ==

1. Upload the plugin zip under **Plugins → Add New → Upload Plugin** and click **Install Now**.
2. Click **Activate**. A **Memes** menu appears in the dashboard.
3. Go to **Memes → Add New Meme**, upload the meme image as the featured image, add a title and assign a category.
4. Visit `/memes/` on your site to see the grid.

== Frequently Asked Questions ==

= Will memes appear in my blog? =

No. The Meme post type is completely separate from `post`. It has its own admin menu, its own URLs and its own archive — it never appears in blog queries unless you add it explicitly.

= Can I use my own templates? =

Yes. Place `archive-suw_meme.php` and/or `single-suw_meme.php` in your active theme root and the plugin templates will be ignored.

= What URL slug does it use? =

Memes live at `yoursite.com/memes/` and categories at `yoursite.com/meme-category/slug/`. After activation go to **Settings → Permalinks** and click **Save Changes** once to flush rewrite rules.

= What happens when I deactivate or uninstall? =

Deactivating leaves your meme posts and categories untouched. Uninstalling removes only the plugin's files — your content stays in the database.

== Screenshots ==

1. The meme archive grid with category filter tabs.
2. A single meme page with breadcrumb and prev/next navigation.

== Changelog ==

= 1.0.0 =
* Initial release.
* Meme custom post type and Meme Category taxonomy.
* Archive grid with category filter tabs.
* Single meme template with Schema.org ImageObject structured data.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
