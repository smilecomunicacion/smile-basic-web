=== SMiLE Basic Web ===
Contributors: smilecomunicacion
Tags: contact, reCAPTCHA, SMTP, sitemaps, svg
Requires at least: 6.3
Tested up to: 6.8
Stable tag: 1.3.9
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

SMiLE Basic Web is a versatile plugin combining features like Contact Form, sitemap generation, and configuration tools, all from unified interface.

== Description ==
SMiLE Basic Web is a powerful, modular, and user-friendly WordPress plugin that integrates multiple essential tools into a single solution. It includes a flexible Contact Form system with real-time email preview via the Customizer, a dynamic Sitemap Generator supporting various formats, and a comprehensive Cookie Consent Manager fully compliant with international data protection regulations such as GDPR and ePrivacy. With lightweight, accessible, and brand-adaptable interfaces, SMiLE Basic Web empowers site owners to ensure transparency, control, and legal compliance while maintaining a seamless user experience.

**Key Features:**

- Customizable contact form with advanced SMTP configuration.
- Support for multiple custom fields, including new types: single/multi-select, user email, and textarea.
- Required field indicators and placeholders.
- Field reordering with drag-and-drop interface.
- Google reCAPTCHA v3 integration for spam protection.
- Send a copy of the form to the user, with a customizable message.
- Insert logo and company link in user copy emails.
- Privacy policy and legal notice checkbox fields with linked pages.
- Optional marketing opt-in field with customizable text.
- Explanation field to describe the purpose of the form.
- Real-time preview of the user email using the WordPress Customizer.
- Modular tab interface supporting additional tools.
- Dynamic generation of:
  - `llms.txt` (text or JSON format).
  - `sitemap.xml`
  - `sitemap-images.xml`
  - `robots.txt`
- New “General” tab that centralises global options.
- Toggle to allow safe SVG / SVGZ uploads (sanitised & thumb-ready).
- Automatic image Alt-Text: copies IPTC/XMP “Alt Text Accessibility” (fallback to Title).
-Cookie Consent Panel:
    - Clean, responsive, and customizable cookie banner.
    - Three display sizes: Small, Large, or Fullscreen.
    - Consent tab with position options: Left, Center, or Right.
    - Auto-hide on Accept or Deny with full keyboard accessibility.
    - Multilingual-ready and fully translatable (.pot included).
    - Preferences panel for per-script consent using `<details>`.
    - Add unlimited scripts with name, description, and JS code.
    - Scripts injected only on Accept; removed on Deny.
    - Consent stored securely in LocalStorage.
    - Backend settings for texts, styles, legal pages, and tab behavior.
    - Fully compliant with GDPR, CNIL, and ePrivacy.
    - Built with vanilla JavaScript – no jQuery dependency.


Use the shortcode `[smile_contact_form]` to embed the form on any page or post.

== Installation ==
1. Upload the `smile-basic-web` plugin folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in your WordPress admin panel.
3. Go to the "SMiLE Basic Web" settings page in the admin menu.
4. Configure the Contact Form and other tools in their respective tabs.
5. Insert the form anywhere using the `[smile_contact_form]` shortcode.
6. In the “Sitemaps” tab, activate the dynamic generation of llms.txt, sitemap.xml, sitemap-images.xml and robots.txt.
7. Enable the Cookie Notice and configure the appearance and behavior of the panel.
7. Optionally add third-party tracking scripts and descriptions in the Preferences section.


== External services ==
This plugin integrates Google reCAPTCHA v3 to protect the form from spam and abuse.

*What the service is and what it is used for:*
Google reCAPTCHA v3 analyzes user behavior to block automated spam submissions.

*What data is sent and when:*
Upon form submission, the following data is sent to Google:
- reCAPTCHA token
- User's IP address
- Your reCAPTCHA secret key

