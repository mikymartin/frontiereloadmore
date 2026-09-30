# Frontiere Load More

Paginazione incrementale delle liste news di Frontiere, compatibile con
TYPO3 13.4 e `georgringer/news` 12.3. Dopo i primi 9 elementi, ogni clic su
«Vedi altro» aggiunge il blocco successivo; il numero per blocco è configurabile.
La funzione riguarda esclusivamente le liste news: il rendering della pagina
dettaglio rimane gestito dalle estensioni del sito.

L'estensione contiene CSS, JavaScript e un override del partial
`List/ListSelection.html` del tema Frontiere. Non registra controller o plugin
Extbase e non sostituisce oggetti TypoScript del dettaglio.

## Installazione

1. Installare `polimi/frontiereloadmore` tramite Composer.
2. Includere lo static TypoScript **Frontiere: Vedi altro news** dopo quelli
   di `news`, `polimipackage` e `frontieretema`.
3. Svuotare la cache TYPO3 e verificare la lista con almeno 20 notizie.

## Aggiornamento alla 1.0.4

Sono stati rimossi controller, repository, template e JavaScript dedicati
alle correlate. Sono state eliminate la dipendenza Composer e TYPO3 e tutte
le configurazioni che intervenivano nel dettaglio. Il titolo dell'estensione
ora è **Frontiere Load More**. Il `.gitignore` segue quello di `frontieretema`.

Aggiornare il pacchetto con la procedura Composer del progetto e svuotare
la cache di sistema, compreso il container DI compilato. Il dettaglio torna
alla visualizzazione configurata dal sito, senza il pulsante aggiunto da
questa estensione. Se si aggiorna per copia manuale, eliminare anche i file
rimossi, senza sovrapporre soltanto quelli nuovi.

Se era ancora installata `frontiere_news_load_more`, disattivarla e rimuovere
il suo static TypoScript prima di usare `frontiereloadmore`.

## Impostazioni

Nel Template / Constant Editor, categoria `frontiereloadmore`:

| Chiave | Predefinito | Effetto |
| --- | ---: | --- |
| `plugin.tx_frontiereloadmore.enabled` | 1 | Accende/spegne «Vedi altro» nelle liste. A 0 torna il partial originale. |
| `plugin.tx_frontiereloadmore.itemsPerPage` | 9 | Numero di news per blocco. |
| `plugin.tx_frontiereloadmore.showCount` | 1 | Mostra/nasconde «9 di 50», «18 di 50», ecc. |

L'opzione è globale per il sito. Quando è accesa, il numero per pagina è
condiviso con le liste `news` del sito. Le varianti hero e slider del partial
Frontiere conservano la visualizzazione esistente.

Con JavaScript, «Vedi altro» richiede la pagina successiva e aggiunge le card.
Senza JavaScript funziona come normale collegamento alla pagina successiva.
In caso di errore di caricamento, un secondo clic apre la pagina successiva.

## Verifiche sul sito

- Liste con 0, 9, 10, 18 e 20 notizie: caricamento, conteggio e scomparsa del
  pulsante all'ultimo blocco.
- Conteggio acceso/spento e funzione accesa/spenta.
- Apertura dettaglio, slider tematico e ultime news secondo la configurazione
  originale del sito.
- JavaScript disabilitato, filtri/categorie e due liste nella stessa pagina.

Il ritorno alla lista tramite cronologia browser non ripristina esplicitamente
i blocchi caricati: il browser può conservarli nella propria cache di navigazione.
