# Archive Layouts for YOOtheme: developer guide

English summary of [DEVELOPING.md](./DEVELOPING.md) (Italian, more detailed).

> Public repository: never commit tokens, passwords, keys, server hosts or customer data, and
> never YOOtheme Pro files (proprietary). The test environment mounts YOOtheme from outside.

## Architecture

```
plugins/plg_system_yooarchive/
  yooarchive.xml                manifest (namespace WebG\Plugin\System\Yooarchive)
  script.php                    PHP >= 8.1, Joomla >= 5.0, enables the plugin on first install
  services/provider.php         Joomla 5/6 service provider
  bootstrap.php                 YOOtheme module loaded with Application::load()
  src/Extension/Yooarchive.php  plugin (SubscriberInterface)
  src/Automation/Automation.php archive/category automation
  src/YOOtheme/...              builder integration
  language/{en-GB,it-IT}/
```

| YOOtheme event | Class | Role |
|---|---|---|
| `BuilderConfig` (priority 10) | `LoadBuilderConfig` | adds the `com_content.archive` template type and its assignment fields |
| `builder.template` | `MatchTemplate` | on the archive view returns type, assignment query and params (`items`, `pagination`, `archive`) |
| `builder.template.load` | `LoadTemplateUrl` | customizer preview URL: the archive menu item matching the template categories |
| `source.init` | `SourceListener` | queries `yooarchiveArticles` (list of `Article`), `yooarchiveArticle`, `yooarchive` (`YooarchiveInfo`) |

Joomla prepares archive items with the `com_content.archive` context, which com_fields does not
map to `article`, so `jcfields` is empty; `ArchiveQueryType::prepareItems()` drops the empty list
and YOOtheme reloads the fields with `com_content.article`.

Automation: `onTableBeforeStore` remembers category and state; `onTableAfterStore` queues an
archive or a move; `onContentChangeState` handles list/toolbar/API state changes; the queue runs
on `onContentAfterSave` / `onContentCleanCache`. Archive = `ArticleModel::publish($pks, 2)`;
move = `Table::store()` with the new `catid` plus `onFinderAfterSave`. Moves need a logged-in user
with `core.edit.state` on the article (and `core.create` on the target category when *Require
create permission* is on), skip articles checked out by others, never run for guests or CLI.
Screen messages are generic language strings; details go to `Log::add()` only. A static `$busy`
flag prevents recursion. An invalid (missing or trashed) archive category pauses the automation.