*Links to terms and privacy policy:*
- [Google Terms of Service](https://policies.google.com/terms)
- [Google Privacy Policy](https://policies.google.com/privacy)

== Frequently Asked Questions ==

= What is SMiLE Basic Web? =
A feature-rich plugin with a modular system for Contact Form, sitemaps, and email customization.

= How can I add or reorder custom fields? =
Navigate to the "Custom Fields" section under the "Contact Form" tab. Use the admin interface to add, edit, delete, and reorder fields.

= How does the user copy feature work? =
If enabled, users receive an HTML email containing their submitted data, a custom message, your logo, and a link to your company.

= Can I preview the email sent to users? =
Yes, go to "Customize" > "Email Preview" in the WordPress Customizer to see how the email will look.
= Are privacy and marketing consents included? =
Yes. You can enable privacy and legal notice checkboxes and add a marketing opt-in field with custom text.

= What are the new features in 1.2.0? =
This version introduces a dedicated “Sitemaps” tab where you can generate llms.txt, sitemap.xml, robots.txt, and images sitemap dynamically.

= Does this plugin block scripts until consent is given? =
Yes. Scripts are injected **only after the user gives consent**, and only those matching accepted categories.

= Is it GDPR-compliant? =
Yes. This plugin was built with GDPR and ePrivacy Directive in mind.

= Can I add my own scripts or analytics tools? =
Absolutely. You can register any custom script in the admin panel, along with a name and purpose.

== Changelog ==

= 1.3.9 =
* UPDATED: Documented compatibility with WordPress 6.8 and modern PHP versions.
* FIXED: Clarified consent-instructions workflow to prevent confusing field duplication in multilingual installs.
* FIXED: Hardened sanitization around Customizer previews so live form styles render reliably.

= 1.3.3 =
* FIXED: allow pasting hexadecimal color values in the colour picker input.
* FIXED: added translation support for minimized label positions (Left, Center, Right).

= 1.3.2 =
* FIXED: JavaScript translations now load correctly by registering `wp_set_script_translations()` for **sbwscf-cookies-panel**, ensuring all cookie-banner strings are translatable.

= 1.3.1 =
* FIXED: Links to Cookies Policy, Privacy Policy and Legal Notice open in a new tab.
* FIXED: “Accept Preferences” button was not displayed on first page load.

= 1.3.0 =
* NEW: Added “General” tab; now loaded first and order overridable via filters.
* NEW: Safe SVG / SVGZ upload support with sanitisation, dedicated checkbox in General tab.
* NEW: Auto-populate image Alternative-Text from embedded XMP AltTextAccessibility or IPTC Title.
* UPDATED: Uninstall script now deletes preview page, options, transients and cache keys across all sites.
* Initial public release of Cookie Consent functionality.
* Includes responsive and accessible cookie panel.


= 1.2.1 =
* FIXED: Resolved settings conflicts between tabs by properly separating `option_group` and `option_page` in `register_setting()`.
* FIXED: Fixed issue that prevented the "SMiLE Basic Web Form Appearance" section from appearing in the WordPress Customizer.
* UPDATED: Fully implemented modular tab architecture (`SBWSCF_Tab_Manager` and `SBWSCF_Tab_Interface`), allowing new features to be added without altering the plugin core.
* UPDATED: Refactored script and style loading system to ensure assets are enqueued only when their corresponding tab is active.
* UPDATED: Integrated JavaScript internationalization using `wp.i18n.__()` and connected it with `wp_set_script_translations()` to enable translations via `.po` files.
* FIXED: Backend reCAPTCHA field validation improved for dynamic required fields.
* NEW: Full Multisite support added to `uninstall.php`, cleaning up options, transients, cron jobs, and custom pages network-wide.
* FIXED: Ensured the email preview page (`sbwscf-customizer-email-preview`) is created and properly linked to the Customizer for live email preview.
* FIXED: Removed duplicate URL entries in `sitemap.xml` to prevent confusion and ensure each canonical URL appears only once.
* UPDATED: `<lastmod>` timestamps in both `sitemap.xml` and `sitemap-images.xml` now include full ISO-8601 date and time for greater precision.
* FIXED: Included images embedded in pages so they now appear correctly in sitemap-images.xml.

= 1.2.0 =
* NEW: Added "Sitemaps" tab with dynamic generation of llms.txt, sitemap.xml, sitemap-images.xml, and robots.txt.
* NEW: Choose between TXT or JSON format for llms.txt.
* NEW: Filter by content types and set priority category in sitemap output.


= 1.1.0 =
* NEW: Added support for select (single/multiple) and "user email" field types.
* NEW: Added drag-and-drop field reordering.
* NEW: Added form explanation field.
* NEW: Added Legal Notice checkbox with link to a specific page.
* NEW: Added optional Marketing Opt-In checkbox with custom label.
* NEW: Introduced real-time email preview with WordPress Customizer.
* NEW: Added validation to prevent duplicate field names.
* UPDATED: Improved sanitization constants and structure.
* UPDATED: Modularized codebase for maintainability.

= 1.0.0 =
* Initial release of SMiLE Basic Web.
* Integrated Contact Form with SMTP settings.
* Supported custom fields with placeholders and required markers.
* Enabled user copy email with company logo and link.
* Added privacy policy checkbox.
* Integrated Google reCAPTCHA v3.

== Upgrade Notice ==

= 1.3.3 =
Please update to benefit from improved HEX input support in the colour picker and full translation readiness for label positions.

= 1.3.2 =
Adds missing JS translation loader; update to see translated cookie-banner labels.

= 1.3.1 =
* Links to Cookies Policy, Privacy Policy and Legal Notice now open in a new tab.
* Fixed issue where the “Accept Preferences” button did not appear on initial visit.


= 1.3.0 =
Major update introducing a new General tab with options for safe SVG uploads and automatic Alt-Text population from EXIF metadata.
Also includes full Cookie Consent functionality. Please review your tracking scripts and cookie settings after upgrading.

= 1.2.1 =
Internal plugin reorganization for improved modularity and future scalability. Review your settings in each tab after updating.

= 1.2.0 =
This version introduces a new “Sitemaps” tab with multiple output formats and dynamic endpoints. Please review your sitemap settings after upgrading.


== Screenshots ==
1. Screenshot-1.png: Admin interface showing SMTP, reCAPTCHA, and custom field configuration.
2. Screenshot-2.png: Appearance customization for the contact form using WordPress Customizer.
3. Screenshot-3.png: Live preview of the email sent to users via the WordPress Customizer.
4. Screenshot-4.png: Sitemaps settings tab for configuring llms.txt, sitemap.xml, and robots.txt.
4. Screenshot-5.png: Enable secure SVG/SVGZ uploads and auto-fill image ALT text from EXIF metadata for better accessibility.
4. Screenshot-6.png: Backend settings panel for customizing colors, text and scripts.
4. Screenshot-7.png: Cookie banner with preferences toggle, script injection only after consent is given.

== Other Notes ==
SMiLE Basic Web is actively maintained. If you find it helpful, consider supporting development through donations. Contributions and feedback are always welcome!

