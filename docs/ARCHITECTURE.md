# Architecture — The Dog Father Hotel

> ⚠️ **OUTDATED — kept for history only.** This document describes an earlier
> "Elementor-first" version of the project. The current build is a **standalone
> theme + plugin with no page builder**. For the accurate, up-to-date overview
> and the full "where do I edit X" map, see **[`BLUEPRINT.md`](BLUEPRINT.md)**.

A technical overview for developers who maintain or extend the project.

---

## High-level philosophy: Elementor-first

The site is intentionally split into three cooperating layers:

```
┌─────────────────────────────────────────────────────────────┐
│  Presentation        →  Elementor Pro (all page design,       │
│                          Theme Builder header/footer/templates)│
├─────────────────────────────────────────────────────────────┤
│  Brand foundation    →  Dog Father Child theme                │
│                          (child of Hello Elementor)            │
├─────────────────────────────────────────────────────────────┤
│  Business logic/data →  Dog Father Control Center plugin       │
│                          (CPTs, settings, shortcodes)          │
└─────────────────────────────────────────────────────────────┘
```

**Nothing about page layout is hardcoded in PHP templates.** The theme is a thin
Hello Elementor child that only:

- provides base brand CSS and consumes the plugin's CSS variables,
- enqueues fonts and the parent/child stylesheets,
- registers menu locations and theme supports,
- registers Elementor Theme Builder locations (gracefully, only if Elementor is
  present),
- strips a little WordPress cruft (emoji scripts) for speed.

This keeps the theme replaceable and PageSpeed-friendly, and lets a
non-technical owner design freely in Elementor.

The **brand colors are the contract** between the layers. The plugin prints them
on every page as CSS custom properties:

```
--dfcc-primary, --dfcc-gold, --dfcc-dark-red, --dfcc-orange,
--dfcc-black, --dfcc-white, --dfcc-radius,
--dfcc-heading-font, --dfcc-body-font
```

The child theme's `style.css` and `theme.json` declare the same palette with
hardcoded fallbacks, so the brand holds even before the plugin is active.

---

## The plugin's self-registering module system

`dog-father-control-center.php` defines constants, loads the base classes and
boots a singleton, `DFCC_Plugin` (`includes/class-dfcc-plugin.php`).

Boot sequence (`DFCC_Plugin::boot()`, on `plugins_loaded` @ priority 20):

1. `glob()` every `includes/modules/class-dfcc-*.php` and `require_once` it.
2. Fire `do_action( 'dfcc_register_modules', $plugin )`. Each module file ends
   with an `add_action( 'dfcc_register_modules', … )` callback that calls
   `$plugin->add_module( new DFCC_Whatever() )`.
3. Call `->register()` on every collected module.
4. Fire `do_action( 'dfcc_loaded', $plugin )`.

Every module extends the abstract `DFCC_Module` (`includes/class-dfcc-module.php`)
and implements `id()`, `label()`, `register()`. This means **adding a feature is
just dropping a new `class-dfcc-*.php` file into `includes/modules/`** — no
central registry to edit. Modules are decoupled and self-contained.

Existing modules:

| File | Responsibility |
|------|----------------|
| `class-dfcc-post-types.php` | Registers all CPTs & taxonomies |
| `class-dfcc-bookings.php` | Booking workflow, meta, admin columns |
| `class-dfcc-dog-profiles.php` | Dog profile records |
| `class-dfcc-theme-settings.php` | Brand colors/fonts + prints CSS vars |
| `class-dfcc-admin-menu.php` | The "Dog Father" admin menu aggregator |

The admin menu is itself extensible: modules add screens via the
`dfcc_admin_pages` filter rather than calling `add_submenu_page` directly.

---

## Custom Post Types & Taxonomies

Registered in `class-dfcc-post-types.php`. Other modules depend on these slugs:

| CPT | Slug | Public | Notes |
|-----|------|--------|-------|
| Bookings | `dfcc_booking` | no | Admin-only; supports title |
| Dog Profiles | `dfcc_dog` | no | Admin-only; title + thumbnail |
| Services | `dfcc_service` | yes | Archive `/services`, REST on |
| Gallery | `dfcc_gallery` | yes | Archive `/gallery`, REST on |
| Testimonials | `dfcc_testimonial` | no | REST on |

| Taxonomy | Slug | Attached to |
|----------|------|-------------|
| Service Categories | `dfcc_service_cat` | `dfcc_service` |
| Albums | `dfcc_gallery_cat` | `dfcc_gallery` |

---

## Option groups (settings)

Settings are stored as serialized arrays in named options and read via the
helper `dfcc_get_setting( $group, $key, $default )`:

| Option group | Holds |
|--------------|-------|
| `dfcc_theme_settings` | Brand colors (`color_primary` … `color_white`), `heading_font`, `body_font`, `border_radius`, `dark_mode_first` |
| `dfcc_global_settings` | Business name, phone, WhatsApp, email, address, hours, `currency`, map, integrations |

Helpers (`includes/dfcc-helpers.php`):

- `dfcc_get_setting()` — safe array option reader.
- `dfcc_brand_color( $slug )` — a brand color with hardcoded fallback.
- `dfcc_admin_cap()` — filterable management capability (`dfcc_admin_capability`).
- `dfcc_menu_slug()` — top-level admin menu slug (`dfcc-dashboard`).
- `dfcc_money( $amount )` — formats with the configured currency.

`DFCC_Theme_Settings::print_css_variables()` is hooked to `wp_head` (priority 5)
and `enqueue_block_assets` (editor) — this is the single source of truth that
pushes brand tokens to the front end and the Elementor/block editors.

---

## Shortcodes

The plugin exposes these shortcodes (used by the imported Elementor templates):

`[dfcc_services]`, `[dfcc_gallery]`, `[dfcc_testimonials]`,
`[dfcc_booking_form]`, `[dfcc_portal_login]`, `[dfcc_portal_dashboard]`,
`[dfcc_portal_dogs]`, `[dfcc_phone]`, `[dfcc_whatsapp]`, `[dfcc_email]`,
`[dfcc_address]`, `[dfcc_business_name]`, `[dfcc_map]`, `[dfcc_hours]`.

Templates embed dynamic sections by placing these inside Elementor **Shortcode**
widgets, so design stays in Elementor while data stays in the plugin.

---

## How to extend

Because of the module system, new features are additive and low-risk.

1. **New admin/data feature** — add `includes/modules/class-dfcc-<feature>.php`,
   extend `DFCC_Module`, self-register on `dfcc_register_modules`, add admin
   screens through the `dfcc_admin_pages` filter, register any CPT/meta in your
   module's `register()`.
2. **New front-end block** — register a shortcode in your module and drop it
   into Elementor via a Shortcode widget. Keep markup minimal; rely on the brand
   CSS variables for styling.

### Future roadmap (designed-for, not yet built)

- **Payments** — a `class-dfcc-payments.php` module hooking the booking flow to a
  gateway (e.g. Stripe/Tap/HyperPay for SAR); status transitions on the
  `dfcc_booking` CPT.
- **Multilingual** — pair with WPML/Polylang; all strings already use the
  `dog-father-control-center` / `dog-father-child` text domains. Arabic-ready
  fonts (Cairo, Tajawal) are already offered in Theme Settings.
- **Shop/retail** — a module integrating WooCommerce for products/retail while
  keeping bookings in the native CPT.
- **App / REST** — Services, Gallery and Testimonials already expose
  `show_in_rest`. A dedicated REST module could expose bookings & the customer
  portal for a mobile app.

All of the above slot in as new module files without touching existing code,
honouring the self-registering architecture.
