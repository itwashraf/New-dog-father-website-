# The Dog Father Hotel — Website

A luxury, five-star **dog hotel** website: black-first, gold/yellow accents,
large imagery, smooth animations and Apple/Tesla-level simplicity. Built to be
fast, accessible, SEO-friendly and easy for a non-technical owner to run.

**Upload it, activate it, click once — you have a finished website.** No parent
theme, no Elementor required. Still fully editable with Elementor *and* the
Dog Father control panel.

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
- **Dog Father** theme — a **standalone** luxury theme (no parent theme, no
  dependencies). Renders a complete homepage out of the box and steps aside for
  Elementor when used.
- **Dog Father Control Center** plugin — bookings, dog profiles, services,
  gallery, testimonials, FAQs, homepage editor, brand settings, SEO,
  integrations, and **one-click site setup**.
- **Elementor** (optional, free is enough) — for pixel-level page editing.

**Editable, not hardcoded:** the homepage copy lives in the control panel; any
page can be taken over by Elementor at any time.

---

## Folder structure

```
.
├── README.md                          ← you are here
├── docs/
│   ├── QUICK-START.md                 ← upload & go (start here)
│   ├── INSTALLATION.md                ← detailed setup
│   ├── OWNER-GUIDE.md                 ← day-to-day editing & shortcodes
│   └── ARCHITECTURE.md                ← module system, CPTs, options, extending
├── themes/
│   └── dog-father/                    ← the standalone theme
│       ├── style.css  functions.php  theme.json  README.txt
│       ├── header.php  footer.php  front-page.php  page.php
│       ├── single.php  archive.php  search.php  index.php  404.php
│       ├── inc/                       ← helpers + template tags
│       ├── template-parts/home/       ← hero, about, services, … sections
│       └── assets/                    ← theme.css + theme.js
└── plugins/
    └── dog-father-control-center/
        ├── dog-father-control-center.php
        ├── includes/                  ← self-registering modules, helpers
        ├── admin/                     ← admin views/assets
        └── templates/elementor/       ← optional importable Elementor designs
```

---

## Quick start

1. Zip & upload **`plugins/dog-father-control-center`** → activate.
2. Zip & upload **`themes/dog-father`** → activate.
3. Go to **Dog Father → Setup** → **Run Setup Now** (auto-runs on theme
   activation too).
4. Fill in **Dog Father → Global Settings** and tweak **Theme Settings**
   (colors) and **Homepage** copy.

Full walkthrough: **[docs/QUICK-START.md](docs/QUICK-START.md)**. Everyday
editing: **[docs/OWNER-GUIDE.md](docs/OWNER-GUIDE.md)**. Extending it:
**[docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)**.

---

## Shortcodes (provided by the plugin)

`[dfcc_services]` · `[dfcc_gallery]` · `[dfcc_testimonials]` ·
`[dfcc_booking_form]` · `[dfcc_portal_login]` · `[dfcc_portal_dashboard]` ·
`[dfcc_portal_dogs]` · `[dfcc_phone]` · `[dfcc_whatsapp]` · `[dfcc_email]` ·
`[dfcc_address]` · `[dfcc_business_name]` · `[dfcc_map]` · `[dfcc_hours]`

See the OWNER-GUIDE for parameters and usage.

---

## Feature checklist (mapped to the brief)

- [x] **Standalone** luxury theme — no parent theme, zero dependencies
- [x] Complete homepage out of the box: hero, trust, about, services, why-us,
      stats, gallery, testimonials, FAQ, CTA, contact
- [x] **One-click setup** auto-creates pages, menus & demo content
- [x] Black background + white text, premium Poppins/Inter type
- [x] Editable brand colors/fonts → live site-wide CSS variables
- [x] Homepage editor + section show/hide in the control panel
- [x] Sticky luxury header (logo, dropdown nav, click-to-call, Book Now) +
      multi-column footer + WhatsApp float — all built in, no Pro needed
- [x] Full template set: front-page, page, single, archive, search, 404, blog
- [x] Elementor-aware: building a page in Elementor overrides the theme section
- [x] Accessibility: reduced-motion, focus-visible, skip link, sr-only
- [x] Performance: swap fonts + preconnect, lean CSS, reveal-on-scroll, no emoji
- [x] `theme.json` v2 brand palette, fonts, wide/content sizes
- [x] Booking system, dog profiles, services, gallery, testimonials, FAQs
- [x] SEO (schema/OG), integrations (payments/analytics/pixels), reports, backup
- [x] Optional importable Elementor templates included
- [x] All JSON validated; all PHP lints clean; boot + setup verified

---

## Notes

- Add `themes/dog-father/screenshot.png` (1200×900) before distributing the
  theme (see `themes/dog-father/README.txt`).
- Built for **PageSpeed 90+**: lean CSS, swap fonts, preconnects, no emoji
  scripts. Keep uploaded images optimised.
- Demo gallery items are placeholders — set a Featured Image on each to show
  real photos.
