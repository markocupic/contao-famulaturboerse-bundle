<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @package Es_resetpassword
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    // Dca
    'tl_famulatur_angebot' => 'system/modules/famulatur/classes/dca/tl_famulatur_angebot.php',
    'tl_module_famulatur' => 'system/modules/famulatur/classes/dca/tl_module_famulatur.php',

    // Classes
    'Markocupic\Famulatur\Classes\FamulaturHelper'      => 'system/modules/famulatur/classes/FamulaturHelper.php',

    // Hooks
    'Markocupic\Famulatur\Hooks\InsertFamulaturAngebot'      => 'system/modules/famulatur/hooks/InsertFamulaturAngebot.php',
    'Markocupic\Famulatur\Hooks\UpdateFamulaturAngebot'      => 'system/modules/famulatur/hooks/UpdateFamulaturAngebot.php',

    // Frontend modules
    'Markocupic\Famulatur\Modules\ModuleAngebotForm'    => 'system/modules/famulatur/modules/ModuleAngebotForm.php',
    'Markocupic\Famulatur\Modules\ModuleAngebotDashboard'    => 'system/modules/famulatur/modules/ModuleAngebotDashboard.php',
    'Markocupic\Famulatur\Modules\ModuleAngebotList'    => 'system/modules/famulatur/modules/ModuleAngebotList.php',
    'Markocupic\Famulatur\Modules\ModuleAngebotReader'    => 'system/modules/famulatur/modules/ModuleAngebotReader.php',


    // Models
    'Contao\FamulaturAngebotModel' => 'system/modules/famulatur/models/FamulaturAngebotModel.php',
));


