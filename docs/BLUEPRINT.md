# The Dog Father — Project Blueprint

> The single source of truth for understanding, editing, maintaining and
> migrating this website. If you are taking over this project, **read this first**.
> It explains how everything fits together and, crucially, **where every editable
> thing on the site lives in the dashboard**.

Last updated: 2026-06 · Applies to theme `dog-father` + plugin
`dog-father-control-center`.

---

## 1. What this project is

A complete, self-contained website for **The Dog Father Hotel** (a dog boarding
hotel). It is built from **two pieces only** — no page builder, no paid plugins:

| Piece | Folder | Job |
|-------|--------|-----|
| **Theme** — "The Dog Father" | `themes/dog-father/` | All the **visual design** and the public pages (homepage sections, header, footer, blog, etc.). |
| **Plugin** — "Dog Father Control Center" | `plugins/dog-father-control-center/` | All the **content, settings and data** (services, FAQs, testimonials, bookings, colors, business info…) and the whole **Dog Father admin menu**. |

**Golden rule:** the *theme draws*, the *plugin stores*. The theme never hardcodes
business content — it asks the plugin for it and falls back to sensible defaults
if the plugin is missing.

> ⚠️ The old `docs/ARCHITECTURE.md` describes a previous "Elementor-first" version
> and is **out of date**. This blueprint reflects the current standalone build.
> Kubio and Elementor are **not** required or used.

---

## 2. How a piece of content gets onto the page

This is the most important concept. Every editable value follows the same path:

```
Owner types it in           WordPress stores it          Theme reads it           Visitor sees it
the Dog Father admin   →    in an option / CPT      →    via a helper       →     on the page
(e.g. "Homepage")           (e.g. dfcc_home_settings)    (e.g. dfather_home())   (template part)
```

Concretely, the **homepage hero headline**:

1. Owner edits it at **Dog Father → Homepage → Hero Banner → Headline**.
2. Saved into the option array `dfcc_home_settings['hero_title']`.
3. Template `themes/dog-father/template-parts/home/hero.php` reads it with
   `dfather_home( 'hero_title', '…default…' )`.
4. It renders inside the hero section.

If you understand this one loop, you understand the whole project.

### The helper functions (in `themes/dog-father/inc/helpers.php`)

| Helper | Reads from | Use it for |
|--------|-----------|------------|
| `dfather_home( $key, $default )` | option `dfcc_home_settings` | Homepage content (hero, about, trust, why, stats, CTA, section titles/buttons). |
| `dfather_info( $key, $default )` | option `dfcc_global_settings` | Business info (name, phone, WhatsApp, email, address, hours, social links, footer credit). |
| `dfather_show( $section )` | option `dfcc_home_settings` (`show_*`) | Whether a homepage section is switched on. |
| `dfather_social_links()` | option `dfcc_global_settings` | The list of social profiles that have a URL set. |
| `dfather_default_image( $slot )` | filterable defaults | A fallback photo (hero/about/cta) when none is uploaded. |
| `dfather_icon_html( $icon )` | — | Renders a Dashicon name **or** an emoji safely. |

On the plugin side every option is read through one function:
`dfcc_get_setting( $group, $key, $default )` (in `includes/dfcc-helpers.php`).
**Note:** an empty saved value falls back to the default — so clearing a field
restores its default rather than blanking it.

---

## 3. The data stores (where WordPress keeps everything)

### Option groups (key/value settings)

| Option name | Edited at | Holds |
|-------------|-----------|-------|
| `dfcc_home_settings` | Dog Father → **Homepage** | All homepage text, images, toggles, trust bar, why-choose-us, buttons, eyebrows. |
| `dfcc_global_settings` | Dog Father → **Global Settings** | Business name, phone, WhatsApp, email, address, hours, currency, map, **social links**, **footer credit**. |
| `dfcc_theme_settings` | Dog Father → **Theme Settings** | Brand colors, **section colors**, fonts, border radius. |
| `dfcc_integration_settings`, `dfcc_seo_settings`, `dfcc_notification_settings`, `dfcc_security_settings` | Integrations / SEO / Notifications / Security | Their respective settings. |

