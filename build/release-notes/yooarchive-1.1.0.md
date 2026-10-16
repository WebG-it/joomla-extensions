## Archive Layouts for YOOtheme 1.1.0

First public release of **Archive Layouts for YOOtheme** (`plg_system_yooarchive`), a Joomla
system plugin by WebG that lets you design the Joomla *Archived Articles* page with the
YOOtheme Pro builder.

**Features**
- YOOtheme Pro template type *Archived Articles* (menu item *Articles > Archived Articles*),
  assignable by category (with child categories), page number and language.
- Dynamic content group *Archive*: archived articles with every article field and all custom
  fields, single article, archive information (count, page, month/year filter, filter form).
  Works with the YOOtheme Pagination element.
- Optional automation (off by default): archiving moves the article to a chosen category, a
  published article saved or batch-moved into that category is archived through the Joomla
  model. Permission-aware: logged-in users with Edit State only (and Create on the archive
  category, configurable), checked-out articles skipped, never for guests or CLI.
- English and Italian. Survives YOOtheme updates.

**Requirements**: Joomla 5 or 6, PHP 8.1+, YOOtheme Pro 5 (tested on Joomla 6.2.0, PHP 8.4,
YOOtheme Pro 5.0.51). JED Checker: no errors, no warnings.

**Install**: download `plg_system_yooarchive-1.1.0.zip` below and upload it in
*System > Install > Extensions*. Updates arrive through the WebG update server.

Docs: [user guide](https://github.com/WebG-it/joomla-extensions/blob/main/docs/yooarchive/USAGE.md) ·
[guida in italiano](https://github.com/WebG-it/joomla-extensions/blob/main/docs/yooarchive/USO.md) ·
[changelog](https://github.com/WebG-it/joomla-extensions/blob/main/plugins/plg_system_yooarchive/CHANGELOG.md)

sha256 `eb5134a07a624e5438d54602b6ba2a2be6dcc7935662af8ba0589ce52c85f795`
