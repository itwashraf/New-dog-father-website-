# Installation Guide — The Dog Father Hotel

This guide walks you through setting up the website from scratch. It is written
for a **non-technical owner**. Follow the steps in order. You do not need to
write any code.

---

## What you'll end up with

- A fast, luxury, black-and-gold website.
- All pages designed visually in **Elementor** (drag-and-drop).
- A **Dog Father Control Center** dashboard for bookings, dog profiles,
  services, gallery, testimonials, brand colors and integrations.

## The pieces

| Piece | What it is | Required |
|-------|------------|----------|
| WordPress | The platform the site runs on | Yes |
| Hello Elementor | The lightweight parent theme | Yes |
| **Dog Father Child** | Our brand child theme (this repo) | Yes |
| Elementor (free) | The page builder | Yes |
| Elementor **Pro** | Unlocks Theme Builder (header/footer) + more widgets | Yes |
| **Dog Father Control Center** | Our business plugin (this repo) | Yes |

---

## Step 1 — Install WordPress

1. Ask your host (or use a one-click installer) to install the latest
   **WordPress (6.2+)** on your domain.
2. Log in to the admin area at `https://yourdomain.com/wp-admin`.

## Step 2 — Install the themes & Elementor

1. Go to **Appearance → Themes → Add New**.
2. Search for **Hello Elementor**, click **Install** (do **not** activate yet).
3. Go to **Plugins → Add New**, search for **Elementor**, **Install & Activate**.
4. Install **Elementor Pro** (purchased from elementor.com): **Plugins → Add New
   → Upload Plugin**, choose the Pro `.zip`, **Install & Activate**, then enter
   your license key when prompted.

## Step 3 — Install the Dog Father Child theme

1. Zip the folder `themes/dog-father-child` from this repository (right-click →
   Compress / "Send to → Zipped folder"). The zip must contain the
   `dog-father-child` folder.
2. In WordPress: **Appearance → Themes → Add New → Upload Theme**.
3. Choose the zip, **Install**, then **Activate**.
4. WordPress may warn it needs the *Hello Elementor* parent — that's fine, you
   installed it in Step 2. The child theme uses it automatically.

## Step 4 — Install the Dog Father Control Center plugin

1. Zip the folder `plugins/dog-father-control-center` from this repository.
2. In WordPress: **Plugins → Add New → Upload Plugin**.
3. Choose the zip, **Install Now**, then **Activate**.
4. A new **Dog Father** menu appears in the left admin sidebar. On activation it
   seeds default brand colors and content types.

## Step 5 — Import the Elementor page & template designs

The ready-made designs live in
`plugins/dog-father-control-center/templates/elementor/`.

### Pages (Home, About, Services, Contact, Book Now)

1. Go to **Templates → Saved Templates → Import Templates**.
2. Upload each `*.json` page file (`home.json`, `about.json`, `services.json`,
   `contact.json`, `book-now.json`).
3. For each one, create a Page (**Pages → Add New**), open it with **Edit with
   Elementor**, then **Add Template** (folder icon) → **My Templates** → insert
   the matching design. Update and publish.

### Header & Footer (requires Elementor Pro)

1. Go to **Templates → Theme Builder**.
2. **Add New → Header**, then inside the editor use **Add Template → Import** or
   the folder icon to insert `header.json`. Set the display condition to
   **Entire Site** and publish.
3. Repeat with **Footer** and `footer.json`.

## Step 6 — Set the homepage

1. Go to **Settings → Reading**.
2. Set **Your homepage displays → A static page**.
3. Choose your **Home** page as Homepage. Save.

## Step 7 — Configure brand colors & global settings

1. Go to **Dog Father → Theme Settings**.
2. Confirm/adjust the brand colors. Defaults:
   - Primary Yellow `#FFF10A`, Luxury Gold `#FEC208`, Dark Red `#CF240A`,
     Accent Orange `#FF2D08`, Black `#000000`, White `#FFFFFF`.
3. Pick the heading font (Poppins) and body font (Inter), and a border radius.
4. Save. Changing colors here updates the whole site automatically (the theme
   and Elementor read these values live).
5. Visit **Dog Father → Settings / Integrations** to enter business name,
   phone, WhatsApp, email, address, opening hours, currency and map location —
   these power the `[dfcc_*]` shortcodes used across the pages.

## Step 8 — Create the menus

1. Go to **Appearance → Menus**.
2. Create a menu (e.g. "Main") with Home, About, Services, Contact, Book Now.
3. Assign it to the **Primary Menu** location.
4. Create / assign a **Footer Menu** and a **Mobile Menu** the same way.

## Step 9 — Add your content

- **Dog Father → Services** — add your service offerings.
- **Dog Father → Gallery** — upload photos.
- **Dog Father → Testimonials** — add reviews.
- **Dog Father → Bookings / Dog Profiles** — fill in as requests arrive.

The shortcodes on the imported pages (e.g. `[dfcc_services]`,
`[dfcc_gallery]`, `[dfcc_testimonials]`) display this content automatically.

---

## You're done

Open your site in a private/incognito window to see it as visitors do. To make
changes later, see **OWNER-GUIDE.md**.
