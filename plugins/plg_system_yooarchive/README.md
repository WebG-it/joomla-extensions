# Archive Layouts for YOOtheme (plg_system_yooarchive)

Joomla system plugin by WebG. It adds to the YOOtheme Pro builder a template type for the
Joomla archive view (`com_content` view `archive`, menu item type *Articles > Archived
Articles*), plus an optional automation that keeps archived articles in one category.

- Joomla 5.x and 6.x, PHP 8.1 or later, YOOtheme Pro 5 (with YOOtheme missing or older than
  5.0 the builder part stays off and administrators are warned; the automation still works).
- Tested on Joomla 6.2.0 and 5.4.10, PHP 8.4, YOOtheme Pro 5.0.51, together with Essentials for
  YOOtheme Pro 3.0.15 and 4SEO 7.2.0 (see *Tested compatibility* below).
- Nothing is written inside `templates/yootheme`: the plugin survives YOOtheme updates.
- License: GNU GPL version 2 or later (see `LICENSE.txt`).

## Builder template

After installing (the plugin is enabled automatically) open YOOtheme Pro, sidebar item
**TEMPLATES**, button **NEW TEMPLATE**, and choose the page **Archived Articles**. Assignment:

| Field | Meaning |
|---|---|
| Limit by Categories | The archive menu item must be filtered on one of these categories (Joomla passes them as `catid[]`). Empty = every archive page. Child categories: exclude, include or only. |
| Limit by Page Number | All pages, first page, all except the first. |
| Limit by Language | As for the other YOOtheme templates (multilingual sites). |

Dynamic content, group **Archive**:

- **Archived Articles**: list of the archived articles of the current page, exposed as the
  YOOtheme *Article* type, so every article field (title, images, link, dates, category,
  author, tags, meta) and every custom field is available. Arguments: start, quantity, and an
  optional re-sort of the current page (published, created, modified, title, hits, ordering, id).
- **Archived Article**: one article of the current page.
- **Archive Information**: article count, current page and page count, filtered year and
  month, search text, categories of the menu item, and a ready-made **filter form** (HTML:
  month, year, title search when enabled in the menu item, items per page). The filter form is
  the only field that returns HTML; search text and category titles are escaped.

The YOOtheme **Pagination** element works on this template (the Joomla archive pagination
is passed to the builder, filters included).

