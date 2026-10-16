# Guida sviluppatore: plg_system_yooarchive

Per **sviluppatore o AI** che deve modificare, collaudare o rilasciare il plugin.

> Repo **PUBBLICO**: mai token, password, chiavi, host, percorsi di server o dati di clienti.
> Mai file di YOOtheme Pro (proprietari): l'ambiente di prova li monta da fuori dal repo.

## 1. Architettura

```
plugins/plg_system_yooarchive/
  yooarchive.xml                manifest (namespace WebG\Plugin\System\Yooarchive)
  script.php                    PHP >= 8.1, Joomla >= 5.0, attiva il plugin alla prima installazione
  services/provider.php         service provider Joomla 5/6
  bootstrap.php                 modulo YOOtheme caricato con Application::load()
  src/Extension/Yooarchive.php  plugin (SubscriberInterface)
  src/Automation/Automation.php automazione archiviazione/categoria
  src/YOOtheme/...              integrazione col builder
  language/{en-GB,it-IT}/       lingue dentro la cartella del plugin
```

**Integrazione YOOtheme** (stessa architettura di `plugins/system/jfiltersyootheme` di
Blue-Coder e dei template core di `builder-joomla-source`). Su `onAfterInitialise` il plugin,
se esiste `YOOtheme\Application`, carica `bootstrap.php`, che registra:

