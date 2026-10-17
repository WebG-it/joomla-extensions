# Layout archivio per YOOtheme (plg_system_yooarchive): guida d'uso

Per chi **gestisce il sito**: installare, creare la pagina archivio col builder, aggiornare, disinstallare. Tutto da interfaccia Joomla e YOOtheme.

> Sviluppatore o AI? Vai su [DEVELOPING.md](./DEVELOPING.md).

## A cosa serve

Joomla ha una vista **Articoli archiviati** (voce di menu *Articoli > Articoli archiviati*),
ma YOOtheme Pro non permette di disegnarla col builder: si vede solo l'elenco standard. Il
plugin aggiunge il tipo di template **Articoli archiviati** al builder, con gli articoli
archiviati come contenuto dinamico (titolo, immagini, link, date, categoria, autore, tag e
**tutti i campi personalizzati**), la paginazione e i filtri per mese e anno.

La versione 2.0 fa solo questo: il layout. Tenere gli articoli archiviati in una categoria in
automatico è un'esigenza diversa ed è ora un plugin separato, **Auto Archive** (funziona con o
senza YOOtheme): vedi [Auto Archive](../autoarchive/USO.md) e
[Aggiornare da 1.x](#aggiornare-da-1x).

Requisiti: Joomla 5 o 6, PHP 8.1+, YOOtheme Pro 5. Provato con YOOtheme Pro 5.0.51 su Joomla 6.2.0 e
5.4.10 (PHP 8.4), insieme a Essentials for YOOtheme Pro e 4SEO: vedi
[Compatibilità testata](#compatibilità-testata).

## 1. Installare

1. *Sistema > Installa > Estensioni > Carica il pacchetto* e scegli `plg_system_yooarchive-X.Y.Z.zip`.
2. Il plugin si attiva da solo. Non tocca nulla finché non crei un template.

## 2. Creare la pagina archivio

1. Se non c'è, crea la voce di menu *Articoli > Articoli archiviati*. Nelle opzioni della voce
   scegli le categorie (vuoto = tutte), l'ordine (*Ordine articoli*, *Data per l'ordinamento*)
   e quanti articoli per pagina (*# Articoli*).
2. Apri YOOtheme Pro (menu *YOOtheme* del backend), voce **TEMPLATE** nella barra a sinistra,
   pulsante **NUOVO TEMPLATE**; in *Pagina* scegli **Articoli archiviati**.
   - *Limita per categorie*: il template vale solo per le voci archivio filtrate su quelle
     categorie (con o senza sottocategorie). Vuoto = tutte le pagine archivio.
   - *Limita per numero di pagina* e *Limita per lingua* come negli altri template.
3. Disegna il layout. Nel *Contenuto dinamico* di un elemento (per esempio una Griglia) scegli
   il gruppo **Archivio** (in un'interfaccia in inglese: **Archive**):
   - **Articoli archiviati**: l'elenco della pagina corrente. *Inizio* e *Quantità* per
     prenderne una parte; *Ordine* per riordinare gli articoli della pagina (l'ordine fra le
     pagine resta quello della voce di menu).
   - **Articolo archiviato**: un solo articolo.
   - **Informazioni archivio**: numero di articoli, pagina, anno e mese filtrati, categorie e
     un **modulo filtri** già pronto (mese, anno, articoli per pagina) da mettere in un
     elemento HTML.
4. Aggiungi l'elemento **Paginazione** sotto la griglia.
5. L'anteprima del customizer apre da sola la voce archivio giusta.

Senza un template assegnato la pagina archivio resta quella di sempre.

## Compatibilità testata

Provato il 16-10-2026 in un ambiente Docker locale (Apache, PHP 8.4.26, MariaDB 11.4) con tutte
le estensioni qui sotto attive insieme, su due siti:

| Software | Versione |
|---|---|
| Joomla | 6.2.0 e 5.4.10 |
| YOOtheme Pro | 5.0.51 |
| Essentials for YOOtheme Pro (ZOOlanders) | 3.0.15 (PRO) |
| 4SEO (Weeblr) | 7.2.0.2824 (full) |

Cosa è stato verificato, su entrambe le versioni di Joomla:

- **Installazione, aggiornamento e disinstallazione** del plugin con le altre estensioni attive.
  Ordine 1: prima questo plugin, poi 4SEO ed Essentials. Ordine 2: plugin disinstallato e
  reinstallato con 4SEO ed Essentials già attivi. Aggiornamento da 1.1.0 a 1.2.0, da 1.2.0
  a 1.2.1 e da 1.2.1 a 2.0.0 (dal server di aggiornamento). Log degli errori PHP e console del browser vuoti.
- **YOOtheme Pro**: i template degli altri tipi di pagina (articolo, blog di categoria, articoli
  in evidenza, Ricerca intelligente) producono lo stesso HTML con il plugin acceso e spento
  (cambiano solo i contatori di visite e le sonde casuali di 4SEO). Tempo di apertura del
  customizer e tempi di risposta delle pagine uguali entro il rumore di misura.
- **Essentials for YOOtheme Pro**: i suoi elementi (Markdown, Social Sharing) e le sue sorgenti
  (una sorgente CSV) funzionano in tutti i template, compreso *Articoli archiviati*; il gruppo
  *Archivio* compare nel selettore delle sorgenti degli elementi Essentials e li alimenta; i
  filtri di Essentials (per esempio *Date modify*) funzionano sui campi dell'archivio; nessun
  conflitto di nomi di tipi GraphQL o di sorgenti, di eventi del builder o di tipi di template.
  Il customizer si apre senza errori.
- **4SEO**: nelle pagine archivio costruite col template titolo, meta description, canonical,
  dati strutturati e breadcrumb sono identici a quelli della pagina archivio normale di Joomla
  (plugin spento), anche sulle pagine numerate (`?start=`) e con filtri mese e anno che hanno
  risultati (un filtro senza risultati non è stato confrontato).
  L'analisi del sito di 4SEO segue i link del template (articoli e paginazione). Sitemap con
  *Escludi archiviati* attivo: gli articoli archiviati restano fuori; spento: compaiono.

Non provato: l'aggiornamento di YOOtheme Pro a 5.0.52 con il plugin attivo (pacchetto non
disponibile), i *Flussi di lavoro* di Joomla.

**Note per chi usa 4SEO.** Le pagine numerate dell'archivio sono trattate da 4SEO come tutte le
altre. Se sposti anche gli articoli in una categoria archivio, 4SEO non crea un redirect quando un
articolo cambia indirizzo: i redirect 301 per gli spostamenti li salva [Auto Archive](../autoarchive/USO.md).
Dopo aver archiviato molti articoli insieme rifai l'analisi del sito in 4SEO (*Pagine > Reset
analisi*), così sitemap e canonical usano i nuovi indirizzi.

**Note per chi usa Essentials.** Nell'elemento *Social Sharing* lascia vuoto l'URL condiviso per
condividere la pagina corrente: se lo colleghi al campo *Link* dell'articolo riceve un indirizzo
non SEF, sul template archivio come sul template articolo di YOOtheme (comportamento di Essentials).

![Gruppo Archivio nel selettore delle sorgenti di un elemento Essentials](./images/essentials-source-picker.jpg)

## 3. Aggiornare

Arriva da *Sistema > Aggiorna > Estensioni* come gli altri plugin WebG.

### Aggiornare da 1.x

Fino alla 1.2 questo plugin conteneva anche l'automazione dell'archivio (sposta gli articoli
archiviati in una categoria, archivia quelli messi lì, redirect 301). Dalla 2.0 è il plugin
separato **Auto Archive**.

- **Automazione spenta** (di serie): aggiorna e basta. Non cambia nulla.
- **Automazione accesa**: l'aggiornamento alla 2.0 viene **rifiutato con un messaggio chiaro e
  senza cambiare nulla** finché Auto Archive non è installato, attivo e acceso. Installa prima Auto
  Archive 1.0 (importa le tue impostazioni e spegne la vecchia automazione, così non gira due
  volte), poi aggiorna questo plugin. Se aggiorni per sbaglio resta installata e funzionante la
  versione vecchia.

## 4. Disinstallare

*Sistema > Gestisci > Estensioni*, cerca "Layout archivio", *Disinstalla*. Non restano tabelle
né file del plugin. I template del builder di tipo *Articoli archiviati* restano in YOOtheme come
"non assegnati" e tornano attivi se reinstalli il plugin; la pagina archivio torna quella di
Joomla. Se usavi l'automazione della 1.x, i redirect che aveva salvato restano in *Sistema >
Reindirizzamenti* e il suo log in `administrator/logs/` (ora li gestisce Auto Archive).
