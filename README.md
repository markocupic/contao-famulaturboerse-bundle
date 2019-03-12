# Contao Famulaturbörse Bundle
Das Bundle enthält die Frontend- und Backend-Module für https://degam-famulaturboerse.de

## Installation

### Opengeo Datenbank
Vor der Installation sollte die Opengeo Datenbank auf dem Server installiert sein. Die Datei befindet sich in src/Resources/contao. Die Openge-Datenbank wird für die Umkreissuche benötigt.

### Plugin Installation
Danach über den Contao Manager oder die Konsole das Bundle installieren.
```php
composer require markocupic/contao-famulaturboerse-bundle
```
Jetzt die Datenbak updaten.

Das Plugin installiert zusätzlich die Erweiterungen:

```php
"require": {
  ...
  ....
  .....
  "codefog/contao-haste": "^4.23",
  "terminal42/notification_center": "^1.5"
  .....
  ....
  ...
},
```

### Opengeo Einstellungen
In den Backend Einstellungen müssen die Datenbankverbindungsoptionen gesetzt werden.
Datenbankuser, Passwort, Port, Datenbankname

### Seiten/Frontend-Module anlegen
Es müssen 4 Seiten angelegt werden:
- Famulatur-Angebote (Reader)
- Famulatur-Angebote (Liste)
- Formularseite zum Erfassen der Famulatur-Angebote
- Dashboard für eingeloggte Member


Es müssen 4 Module am besten in dieser Reihenfolge angelegt werden.
- Famulatur-Angebote (Reader)
- Famulatur-Angebote (Liste)
- Formularseite zum Erfassen der Famulatur-Angebote
- Famulatur-Angebote Dashboard

Zusätzlich muss jetzt noch die Benachrichtigung im Notification Center erstellt werden und die SMTP-Einstellungen gemacht werden für den E-Mailversand vei neuen Inserts in die Famulatur-Tabelle.

### Hooks
Um auf Updates und Inserts reagieren zu können existieren 2 Hooks. Siehe config.php

Danach die Module den Seiten/Artikeln zuweisen
