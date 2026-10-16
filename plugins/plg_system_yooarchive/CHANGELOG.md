# Changelog

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
