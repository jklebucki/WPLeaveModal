=== Leave Modal ===
Contributors: jklebucki
Tags: modal, external link, redirect, confirmation, shortcode, accessibility
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shows theme-friendly confirmation dialogs before visitors leave your site to external URLs. Define multiple modals in Settings and connect each to triggers via a data attribute or shortcode.

== Description ==

Leave Modal lets you define **one or more modals**, each with its own title, message (HTML allowed), destination label, redirect URL, and footer button labels.

**Triggers (pick one):**

* **Data attribute** — On any element: `data-wp-leave-modal="your-slug"` (slug must match a modal defined under **Settings → Leave Modal**). Works in Custom HTML, theme templates, and many page builders that allow custom attributes.
* **Shortcodes** — `[leave_modal_button modal="your-slug" label="Button text"]`, `[leave_modal_trigger modal="your-slug"]` (alias), or `[leave_modal_link modal="your-slug" href="https://…" label="Link text"]` for an `<a>` trigger. Optional `class="extra-classes"`. You can use `url="…"` instead of `href`.

* **Anchor links** — Any `<a href="…" data-wp-leave-modal="your-slug">` works like a button trigger. Primary click opens the modal; **Ctrl/Cmd/Shift+click** and **middle-click** keep the browser default (e.g. open in a new tab). If the modal has no **Redirect URL** in settings, a safe `http`/`https` value from the link’s `href` is used for **Continue** (settings URL still wins when set).

The front end loads **one dialog shell**; opening a trigger fills it from the matching modal configuration. **Continue** is enabled when there is a valid `http` or `https` destination from settings or from the trigger link’s `href` (see above).

**Accessibility** — `role="dialog"`, focus trap, Escape to close, focus return to the trigger.

**Upgrade note** — If you used version 1.0.x, your previous single-modal settings are migrated automatically to a modal with slug `default`.

== Installation ==

1. Upload the `wp-leave-modal` folder to `/wp-content/plugins/`, or install the zip via **Plugins → Add New → Upload Plugin**.
2. Activate **Leave Modal** through the **Plugins** menu.
3. Go to **Settings → Leave Modal**, add or edit modals (each needs a unique **slug**), and save.
4. Add triggers: place a shortcode in content, or add `data-wp-leave-modal="slug"` to buttons/links in HTML.

== Frequently Asked Questions ==

= Can I use different modals on the same page? =

Yes. Each trigger references a modal by **slug**; you can use several slugs on one page.

= Where is the modal markup output? =

A single dialog shell is printed once in `wp_footer` when assets load (shortcode in content, `data-wp-leave-modal` in post HTML, or the `wp_leave_modal_enqueue` filter).

= Does it work with block themes? =

Yes. Use a Shortcode block or HTML block with `data-wp-leave-modal`.

== Changelog ==

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

= 1.1.1 =
Improves `<a>` triggers and optional `href` fallback for Continue.

= 1.1.0 =
Adds multiple modals and slug-based triggers. Existing sites are migrated to a `default` modal.
