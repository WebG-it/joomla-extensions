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

Requisiti: Joomla 5 o 6, PHP 8.1+, YOOtheme Pro 5 (provato con 5.0.51 su Joomla 6.2.0 e PHP 8.4).

## 1. Installare

1. *Sistema > Installa > Estensioni > Carica il pacchetto* e scegli `plg_system_yooarchive-X.Y.Z.zip`.
2. Il plugin si attiva da solo. Non tocca nulla finché non crei un template o accendi
   l'automazione.

## 2. Creare la pagina archivio

1. Se non c'è, crea la voce di menu *Articoli > Articoli archiviati*. Nelle opzioni della voce
   scegli le categorie (vuoto = tutte), l'ordine (*Ordine articoli*, *Data per l'ordinamento*)
   e quanti articoli per pagina (*# Articoli*).
2. Apri YOOtheme Pro, *Template > Nuovo template*, pagina **Articoli archiviati**.
   - *Limita per categorie*: il template vale solo per le voci archivio filtrate su quelle
     categorie (con o senza sottocategorie). Vuoto = tutte le pagine archivio.
   - *Limita per numero di pagina* e *Limita per lingua* come negli altri template.
3. Disegna il layout. Nel *Contenuto dinamico* di un elemento (per esempio una Griglia) scegli
   il gruppo **Archivio**:
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
| Categoria archivio | Dove finiscono gli articoli archiviati. Senza categoria, o se la categoria viene cancellata o messa nel cestino, l'automazione si ferma e gli amministratori vedono un avviso. |
| Richiedi il permesso di creare | Acceso di serie: sposta solo se chi archivia può anche creare articoli nella categoria archivio. |
| Sposta gli articoli archiviati | Archivia (da elenco, barra, modifica o API) = sposta nella categoria. |
| Archivia gli articoli spostati nella categoria | Un articolo pubblicato salvato o spostato (anche con *Elaborazione in serie*) nella categoria viene archiviato. |
| Forza archiviato | Spento: puoi ripubblicare apposta un articolo che sta già nella categoria. Acceso: viene riarchiviato sempre. |

Dopo ogni azione compare un messaggio blu che dice cosa ha fatto il plugin. Ricerca
intelligente, cache e cronologia si aggiornano come con un clic a mano.

Permessi: l'articolo viene spostato solo se chi lo archivia è un utente collegato con il
permesso *Modifica stato* sull'articolo (e, con l'opzione accesa, *Crea* nella categoria
archivio). Gli articoli aperti in modifica da un altro utente non vengono toccati. Ospiti e
riga di comando non spostano nulla.

Se qualcosa non va (permesso mancante, alias doppio nella categoria di arrivo) compare un
messaggio giallo generico; i dettagli (numeri degli articoli, errore del database) finiscono
solo nel log `administrator/logs/plg_system_yooarchive.php`. Nel sito pubblico (modifica
articoli dal frontend) i messaggi li vede solo chi può gestire gli articoli.

## 4. Aggiornare

Arriva da *Sistema > Aggiorna > Estensioni* come gli altri plugin WebG.

## 5. Disinstallare

*Sistema > Gestisci > Estensioni*, cerca "Layout archivio", *Disinstalla*. Non restano tabelle
né file. I template del builder di tipo *Articoli archiviati* restano in YOOtheme come "non
assegnati" e tornano attivi se reinstalli il plugin; la pagina archivio torna quella di Joomla.
