# Changelog

## 1.2.0 (2026-10-16)

Compatibility release: tested together with Essentials for YOOtheme Pro and 4SEO.

- New option *301 redirect from the old address* (on by default): when the category is part of
  the article URL, the automation stores a 301 redirect from the old to the new address in the
  Joomla *Redirects* component, both for archived articles moved by the plugin and for published
  articles that a user moves into the archive category. 4SEO does not create redirects when an
  article URL changes, so without this the old address returned 404. Administrators are warned
  if the core *System - Redirect* plugin is off.
- The *Archive* sources have a description in the source picker, like the other sources when
  Essentials for YOOtheme Pro is installed.
- Manifest: added the `<licence>` element required by the `develop` branch of the JED Checker
  (rule PH3), next to the standard `<license>`.
- Tested compatibility documented: Joomla 6.2.0 and 5.4.10, PHP 8.4, YOOtheme Pro 5.0.51,
  Essentials for YOOtheme Pro 3.0.15, 4SEO 7.2.0.

## 1.1.0 (2026-10-16)

Security review.

- Automation moves only for a logged-in user with *Edit State* on the article; articles checked
  out by another user are skipped; guests and the command line never move.
- New option *Require create permission* (on by default): the user must also be allowed to
  create articles in the archive category.
- Screen messages are generic and translated; details (ids, database errors) only in the log.
  In the frontend messages are shown only to users who can manage articles.
- Archive category deleted or trashed: the automation pauses and administrators are warned.
- Search text and category titles of *Archive Information* are escaped; the filter form is the
  only HTML field.
- Archive category unpublished: administrators are warned that moved articles are not public.
- YOOtheme Pro missing or older than 5.0: the builder integration stays off and administrators
  are warned on the extension and template screens.

## 1.0.0 (2026-10-16)

First release.

- YOOtheme Pro builder template type *Archived Articles* for the Joomla archive view, with
  assignment by category (with child categories), page number and language.
- Dynamic sources *Archived Articles*, *Archived Article* and *Archive Information* (count,
  page, filtered month and year, search text, categories, filter form).
- Custom fields of archived articles available in the builder (Joomla prepares archive items
  with the `com_content.archive` context, which loads no fields).
- Customizer preview opens the archive menu item that matches the template categories.
- Optional automation: archive moves to a chosen category, a published article saved or
  batch-moved into that category is archived through the article model; "force archived".
- en-GB and it-IT languages.
