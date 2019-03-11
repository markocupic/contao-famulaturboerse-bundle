# Contao Famulaturbörse bundle
Das Bundle enthält die Frontend und Backend-Module für https://degam-famulaturboerse.de

## Installation

### Opengeo Datenbank
Vor der Installation sollte die opengeo Datenbank auf dem Server installiert sein. Die Datei findest sich in src/Resources/contao. Die Datenbank wird für die Umkreissuche benötigt.

### Plugin INstallation
Danach über den Manager oder die Konsole das Bundle installieren.
´´´
composer require markocupic/contao-famulaturboerse-bundle
´´´
Jetzt die Datenbak updaten.
Das Plugin installiert zusätzlich die Erweiterungen:
´´´
"require": {
  "contao/core-bundle": "^4.4",
  "codefog/contao-haste": "^4.23",
  "markocupic/notification-center-default-email-bundle": "^1.0",
  "terminal42/notification_center": "^1.5"
},
´´´
### Opengeo Einstellungen
IN den Backend Einstellungen müssen die Datenbankverbindungsoptionen gesetzt werden.
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

Danach die Module den Seiten/Artikeln zuweisen
