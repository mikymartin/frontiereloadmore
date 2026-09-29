# Frontiere: Vedi altro news

Compatibile con TYPO3 13.4, `georgringer/news` 12.3 e il tema Frontiere allegato.
Non modifica le estensioni originali. La nuova estensione contiene CSS, JavaScript,
un override del solo partial `List/ListSelection.html` di Frontiere e un plugin
dedicato alle notizie correlate.

## Installazione

1. Installare `polimi/frontiereloadmore` con Composer oppure caricare la
   cartella `frontiereloadmore` nella directory delle estensioni.
2. Includere lo static TypoScript **Frontiere: Vedi altro news** dopo gli static
   di `news`, `polimipackage`, `newsenhanced` e `frontieretema`.
3. Verificare che i TypoScript del tema che definiscono `plugin.tx_news.view` e
   `plugin.tx_newsenhanced_newscarousels.view` siano caricati. Il plugin
   `newsenhanced` viene sostituito solo per `lib.tx_newsenhanced`, il punto
   usato da `Detail.html` per comporre la pagina della notizia.
4. Svuotare la cache TYPO3 e provare con almeno 20 notizie nella lista e più
   di 9 notizie pertinenti a un articolo nel dettaglio.

Se era installata la precedente `frontiere_news_load_more`, disattivarla e
rimuovere il suo static TypoScript prima di attivare `frontiereloadmore` e
includere il nuovo static. Le due estensioni non devono gestire insieme
`lib.tx_newsenhanced` e il partial della lista.

**Aggiornamento dalla 1.0.0:** la 1.0.1 aggiunge `Configuration/Services.yaml`.
Senza questo file, l'apertura di qualsiasi dettaglio può produrre una
`ServiceNotFoundException` per `RelatedController`. Dopo l'aggiornamento
svuotare la cache di sistema (compreso il container DI compilato) ed effettuare
una richiesta nuova alla pagina di dettaglio, non soltanto alla lista.

**Aggiornamento alla 1.0.2:** il repository delle correlate usa esplicitamente
il modello `GeorgRinger\News\Domain\Model\News`: la 1.0.1 cercava per
convenzione un modello `RelatedNews` inesistente. Svuotare nuovamente la cache
di sistema dopo la sostituzione e provare il dettaglio prima del rilascio.
La logica delle correlate e delle ultime news ora usa direttamente il repository
di `news`, senza estendere il repository PHP di `newsenhanced`.

**Aggiornamento alla 1.0.3:** il paginator per i risultati Extbase viene
importato da `TYPO3\CMS\Extbase\Pagination`, come richiesto da TYPO3 13.4.
La 1.0.2 lo cercava nel namespace `Core\Pagination` e interrompeva
l'apertura del dettaglio news. Il totale mostrato nel conteggio viene letto
dal risultato della query, non da una proprietà inesistente del paginator.

## Impostazioni

Nel Template / Constant Editor, categoria `frontiereloadmore`:

| Chiave | Predefinito | Effetto |
| --- | ---: | --- |
| `plugin.tx_frontiereloadmore.enabled` | 1 | Accende/spegne la funzionalità; a 0 ripristina i template e lo slider originali. |
| `plugin.tx_frontiereloadmore.itemsPerPage` | 9 | Numero per blocco nella lista principale e nelle correlate. |
| `plugin.tx_frontiereloadmore.showCount` | 1 | Mostra/nasconde “9 di 50”, “18 di 50”, ecc. |

L'opzione è globale per il sito. Quando è accesa la configurazione di
`plugin.tx_news.settings.list.paginate.itemsPerPage` è condivisa con le altre
liste `news` del sito. Le varianti hero e slider del partial Frontiere restano
quelle esistenti; la lista ordinaria usa “Vedi altro”.

Le correlate usano le categorie discendenti configurate in
`plugin.tx_newsenhanced_newscarousels.idsParentCategoryRelatedNews`, esclusione
dell'articolo corrente e finestra di due anni. Per garantire che i record non
si ripetano tra i blocchi, l'ordinamento è stabile (`datetime DESC, uid DESC`):
non è più casuale. `maxNumberRelatedNews` non limita più il numero complessivo
quando “Vedi altro” è attivo; tornando allo stato spento riacquista il suo
comportamento originale. Le notizie più recenti mantengono la logica attuale.

Il link funziona come normale collegamento a pagina successiva in assenza di
JavaScript; ogni clic via JavaScript richiede invece la pagina successiva,
aggiunge le card e aggiorna lo Swiper nelle correlate. In caso di errore, un
secondo clic apre la pagina successiva normalmente.

## Verifiche prima del rilascio

- Controllare la lista con 0, 9, 10, 18, 20 notizie e il conteggio acceso/spento.
- Aprire una news con più di 9 correlate e verificare ordine, assenza di
  duplicati, controlli e progress bar Swiper dopo ogni caricamento.
- Provare link “Vedi altro” con JavaScript disabilitato, pagina diretta > 1,
  filtri/categorie eventualmente presenti e due liste nella stessa pagina.
- Con `enabled = 0`, verificare la lista e le correlate originali.

Il ritorno alla lista tramite cronologia browser dopo l'apertura di una news
non ripristina esplicitamente i blocchi caricati. Il browser può conservare la
pagina con la propria cache di navigazione; per un ripristino garantito serve
una successiva iterazione dedicata.
