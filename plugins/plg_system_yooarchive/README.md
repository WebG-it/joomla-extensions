# Archive Layouts for YOOtheme (plg_system_yooarchive)

Joomla system plugin by WebG. It adds to the YOOtheme Pro builder a template type for the
Joomla archive view (`com_content` view `archive`, menu item type *Articles > Archived
Articles*). Since 2.0 it does only the layout: the optional automation that keeps archived
articles in one category (up to 1.2.x part of this plugin) is the separate plugin **Auto Archive**.

- Joomla 5.x and 6.x, PHP 8.1 or later, YOOtheme Pro 5 (with YOOtheme missing or older than
  5.0 the builder part stays off and administrators are warned).
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

## Updating from 1.x

Up to 1.2.x this plugin also contained the archive automation. It is now **Auto Archive**
(https://github.com/WebG-it/joomla-extensions/blob/main/docs/autoarchive/USAGE.md). With the
automation **off** the update changes nothing. With the automation **on**, the update to 2.0 is
refused with a clear message (nothing is changed) until Auto Archive is installed and enabled;
Auto Archive imports the old settings and switches the old automation off when it is installed.

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
  installed again with 4SEO and Essentials already active. Update from 1.1.0 to 1.2.0, from
  1.2.0 to 1.2.1 and from 1.2.1 to 2.0.0 (through the update server). PHP error log and browser console empty.
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
  archived* on: archived articles are left out; off: they are listed.

Not tested: the update of YOOtheme Pro to 5.0.52 with this plugin active (the package was not
available), Joomla *Workflows*.

**Notes for 4SEO users.** Numbered archive pages are treated by 4SEO like any other page. 4SEO
does not create a redirect when an article changes URL: if you move articles into an archive
category, Auto Archive stores the 301 redirects for the moves it makes.

**Notes for Essentials users.** In the *Social Sharing* element leave the shared URL empty to
share the current page: mapped to the article *Link* field it receives a non-SEF address, on the
archive template as on the YOOtheme article template (Essentials behaviour).

## Uninstall

*System > Manage > Extensions*, select the plugin, *Uninstall*. The plugin keeps no tables and
no files outside its folder. Builder templates of type *Archived Articles* stay in YOOtheme as
"not assigned" and come back if the plugin is reinstalled.

## Development

Source, build script and Docker test environment: https://github.com/WebG-it/joomla-extensions
