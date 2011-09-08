Università degli Studi di Brescia

Elaborato per il corso di Programmazione Web - a.a. 2010/2011
=============================================================

Andrea Piccinelli - 83392
Roberta Lorenzi   - 72361

Sito Web del Gruppo di Basi di Dati e Sistemi Informativi: Pubblicazioni e Corsi

Progetto hostato su GitHub:
https://github.com/frasten/programmazione-web


Configurazione iniziale
-----------------------

* È necessario configurare i parametri relativi al proprio hosting nel
  file `www/inc/config.inc.php`, prestando attenzione soprattutto ai
  dati di accesso al database MySQL.

* Si presti attenzione alla presenza dei file .htaccess (sotto i sistemi
  Unix-like essi appaiono nascosti), sono situati in queste posizioni:
  - www/.htaccess
  - www/inc/.htaccess

* È inoltre importante che il webserver abbia accesso in scrittura alla
  directory di installazione.
  Se ciò non fosse possibile, limitarsi a fornire l'accesso in scrittura
  solo alla directory di upload con questa procedura manuale:
  - creare una directory chiamata `uploads` nella directory www/
  - Dare accesso in scrittura al webserver per tale directory (il metodo
    più rapido per i sistemi unix-like è di dare il seguente comando:
    chmod 777 <path_del_sito>/www/uploads/

    È possibile fare lo stesso anche con un client FTP che supporti la
    gestione dei permessi (ad esempio FileZilla è gratuito, opensource
    e multipiattaforma).

  In alternativa è possibile specificare un path differente da
  quello di default per l'upload, assicurandosi che sia già
  scrivibile dal webserver e che sia accessibile tramite protocollo
  HTTP.
  Per fare ciò basta modificare il valore della variabile:
  $config['upload_path']
  nel file www/inc/config.inc.php.

* Nel caso si debba creare da zero il database e l'utente associato,
  è possibile dare i seguenti comandi d'esempio al prompt di MySQL:

  ```sql
  CREATE DATABASE pw2011;
  CREATE USER 'pw'@'localhost' IDENTIFIED BY '12345';
  GRANT ALL PRIVILEGES ON pw2011.* TO 'pw'@'localhost' WITH GRANT OPTION;
  FLUSH PRIVILEGES;
  ```

  Ovviamente è bene modificare tutti i parametri di configurazione come
  si preferisce; non si dimentichi di modificare i parametri nel file
  www/inc/config.inc.php, così come impostati in fase di creazione.


Il primo utilizzo
-----------------

Al primo utilizzo non esiste ancora un utente amministratore.
Esso viene creato al primo accesso alla pagina /admin.php.
Verranno così comunicate le credenziali d'accesso, che saranno comunque
modificabili a piacere accedendo alla gestione utenti nel portale.


La documentazione
-----------------

È presente una relazione nella directory Docs/Elaborato/, in formato
LaTeX.
Sotto Linux il file in formato PDF è generabile entrando in tale
directory e dando il comando `make`, previa l'installazione dei
pacchetti di LaTeX.
Ad esempio sotto Ubuntu è possibile installarli attraverso il comando:
  sudo apt-get install texlive-latex-base texlive-latex-recommended \
    texlive-lang-italian texlive-fonts-recommended


Sono consultabili anche dei mockup, creati con Balsamiq Mockups,
software purtroppo non gratuito, ma che nella sua versione demo consente
comunque di importare/esportare dei file in formato XML, contenenti il
mockup in questione.

La versione dimostrativa è utilizzabile anche tramite browser web
a questo indirizzo:
http://builds.balsamiq.com/b/mockups-web-demo/
