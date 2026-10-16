# WebG Joomla Extensions

Joomla extensions by [WebG](https://www.webg.it), free software under the GNU GPL v2 or later,
with a shared update server: once installed, new versions appear in *System > Update >
Extensions*.

| Extension | Type | Version | Docs |
|---|---|---|---|
| [Archive Layouts for YOOtheme](#archive-layouts-for-yootheme) | plugin / system | 1.1.0 | [User guide](./docs/yooarchive/USAGE.md) · [Guida (IT)](./docs/yooarchive/USO.md) |
| Cloudflare Cache Purge | plugin / content | 1.1.4 | [Uso (IT)](./docs/USO.md) |

Support: [GitHub issues](https://github.com/WebG-it/joomla-extensions/issues) (write your
Joomla, PHP and template versions; never post passwords, keys or private data).

---

## Archive Layouts for YOOtheme

Design the Joomla **Archived Articles** page with the **YOOtheme Pro builder**, like category
and featured pages, and optionally keep archived articles in one category automatically.

![Archive page built with the YOOtheme Pro builder (test data)](./docs/yooarchive/images/frontend-grid.jpg)

**What it does**

- New YOOtheme template type **Archived Articles** for the menu item *Articles > Archived
  Articles*, assignable by category (with child categories), page number and language.
- Dynamic content group **Archive**: the archived articles of the page with every article
  field and **all custom fields**, a single article, and archive information (count, page,
  filtered month and year, ready-made filter form). Works with the YOOtheme Pagination element.
- Optional automation: *Archive* moves the article to a chosen category; a published article
  saved or batch-moved into that category is archived through the Joomla model (Smart Search,
  cache and versions stay in sync). Permission-aware, off by default.
- Survives YOOtheme updates (nothing is written in the template folder). English and Italian.

**Requirements**: Joomla 5 or 6, PHP 8.1+, YOOtheme Pro 5. Tested on Joomla 6.2.0, PHP 8.4,
YOOtheme Pro 5.0.51.

**Install**: download `plg_system_yooarchive-1.1.0.zip` from the
[releases](https://github.com/WebG-it/joomla-extensions/releases) (or [dist/](./dist)), then
*System > Install > Extensions > Upload Package File*. Full guide: [USAGE.md](./docs/yooarchive/USAGE.md).

**Updates**: automatic through the WebG update server, in *System > Update > Extensions*.

| Builder: dynamic source | Builder: custom fields | Automation options |
|---|---|---|
| ![](./docs/yooarchive/images/builder-source.jpg) | ![](./docs/yooarchive/images/builder-fields.jpg) | ![](./docs/yooarchive/images/automation-options.jpg) |

License: GNU GPL v2 or later. YOOtheme Pro is a separate commercial product of YOOtheme GmbH and
is not included.

---

## Note interne (IT): come funziona questo repository


Update server **a collezione** per i plugin Joomla sviluppati da WebG. Un solo URL,
condiviso da tutti i plugin: i siti Joomla controllano qui gli aggiornamenti e li
applicano con un click da **Sistema → Aggiorna**.

> ⚠️ **Repo PUBBLICO.** Qui dentro vanno solo zip GPL e XML di versione.
> **Mai** token, password, chiavi o altri dati sensibili.

## Struttura

```
plugins/<pacchetto>/        ← sorgente dei plugin (da yooarchive in poi)
build/build_plugin.py       ← build riproducibile dello zip (python build/build_plugin.py plugins/<pacchetto>)
dev/<plugin>/               ← ambiente Docker di collaudo (senza file proprietari di terzi)
collection.xml              ← update server condiviso (questo URL va nel manifest di OGNI plugin)
updates/<element>.xml       ← elenco versioni di un singolo plugin
dist/<pacchetto>-X.Y.Z.zip  ← lo zip installabile
```

URL del collection server (da mettere in `<updateservers>` di ogni plugin):

```
https://raw.githubusercontent.com/WebG-it/joomla-extensions/main/collection.xml
```

## Plugin pubblicati

| Plugin | element | Tipo | Versione | Documentazione |
|--------|---------|------|----------|----------------|
| Cloudflare Cache Purge | `cfpurge` | plugin / content | 1.1.4 | [Uso (umano)](./docs/USO.md) · [Sviluppo (AI/dev)](./docs/DEVELOPING.md) |
| Archive Layouts for YOOtheme | `yooarchive` | plugin / system | 1.1.0 | [Uso](./docs/yooarchive/USO.md) · [Usage (EN)](./docs/yooarchive/USAGE.md) · [Sviluppo](./docs/yooarchive/DEVELOPING.md) · [Developing (EN)](./docs/yooarchive/DEVELOPING.en.md) |

## Documentazione (due pubblici distinti)

- 👤 **[docs/USO.md](./docs/USO.md)** — per chi **gestisce il sito**: installare, ottenere
  le credenziali Cloudflare, configurare, aggiornare, **disinstallare**. Tutto da interfaccia
  Joomla, nessuna compilazione.
- 🤖 **[docs/DEVELOPING.md](./docs/DEVELOPING.md)** — per **sviluppatore o AI**: architettura,
  modifica del codice, **compilazione dello zip**, processo di rilascio.

## Aggiungere un plugin nuovo (3 mosse)

1. Aggiungi lo zip in `dist/<pacchetto>-X.Y.Z.zip`.
2. Crea `updates/<element>.xml` (copia da `updates/cfpurge.xml`, cambia element/folder/version/downloadurl).
3. Aggiungi **una riga** `<extension>` in `collection.xml`.

Nel manifest del plugin nuovo basta lo stesso blocco:

```xml
<updateservers>
    <server type="collection" name="WebG Joomla Extensions">https://raw.githubusercontent.com/WebG-it/joomla-extensions/main/collection.xml</server>
</updateservers>
```

## Rilasciare una nuova versione di un plugin esistente

1. Bump `<version>` nel manifest + ribuild zip in `dist/`.
2. Aggiorna `version` e `downloadurl` in `updates/<element>.xml` (e in `collection.xml`).

I siti vedranno l'update entro il ciclo di controllo di Joomla.