The order across pages and the number of articles per page are those of the menu item
(*Article Order*, *Date for Ordering*, *# Articles*), exactly as in Joomla. Month and year
filters are the native ones (`?month=&year=`).

Without a template assigned to a page, the archive is rendered by Joomla/YOOtheme as before.

## Automation (off by default)

In the plugin options, tab *Automation*:

- **Archive category**: the category that holds the archived articles. If it is deleted or
  trashed the automation pauses and the administrators see a warning. It must be published:
  otherwise moved articles are not public and the administrators see a warning.
- **Require create permission** (on by default): see *Permissions* below.
- **Move archived articles**: an article archived from the list, the toolbar, the edit form or
  the Web Services API is moved to the archive category.
- **301 redirect from the old address** (on by default): when the category is part of the
  article URL, entering the archive category changes the address; the old one is redirected
  (301) to the new one with a record in the Joomla *Redirects* component (served by the core
  *System - Redirect* plugin; administrators are warned if it is off). Applies to the moves made
  by the plugin and to the published articles that a user moves into the archive category.
- **Archive articles moved into the category**: a published article saved into the archive
  category, or moved there with *Batch*, is archived.
- **Force archived** (off by default): when on, a published article in the archive category is
  archived again even if it was republished on purpose.

Archiving goes through the Joomla article model (`publish()`), so Smart Search, cache, action
logs and the other plugins see a normal state change, with the usual permission and check-out
rules. Moves update the article asset and the Smart Search index. A guard prevents any recursion.

**Redirects** are written by the plugin (no permission on the Redirects component is needed),
only for the moves allowed below; an existing redirect for the same old address that the plugin
did not create is never changed. The log is rotated above 1 MB (one previous file kept as `plg_system_yooarchive.1.php`).

**Permissions.** An article is moved only when a logged-in user archives it and that user has
*Edit State* on the article; with *Require create permission* on, the user also needs *Create*
in the archive category. Articles being edited by another user (checked out) are skipped.
Guests and the command line never move anything.

**Messages.** The backend shows short, generic messages; ids and database errors go only to
the log `administrator/logs/plg_system_yooarchive.php`. In the frontend (article editing) the
messages are shown only to users who can manage articles.

Limits: one archive category per site; moves ignore the article language (use a category with
language *All* on multilingual sites); not tested with Joomla *Workflows* enabled (the
default is off).

## Tested compatibility

Tested on 16-10-2026 in a local Docker environment (Apache, PHP 8.4.26, MariaDB 11.4) with all
the extensions below active together, on two sites:

| Software | Version |
|---|---|
| Joomla | 6.2.0 and 5.4.10 |
| YOOtheme Pro | 5.0.51 |
| Essentials for YOOtheme Pro (ZOOlanders) | 3.0.15 (PRO) |
| 4SEO (Weeblr) | 7.2.0.2824 (full) |

What was checked, on both Joomla versions:

- **Install, update and uninstall** of this plugin with the other extensions active. Order 1:
  this plugin installed first, then 4SEO and Essentials. Order 2: this plugin uninstalled and
  installed again with 4SEO and Essentials already active. Update from 1.1.0 to 1.2.0 and from
  1.2.0 to 1.2.1 (through the update server). PHP error log and browser console empty.
- **YOOtheme Pro**: templates of the other page types (single article, category blog, featured
  articles, Smart Search) render the same HTML with this plugin on and off (only hit counters and
  random 4SEO probes differ). Customizer opening time and page response times are the same
  within measurement noise.
- **Essentials for YOOtheme Pro**: its elements (Markdown, Social Sharing) and its sources (a CSV
  source) work in every template, including *Archived Articles*; the *Archive* group appears in
  the source picker of Essentials elements and feeds them; Essentials source filters (for
  example *Date modify*) work on the archive fields; no clash of GraphQL type or source names,
  of builder events or of template types. The customizer opens with no error.
- **4SEO**: on archive pages built with the template, title, meta description, canonical,
  structured data and breadcrumb are identical to those of the plain Joomla archive page
  (plugin off), also on numbered pages (`?start=`) and with month and year filters that have
  results (a filter with no results was not compared). The 4SEO site
  analysis follows the links of the template (articles and pagination). Sitemap with *Exclude
  archived* on: archived articles are left out; off: they are listed with their new address
  after the automation has moved them.

Not tested: the update of YOOtheme Pro to 5.0.52 with this plugin active (the package was not
available), Joomla *Workflows*.

**Notes for 4SEO users.** 4SEO does not create a redirect when an article changes URL. Keep the
option *301 redirect from the old address* on (with the core *System - Redirect* plugin on) if
the category is part of your article URLs. After archiving many articles at once, run a new
site analysis in 4SEO (*Pages > Reset analysis*) so that sitemap and canonical tags use the new
addresses. Numbered archive pages are treated by 4SEO like any other page.

With article URLs that contain the id (`/<id>-alias`) Joomla finds the article by id whatever
the category in the address, so the old URL stays live after the move (no 404) and the
redirect is not used; 4SEO then keeps the old canonical until its next site analysis.

**Notes for Essentials users.** In the *Social Sharing* element leave the shared URL empty to
share the current page: mapped to the article *Link* field it receives a non-SEF address, on the
archive template as on the YOOtheme article template (Essentials behaviour).

## Uninstall

*System > Manage > Extensions*, select the plugin, *Uninstall*. The plugin keeps no tables and
no files outside its folder. Builder templates of type *Archived Articles* stay in YOOtheme as
"not assigned" and come back if the plugin is reinstalled. The redirects stored by the
automation stay in *System > Redirects* (note starting with "Archive Layouts for YOOtheme:")
and the log stays in `administrator/logs/`.

## Development

Source, build script and Docker test environment: https://github.com/WebG-it/joomla-extensions
