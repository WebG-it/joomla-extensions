## Archive Layouts for YOOtheme 1.2.0

Compatibility release of **Archive Layouts for YOOtheme** (`plg_system_yooarchive`): tested
together with **Essentials for YOOtheme Pro** and **4SEO** on Joomla 6.2 and 5.4.

**New**
- Option *301 redirect from the old address* (on by default): when an article enters the
  archive category and its URL changes (the category is part of the address), a 301 redirect
  from the old address is stored in Joomla *Redirects*, so links and search engines do not end
  on a 404 page. Covers the moves made by the automation and the published articles moved into
  the archive category by a user. Needs the core *System - Redirect* plugin (administrators are
  warned when it is off). 4SEO does not create these redirects by itself.
- Descriptions for the *Archive* sources in the builder source picker.

**Fixed**
- Manifest: `<licence>` element required by the `develop` branch of the JED Checker (rule PH3).

**Tested compatibility** (all active together): Joomla 6.2.0 and 5.4.10, PHP 8.4, YOOtheme Pro
5.0.51, Essentials for YOOtheme Pro 3.0.15, 4SEO 7.2.0.2824. Install, update and uninstall in both
orders; Essentials elements, sources and filters in every template including *Archived
Articles*; other YOOtheme templates unchanged; 4SEO title, description, canonical, structured
data, breadcrumb and sitemap unchanged. Details:
[tested compatibility](https://github.com/WebG-it/joomla-extensions/blob/main/docs/yooarchive/USAGE.md#tested-compatibility).
JED Checker `master`: no errors, warnings or notices. JED Checker `develop`: no errors or
warnings, one notice about the `<licence>` element that its own rule PH3 requires.

**Requirements**: Joomla 5 or 6, PHP 8.1+, YOOtheme Pro 5.

**Install or update**: download `plg_system_yooarchive-1.2.0.zip` below and upload it in
*System > Install > Extensions*, or update from *System > Update > Extensions*.

Docs: [user guide](https://github.com/WebG-it/joomla-extensions/blob/main/docs/yooarchive/USAGE.md) ·
[guida in italiano](https://github.com/WebG-it/joomla-extensions/blob/main/docs/yooarchive/USO.md) ·
[changelog](https://github.com/WebG-it/joomla-extensions/blob/main/plugins/plg_system_yooarchive/CHANGELOG.md)

sha256 `a103fb557176fd4532efade8e623a36b4a329d4c796bf35813052e468a5a5a92`
