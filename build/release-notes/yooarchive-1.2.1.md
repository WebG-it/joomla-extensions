## Archive Layouts for YOOtheme 1.2.1

Maintenance release after the review of 1.2.0. Update from *System > Update > Extensions*.

**Changed**
- Redirects: an existing redirect for the same old address that the plugin did not create
  (made by hand or by another extension) is never changed. The plugin only updates or
  unpublishes its own redirects, recognised by the note "Archive Layouts for YOOtheme:".
- The warning about the core *System - Redirect* plugin being off also appears when only
  *Archive articles moved into the category* is on.
- The plugin log is rotated above 1 MB (the previous file is kept as
  `plg_system_yooarchive.1.php`).

**Good to know**
- Redirects are written by the plugin, so the user needs no permission on the Redirects
  component; they are written only for moves allowed by the automation rules (*Edit State* on
  the article, and *Create* in the archive category when that option is on).
- With article URLs that contain the id (`/<id>-alias`) the old address stays live after the
  move, so no redirect is used.
- After uninstall the redirects stored by the plugin stay in *System > Redirects* (note starting
  with "Archive Layouts for YOOtheme:") and the log stays in `administrator/logs/`.

**Checked**: update 1.2.0 to 1.2.1 through the update server on Joomla 6.2.0 and 5.4.10 (with
YOOtheme Pro 5.0.51, Essentials for YOOtheme Pro 3.0.15 and 4SEO 7.2.0 active); a manual redirect
for the same old address stays untouched. JED Checker `master` and `develop`: no errors, no
warnings, one notice about the `<licence>` element (required by rule PH3 of `develop`, not known
to the manifest validation).

Docs: [user guide](https://github.com/WebG-it/joomla-extensions/blob/main/docs/yooarchive/USAGE.md) ·
[guida in italiano](https://github.com/WebG-it/joomla-extensions/blob/main/docs/yooarchive/USO.md) ·
[changelog](https://github.com/WebG-it/joomla-extensions/blob/main/plugins/plg_system_yooarchive/CHANGELOG.md)

sha256 `2ec106d1d15de4dc3c735ab86cc8b61ed9abae6378933890d40dcad63270bb5a`
