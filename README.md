<h1 align="center">Digital Green Certificate SDK PHP</h1>        

# Indice
- [Contesto](#contesto)
- [Installazione](#installazione)
- [Uso](#uso)
- [Licenza](#licenza)
  - [Autori / Copyright](#autori--copyright)
  - [Dettaglio licenza](#dettaglio-licenza)

# Contesto
Questo repository contiene un Software Development Kit (SDK), basato su 
<a href="https://github.com/ministero-salute/it-dgc-verificac19-sdk-android/">
ministero-salute/it-dgc-verificac19-sdk-android</a>, che consente di integrare nei sistemi
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
 
# Installazione
E' necessario clonare questo progetto, nel seguente modo:

```
your_project_folder
|___sdk_repo_folder
```
a questo punto lanciare all'interno della cartella `sdk_repo_folder` il comando
```
composer install
```
e settare i permessi della cartella `sdk_repo_folder\assets` in modo tale che 
il webserver possa leggere, creare ed editare i file contenuti in essa.

###   

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
 

## Contributori

Qui c'&egrave; una lista di contributori. Grazie per essere partecipi nel
miglioramento del progetto giorno dopo giorno!
    
<a href="https://github.com/ministero-salute/it-dgc-verificac19-sdk-android">  
  <img    
  src="https://contributors-img.web.app/image?repo=herald-si/verificac19-sdk-php"   
  />    
</a>    
    
# Licenza

## Dettaglio Licenza
La licenza per questo repository &egrave; una `Apache License 2.0`.
All'interno del file [LICENSE](./LICENSE.md) sono presenti le informazioni
specifiche.