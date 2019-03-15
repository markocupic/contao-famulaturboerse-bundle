<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */

// Fields
$GLOBALS['TL_DCA']['tl_settings']['fields']['openGeoDbHost'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['openGeoDbHost'],
    'inputType' => 'text',
    'eval'      => array('tl_class' => 'w50 wizard'),
);
$GLOBALS['TL_DCA']['tl_settings']['fields']['openGeoDbPort'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['openGeoDbPort'],
    'inputType' => 'text',
    'eval'      => array('rgxp' => 'natural', 'tl_class' => 'w50 wizard'),
);
$GLOBALS['TL_DCA']['tl_settings']['fields']['openGeoDbUser'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['openGeoDbUser'],
    'inputType' => 'text',
    'eval'      => array('tl_class' => 'w50 wizard'),
);
$GLOBALS['TL_DCA']['tl_settings']['fields']['openGeoDbPassword'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['openGeoDbPassword'],
    'inputType' => 'text',
    'eval'      => array('tl_class' => 'w50 wizard'),
);
$GLOBALS['TL_DCA']['tl_settings']['fields']['openGeoDbDatabase'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['openGeoDbDatabase'],
    'inputType' => 'text',
    'eval'      => array('tl_class' => 'w50 wizard'),
);
$GLOBALS['TL_DCA']['tl_settings']['fields']['openCageApiKey'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['openCageApiKey'],
    'inputType' => 'text',
    'eval'      => array('tl_class' => 'clr wizard'),
);

// Add to palette
\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('open_geo_legend', '')
    ->addField(['openGeoDbHost', 'openGeoDbPort', 'openGeoDbUser', 'openGeoDbPassword', 'openGeoDbDatabase'], 'open_geo_legend',\Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_settings');
\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('open_cage_legend', '')
    ->addField('openCageApiKey', 'open_cage_legend',\Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_settings');