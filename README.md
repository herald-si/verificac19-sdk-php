<h1 align="center">Digital Green Certificate SDK PHP</h1>

# Indice
- [Contesto](#contesto)
- [Changelog](#changelog)
- [Installazione](#installazione)
  - [Permessi cartella assets](#permessi-cartella-assets)
- [Uso](#uso)
  - [Cache Folder](#cache-folder)
  - [Proxy](#proxy)
  - [Modalità di scansione](#scan-mode)
- [Debug mode](#debug-mode)
  - [Force cache update](#force-cache-update)
  - [Errori di scansione](#visualizza-errori-scansione)
- [Licenza](#licenza)
  - [Dettaglio licenza](#dettaglio-licenza)

# Contesto
**Attenzione, questo repository è derivato dalle specifiche presenti in <a href="https://github.com/ministero-salute/it-dgc-verificac19-sdk-android/">ministero-salute/it-dgc-verificac19-sdk-android</a>!**

**L'elenco le librerie utilizzabili è presente in questa <a href="https://github.com/ministero-salute/it-dgc-verificac19-sdk-onboarding#lista-librerie">lista</a>. La pagina contiene anche informazioni sulle policy di accettazione e rimozione dalla lista stessa. Fate riferimento ad essa prima di utilizzo in ambienti di produzione.**

Questo repository contiene un Software Development Kit (SDK), che consente di integrare nei sistemi
le funzionalit&agrave; di verifica della Certificazione verde COVID-19, mediante
la lettura del QR code.

# Trattamento dati personali
Il trattamento dei dati personali svolto dalle soluzioni applicative sviluppate
a partire dalla presente SDK deve essere effettuato limitatamente alle
informazioni pertinenti e alle operazioni strettamente necessarie alla verifica
della validit&agrave; delle Certificazioni verdi COVID-19. Inoltre &egrave; fatto esplicito
divieto di conservare il codice a barre bidimensionale (QR code) delle
Certificazioni verdi COVID-19 sottoposte a verifica, nonché di estrarre,
consultare, registrare o comunque trattare per finalit&agrave; ulteriori rispetto
a quelle previste per la verifica della Certificazione verde COVID-19 o le
informazioni rilevate dalla lettura dei QR code e le informazioni fornite in
esito ai controlli, come indicato nel DPCM 12 ottobre 2021

# Requisiti
- PHP >= 7.3
- COSE-lib requires the GMP or bcmath extension [vedi issue #31](https://github.com/herald-si/verificac19-sdk-php/issues/31#issuecomment-993470072)
- SQLite per la gestione delle DCC Revoke List

# Changelog
Il changelog è disponibile [qui](./CHANGELOG.md).

# Installazione
E' necessario clonare questo progetto, nel seguente modo:

```
your_project_folder
|___sdk_repo_folder
```
a questo punto lanciare all'interno della cartella `sdk_repo_folder` il comando
```
composer install --no-dev
```

## Permessi cartella assets

E' necessario settare i permessi della cartella `sdk_repo_folder\assets` in modo tale che
il webserver possa leggere, creare ed editare i file contenuti in essa.

Nel caso in cui non fosse possibile cambiare i permessi della cartella,
dalla release `1.0.5` esiste la possibilità di modificare il path di salvataggio dei
file, vedi [Cache Folder](#cache-folder).


# Uso
L'applicazione di verifica dovr&agrave; importare la cartella `vendor` dell'SDK.
```php
require __DIR__ . '/sdk_repo_folder/vendor/autoload.php';
```

A questo punto &egrave; possibile utilizzare una libreria di scansione di QR Code
a scelta che, dopo aver letto un QR Code di un EU DCC, passi la stringa
estratta al validatore
`Herald\GreenPass\Utils\CertificateValidator`.

Esempio:

```php
...
require __DIR__ . '/sdk_repo_folder/vendor/autoload.php';
use Herald\GreenPass\Utils\CertificateValidator;

$gp_string = 'HC1:6BF.......';
$gp_reader = new CertificateValidator($gp_string);
$gp_info = $gp_reader->getCertificateSimple();

// Mostro la struttura dell'esito validazione
echo "<pre>" . print_r($gp_info, true) . "</pre>";
...
```

Osservando la risposta del metodo &egrave; restituito un oggetto
`Herald\GreenPass\Model\CertificateSimple` che contiene
il risultato della verifica.
Il data model contiene i dati relativi alla
persona, la data di nascita, il timestamp di verifica e lo stato della
verifica.

Basandosi su questi dati &egrave; possibile disegnare la UI e fornire all'operatore lo
stato della verifica del DCC.

## Cache Folder

Dalla release `1.0.5` esiste la possibilità di modificare il path di salvataggio dei
file, utilizzando il metodo `overrideCacheFilePath` della classe `FileUtils`:

```php
Herald\GreenPass\Utils\FileUtils::overrideCacheFilePath("/absolute/path/to/cache/folder");
```
oppure su Windows:
```php
Herald\GreenPass\Utils\FileUtils::overrideCacheFilePath("c:\path\to\cache\folder");
```
Dalla release `1.2.0` è possibile aggiornare i file contenuti nella cache utilizzando il metodo `update*()` della classe `UpdateService`:
```php
//aggiorna lo status dei certificati
Herald\GreenPass\Utils\UpdateService::updateCertificatesStatus();
//aggiorna la lista dei certificati
Herald\GreenPass\Utils\UpdateService::updateCertificateList();
//aggiorna le regole di validazione
Herald\GreenPass\Utils\UpdateService::updateValidationRules();
//aggiorna le liste di revoca
Herald\GreenPass\Utils\UpdateService::updateRevokeList();
```
oppure per aggiornare tutte le liste:
```php
Herald\GreenPass\Utils\UpdateService::updateAll();
```
In ogni caso, queste liste vengono aggiornate solo se sono passate 24 ore dall'ultimo aggiornamento, non viene forzato l'update.

E', quindi, possibile all'interno dell'applicativo che utilizza questo SDK creare un cron che viene chiamato periodicamente (orario/6 ore/giornaliero) per il download delle regole.

In questo modo durante la verifica della stringa del GreenPass è probabile che le stesse siano già aggiornate, riducendo i tempi di verifica.

## Proxy
(thanks to [@darpins](https://github.com/darpins))

Dalla release `1.2.3` è possibile utilizzare un proxy per le chiamate agli endpoint per il download delle regole/liste di certificato, utilizzando il metodo `setProxy` della classe `EndpointService`:

```php
Herald\GreenPass\Utils\EndpointService::setProxy("https://username:password@192.168.0.1:8000");
```
## Scan Mode
Funzionalità Legacy.

Per selezionare la tipologia, è possibile passare al costruttore del validatore un parametro di tipo `Herald\GreenPass\Validation\Covid19\ValidationScanMode`.

Nel caso in cui non venisse scelto, viene impostata di default la tipologia `BASE`.

Dalla versione `1.6.0` è possibile utilizzare solo la tipologia `BASE`.

```php
// set scan mode to BASE
$scanMode = ValidationScanMode::CLASSIC_DGP;

$gp_reader = new CertificateValidator($gp_string, $scanMode);
```
# Debug mode
Per aiutare l'implementazione di questo sdk, è stata introdotta una funzionalità di Debug.
E' possibile abilitare la stessa utilizzando il metodo `enableDebugMode` della classe `EnvConfig` e disabilitarlo con il metodo `disableDebugMode`:

```php
Herald\GreenPass\Utils\EnvConfig::enableDebugMode();
... do some test ...
Herald\GreenPass\Utils\EnvConfig::disableDebugMode();
```
## Force cache update
E' possibile, solo con debug mode attivo, forzare l'aggiornamento dei file nella cache, passando il parametro opzionale `force_update` a `true`.
Esempi di funzionamento:
```php
//non viene forzato l'aggiornamento, manca debug mode
Herald\GreenPass\Utils\UpdateService::updateCertificatesStatus(true);
---
//non viene forzato l'aggiornamento, parametro force update a false
Herald\GreenPass\Utils\EnvConfig::enableDebugMode();
Herald\GreenPass\Utils\UpdateService::updateCertificatesStatus();
---
//viene forzato l'aggiornamento, non usare in produzione!
Herald\GreenPass\Utils\EnvConfig::enableDebugMode();
Herald\GreenPass\Utils\UpdateService::updateCertificatesStatus(true);

```
## Visualizza errori scansione
Abilitando il debug mode:
- in tutti i casi in cui la risposta alla scansione avrebbe generato un esito `NOT_EU_DCC`, viene invece mostrato lo stack di errore che ha generato questo esito.
- in tutti gli altri casi, viene mostrato l'esito della validazione, ma viene restituita la stringa `DISABLE-DEBUG-MODE-IN-PRODUCTION` al posto del nome e cognome contenuti nel greenpass (per evitare di mantenere abilitato il debug mode in produzione).

# Licenza

## Dettaglio Licenza
La licenza per questo repository &egrave; una `Apache License 2.0`.
All'interno del file [LICENSE](./LICENSE) sono presenti le informazioni
specifiche.

## Contributori

Qui c'&egrave; una lista di contributori. Grazie per essere partecipi nel
miglioramento del progetto giorno dopo giorno!

<a href="https://github.com/herald-si/verificac19-sdk-php">
  <img
  src="https://contributors-img.web.app/image?repo=herald-si/verificac19-sdk-php"
  />
</a>
