# Layout archivio per YOOtheme (plg_system_yooarchive): guida d'uso

Per chi **gestisce il sito**: installare, creare la pagina archivio col builder, accendere
l'automazione, aggiornare, disinstallare. Tutto da interfaccia Joomla e YOOtheme.

> Sviluppatore o AI? Vai su [DEVELOPING.md](./DEVELOPING.md).

## A cosa serve

Joomla ha una vista **Articoli archiviati** (voce di menu *Articoli > Articoli archiviati*),
ma YOOtheme Pro non permette di disegnarla col builder: si vede solo l'elenco standard. Il
plugin aggiunge il tipo di template **Articoli archiviati** al builder, con gli articoli
archiviati come contenuto dinamico (titolo, immagini, link, date, categoria, autore, tag e
**tutti i campi personalizzati**), la paginazione e i filtri per mese e anno.

In più, se lo accendi, tiene gli archiviati in una categoria: clic su **Archivia** e
l'articolo finisce nella categoria scelta; sposti un articolo in quella categoria e viene
archiviato.

Requisiti: Joomla 5 o 6, PHP 8.1+, YOOtheme Pro 5. Provato con YOOtheme Pro 5.0.51 su Joomla 6.2.0 e
5.4.10 (PHP 8.4), insieme a Essentials for YOOtheme Pro e 4SEO: vedi
[Compatibilità testata](#compatibilità-testata).

## 1. Installare

1. *Sistema > Installa > Estensioni > Carica il pacchetto* e scegli `plg_system_yooarchive-X.Y.Z.zip`.
2. Il plugin si attiva da solo. Non tocca nulla finché non crei un template o accendi
   l'automazione.

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

## 3. Automazione (spenta di serie)

*Sistema > Plugin > Sistema - Layout archivio per YOOtheme*, scheda **Automazione**:

| Opzione | Cosa fa |
|---|---|
| Attiva automazione | Accende tutto il resto. |
| Categoria archivio | Dove finiscono gli articoli archiviati. Senza categoria, o se la categoria viene cancellata o messa nel cestino, l'automazione si ferma e gli amministratori vedono un avviso. Deve essere **pubblicata**: se non lo è, gli articoli spostati non compaiono nel sito pubblico (neanche nella pagina archivio) e gli amministratori vedono un avviso giallo. |
| Richiedi il permesso di creare | Acceso di serie: sposta solo se chi archivia può anche creare articoli nella categoria archivio. |
| Sposta gli articoli archiviati | Archivia (da elenco, barra, modifica o API) = sposta nella categoria. |
| Redirect 301 dal vecchio indirizzo | Acceso di serie. Se la categoria fa parte dell'URL dell'articolo, entrare nella categoria archivio cambia l'indirizzo: il vecchio viene reindirizzato (301) al nuovo tramite il componente *Reindirizzamenti* di Joomla, sia per gli spostamenti fatti dal plugin sia per gli articoli che un utente sposta nella categoria. Serve il plugin *Sistema - Reindirizzamento* attivo (se è spento gli amministratori vedono un avviso). Se l'indirizzo non cambia non viene salvato nulla. |
| Archivia gli articoli spostati nella categoria | Un articolo pubblicato salvato o spostato (anche con *Elaborazione in serie*) nella categoria viene archiviato. |
| Forza archiviato | Spento: puoi ripubblicare apposta un articolo che sta già nella categoria. Acceso: viene riarchiviato sempre. |

Dopo ogni azione compare un messaggio blu che dice cosa ha fatto il plugin (anche quanti redirect
ha salvato: li trovi in *Sistema > Reindirizzamenti*). Ricerca
intelligente, cache e cronologia si aggiornano come con un clic a mano.

Lingue: lo spostamento non guarda la lingua. Un articolo in inglese finisce nella categoria
archivio anche se quella categoria è in italiano; su un sito multilingua usa una categoria
archivio con lingua *Tutte*.

I redirect li scrive il plugin, quindi all'utente non serve alcun permesso sul componente
Reindirizzamenti; vengono scritti solo per gli spostamenti ammessi dalle regole qui sotto. Un
redirect già esistente per lo stesso vecchio indirizzo e non creato dal plugin non viene mai
toccato.

Permessi: l'articolo viene spostato solo se chi lo archivia è un utente collegato con il
permesso *Modifica stato* sull'articolo (e, con l'opzione accesa, *Crea* nella categoria
archivio). Gli articoli aperti in modifica da un altro utente non vengono toccati. Ospiti e
riga di comando non spostano nulla.

Se qualcosa non va (permesso mancante, alias doppio nella categoria di arrivo) compare un
messaggio giallo generico; i dettagli (numeri degli articoli, errore del database) finiscono
solo nel log `administrator/logs/plg_system_yooarchive.php`. Nel sito pubblico (modifica
articoli dal frontend) i messaggi li vede solo chi può gestire gli articoli.

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
  reinstallato con 4SEO ed Essentials già attivi. Aggiornamento da 1.1.0 a 1.2.0 e da 1.2.0
  a 1.2.1 (dal server di aggiornamento). Log degli errori PHP e console del browser vuoti.
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
  *Escludi archiviati* attivo: gli articoli archiviati restano fuori; spento: compaiono con il
  nuovo indirizzo dopo lo spostamento fatto dall'automazione.

Non provato: l'aggiornamento di YOOtheme Pro a 5.0.52 con il plugin attivo (pacchetto non
disponibile), i *Flussi di lavoro* di Joomla.

**Note per chi usa 4SEO.** 4SEO non crea un redirect quando un articolo cambia indirizzo. Se la
categoria fa parte degli URL degli articoli tieni accesa l'opzione *Redirect 301 dal vecchio
indirizzo* (con il plugin *Sistema - Reindirizzamento* di Joomla attivo). Dopo aver archiviato
molti articoli insieme rifai l'analisi del sito in 4SEO (*Pagine > Reset analisi*), così sitemap
e canonical usano i nuovi indirizzi. Le pagine numerate dell'archivio sono trattate da 4SEO come
tutte le altre.

Con URL degli articoli che contengono l'id (`/<id>-alias`) Joomla trova l'articolo dall'id
qualunque sia la categoria nell'indirizzo: dopo lo spostamento il vecchio URL resta vivo (niente
404) e il redirect non serve; 4SEO tiene il vecchio canonical fino alla nuova analisi del sito.

**Note per chi usa Essentials.** Nell'elemento *Social Sharing* lascia vuoto l'URL condiviso per
condividere la pagina corrente: se lo colleghi al campo *Link* dell'articolo riceve un indirizzo
non SEF, sul template archivio come sul template articolo di YOOtheme (comportamento di Essentials).

![Gruppo Archivio nel selettore delle sorgenti di un elemento Essentials](./images/essentials-source-picker.jpg)

## 4. Aggiornare

Arriva da *Sistema > Aggiorna > Estensioni* come gli altri plugin WebG.

## 5. Disinstallare

*Sistema > Gestisci > Estensioni*, cerca "Layout archivio", *Disinstalla*. Non restano tabelle
né file del plugin. Restano i redirect salvati dall'automazione in *Sistema > Reindirizzamenti*
(la nota inizia con "Archive Layouts for YOOtheme:", da lì li togli se vuoi) e il log
`administrator/logs/plg_system_yooarchive.php` (e `plg_system_yooarchive.1.php`). I template del builder di tipo *Articoli archiviati* restano in YOOtheme come "non
assegnati" e tornano attivi se reinstalli il plugin; la pagina archivio torna quella di Joomla.