| Evento YOOtheme | Classe | Cosa fa |
|---|---|---|
| `BuilderConfig` (priorita' 10) | `LoadBuilderConfig` | aggiunge il tipo `com_content.archive` e i campi di assegnazione (categorie e sottocategorie, pagine, lingua) |
| `builder.template` | `MatchTemplate` | sulla vista archivio restituisce tipo, query di assegnazione e parametri (`items`, `pagination`, `archive`) |
| `builder.template.load` | `LoadTemplateUrl` | URL di anteprima: la voce di menu archivio con le categorie del template |
| `source.init` | `SourceListener` | query `yooarchiveArticles` (lista di `Article`), `yooarchiveArticle`, `yooarchive` (tipo `YooarchiveInfo`) |

Gli articoli sono esposti come tipo YOOtheme `Article`: tutti i campi e i campi custom arrivano
senza codice nostro. Unico intervento: Joomla prepara gli item dell'archivio col contesto
`com_content.archive`, che com_fields non mappa su `article`, e lascia `jcfields` vuoto;
`ArchiveQueryType::prepareItems()` toglie la lista vuota cosi' YOOtheme ricarica i campi col
contesto `com_content.article`.

`MatchTemplate` usa solo il modello (`getItems`, `getPagination`, `getState`, `getYears`), mai
`HtmlView::get()` che in Joomla 5.3+ e' deprecato. Il modulo filtri replica quello nativo
(mese, anno, testo se la voce lo abilita, articoli per pagina) con classi UIkit e metodo GET.

**Automazione** (`Automation.php`, attiva solo con interruttore acceso e categoria scelta):

- `onTableBeforeStore` ricorda categoria e stato precedenti dell'articolo.
- `onTableAfterStore` mette in coda: archiviazione (pubblicato che arriva nella categoria, o
  sempre con "forza archiviato") oppure spostamento (articolo appena diventato archiviato da
  un salvataggio).
- `onContentChangeState` (elenco, barra, API): stato 2 = sposta subito; stato 1 con "forza" =
  riarchivia.
- La coda si esegue su `onContentAfterSave` e `onContentCleanCache`, cioe' a salvataggio
  concluso (Joomla 6 emette `onContentAfterSave` anche per ogni articolo spostato in serie,
  quindi nel batch l'archiviazione avviene articolo per articolo), mai dentro `store()`.
- Archiviare = `ArticleModel::publish($pks, 2)` del modello amministratore: scattano Smart
  Search, cache, cronologia, log azioni. Spostare = `Table::store()` con nuovo `catid` (asset
  riparentato) + `onFinderAfterSave` per reindicizzare la categoria.
- Un flag statico `$busy` ignora gli eventi generati dal plugin stesso: niente ricorsioni.
- Spostare richiede un utente collegato con `core.edit.state` sull'articolo (e `core.create` sulla
  categoria archivio se l'opzione *Richiedi il permesso di creare* e' accesa); salta gli articoli
  bloccati da un altro utente; mai per ospiti o CLI. A video solo stringhe di lingua generiche,
  dettagli solo in `Log::add()`. Categoria archivio inesistente o nel cestino = automazione ferma
  e avviso agli amministratori.

## 2. Ambiente di prova (Docker)

Cartella `dev/yooarchive/`: Joomla 6.2.0 ufficiale (zip scaricato e verificato con lo sha256
della release), PHP 8.4 + Apache (immagine `joomla:6.1-php8.4-apache` usata solo come runtime),
MariaDB 11.4. Porta locale 8765, solo su 127.0.0.1. Le password le scegli tu in `.env`
(non versionato); nel repo ci sono solo segnaposto in `.env.example`.

**YOOtheme Pro non e' nel repo.** Serve una copia con licenza, fuori dal repo:
- lo zip ufficiale `yootheme_j_X.Y.Z.zip` dall'account yootheme.com, oppure
- i file installati di un sito con licenza: `templates/yootheme`, `plugins/system/yootheme`,
  `plugins/fields/location`, `plugins/fields/mediafile`, `modules/mod_yootheme_builder`,
  `administrator/modules/mod_yootheme_link`.

Si indica la cartella in `dev/yooarchive/.env` (copia di `.env.example`, mai committato).

```bash
cd dev/yooarchive
mkdir -p .cache && curl -L -o .cache/joomla-6.2.0.zip \
  https://github.com/joomla/joomla-cms/releases/download/6.2.0/Joomla_6.2.0-Stable-Full_Package.zip
# sha256 atteso: eaafc68b5990d7b9dd9156656d4c3c3547743d1d7a85682a92e2f9121b58f018
cp .env.example .env            # imposta YOOTHEME_SRC e le password DEV_*
docker compose up -d
docker compose exec web bash /dev-scripts/setup.sh      # Joomla, YOOtheme, lingua it-IT
docker compose exec web php /dev-scripts/seed.php       # categorie, campi custom, 21 articoli
docker compose exec web php /dev-scripts/seed_menu.php  # 3 voci "Articoli archiviati"
```

Admin: http://localhost:8765/administrator (utente `admin`, password `DEV_ADMIN_PASSWORD` del tuo `.env`).

Installare lo zip compilato: dal backend (*Sistema > Installa*) oppure da CLI copiandolo prima
in `tmp/` (da una cartella montata da Windows l'installer CLI fallisce senza messaggio):

```bash
docker compose exec web bash -c 'cp /dist/plg_system_yooarchive-1.0.0.zip tmp/ && chown www-data tmp/*.zip && \
  su -s /bin/bash www-data -c "php cli/joomla.php extension:install --path=/var/www/html/tmp/plg_system_yooarchive-1.0.0.zip"'
```

Strumenti di prova (solo sviluppo): `scripts/template.php` (crea/cambia template YOOtheme),
`scripts/set_layout.php` (mette un layout JSON in un template), `layouts/*.json` (griglia di
esempio con campi custom, filtri e paginazione), `scripts/api_test.php` (automazione via API), `scripts/security_test.php` (permessi
dell'automazione con un utente Manager di prova),
`scripts/sql.php` (query sul DB di prova). Log PHP: `administrator/logs/php-error.log`.

Fermare: `docker compose stop`. Rifare da zero: `docker compose down -v`.

### JED Checker

Il checker ufficiale (https://github.com/joomla-extensions/jedchecker, ramo master) su Joomla 6
richiede il plugin *Behaviour - Backward Compatibility 6* acceso (usa classi Filesystem tolte
in J6). Accenderlo solo per il controllo e rispegnerlo dopo: il plugin va collaudato col
compat spento, come su un sito Joomla 6 pulito.

## 3. Compilare

```bash
python build/build_plugin.py plugins/plg_system_yooarchive
```

Zip in `build/out/` (ignorato da git), percorsi con `/`, file alla radice, ordine e date fissi:
stesso sorgente = stesso sha256 su ogni PC (`.gitattributes` forza LF). Mai `Compress-Archive`.

## 4. Rilasciare

1. Bump `<version>` in `yooarchive.xml`, voce in `CHANGELOG.md` e in `changelogs/yooarchive.xml`.
2. Compila, ripassa JED Checker (zero errori e zero avvisi), collaudo nel Docker.
3. Copia lo zip in `dist/plg_system_yooarchive-X.Y.Z.zip`.
4. Crea/aggiorna `updates/yooarchive.xml` copiando `updates/cfpurge.xml`: element `yooarchive`,
   folder `system`, versione, downloadurl, sha256 stampato dalla build,
   `<targetplatform name="joomla" version="(5|6)\.[0-9]+" />`, `<php_minimum>8.1</php_minimum>`,
   changelogurl `changelogs/yooarchive.xml`.
5. Aggiorna la versione nella riga `yooarchive` di `collection.xml`.
6. Tag `yooarchive-X.Y.Z` e GitHub Release con lo zip allegato e le note del CHANGELOG.

