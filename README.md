# Omega Design

A flexible, full-site-editing (FSE) WordPress block theme with built-in mega menu navigation, WooCommerce support, per-page display controls, and a light/dark color mode system — all managed from a dedicated **Omega Design** admin dashboard.

Omega Design emphasizes simplicity and adaptability. It offers flexible design options, supported by a variety of block patterns for different page types (services, landing pages, blogs, portfolios, online magazines, or business/e-commerce sites), with layouts ranging from text-focused to image-heavy.

---

## Requirements

| Requirement       | Minimum |
|--------------------|---------|
| WordPress          | 5.8 (6.7+ recommended, tested up to 6.9) |
| PHP                | 7.4 |
| WooCommerce        | Optional — required only for shop features |

The theme checks both WordPress and PHP versions on load and shows an admin notice if either is below the required minimum.

---

## Features

- **Full Site Editing (FSE)** block theme built on `theme.json`, with templates and template parts for the homepage, blog, search, 404, single post/page, and privacy policy.
- **Mega Menu system** — a dedicated `megamenu` nav menu location, rendered from `parts/megamenu.html`, with a set of ready-made mega menu block patterns (`pattern/megamenu-*.php`): simple, columns, icon grid, featured, and posts layouts. Configurable from Appearance → Menus and the Omega Design dashboard.
- **Color Mode (Light / Dark / Auto)** — a site-wide setting (Automatic / Always Light / Always Dark) that follows each visitor's system preference or locks the whole site to one look, including support for a separate dark-mode logo.
- **Custom logo support**, including an optional dedicated dark-mode logo shown automatically when dark color mode is active (light/dark logo pair swapped via CSS).
- **Per-page/post display controls** (editor sidebar panels, stored as post meta):
  - **Page Width** — override the default content width for an individual post/page.
  - **Hide Page Title** — hide the title block on specific posts/pages.
  - **Background Color** — set a custom (and optional dark-mode) background color per post/page.
- **Blog sidebar** — optional widget-ready sidebar for category archives, with position (left/right) and width (20–50%) controls, managed from the Omega Design dashboard.
- **WooCommerce integration** — full theme support (`add_theme_support('woocommerce')`), product gallery zoom/lightbox/slider, and dedicated block templates for shop, product, cart, checkout, my-account, and order-confirmation pages. Includes a custom-styled "please log in" notice on the checkout block for guests, with optional integration for the "My Login Form" plugin's registration page.
- **Omega Design admin dashboard** (top-level admin menu) with pages for:
  - Dashboard (setup overview)
  - Site Builder
  - Settings
  - Appearance (logo upload, color mode)
  - Menus
  - Widgets
- **Custom "Omega Design" block category** registered in the block editor for any theme-provided blocks/patterns.
- **Custom uploads directory** (`wp-content/uploads/omega-design/`) with protected subfolders for images, fonts, logs, backups, and exports — created automatically on theme activation, each with an `index.php` and `.htaccess` to prevent directory listing.
- **Translation-ready** (`omega-design` text domain, loads from `/languages`), with RTL language support.
- Accessibility-ready, wide/full-width block alignment, custom color palette (the default WordPress core color/gradient palette is removed in favor of the theme's own).

---

## Installation

1. Download or clone this repository.
2. Copy (or symlink) the theme folder into your WordPress installation's `wp-content/themes/` directory as `omega-design`.
3. In WordPress admin, go to **Appearance → Themes** and activate **Omega Design**.
4. On activation, the theme automatically creates its uploads directory structure under `wp-content/uploads/omega-design/`.

### Installing via Git directly into a site

```bash
cd wp-content/themes
git clone git@github.com:Omega-Design-360/OMEGA-DESIGN-THEME.git omega-design
```

Then activate it from **Appearance → Themes** as above.

### Optional: build tooling

The theme ships with `@wordpress/scripts` as a dev dependency for any future editor/JS build steps. If you plan to modify the JS in `assets/js/`, install dependencies with:

```bash
npm install
```

(No build step is currently required to use the theme as-is — the assets in `assets/js/` and `assets/css/` are served directly.)

---

## Getting Started / How to Use

1. **Activate the theme** as described above.
2. Open the **Omega Design** menu in the WordPress admin sidebar (dashboard icon) — this is the central place to configure the theme:
   - **Dashboard** — quick overview of your current setup.
   - **Appearance** — upload your site logo, and optionally a separate dark-mode logo.
   - **Settings** — configure the Color Mode (Automatic / Always Light / Always Dark) and the blog Sidebar (enable/disable, position, width).
   - **Site Builder / Menus / Widgets** — jump directly to the relevant Site Editor / Menus / Widgets screens.
3. **Set up navigation**:
   - Go to **Appearance → Menus** and assign menus to the **Primary Menu**, **Footer Menu**, and **Mega Menu** locations.
   - Use the mega menu block patterns (Columns, Icon Grid, Featured, Posts, Simple) inside the `parts/megamenu.html` template part (via the Site Editor) to build out your mega menu's dropdown content.
4. **Customize global styles** (colors, typography, spacing) via **Appearance → Editor → Styles** in the Site Editor — this is the recommended place for site-wide design changes, rather than editing `style.css` directly.
5. **Per-page options**: when editing a post or page in the block editor, open the sidebar panel to find Omega Design's **Page Width**, **Hide Page Title**, and **Background Color** controls.
6. **WooCommerce sites**: activate WooCommerce and the theme will automatically pick up dedicated shop/product/cart/checkout templates — no extra setup required. Configure store pages as usual under **WooCommerce → Settings**.
7. **Blog sidebar**: enable it from the Omega Design → Settings page, then add widgets to the **Blog Sidebar** widget area under **Appearance → Widgets**.

---

## Theme Structure

```
omega-design/
├── admin/                  Admin-facing assets/screens
├── assets/
│   ├── css/                Frontend & admin stylesheets (style, admin, megamenu, color-mode, background-color-editor)
│   └── js/                 Frontend & editor scripts (editor, megamenu, color-mode, content-width, title-toggle, background-color, checkout-auth-notice)
├── blocks/                 Custom block definitions
├── includes/
│   ├── core/               Theme bootstrap: core.php (autoloader/init), hooks.php (enqueue & setup), loader.php
│   ├── customizer/         Theme settings: megamenu, color_mode, sidebar, content_width, title, background_color, menus (admin pages), patterns
│   ├── admin/               Admin dashboard functionality
│   ├── metabox/             Post/page meta box registrations
│   ├── helpers/             Shared helper functions
│   ├── parts/ / pattern/ / templates/   PHP-registered template parts / patterns / templates
├── parts/                   HTML template parts (header, footer, sidebar, megamenu, hero-sections, featured, testimonials, woocommerce)
├── pattern/                 Mega menu block patterns (PHP-registered)
├── templates/               Block templates (front-page, home, index, single, page, archive, category, search, 404, privacy-policy, WooCommerce templates)
├── widgets/                 Custom widget definitions
├── woocommerce/             WooCommerce template overrides
├── functions.php            Constants, uploads directory setup, compatibility checks, nav menu registration
├── theme.json               Global styles & settings (FSE)
├── style.css                 Theme header / stylesheet metadata
└── package.json              @wordpress/scripts dev dependency
```

---

## Support & Contributing

This is a private theme repository for **Omega Design**. For bugs, feature requests, or contributions, please open an issue or pull request on this repository.

## License

GNU General Public License v2 or later. See [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html).
