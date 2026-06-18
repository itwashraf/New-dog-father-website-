# Setup Without Elementor Pro

This site is fully usable on **free Elementor**. The only thing Elementor Pro
would add is the visual Theme Builder for the header/footer — and that is now
handled by the **theme itself** (`header.php` / `footer.php`), so you don't
need Pro to get a complete, branded site.

Follow these steps in order.

## 1. Re-upload the updated theme

The header and footer are now built into the theme. Re-upload / update the
`dog-father-child` theme so it includes the new `header.php`, `footer.php`, and
the updated `style.css`. Keep **Hello Elementor** installed (it's the parent).

> Make sure both **Hello Elementor** and the **Dog Father Child** theme are
> present, and that **Dog Father Child** is the *active* theme.

## 2. Set your brand + contact info

`WP Admin → Dog Father → Global Settings`
- Business name, tagline, phone, WhatsApp, email, address, opening hours, map embed.

`WP Admin → Dog Father → Theme Settings`
- Confirm brand colors (these flow into the whole site automatically).

These values automatically appear in the header (phone + Book Now) and footer
(contact columns).

## 3. Create your menus

`Appearance → Menus`
- Create a menu, add your pages, and assign it to the **Primary Menu** location
  (this is the top navigation). Dropdowns work automatically (just nest items).
- Optionally create a second menu for the **Footer Menu** location.

## 4. Import the page templates (free Elementor)

`Templates → Saved Templates → Import Templates` → upload these from
`plugins/dog-father-control-center/templates/elementor/`:
- `home.json`
- `about.json`
- `services.json`
- `contact.json`
- `book-now.json`

> Ignore `header.json` and `footer.json` for now — those are Theme Builder
> parts that only apply if you later add Elementor Pro. You do **not** need them.

## 5. Build each page

For every page (Home, About, Services, Contact, Book Now):
1. `Pages → Add New`, give it a title, **Publish**.
2. In the right sidebar, set **Template → Elementor Full Width**
   (this keeps the theme's header/footer and lets the content go edge-to-edge).
3. Click **Edit with Elementor**.
4. Click the grey **folder icon** ("Add Template") in the canvas → **My
   Templates** tab → insert the matching template (e.g. the `home` template on
   the Home page).
5. **Update**.

> Important: do **not** use the "Elementor Canvas" template — that one hides the
> header and footer on purpose. Use **Elementor Full Width**.

## 6. Set the homepage

`Settings → Reading → Your homepage displays → A static page`
- **Homepage:** Home
- **Posts page:** (a page named "Blog", optional)

## 7. Done — check the preview

You should now see:
- A sticky black header with your logo/name, menu, phone, and a gold **Book Now**
  button.
- Your Elementor-built page content.
- A branded multi-column footer with contact details and copyright.

## Why it looked "messed up" before

The theme is intentionally minimal — all design comes from Elementor templates
+ the theme header/footer. Before importing the templates and creating menus,
WordPress had nothing to render except raw content, so it showed your old
content with no styling. Once steps 3–6 are done, the luxury design appears.

## Dynamic sections (already wired)

The templates use shortcodes that pull live data from the plugin — no manual
rebuilding needed when you add content:

| Shortcode | Shows |
| --- | --- |
| `[dfcc_services]` | Service cards (from Dog Father → Services) |
| `[dfcc_gallery]` | Gallery grid with lightbox |
| `[dfcc_testimonials]` | Approved reviews |
| `[dfcc_booking_form]` | The booking form |
| `[dfcc_map]` `[dfcc_phone]` `[dfcc_address]` `[dfcc_hours]` | Contact info |

## If you add Elementor Pro later

You can switch the header/footer to the visual Theme Builder at any time:
import `header.json` / `footer.json` under `Templates → Theme Builder`, set
their display condition to **Entire Site**, and they will take over from the
theme's `header.php` / `footer.php`.
