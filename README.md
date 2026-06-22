# WebG Joomla Extensions — Update Server

Update server **a collezione** per i plugin Joomla sviluppati da WebG. Un solo URL,
condiviso da tutti i plugin: i siti Joomla controllano qui gli aggiornamenti e li
applicano con un click da **Sistema → Aggiorna**.

> ⚠️ **Repo PUBBLICO.** Qui dentro vanno solo zip GPL e XML di versione.
> **Mai** token, password, chiavi o altri dati sensibili.

## Struttura

```
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
| Content - Cloudflare Cache Purge | `cfpurge` | plugin / content | 1.1.1 | [Uso (umano)](./docs/USO.md) · [Sviluppo (AI/dev)](./docs/DEVELOPING.md) |

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
