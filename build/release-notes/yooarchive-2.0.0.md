## Archive Layouts for YOOtheme 2.0.0

The plugin is split in two. This one keeps the **layout** (the Archived Articles template and
sources for the YOOtheme Pro builder). The archive **automation** is now the separate plugin
[Auto Archive](https://github.com/WebG-it/joomla-extensions/blob/main/docs/autoarchive/USAGE.md),
which also works without YOOtheme. Update from *System > Update > Extensions*.

**If you never switched the automation on**: just update, nothing changes.

**If the automation is on**: the update to 2.0.0 is refused with a clear message and nothing is
changed, until Auto Archive 1.0.0 is installed, enabled and switched on. Install Auto Archive first: it
imports your settings (category and options) and switches the old automation off so the two never
run together; then update this plugin. The 301 redirects stored by 1.x stay in *System >
Redirects* and are managed by Auto Archive.

**Changed**
- Removed from this plugin: the automation, its options, messages and log.
- The old automation files are removed on update; the options screen says where the automation went.
- Unchanged: the template type, the *Archive* dynamic sources, the filter form, assignment fields
  and the compatibility with Essentials for YOOtheme Pro and 4SEO.

**Checked**: update 1.2.1 to 2.0.0 on Joomla 6.2.0 and 5.4.10 with the automation off, with it on
and Auto Archive installed first, and with it on and Auto Archive missing (update refused, nothing
changed); also with YOOtheme Pro 5.0.51, Essentials for YOOtheme Pro 3.0.15 and 4SEO 7.2.0 active.
JED Checker `master` and `develop`: no errors, no warnings, one notice about the `<licence>` element.

Docs: [user guide](https://github.com/WebG-it/joomla-extensions/blob/main/docs/yooarchive/USAGE.md) ·
[guida in italiano](https://github.com/WebG-it/joomla-extensions/blob/main/docs/yooarchive/USO.md) ·
[changelog](https://github.com/WebG-it/joomla-extensions/blob/main/plugins/plg_system_yooarchive/CHANGELOG.md)
