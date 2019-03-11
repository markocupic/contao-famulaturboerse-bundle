<?php
/**
 * Created by PhpStorm.
 * User: Marko
 * Date: 10.03.2019
 * Time: 19:43
 */

\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('open_geo_legend','', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField(['openGeoDbHost', 'openGeoDbPort', 'openGeoDbUser','openGeoDbPassword','openGeoDbDatabase'], 'open_geo_legend')
    ->applyToPalette('default', 'tl_settings');

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
