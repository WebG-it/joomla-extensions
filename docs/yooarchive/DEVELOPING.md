# Guida sviluppatore: plg_system_yooarchive

Per **sviluppatore o AI** che deve modificare, collaudare o rilasciare il plugin.

> Repo **PUBBLICO**: mai token, password, chiavi, host, percorsi di server o dati di clienti.
> Mai file di YOOtheme Pro (proprietari): l'ambiente di prova li monta da fuori dal repo.

## 1. Architettura

```
plugins/plg_system_yooarchive/
  yooarchive.xml                manifest (namespace WebG\Plugin\System\Yooarchive)
  script.php                    PHP >= 8.1, Joomla >= 5.0, attiva il plugin alla prima installazione,
                                preflight che protegge chi aveva l'automazione accesa (1.x)
  services/provider.php         service provider Joomla 5/6
  bootstrap.php                 modulo YOOtheme caricato con Application::load()
  src/Extension/Yooarchive.php  plugin (SubscriberInterface)
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

**Automazione**: non e' piu' in questo plugin. Fino alla 1.2 c'era `src/Automation/Automation.php`;
dalla 2.0 e' il plugin separato `plg_system_autoarchive` ([guida](../autoarchive/DEVELOPING.md)).
Lo `script.php` di questo plugin ha un `preflight` che, in aggiornamento da 1.x con l'automazione
accesa e Auto Archive non installato o spento, rifiuta l'aggiornamento con un messaggio chiaro
(niente cambia), e in `postflight` toglie i file della vecchia automazione (`removeFiles`).

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

Su Windows con Git Bash anteponi `MSYS_NO_PATHCONV=1` ai comandi `docker compose exec` e
`docker run` (altrimenti Git Bash trasforma `/dev-scripts/...` in un percorso Windows). Da
PowerShell, Linux o macOS non serve. Tutti i comandi partono dalla radice del repo.

```bash
python build/build_plugin.py plugins/plg_system_yooarchive      # zip in build/out/
cd dev/yooarchive
mkdir -p .cache && curl -L -o .cache/joomla-6.2.0.zip \
  https://github.com/joomla/joomla-cms/releases/download/6.2.0/Joomla_6.2.0-Stable-Full_Package.zip
sha256sum .cache/joomla-6.2.0.zip
# atteso: eaafc68b5990d7b9dd9156656d4c3c3547743d1d7a85682a92e2f9121b58f018
cp .env.example .env            # imposta YOOTHEME_SRC e le password DEV_*
docker compose up -d
# aspetta che l'estrazione di Joomla sia finita (Apache parte solo dopo)
until docker compose logs web | grep -q AH00163; do sleep 3; done
docker compose exec web bash /dev-scripts/setup.sh      # Joomla, YOOtheme, lingua it-IT
docker compose exec web php /dev-scripts/seed.php       # categorie, campi custom, 21 articoli
docker compose exec web php /dev-scripts/seed_menu.php  # 3 voci "Articoli archiviati"
```

`setup.sh` si rifiuta di partire se l'estrazione non e' completa (manca il file marcatore
`.dev-extracted` scritto da `entrypoint.sh` alla fine).

Admin: http://localhost:8765/administrator (utente `admin`, password `DEV_ADMIN_PASSWORD` del tuo `.env`).

Installare lo zip compilato: dal backend (*Sistema > Installa > Carica il pacchetto*) oppure da
CLI copiandolo prima in `tmp/` (da una cartella montata da Windows l'installer CLI fallisce senza
messaggio). Il nome dello zip si prende dalla versione piu' recente in `build/out/`:

```bash
ZIP=$(cd ../../build/out && ls plg_system_yooarchive-*.zip | sort -V | tail -1)
docker compose exec web bash -c "cp /dist/$ZIP tmp/ && chown www-data tmp/$ZIP && \
  su -s /bin/bash www-data -c 'php cli/joomla.php extension:install --path=/var/www/html/tmp/$ZIP'"
