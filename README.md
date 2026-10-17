# WebG Joomla Extensions (catalog)

Joomla extensions by [WebG](https://www.webg.it), free software under the GNU GPL v2 or later.
**Each extension now lives in its own repository**, with its own releases, issues, documentation and
update server. This repository is the catalog and keeps the old shared update address alive for
sites installed before the split.

| Extension | Type | Version | Repository |
|---|---|---|---|
| Archive Layouts for YOOtheme | plugin / system | 2.0.1 | [WebG-it/archive-layouts](https://github.com/WebG-it/archive-layouts) |
| Auto Archive | plugin / system | 1.1.0 | [WebG-it/auto-archive](https://github.com/WebG-it/auto-archive) |
| Cloudflare Cache Purge | package (plugins, module, API) | 2.1.0 | [WebG-it/cloudflare-cache-purge](https://github.com/WebG-it/cloudflare-cache-purge) |

Shared tools for developing and testing them (Docker test sites for Joomla 5 and 6, JED Checker
helpers, reproducible zip build): [WebG-it/joomla-dev-tools](https://github.com/WebG-it/joomla-dev-tools).

Support, bug reports and downloads: in the repository of the extension you use.

## Update servers

| Extension | Update server |
|---|---|
| Archive Layouts for YOOtheme | `https://raw.githubusercontent.com/WebG-it/archive-layouts/main/updates/yooarchive.xml` |
| Auto Archive | `https://raw.githubusercontent.com/WebG-it/auto-archive/main/updates/autoarchive.xml` |
| Cloudflare Cache Purge | `https://raw.githubusercontent.com/WebG-it/cloudflare-cache-purge/main/updates/cfpurge.xml` |

`collection.xml` in this repository lists the same three extensions and points to those update
servers.

## If your site still uses the old address

The versions released before the split (Archive Layouts 2.0.0 and earlier, Auto Archive 1.0.1, Cloudflare
Cache Purge 1.1.4 and earlier) check `https://raw.githubusercontent.com/WebG-it/joomla-extensions/main/collection.xml`. That
address keeps working: it announces Archive Layouts 2.0.1 (a patch that changes only the update address), Auto Archive 1.1.0 and Cloudflare Cache Purge 2.1.0. In *System > Update >
Extensions* install the update: the patch changes nothing but the update address, and from then on the
extension checks its own repository. Auto Archive and Cloudflare Cache Purge are not installed on any
known site: their releases from 1.1.0 and 2.1.0 on already declare their own repository, and the old 1.x versions
are offered the new ones from this address too.

## What stays here

- `collection.xml`, `updates/*.xml`, `changelogs/*.xml`: the legacy update files described above
  (changelogs are read by already installed manifests). Frozen: the live copies are in the
  extension repositories.
- `dist/`: the zips released from this repository (old download addresses keep working).
- The full history, including everything that moved: sources, docs and tools are in the commits
  before the split.
