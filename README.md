# Omega Design

A flexible, full-site-editing (FSE) WordPress block theme with block-native mega menu navigation, a complete WooCommerce storefront system (5 selectable product-page layouts, a redesigned cart and checkout), classic header/footer style systems, per-page display controls, a light/dark color mode system, and its own icon library — all managed from a dedicated **Omega Design** admin dashboard.

Omega Design emphasizes simplicity and adaptability. It offers flexible design options, supported by a variety of block patterns for different page types (services, landing pages, blogs, portfolios, online magazines, or business/e-commerce sites), with layouts ranging from text-focused to image-heavy.

---

## Contents

- [Requirements](#requirements)
- [Features](#features)
  - [Site foundation](#site-foundation)
  - [Header, footer & navigation](#header-footer--navigation)
  - [Per-page display controls](#per-page-display-controls)
  - [WooCommerce](#woocommerce)
  - [Icons](#icons)
  - [Omega Design admin dashboard](#omega-design-admin-dashboard)
- [Installation](#installation)
- [Getting Started / How to Use](#getting-started--how-to-use)
- [Theme Structure](#theme-structure)
- [Support & Contributing](#support--contributing)
- [License](#license)

---

## Requirements

| Requirement       | Minimum |
|--------------------|---------|
| WordPress          | 6.7 (tested up to 6.9) |
| PHP                | 7.4 |
| WooCommerce        | Optional — required only for shop features (built and tested against 11.0) |

The theme checks both WordPress and PHP versions on load and shows an admin notice if either is below the required minimum. The theme's own version number is read directly from this file's `Version:` header (below), so it stays correct everywhere it's shown (the admin dashboard badge, asset cache-busting) without needing to be updated in more than one place.

---

## Features

### Site foundation

- **Full Site Editing (FSE)** block theme built on `theme.json`, with templates and template parts for the homepage, blog, search, 404, single post/page, and privacy policy.
- **Color Mode (Light / Dark / Auto)** — a site-wide setting that follows each visitor's system preference or locks the whole site to one look. Every color in `theme.json` has a matching dark-mode counterpart, switched by `color-mode.css` — no separate dark palette to keep in sync by hand.
- **Custom logo support**, including an optional dedicated dark-mode logo shown automatically when dark color mode is active (light/dark logo pair swapped via CSS, applied everywhere the logo renders — including the classic header styles below).
- **Blog sidebar** — optional block-editable sidebar for category archives, with position (left/right) and width (20–50%) controls.
- **Custom "Omega Design" block category** registered in the block editor for theme-provided blocks/patterns.
- **Custom uploads directory** (`wp-content/uploads/omega-design/`) with protected subfolders for images, fonts, logs, backups, and exports — created automatically on theme activation, each with an `index.php` and `.htaccess` to prevent directory listing.
- **Translation-ready** (`omega-design` text domain, loads from `/languages`), with RTL language support.
- Accessibility-ready, wide/full-width block alignment, custom color palette (the default WordPress core color/gradient palette is removed in favor of the theme's own).

### Header, footer & navigation

- **Mega Menu system** — a dedicated `megamenu` nav menu location, with ready-made mega menu block patterns (`pattern/megamenu-*.php`): Simple, Columns, Icon Grid, Featured, and Posts layouts. Build the dropdown content for each once as its own post under **Omega Design → Mega Menus**, then attach it to any nav item from the Site Editor's Navigation panel.
- **Classic Header — 5 alternate layouts** (Minimal Bar, Centered, Split, Boxed Nav, Bold Accent), as a fast, hand-written alternative to the block-based Site Editor header — pick one from **Omega Design → Settings → Header & Announcement**, or leave it on "Block Navigation" to keep editing the header visually in the Site Editor. Every classic style supports:
  - A sticky option (hides on scroll down, reveals on scroll up)
  - Appearance overrides (background/text color, font family, menu text size, header height) that fall back to the theme's own Global Styles when left blank
  - The site's mini-cart and account icons automatically, when WooCommerce is active
- **Classic Footer — 5 alternate layouts** (Simple, Columns, Centered, Newsletter CTA, Bold), configured the same way from **Settings → Footer**: pick a classic menu, an optional tagline, custom copyright text (`{year}` is replaced automatically), and — for the Newsletter CTA style — a call-to-action button.
- **Announcement Bar** — an independent bar shown above the header on every page (phone number, promo message, social links, or any other HTML), with its own background/text color and an optional visitor-dismissible toggle.
- **Header & Footer visibility** — hide either one on a specific page or post from that page's own editor sidebar, without affecting the rest of the site.

### Per-page display controls

Available from the block editor's sidebar (Page Settings panel) on individual posts/pages, stored as post meta:

- **Page Width** — override the default content width (Normal / Wide / Full) for an individual post/page.
- **Hide Page Title** — hide the title block on specific posts/pages.
- **Background Color** — set a custom (and optional dark-mode) background color per post/page.
- **Featured Image visibility** — show or hide a post/page's featured image independently of its title.
- **Hide Header / Hide Footer** — per-page overrides of the Classic Header/Footer visibility above.

### WooCommerce

Full theme support (`add_theme_support('woocommerce')`) plus a from-scratch storefront design system:

- **5 selectable Product Page layouts** — a single product page can be rendered as any of the following, switched instantly from **Settings → WooCommerce** with a live preview of each option (no code, no page rebuild):

  | Layout | Look |
  |---|---|
  | **Gallery Feature** *(default)* | Large image/screenshot stage with an airy, unboxed buy column — Poppins display type. |
  | **Command Deck** | The familiar dense e-commerce layout: thumbnail rail, elevated buy panel, a spec grid below. |
  | **Split Stage** | A bold, full-bleed dark photography panel for the gallery against a light info panel — Jost display type. |
  | **Spec Sheet** | A software-pricing-table shape: version/license/shipping facts as a real table, not a badge row. |
  | **Boutique** | Maximal restraint — a single centered column, one oversized price, no card chrome. |

  Every layout is assembled from WooCommerce's own real blocks (gallery, price, rating, stock, the variation-aware add-to-cart form), so variations, stock status, and cart behavior work identically regardless of which one is active — only the surrounding structure and typography change. Each layout's "facts" panel adapts automatically to the product: physical products show weight/dimensions/shipping class (when set), while virtual/downloadable products show delivery, download-limit, and access-expiry details instead — nothing fabricated, a field is simply omitted when the underlying data isn't set.
- **Redesigned Cart page** — a two-column layout (line items + a sticky order-summary card), restyled quantity stepper and coupon form, a proper sale badge, and small interaction polish (a spring animation on quantity +/‑, an optimistic fade on removing an item, and a pulse on the total when it actually changes).
- **Redesigned Checkout page** — numbered steps for each section (Contact, Shipping, Payment, …), selectable-card styling for shipping/payment options, and the Terms checkbox + Place Order button relocated into the order-summary sidebar so the primary action always sits next to the total it's confirming.
- **Guest checkout notice** — a custom-styled "please log in to continue" prompt on the checkout block for guests when guest checkout is disabled, with optional integration for the "My Login Form" plugin's registration page.
- Product gallery zoom/lightbox/slider support, and dedicated block templates for shop, product, cart, checkout, my-account, and order-confirmation pages.

### Icons

- **Omega Icon block** (`omega-design/icon`) — a self-contained icon picker with the theme's own animated, multi-part stroke icons (not flat glyphs), with a built-in hover choreography. Add the `omega-ico-host` class to an ancestor block to trigger it.
- **Material Symbols library** — the full Material Symbols Outlined catalog (~4,000 icons) registered into WordPress core's native icon picker (the `core/icon` block's "Icon library" dropdown), so it's available anywhere that block is used without a third-party icon plugin.

### Omega Design admin dashboard

A top-level **Omega Design** admin menu, entirely reskinned to match the site's own light/dark Color Mode rather than carrying a separate admin-only palette:

- **Dashboard** — Site Logo, Color Mode, and Quick Links at a glance, plus status cards for WooCommerce, Mega Menu, Sidebar Widgets, and environment compatibility.
- **Settings** — a sidebar-tabbed screen (General · Header & Announcement · Menus & Pages · Footer · WooCommerce) instead of one long scrolling page, so a short card is never stretched to match a much taller neighbor. Includes a Mega Menu resource list (your actual mega menus, with edit links) and a WooCommerce Pages status card (Shop/Cart/Checkout/My Account, each with a live "set or missing" badge).
- **Site Builder / Menus / Widgets / Appearance** — direct links to the relevant Site Editor / Menus / Widgets / Customizer screens.
- The Dashboard/Settings chips in the admin header highlight whichever one you're currently on.

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

### Assets

The theme serves its JavaScript and CSS directly from `assets/`; no npm install or build step is required.

---

## Getting Started / How to Use

1. **Activate the theme** as described above.
2. Open the **Omega Design** menu in the WordPress admin sidebar (dashboard icon) — this is the central place to configure the theme:
   - **Dashboard** — quick overview of your current setup.
   - **Settings → General** — upload your site logo (and optionally a separate dark-mode logo), the site-wide Color Mode, and the blog Sidebar.
   - **Settings → Header & Announcement** — pick Block Navigation or one of the 5 Classic Header styles, and configure the Announcement Bar.
   - **Settings → Menus & Pages** — your published Mega Menus, and pointers to the per-page Title/Width/Sidebar controls.
   - **Settings → Footer** — pick Block Footer or one of the 5 Classic Footer styles.
   - **Settings → WooCommerce** — pick the active Product Page layout, and check your store's core pages are all set.
   - **Site Builder / Menus / Widgets** — jump directly to the relevant Site Editor / Menus / Widgets screens.
3. **Set up navigation**:
   - Go to **Appearance → Menus** and assign menus to the **Primary Menu**, **Footer Menu**, and **Mega Menu** locations (or to a Classic Header/Footer's own menu picker, if you're using one of those styles instead).
   - Build mega menu dropdown content under **Omega Design → Mega Menus** using the Simple / Columns / Icon Grid / Featured / Posts patterns, then attach each to a nav item from the Site Editor's Navigation panel.
4. **Customize global styles** (colors, typography, spacing) via **Appearance → Editor → Styles** in the Site Editor — this is the recommended place for site-wide design changes, rather than editing `style.css` directly.
5. **Per-page options**: when editing a post or page in the block editor, open the sidebar's Page Settings panel to find Omega Design's Page Width, Hide Page Title, Featured Image, and Hide Header/Footer controls.
6. **WooCommerce sites**: activate WooCommerce and the theme automatically picks up its dedicated shop/product/cart/checkout templates — no extra setup required. Configure store pages as usual under **WooCommerce → Settings**, then choose a Product Page layout from **Omega Design → Settings → WooCommerce**.
7. **Blog sidebar**: enable it and choose its position/width from **Customize → Omega Design → Sidebar** (or override per-page from that page's own Page Settings panel in the editor). Its content — Search, Categories, Recent Posts, Tags — is a normal block template part, so edit it directly from **Appearance → Editor → Template Parts → Sidebar**, the same as any other template part.

---

## Theme Structure

```
omega-design/
├── assets/           Frontend/editor CSS, JavaScript, fonts, icons, and images
├── blocks/           Custom block definitions and render templates
├── includes/         Core modules, Customizer modules, traits, and compatibility helpers
├── parts/            Site Editor template parts
├── pattern/          Block patterns
├── templates/        Site Editor block templates
├── template-parts/   Classic header and footer layouts
├── woocommerce/      WooCommerce templates and classic overrides
├── functions.php     Theme bootstrap
├── theme.json        Global styles and settings
└── style.css         Theme metadata and canonical version
```

---

## Support & Contributing

This is a private theme repository for **Omega Design**. For bugs, feature requests, or contributions, please open an issue or pull request on this repository.

## License

GNU General Public License v2 or later. See [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html).