### Custom Post Types (lists of things)

| CPT slug | Managed at | Is |
|----------|-----------|-----|
| `dfcc_service` | Dog Father → **Manage Services** | A service card (price, icon, features, featured/visible). |
| `dfcc_faq` | Dog Father → **Manage FAQs** | A question (title) + answer (content). |
| `dfcc_testimonial` | Dog Father → Testimonials | A review (rating, author, role meta). |
| `dfcc_gallery` | Dog Father → Gallery | A photo (uses the Featured Image). |
| `dfcc_booking` | Dog Father → Bookings | A customer booking. |
| `dfcc_dog` | Dog Father → Dog Profiles | A guest dog profile. |

---

## 4. Full editability map — "where do I change X?"

Everything visible on the homepage and chrome, and exactly where to edit it.

### Header (top bar)
| Thing | Where |
|-------|-------|
| Logo | Appearance → Customize → Site Identity (or a menu logo). |
| Menu links | Appearance → Menus → location **Primary Menu**. |
| Phone **icon** (tap to call) | Dog Father → Global Settings → **Phone**. |
| WhatsApp **icon** | Dog Father → Global Settings → **WhatsApp Number**. |
| "Book Now" button text | Dog Father → Homepage → Section Titles & Buttons → **Header "Book Now" Button Label**. |
| "Book Now" button link | Dog Father → Homepage → Hero → **Primary Button URL** (shared). |

### Hero banner
Dog Father → **Homepage → Hero Banner**: eyebrow, headline, subtitle, background
image (defaults to a stock dog photo), both buttons (labels + URLs).

### Trust bar (4 small highlights under the hero)
Dog Father → **Homepage → Trust Bar**: each of the 4 items has an Icon
(Dashicon name or emoji), Title, Subtitle.

### About section
Dog Father → **Homepage → About Section**: eyebrow, title, text, image (default
photo), **badge text**, **button label + URL**.

### Services
- Cards: Dog Father → **Manage Services** (add/edit/price/icon/features/show-hide).
- Heading + button: Dog Father → Homepage → Section Titles & Buttons
  (Services Eyebrow/Title/Button Label/Button URL).

### Why Choose Us
Dog Father → **Homepage → Why Choose Us**: eyebrow, title, and 4 reasons
(icon + title + text each).

### Statistics band
Dog Father → **Homepage → Statistics**: 4 × (number + label).

### Gallery
- Photos: Dog Father → Gallery (add items with Featured Image).
- Heading + button: Dog Father → Homepage → Section Titles & Buttons.

### Testimonials
- Reviews: Dog Father → Testimonials.
- Heading: Dog Father → Homepage → Section Titles & Buttons (eyebrow + title).

### FAQ
- Questions: Dog Father → **Manage FAQs**.
- Heading: Dog Father → Homepage → Section Titles & Buttons (FAQ eyebrow + title).

### Call to action (CTA)
Dog Father → **Homepage → Call to Action**: title, text, button label + URL,
**background image** (default photo).

### Contact
Dog Father → **Global Settings** (address, phone, WhatsApp, email, hours, Google
Map embed) + Homepage → Contact eyebrow/title.

### Footer
| Thing | Where |
|-------|-------|
| Business name + tagline | Global Settings (Business Name / Tagline). |
| Social icons | Global Settings → **Social Media** (Facebook, Instagram, TikTok, YouTube, X). |
| "Explore" links | Appearance → Menus → location **Footer Menu**. |
| Contact column | Global Settings. |
| Hours column | Global Settings → Opening Hours. |
| "Designed & developed by" credit | Global Settings → **Footer Credit** (text, link, or hide it). |

### Colors, fonts, rounding
Dog Father → **Theme Settings**:
- **Brand Colors** — the master palette (primary yellow, gold, dark red, orange,
  black, white).
