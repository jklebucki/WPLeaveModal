=== Leave Modal ===
Contributors: jklebucki
Tags: modal, external link, redirect, confirmation, shortcode, accessibility
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shows a confirmation popup before visitors go to another website. You set the text and destination in **Settings → Leave Modal**; you connect the popup to a button or link with a shortcode or a small HTML snippet.

== Description ==

**What it does**

When someone clicks your trigger (button or link), they see a popup: your title, your message, the destination address, and **Cancel** / **Continue**. **Cancel** closes the popup. **Continue** sends them to the URL you configured.

You can create **several different popups**. Each one has a short internal name called a **slug** (for example `partner` or `default`). The same name must be used in the shortcode or in the HTML attribute so WordPress knows which popup to show.

**How to put a trigger on a page (choose one)**

1. **Shortcode (easiest)** — Paste into a post or page (replace `partner` with your slug from Settings):

`[leave_modal_button modal="partner" label="Visit partner site"]`

Same idea: `[leave_modal_trigger modal="partner" label="More"]`

For a **text link** instead of a button:

`[leave_modal_link modal="partner" href="https://example.com" label="Open partner"]`  
(You may use `url="https://…"` instead of `href`.)

2. **Custom HTML** — If your theme or builder lets you add HTML, use the same slug as in Settings:

`<button type="button" data-wp-leave-modal="partner">Button text</button>`

Or a normal link:

`<a href="https://example.com" data-wp-leave-modal="partner">Link text</a>`

If **Redirect URL** is empty in Settings but the link has a valid `http`/`https` address, **Continue** uses that link address. If you fill **Redirect URL** in Settings, that value is used instead.

**Modified clicks** — Ctrl/Cmd/Shift+click or middle-click behaves like a normal browser link (e.g. open in a new tab) and does **not** open the popup. A normal single click opens the popup.

**Accessibility** — The popup can be closed with Escape; focus returns to the element that opened it.

**Upgrading from 1.0.x** — Old single-popup settings are moved to one popup with slug `default`. Use `modal="default"` in shortcodes unless you rename it.

== Installation ==

1. Upload the `wp-leave-modal` folder to `wp-content/plugins/`, or upload a zip under **Plugins → Add New → Upload Plugin**.
2. Activate **Leave Modal** under **Plugins**.
3. Open **Settings → Leave Modal**. For each popup, set a **slug** (short name), title, message, labels, and **Redirect URL** if you want the destination stored in the admin. Click **Save**.
4. Add a shortcode to a post or page, or add custom HTML with `data-wp-leave-modal="your-slug"` matching the slug from step 3.

== Frequently Asked Questions ==

= Can I use different popups on the same page? =

Yes. Give each popup its own **slug** in Settings, then use that slug in each shortcode or HTML attribute. You can mix different slugs on one page.

= The popup does not appear. What should I check? =

Make sure the **slug** in your shortcode or `data-wp-leave-modal` matches a slug under **Settings → Leave Modal** exactly. From version **1.1.2**, if you have saved at least one modal in settings, scripts load on normal frontend pages automatically (so page builders that store content outside the raw post editor still work). To turn that off and only load when a trigger appears in the classic post content, use: `add_filter( 'wp_leave_modal_enqueue_if_configured', '__return_false' );` If the popup still never shows, ask a developer to verify nothing blocks scripts, or force loading with: `add_filter( 'wp_leave_modal_enqueue', '__return_true' );`

= Does it work with block themes (Gutenberg)? =

Yes. Add a **Shortcode** block for shortcodes, or a **Custom HTML** block if you paste HTML with `data-wp-leave-modal`.

== Changelog ==

= 1.1.2 =
* Fix: load CSS/JS when modals exist in settings even if shortcodes are not present in raw `post_content` (page builders, widgets). Optional filter `wp_leave_modal_enqueue_if_configured` to disable.
* Improve early detection of shortcodes on a static front page.

= 1.1.1 =
* Anchor triggers: respect modified / middle-click; optional redirect from link `href` when modal Redirect URL is empty.
* Shortcode `[leave_modal_link]` outputs an `<a>` with `data-wp-leave-modal`.

= 1.1.0 =
* Multiple modals (repeater in settings), slug-based binding.
* Triggers via `data-wp-leave-modal="slug"` or shortcodes with required `modal` attribute.
* `leave_modal_trigger` as an alias for `leave_modal_button`.
* Legacy single-modal options migrate to slug `default`.

= 1.0.0 =
* Initial release: settings page, shortcode, modal UI, assets, and localization-ready strings.

== Upgrade Notice ==

= 1.1.2 =
Fixes missing popup when using page builders or when triggers are not stored in raw post content.

= 1.1.1 =
Improves `<a>` triggers and optional `href` fallback for Continue.

= 1.1.0 =
Adds multiple modals and slug-based triggers. Existing sites are migrated to a `default` modal.
