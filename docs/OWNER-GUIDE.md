# Owner Guide — Running The Dog Father Hotel Website

Everything you need to manage the site day to day. No coding required.

---

## 1. The two control panels

- **Elementor** — controls how pages *look* (layout, images, text, buttons).
  Edit any page by opening it and clicking **Edit with Elementor**.
- **Dog Father** menu (the plugin) — controls your *business data* (bookings,
  dogs, services, gallery, testimonials) and your *brand* (colors, fonts,
  contact details, integrations).

A simple rule: **design = Elementor, data & brand = Dog Father menu.**

---

## 2. Editing a page with Elementor

1. **Pages → All Pages**, hover the page, click **Edit with Elementor**.
2. Click any text or image to edit it in the left panel.
3. Drag widgets from the left panel onto the page to add new blocks.
4. Click the **green Update** button to save.
5. Use the **responsive (device) icons** at the bottom to check phone/tablet
   views — the design is mobile-first, always confirm mobile looks right.

To rearrange sections, hover a section's blue handle and drag it.

---

## 3. Changing colors & fonts (brand)

Go to **Dog Father → Theme Settings**.

- **Colors:** Primary Yellow, Luxury Gold, Dark Red, Accent Orange, Black,
  White. Change any value and **Save** — it updates everywhere instantly,
  including inside Elementor, because the whole site reads these live brand
  variables (`--dfcc-primary`, `--dfcc-gold`, etc.).
- **Fonts:** choose a heading font (default Poppins) and body font (default
  Inter) from the dropdowns.
- **Border radius:** controls how rounded buttons/cards are.

> Tip: keep Black as the main background and Gold/Yellow as accents for the
> luxury look.

---

## 4. Managing bookings

**Dog Father → Bookings.**

- Each booking request from the website's booking form appears here.
- Open a booking to see the customer's name, **phone**, dates and dog details.
- Update its status as you process it.
- You can also add a booking manually with **Add New**.

The public booking form is the `[dfcc_booking_form]` shortcode, already placed
on the **Book Now** and **Contact** pages.

---

## 5. Managing dog profiles

**Dog Father → Dog Profiles.**

- Store each dog's name, photo, owner, phone, breed, notes and care needs.
- Useful as a guest record and to personalise care.

---

## 6. Managing services, gallery & testimonials

| Content | Where | Shows on site via |
|---------|-------|-------------------|
| Services | **Dog Father → Services** | `[dfcc_services]` |
| Gallery photos | **Dog Father → Gallery** | `[dfcc_gallery]` |
| Testimonials | **Dog Father → Testimonials** | `[dfcc_testimonials]` |

For each, click **Add New**, add a title, description, photo (featured image)
and publish. Services and Gallery support categories/albums for grouping.

---

## 7. Shortcodes reference

A shortcode is a small tag in `[square brackets]` that displays dynamic content.
Drop them into any Elementor **Shortcode widget** (or any text area). They all
read live data from the Dog Father plugin.

### Content blocks

| Shortcode | Shows | Common parameters |
|-----------|-------|-------------------|
| `[dfcc_services]` | Your services grid | `category`, `limit`, `columns` |
| `[dfcc_gallery]` | Photo gallery | `album`, `limit`, `columns` |
| `[dfcc_testimonials]` | Customer reviews | `limit`, `columns` |
| `[dfcc_booking_form]` | The booking request form | `service` (pre-select a service) |

### Customer portal

| Shortcode | Shows |
|-----------|-------|
| `[dfcc_portal_login]` | Login form for the customer portal |
| `[dfcc_portal_dashboard]` | Logged-in customer's dashboard |
| `[dfcc_portal_dogs]` | The customer's saved dog profiles |

### Business info (from Dog Father → Settings/Integrations)

| Shortcode | Shows |
|-----------|-------|
| `[dfcc_phone]` | Click-to-call phone number |
| `[dfcc_whatsapp]` | WhatsApp link/button |
| `[dfcc_email]` | Email address |
| `[dfcc_address]` | Street address |
| `[dfcc_business_name]` | The business name |
| `[dfcc_map]` | Embedded location map |
| `[dfcc_hours]` | Opening hours |

> Parameters are optional. Example: `[dfcc_services category="grooming" columns="3"]`.
> If you omit them, sensible defaults are used.

---

## 8. Managing menus & pages

### Menus

**Appearance → Menus.** Add/remove items, drag to reorder, then assign the menu
to a location: **Primary**, **Footer** or **Mobile**.

### Pages

**Pages → Add New** to create a page; **Edit with Elementor** to design it. To
reuse a ready-made design, open Elementor → folder icon → **My Templates**.

---

## 9. SEO

- Each page/post has an SEO area (title & meta description) — fill these in with
  your keywords (e.g. "luxury dog hotel", your city).
- Use **one H1 heading** per page (the main title) and H2s for sections.
- Add descriptive **alt text** to every image (in the media library / image
  widget).
- Keep images optimised; the theme is built for PageSpeed 90+, so avoid
  uploading huge unoptimised photos.

---

## 10. Integrations

**Dog Father → Settings / Integrations.** Enter:

- Business name, phone, WhatsApp number, email, physical address.
- Opening hours and currency (e.g. SAR).
- Map location for `[dfcc_map]`.
- Any third-party keys (analytics, etc.) the plugin exposes.

These values feed every `[dfcc_*]` business shortcode, so you only enter them
once and they appear consistently across the whole site.

---

## Quick troubleshooting

- **A section shows a shortcode in plain text instead of content** — make sure
  the Dog Father plugin is active and you've added some content (services,
  gallery, etc.).
- **Colors didn't change** — re-save **Dog Father → Theme Settings** and refresh
  with a hard reload (Ctrl/Cmd+Shift+R).
- **Header/footer missing** — Elementor **Pro** must be active and the Theme
  Builder header/footer published with the *Entire Site* condition.
