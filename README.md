# The Dog Father Hotel — Website

A luxury, five-star **dog hotel** website: black-first, gold/yellow accents,
large imagery, smooth animations and Apple/Tesla-level simplicity. Built to be
fast, accessible, SEO-friendly and easy for a non-technical owner to run.

---

## Brand

| Token | Color | Use |
|-------|-------|-----|
| Primary Yellow | `#FFF10A` | Highlights, accents |
| Luxury Gold | `#FEC208` | Buttons, headings accents |
| Dark Red | `#CF240A` | Secondary accent |
| Accent Orange | `#FF2D08` | Tertiary accent |
| Black | `#000000` | **Main background** |
| White | `#FFFFFF` | Body text |

Typography: **Poppins** (headings) + **Inter** (body), loaded with
`display=swap`. Brand colors and fonts are changeable from the admin and flow to
the whole site as live CSS variables (`--dfcc-primary`, `--dfcc-gold`, …).

---

## Tech stack

- **WordPress** 6.2+
- **Hello Elementor** (lightweight parent theme)
- **Dog Father Child** theme — brand foundation (this repo)
- **Elementor + Elementor Pro** — all page design + Theme Builder header/footer
- **Dog Father Control Center** plugin — bookings, dog profiles, services,
  gallery, testimonials, brand settings, integrations (this repo)

**Elementor-first:** no page layout is hardcoded. The theme stays out of
Elementor's way; the plugin owns the data and brand.

---

## Folder structure

```
.
├── README.md                          ← you are here
├── docs/
│   ├── INSTALLATION.md                ← step-by-step setup (owner-friendly)
│   ├── OWNER-GUIDE.md                 ← day-to-day editing & shortcodes
│   └── ARCHITECTURE.md                ← module system, CPTs, options, extending
├── themes/
│   └── dog-father-child/              ← the child theme
│       ├── style.css                  ← theme header + base brand CSS
│       ├── functions.php              ← enqueues, menus, supports, perf
│       ├── theme.json                 ← global styles / brand palette (v2)
│       └── screenshot-README.txt      ← note on the theme screenshot
└── plugins/
    └── dog-father-control-center/
        ├── dog-father-control-center.php
        ├── includes/                  ← self-registering modules, helpers
        ├── admin/                     ← admin views/assets
        └── templates/elementor/       ← importable Elementor designs
            ├── home.json   about.json   services.json
            ├── contact.json   book-now.json
            └── header.json   footer.json
```

---

## Quick start

1. Read **[docs/INSTALLATION.md](docs/INSTALLATION.md)** and follow the steps.
2. Install WordPress → Hello Elementor → Elementor Pro.
3. Upload `themes/dog-father-child` (Appearance → Themes) and activate.
4. Upload `plugins/dog-father-control-center` (Plugins) and activate.
5. Import the designs from `plugins/dog-father-control-center/templates/elementor/`
   (Templates → Import, and Theme Builder for header/footer).
6. Set the homepage, configure brand colors and contact details under the
   **Dog Father** menu, and build your menus.

Then use **[docs/OWNER-GUIDE.md](docs/OWNER-GUIDE.md)** for everyday edits, and
**[docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)** to extend it.

---

## Shortcodes (provided by the plugin)

`[dfcc_services]` · `[dfcc_gallery]` · `[dfcc_testimonials]` ·
`[dfcc_booking_form]` · `[dfcc_portal_login]` · `[dfcc_portal_dashboard]` ·
`[dfcc_portal_dogs]` · `[dfcc_phone]` · `[dfcc_whatsapp]` · `[dfcc_email]` ·
`[dfcc_address]` · `[dfcc_business_name]` · `[dfcc_map]` · `[dfcc_hours]`

See the OWNER-GUIDE for parameters and usage.

---

## Feature checklist (mapped to the brief)

- [x] Lightweight, Elementor-friendly **child theme** of Hello Elementor
- [x] Black background + white text defaults, premium font stack
- [x] Consumes plugin brand CSS variables with hardcoded fallbacks
- [x] Brand-colored links/buttons/selection
- [x] Accessibility: reduced-motion, focus-visible, skip link, sr-only
- [x] Parent + child stylesheet enqueue with dependency & version
- [x] Primary / Footer / Mobile nav menu locations
- [x] Theme supports: title-tag, post-thumbnails, html5, custom-logo,
      responsive embeds, align-wide, editor-styles
- [x] Elementor Theme Builder locations registered **gracefully** (no fatal if
      Elementor absent)
- [x] Google Fonts (Poppins + Inter) with `display=swap` + preconnect
- [x] Performance helper (emoji scripts removed)
- [x] `theme.json` v2 brand palette, fonts, wide/content sizes
- [x] Screenshot note (1200×900 intended look)
- [x] Importable Elementor templates: Home, About, Services, Contact, Book Now,
      Header, Footer — black sections, gold/yellow headings, CTA buttons
- [x] Dynamic sections embedded via Shortcode widgets (`[dfcc_*]`)
- [x] Documentation: Installation, Owner Guide, Architecture, README
- [x] All JSON validated; all PHP lints clean
- [x] **New files only** — no existing plugin files modified

---

## Notes

- Replace `themes/dog-father-child/screenshot.png` before shipping (see
  `screenshot-README.txt`).
- Built for **PageSpeed 90+**: lean CSS, swap fonts, preconnects, no emoji
  scripts. Keep uploaded images optimised.