```

Strumenti di prova (solo sviluppo): `scripts/template.php` (crea/cambia template YOOtheme),
`scripts/set_layout.php` (mette un layout JSON in un template), `layouts/*.json` (griglia di
esempio con campi custom, filtri e paginazione), `scripts/sql.php` (query sul DB di prova). Log PHP: `administrator/logs/php-error.log`.

Fermare: `docker compose stop`. Rifare da zero: `docker compose down -v`.

### Prove di compatibilità (YOOtheme Pro, Essentials, 4SEO)

Servono copie con licenza delle estensioni di terzi, **fuori dal repo** (come YOOtheme), in una
cartella con una sottocartella per estensione che contiene i file installati di un sito, con
gli stessi percorsi del sito (`plugins/system/yooessentials`, `media/yooessentials`,
`administrator/components/com_forseo`, `plugins/system/forseo`, ...). Non copiare file di
configurazione o chiavi: Essentials rigenera da solo il suo `secret.php`.

Due siti affiancati, Joomla 6.2 e 5.4, con un file env ciascuno (fuori dal git, per esempio in
`.cache/`) che aggiunge `VENDOR_DIR` (la cartella sopra), `JOOMLA_ZIP` (zip ufficiale verificato
con lo sha256 della release, per esempio `./.cache/joomla-5.4.10.zip`) e una `DEV_PORT` diversa:

```bash
dc() { MSYS_NO_PATHCONV=1 docker compose -p yooarchive-j6 --env-file .cache/compat-j6.env \
       -f docker-compose.yml -f docker-compose.compat.yml "$@"; }
dc up -d && dc exec web bash /dev-scripts/setup.sh
dc exec web php /dev-scripts/seed.php && dc exec web php /dev-scripts/seed_menu.php
dc exec web bash /dev-scripts/loopback_port.sh        # il sito raggiunge il proprio URL (analisi 4SEO)
dc exec web bash /dev-scripts/install_vendor.sh forseo-7.2.0.2824      # installazione "Scopri"
dc exec web bash /dev-scripts/install_vendor.sh yooessentials-3.0.15
dc exec web php /dev-scripts/essentials_csv.php       # sorgente CSV di Essentials per le prove
```

I plugin installati con *Scopri* nascono spenti: accenderli (`sql.php`) e completare la
procedura iniziale di 4SEO dal backend. Con una porta diversa da 80 il crawler di 4SEO si
reindirizza su se stesso (confronta la radice canonica senza porta): per le prove impostare la
radice canonica di 4SEO a `http://localhost/` e il ritardo sitemap al minimo.

Layout di prova in `layouts/compat/` (generati da `make_layouts.py`): ogni tipo di pagina
(archivio, articolo, categoria, home, ricerca) ha un testo marcatore `COMPAT-<tipo>`, elementi
YOOtheme ed elementi Essentials alimentati dalla sorgente della pagina e una griglia sulla
sorgente CSV di Essentials. Si assegnano con `scripts/template.php`.

Altri strumenti: `scripts/automation_on.php` (automazione accesa su una categoria),
`scripts/api_state.php` (cambia stato o categoria di un articolo via API), `scripts/install_zip.sh`
e `scripts/uninstall_ext.sh` (installare e togliere un'estensione da CLI),
`scripts/jedchecker_cli.php` (JED Checker ramo `develop` da riga di comando).

Cosa confrontare, col plugin acceso e spento: HTML delle pagine degli altri tipi (devono
differire solo contatori e sonde casuali di 4SEO), title, description, canonical, dati
strutturati e breadcrumb di 4SEO nelle pagine archivio (anche `?start=` e filtri mese/anno),
sitemap con *Escludi archiviati* sì e no dopo un nuovo giro di analisi, redirect dopo uno
spostamento, log PHP, console del customizer, tempi.

### JED Checker

Il checker ufficiale (https://github.com/joomla-extensions/jedchecker, ramo master) su Joomla 6
richiede il plugin *Behaviour - Backward Compatibility 6* acceso (usa classi Filesystem tolte
in J6). Accenderlo solo per il controllo e rispegnerlo dopo: il plugin va collaudato col
compat spento, come su un sito Joomla 6 pulito.

Si passano entrambi i rami: `master` (caricamento dello zip dal backend) e `develop`
(`scripts/jedchecker_cli.php`, il ramo `develop` ha una classe API e si installa compilando i
segnaposto del suo manifest). La regola PH3 di `develop` vuole un elemento `<licence>` che citi
la GPL, e la validazione del manifest dello stesso ramo lo segnala come elemento sconosciuto
(nota): quell'unica nota è attesa. Anche `master` lo segnala come nota (badge azzurro): 0 errori,
0 avvisi, 1 nota.

## 3. Compilare

```bash
python build/build_plugin.py plugins/plg_system_yooarchive
```

Zip in `build/out/` (ignorato da git), percorsi con `/`, file alla radice, ordine e date fissi:
stesso sorgente = stesso sha256 su ogni PC (`.gitattributes` forza LF). Mai `Compress-Archive`.

## 4. Rilasciare

1. Bump `<version>` in `yooarchive.xml`, voce in `CHANGELOG.md` e in `changelogs/yooarchive.xml`.
2. Compila, ripassa JED Checker `master` e `develop` (zero errori e zero avvisi; unica nota
   attesa quella su `<licence>`), collaudo nel
   Docker, prove di compatibilità se cambiano YOOtheme, Essentials o 4SEO.
3. Copia lo zip in `dist/plg_system_yooarchive-X.Y.Z.zip`.
4. Crea/aggiorna `updates/yooarchive.xml` copiando `updates/cfpurge.xml`: element `yooarchive`,
   folder `system`, versione, downloadurl, sha256 stampato dalla build,
   `<targetplatform name="joomla" version="(5|6)\.[0-9]+" />`, `<php_minimum>8.1</php_minimum>`,
   changelogurl `changelogs/yooarchive.xml`.
5. Aggiorna la versione nella riga `yooarchive` di `collection.xml`.
6. Tag `yooarchive-X.Y.Z` e GitHub Release con lo zip allegato e le note del CHANGELOG.