- **Section Colors** — choose *which part* a color affects: Header background,
  Header text/links, Accents, Button background, Button text, Footer background.
  Leave blank to keep the brand default.
- **Typography & Layout** — heading font, body font, border radius.

### Show / hide whole sections
Dog Father → **Homepage → Show / Hide Sections** (one checkbox per section).

---

## 5. How the colors actually work (CSS variables)

The plugin prints the live palette into every page `<head>` as CSS custom
properties (`DFCC_Theme_Settings::print_css_variables()`):

```
:root{ --dfcc-primary:#…; --dfcc-gold:#…; … --df-header-bg:#…; --df-accent:#…; }
```

- Brand colors → `--dfcc-*`.
- Section colors → `--df-header-bg`, `--df-header-text`, `--df-accent`,
  `--df-btn-bg`, `--df-btn-text`, `--df-footer-bg` (only emitted when set).

The theme stylesheet (`themes/dog-father/assets/css/theme.css`) **consumes** these
variables, e.g. `.df-header { background: var(--df-header-bg); }`. So changing a
color in the dashboard restyles the whole site instantly — no CSS editing.
If the plugin is ever deactivated, the theme prints fallback defaults
(`dfather_fallback_brand_vars()` in `functions.php`) so it still looks on-brand.

---

## 6. The Kubio cleanup tool (Dog Father → Cleanup)

This site was **migrated off the Kubio page builder**. Pages built with Kubio
still contain `<!-- wp:kubio/… -->` block markers, which WordPress can no longer
render — causing "block not supported" warnings and blocking edits/saves.

**Dog Father → Cleanup (Kubio)** fixes this:
1. **Scans** all posts/pages for leftover Kubio markup and lists them.
2. **Clean** (per page or "Clean All") strips the Kubio wrappers, keeping any
   real text/images as plain HTML (same result as the editor's "Keep as HTML",
   in one click). A page that was 100% Kubio becomes empty — which lets the
   theme's own built-in design take over (ideal for the Home page).
3. Every change is **backed up** to post meta `_dfcc_pre_cleanup_content`, so
   **Restore** (per page or "Restore All") fully reverts it.

Code: `includes/modules/class-dfcc-tools.php` (`scan_kubio_posts`,
`strip_kubio`, `handle_kubio_clean`, `handle_kubio_restore`) and view
`admin/views/cleanup.php`.

> After migrating, the recommended flow is: **Cleanup → Clean All**, then manage
> the homepage through **Dog Father → Homepage**.

---

## 7. Code map (where things live)

```
themes/dog-father/
  functions.php              Theme bootstrap: assets, fonts, supports, fallbacks.
  header.php / footer.php    Site chrome (header icons, footer social/credit).
  front-page.php             Homepage controller (see note below).
  inc/helpers.php            dfather_home / dfather_info / dfather_social_links / default images.
  inc/template-tags.php      dfather_icon_html / service card / gallery item / stars.
  template-parts/home/*.php  One file per homepage section (hero, trust, about, services,
                             why, stats, gallery, testimonials, faq, cta, contact).
  assets/css/theme.css       All styles; consumes the CSS variables above.
  assets/js/theme.js         Menu, scroll header, reveal animations, FAQ accordion.

plugins/dog-father-control-center/
  dog-father-control-center.php   Plugin bootstrap.
  includes/class-dfcc-plugin.php  Auto-discovers and registers modules.
  includes/class-dfcc-module.php  Base class; ->view() renders an admin view file.
  includes/dfcc-helpers.php       dfcc_get_setting / dfcc_brand_color / dfcc_money / cache purge.
  includes/modules/*.php          One module per feature (see table below).
  admin/views/*.php               The admin screens' HTML.
  admin/js/admin.js               Color pickers + media upload buttons.
```

