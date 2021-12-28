<h1 align="center">Digital Green Certificate SDK PHP</h1>        

# Indice
- [Contesto](#contesto)
- [Installazione](#installazione)
  - [Permessi cartella assets](#permessi-cartella-assets)
- [Uso](#uso)
  - [Cache Folder](#cache-folder)
  - [Green Pass Rafforzato](#green-pass-rafforzato)
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

Dalla release `1.0.5` esiste la possilità di modificare il path di salvataggio dei 
file, utilizzando il metodo `overrideCacheFilePath` della classe `FileUtils`:

```php
Herald\GreenPass\Utils\FileUtils::overrideCacheFilePath("/absolute/path/to/cache/folder");
```
oppure su Windows:
```php
Herald\GreenPass\Utils\FileUtils::overrideCacheFilePath("c:\path\to\cache\folder");
```

## Green Pass Rafforzato
Dalla versione `1.0.5` è necessario definire una delle due modalità di verifica della Certificazione verde Covid-19: BASE o RAFFORZATA.
* Tipologia BASE `3G`: l'sdk considera valide le certificazioni verdi generate da vaccinazione, da guarigione, da tampone.
* Tipologia RAFFORZATA `2G`: l'sdk considera valide solo le certificazioni verdi generate da vaccinazione o da guarigione.

Per selezionare la tipologia, è possibile passare al costruttore del validatore un parametro di tipo `Herald\GreenPass\Validation\Covid19\ValidationScanMode`.

Nel caso in cui non venisse scelto, viene impostata di default la tipologia BASE.

```php
// set scan mode to 3G (BASE)
$scanMode = ValidationScanMode::CLASSIC_DGP;
// or set scan mode to 2G (RAFFORZATO)
$scanMode = ValidationScanMode::SUPER_DGP;

$gp_reader = new CertificateValidator($gp_string, $scanMode);
```

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
