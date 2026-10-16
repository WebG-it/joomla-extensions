# Archive Layouts for YOOtheme: user guide

For **site managers**: install, build the archive page with the YOOtheme Pro builder, switch on
the automation, update, uninstall. Everything from the Joomla and YOOtheme interfaces.
Italian version: [USO.md](./USO.md). Developers: [DEVELOPING.md](./DEVELOPING.md) (Italian).

## What it does

Joomla has an **Archived Articles** view (menu item *Articles > Archived Articles*), but
YOOtheme Pro cannot design it with the builder: you only get the standard list. This plugin
adds the **Archived Articles** template type to the builder, with the archived articles as
dynamic content (title, images, link, dates, category, author, tags and **all custom fields**),
pagination and the month and year filters.

Optionally it keeps archived articles in one category: click **Archive** and the article is
moved to the chosen category; move an article into that category and it is archived.

Requirements: Joomla 5 or 6, PHP 8.1+, YOOtheme Pro 5 (tested with YOOtheme Pro 5.0.51 on
Joomla 6.2.0 and PHP 8.4).

![Dynamic source in the builder](./images/builder-source.jpg)

## 1. Install

1. Download `plg_system_yooarchive-X.Y.Z.zip` from the
   [releases](https://github.com/WebG-it/joomla-extensions/releases) or from `dist/`.
2. *System > Install > Extensions > Upload Package File*.
3. The plugin is enabled automatically. Nothing changes on the site until you create a
   template or switch on the automation.

## 2. Build the archive page

1. Create (if missing) a menu item *Articles > Archived Articles*. In its options choose the
   categories (empty = all), the order (*Article Order*, *Date for Ordering*) and the number
   of articles per page (*# Articles*).
2. In YOOtheme Pro open *Templates > New Template*, page **Archived Articles**:
   - *Limit by Categories*: the template applies only to archive menu items filtered on
     those categories (with or without child categories). Empty = every archive page.
   - *Limit by Page Number* and *Limit by Language* as for the other templates.
3. Design the layout. In the *Dynamic Content* of an element (for example a Grid) choose the
   **Archive** group:
   - **Archived Articles**: the list of the current page. *Start* and *Quantity* take a part
     of it; *Order* re-sorts the current page (the order across pages is the one of the menu item).
   - **Archived Article**: a single article.
   - **Archive Information**: article count, page, filtered year and month, categories and a
     ready-made **filter form** (month, year, items per page) for an HTML element. The filter
     form is the only field that returns HTML; the other text fields are escaped.
4. Add a **Pagination** element below the grid.
5. The customizer preview opens the matching archive menu item by itself.

Without an assigned template the archive page stays as it was.

![Custom fields mapped in a grid item](./images/builder-fields.jpg)
![Archive page built with the template (test data)](./images/frontend-grid.jpg)

## 3. Automation (off by default)

*System > Plugins > System - Archive Layouts for YOOtheme*, tab **Automation**:

| Option | What it does |
|---|---|
| Enable automation | Switches everything below on. |
| Archive category | Where archived articles go. Without a category, or if the category is deleted or trashed, the automation pauses and administrators see a warning. |
| Require create permission | On by default: move only if the user who archives can also create articles in the archive category. |
| Move archived articles | Archive (list, toolbar, edit form or API) = move to the category. |
| Archive articles moved into the category | A published article saved or moved (also with *Batch*) into the category is archived. |
| Force archived | Off: an article already in the category can be republished on purpose. On: it is archived again every time. |

After each action a blue message tells what the plugin did. Smart Search, cache and versions
are updated as with a manual click.

Permissions: an article is moved only when a logged-in user with *Edit State* on the article
archives it (and, with the option on, with *Create* in the archive category). Articles being
edited by another user are never touched. Guests and the command line never move anything.

If something goes wrong (missing permission, duplicate alias in the target category) a generic
yellow message appears; details (article ids, database error) go only to the log
`administrator/logs/plg_system_yooarchive.php`. In the frontend (article editing) messages are
shown only to users who can manage articles.

![Automation options](./images/automation-options.jpg)

## 4. Update

Updates arrive in *System > Update > Extensions* like any other extension (WebG update server).

## 5. Uninstall

*System > Manage > Extensions*, search "Archive Layouts", *Uninstall*. No tables or files are
left. Builder templates of type *Archived Articles* stay in YOOtheme as "not assigned" and come
back if you reinstall the plugin; the archive page goes back to the Joomla one.

## Support

Questions and bug reports: [GitHub issues](https://github.com/WebG-it/joomla-extensions/issues)
(please write the Joomla, PHP and YOOtheme Pro versions). Never post passwords, keys or
private data in an issue.
