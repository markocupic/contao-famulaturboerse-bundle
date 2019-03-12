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

Zusätzlich muss jetzt noch die Benachrichtigung im Notification Center erstellt werden und die SMTP-Einstellungen gemacht werden für den E-Mailversand bei neuen Inserts in die Famulatur-Tabelle.
Folgende Tags können für die Benachrichtigung benutzt werden: 
##anform_*## für den Zugriff auf die Formularwerte, ##email_text## für den Zugriff auf den ganzen Datensatz und ##link_backend## für den Link ins Contao-Backend zum aktuellen Famulatur-Datensatz.


Danach die Module den Seiten/Artikeln zuweisen.


### Hooks
Um auf Updates und Inserts reagieren zu können, existieren 2 Hooks. Siehe config.php

```php
$GLOBALS['TL_HOOKS']['onInsertFamulaturAngebot'][] = array('Markocupic\Famulatur\Hooks\InsertFamulaturAngebot', 'insertFamulaturAngebot');
$GLOBALS['TL_HOOKS']['onUpdateFamulaturAngebot'][] = array('Markocupic\Famulatur\Hooks\UpdateFamulaturAngebot', 'updateFamulaturAngebot');
```
Der onInsertFamulaturAngebot-Hook wird beispielsweise benutzt, um die Benachrichtigungen bei neuen Inserts zu versenden.
```php
<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */

namespace Markocupic\Famulatur\Hooks;

use Contao\Controller;
use Contao\Environment;
use Contao\FamulaturAngebotModel;
use Contao\MemberModel;
use Contao\Module;
use Contao\StringUtil;
use NotificationCenter\Model\Notification;
use Haste\Form\Form;

/**
 * Class InsertFamulaturAngebot
 * @package Markocupic\Famulatur\Hooks
 */
class InsertFamulaturAngebot
{

    /**
     * @param FamulaturAngebotModel $objAngebotModel
     * @param MemberModel|null $objMember
     * @param Form $objForm
     * @param Module|null $objModule
     */
    public static function insertFamulaturAngebot(FamulaturAngebotModel $objAngebotModel, MemberModel $objMember = null, Form $objForm, Module $objModule = null)
    {
        global $objPage;

        Controller::loadLanguageFile('tl_famulatur_angebot');

        if ($objModule !== null)
        {
            if ($objModule->notifyOnFamulaturAngebotInserts)
            {
                $objEmail = Notification::findByPk($objModule->insertFamulaturAngebotNotification);
                if ($objEmail !== null)
                {
                    // Set token array
                    $arrTokens = array(
                        'link_backend' => sprintf('%s/contao?do=famulatur_angebotn&act=edit&id=%s', Environment::get('url'), $objAngebotModel->id),
                    );

                    // Add wildcards anform_* & email_text
                    $strText = '';
                    $arrRow = $objAngebotModel->row();
                    $arrAllowedFields = StringUtil::deserialize($objModule->formFields, true);
                    foreach ($arrRow as $k => $v)
                    {
                        if (in_array($k, $arrAllowedFields))
                        {
                            $key = $GLOBALS['TL_LANG']['tl_famulatur_angebot'][$k][0] != '' ? $GLOBALS['TL_LANG']['tl_famulatur_angebot'][$k][0] : $k;
                            $strText .= '[' . $key . ']';
                            $strText .= "\r\n";
                            $strText .= html_entity_decode($v) != '' ? html_entity_decode($v) : '----';
                            $strText .= "\r\n";
                            $strText .= "\r\n";
                        }

                        if (strpos($k, 'anform_') === 0)
                        {
                            $arrTokens[$k] = html_entity_decode($v);
                        }
                    }
                    $arrTokens['email_text'] = $strText;

                    $objEmail->send($arrTokens, $objPage->language);
                }
            }
        }
    }
}

class_alias(InsertFamulaturAngebot::class, 'InsertFamulaturAngebot');

```


