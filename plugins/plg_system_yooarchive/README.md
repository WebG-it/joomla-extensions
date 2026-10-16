# Archive Layouts for YOOtheme (plg_system_yooarchive)

Joomla system plugin by WebG. It adds to the YOOtheme Pro builder a template type for the
Joomla archive view (`com_content` view `archive`, menu item type *Articles > Archived
Articles*), plus an optional automation that keeps archived articles in one category.

- Joomla 5.x and 6.x, PHP 8.1 or later, YOOtheme Pro 5 (with YOOtheme missing or older than
  5.0 the builder part stays off and administrators are warned; the automation still works) (tested on Joomla 6.2.0, PHP 8.4, YOOtheme Pro 5.0.51).
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
- **Archive articles moved into the category**: a published article saved into the archive
  category, or moved there with *Batch*, is archived.
- **Force archived** (off by default): when on, a published article in the archive category is
  archived again even if it was republished on purpose.

Archiving goes through the Joomla article model (`publish()`), so Smart Search, cache, action
logs and the other plugins see a normal state change, with the usual permission and check-out
rules. Moves update the article asset and the Smart Search index. A guard prevents any recursion.

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

## Uninstall

*System > Manage > Extensions*, select the plugin, *Uninstall*. The plugin keeps no tables and
no files outside its folder. Builder templates of type *Archived Articles* stay in YOOtheme as
"not assigned" and come back if the plugin is reinstalled.

## Development

Source, build script and Docker test environment: https://github.com/WebG-it/joomla-extensions