**`front-page.php` behaviour:** if the Home page has *manual content* (e.g.
leftover Kubio blocks) it renders that; otherwise it renders the theme's built-in
sections. This is exactly why the Cleanup tool matters — an empty Home page lets
the designed homepage show.

### Key plugin modules
| Module file | Provides |
|-------------|----------|
| `class-dfcc-home-settings.php` | The **Homepage** screen (all section content + toggles). |
| `class-dfcc-global-settings.php` | The **Global Settings** screen + `[dfcc_phone]` etc. shortcodes + social + footer credit. |
| `class-dfcc-theme-settings.php` | The **Theme Settings** screen + prints CSS variables (brand + section colors). |
| `class-dfcc-services.php` | The **Manage Services** screen + `[dfcc_services]` shortcode. |
| `class-dfcc-faq.php` | The `dfcc_faq` CPT + the **Manage FAQs** screen. |
| `class-dfcc-tools.php` | Reports, Notifications, Backup, **Cleanup (Kubio)**, Security, Users. |
| `class-dfcc-post-types.php` | Registers all the CPTs and taxonomies. |
| `class-dfcc-admin-menu.php` | Builds the Dog Father menu from the `dfcc_admin_pages` filter; loads admin assets. |

---

## 8. How to extend it (recipes)

### Add a new editable homepage text field
1. Add the key + type to `fields()` in `class-dfcc-home-settings.php`.
2. Add a sensible default to `defaults()` there.
3. Add an input to `admin/views/home-settings.php`.
4. Read it in the relevant `template-parts/home/*.php` with
   `dfather_home( 'your_key', 'default' )`.

### Add a new homepage section
1. Create `template-parts/home/yoursection.php` (copy an existing one).
2. `get_template_part( 'template-parts/home/yoursection' )` in `front-page.php`.
3. Add a `show_yoursection` toggle in `toggles()` and guard with
   `if ( ! dfather_show( 'yoursection' ) ) return;`.

### Add a new admin screen (any module)
Hook the `dfcc_admin_pages` filter with
`array( 'slug'=>'dfcc-x', 'title'=>'X', 'callback'=>…, 'order'=>N )` and render a
file from `admin/views/` via `$this->view( 'x', $args )`.

### Add a new social network
Add it to `DFCC_Global_Settings::social_networks()` **and** the mirror list in
`dfather_social_links()` (theme helper).

---

## 9. Migrating the project to a new server

1. **Back up settings:** Dog Father → Backup Center → Export (downloads a JSON of
   all option groups + a snapshot of services/testimonials/gallery).
2. **Move WordPress** as usual (database + `wp-content`), *or* a fresh install
   plus: copy `themes/dog-father` to `wp-content/themes/` and
   `plugins/dog-father-control-center` to `wp-content/plugins/`.
3. Activate the theme and the plugin.
4. If starting fresh, Dog Father → Backup Center → Import the JSON from step 1.
5. **Settings → Permalinks → Save** (flushes CPT URLs).
6. If pages still show old builder warnings: Dog Father → **Cleanup → Clean All**.
7. Re-check the **editability map (section 4)** to confirm content is in place.

This repository tracks **only** the theme and plugin source (see `.gitignore`).
Uploaded media and the database are not in git — use the Backup Center / a normal
WordPress migration for those.

---

## 10. Quick troubleshooting

| Symptom | Fix |
|---------|-----|
| "Block not supported" / can't save a page | Dog Father → Cleanup → Clean that page (or All). |
| Homepage shows old design, not the new sections | The Home page still has content — Cleanup it (empty Home page = theme homepage). |
| A color change didn't apply | Clear site cache (Manage Services/FAQ save auto-purges); check it's set in Theme Settings. |
| A default photo shows instead of mine | Upload your image in Homepage (Hero/About) — uploads always win over defaults. |
| Phone/WhatsApp icon missing in header | Set the number in Global Settings; empty numbers hide the icon. |
| FAQ section empty | Add questions in Manage FAQs (until then 4 sample questions show). |
```