Redirects (option *301 redirect from the old address*, since 1.2.0): the public URL is built with
`Route::link('site', RouteHelper::getArticleRoute(id, catid, language))` before and after the
category change (in `move()` for the plugin's own moves, in `onTableBeforeStore` for a published
article a user brings into the category, with the stored category and alias). When the URLs
differ and are SEF, a row is stored in `#__redirect_links` through the com_redirect `Link` table
(301; an existing row with the same old URL is updated; a row starting from the new URL, now a
live page again, is unpublished). The core *System - Redirect* plugin serves it on 404 pages;
administrators are warned when it is off. 4SEO does not create redirects by itself when an
article URL changes (checked with 4SEO 7.2.0). Since 1.2.1 the plugin only updates or unpublishes
rows whose note starts with `Automation::REDIRECT_MARKER`; an existing row for the same old URL
without the marker is left alone (log line only). The log is rotated above 1 MB to
`plg_system_yooarchive.1.php` (kept as .php for Joomla's `die()` guard).

## Test environment (Docker)

`dev/yooarchive/`: official Joomla 6.2.0 zip (sha256 verified), PHP 8.4 + Apache, MariaDB 11.4,
port 8765 on 127.0.0.1. Passwords and the YOOtheme Pro folder are set in `.env` (not versioned,
copy `.env.example`).

On Windows with Git Bash prefix `docker compose exec` and `docker run` with
`MSYS_NO_PATHCONV=1` (otherwise Git Bash rewrites `/dev-scripts/...` into a Windows path). Not
needed from PowerShell, Linux or macOS. Run everything from the repository root.

```bash
python build/build_plugin.py plugins/plg_system_yooarchive      # zip in build/out/
cd dev/yooarchive
mkdir -p .cache && curl -L -o .cache/joomla-6.2.0.zip \
  https://github.com/joomla/joomla-cms/releases/download/6.2.0/Joomla_6.2.0-Stable-Full_Package.zip
sha256sum .cache/joomla-6.2.0.zip
# expected: eaafc68b5990d7b9dd9156656d4c3c3547743d1d7a85682a92e2f9121b58f018
cp .env.example .env        # set YOOTHEME_SRC and the DEV_* passwords
docker compose up -d
# wait until the Joomla extraction is finished (Apache starts only afterwards)
until docker compose logs web | grep -q AH00163; do sleep 3; done
docker compose exec web bash /dev-scripts/setup.sh
docker compose exec web php /dev-scripts/seed.php
docker compose exec web php /dev-scripts/seed_menu.php
```

`setup.sh` refuses to run on an incomplete extraction (the `.dev-extracted` marker written by
`entrypoint.sh` at the end is missing).

Install the built zip from the backend (*System > Install > Upload Package File*) or from the
CLI, copying it into `tmp/` first (the CLI installer fails silently on a Windows-mounted folder);
the file name comes from the latest version in `build/out/`:

```bash
ZIP=$(cd ../../build/out && ls plg_system_yooarchive-*.zip | sort -V | tail -1)
docker compose exec web bash -c "cp /dist/$ZIP tmp/ && chown www-data tmp/$ZIP && \
  su -s /bin/bash www-data -c 'php cli/joomla.php extension:install --path=/var/www/html/tmp/$ZIP'"
```

Test helpers: `scripts/template.php`, `scripts/set_layout.php`, `layouts/*.json`,
`scripts/api_test.php`, `scripts/security_test.php`, `scripts/sql.php`.

### Compatibility tests (YOOtheme Pro, Essentials, 4SEO)

They need licensed copies of the third-party extensions, **outside the repository** (like
YOOtheme), in a folder with one subfolder per extension holding the installed files of a site
with the site paths (`plugins/system/yooessentials`, `media/yooessentials`,
`administrator/components/com_forseo`, `plugins/system/forseo`, ...). Never copy configuration
files or keys: Essentials creates its own `secret.php`.

Two sites side by side, Joomla 6.2 and 5.4, each with an env file (not versioned, for example in
`.cache/`) that adds `VENDOR_DIR` (the folder above), `JOOMLA_ZIP` (official zip checked against
the release sha256, for example `./.cache/joomla-5.4.10.zip`) and its own `DEV_PORT`:

```bash
dc() { MSYS_NO_PATHCONV=1 docker compose -p yooarchive-j6 --env-file .cache/compat-j6.env \
       -f docker-compose.yml -f docker-compose.compat.yml "$@"; }
dc up -d && dc exec web bash /dev-scripts/setup.sh
dc exec web php /dev-scripts/seed.php && dc exec web php /dev-scripts/seed_menu.php
dc exec web bash /dev-scripts/loopback_port.sh        # the site can reach its own URL (4SEO analysis)
dc exec web bash /dev-scripts/install_vendor.sh forseo-7.2.0.2824      # "Discover" install
dc exec web bash /dev-scripts/install_vendor.sh yooessentials-3.0.15
dc exec web php /dev-scripts/essentials_csv.php       # Essentials CSV source for the tests
```

Plugins installed with *Discover* start disabled: enable them (`sql.php`) and complete the 4SEO
setup wizard in the backend. On a port other than 80 the 4SEO crawler redirects to itself (it
compares the canonical root without the port): for the tests set the 4SEO canonical root to
`http://localhost/` and the sitemap delay to the minimum.

Test layouts in `layouts/compat/` (built by `make_layouts.py`): each page type (archive,
article, category, featured, search) has a `COMPAT-<type>` marker, YOOtheme and Essentials
elements fed by the page source and a grid on the Essentials CSV source. Assign them with
`scripts/template.php`.

More helpers: `scripts/automation_on.php`, `scripts/api_state.php`, `scripts/install_zip.sh`,
`scripts/uninstall_ext.sh`, `scripts/jedchecker_cli.php` (JED Checker `develop` branch from the
command line).

Compare with the plugin on and off: HTML of the other page types (only counters and random 4SEO
probes may differ); 4SEO title, description, canonical, structured data and breadcrumb on archive
pages (also `?start=` and month/year filters); sitemap with *Exclude archived* yes and no after a
new analysis; redirects after a move; PHP log, customizer console, timings.

JED Checker (https://github.com/joomla-extensions/jedchecker) needs the *Behaviour - Backward
Compatibility 6* plugin on Joomla 6; switch it on only for the check. Run both branches: `master`
(UI upload) and `develop` (`scripts/jedchecker_cli.php`). The `develop` rule PH3 wants a
`<licence>` element mentioning GPL, which its own manifest validation then reports as an unknown
element (notice): that single notice is expected, in `master` too (0 errors, 0 warnings,
1 notice).

## Build and release

```bash
python build/build_plugin.py plugins/plg_system_yooarchive   # reproducible zip in build/out/
```

1. Bump `<version>`, update `CHANGELOG.md` and `changelogs/yooarchive.xml`.
2. Build, run JED Checker (no errors, no warnings) and the Docker tests.
3. Copy the zip to `dist/`, update `updates/yooarchive.xml` (version, downloadurl, sha256,
   `targetplatform (5|6)`, `php_minimum 8.1`) and the version in `collection.xml`.
4. Tag `yooarchive-X.Y.Z` and create the GitHub release with the zip attached.
