<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */

namespace Markocupic\Famulatur\Modules;

use Contao\Config;
use Contao\Controller;
use Contao\Input;
use Contao\StringUtil;
use Markocupic\Famulatur\Classes\OpenStreetMapGeoCode;
use Patchwork\Utf8;
use Markocupic\Famulatur\Classes\FamulaturModule;
use Contao\BackendTemplate;

/**
 * Class ModuleAngebotReader
 * @package Markocupic\Famulatur\Modules
 */
class ModuleAngebotReader extends FamulaturModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_angebotReader';

    /**
     * @var
     */
    protected $allowedFields;

    /**
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['angebotReader'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Set the item from the auto_item parameter
        if (!isset($_GET['items']) && Config::get('useAutoItem') && isset($_GET['auto_item']))
        {
            Input::setGet('items', Input::get('auto_item'));
        } 

        // Get Famulatur Angebot Model from parent class
        if ($this->getFamulaturAngebotModel() === null)
        {
            return '';
        }

        $this->allowedFields = StringUtil::deserialize($this->formFields, true);

        return parent::generate();
    }

    /**
     *
     */
    protected function compile()
    {
        Controller::loadLanguageFile('tl_famulatur_angebot');

        $arrItem = array();
        $arrRow = $this->objFamulaturAngebot->row();
        foreach ($arrRow as $k => $v)
        {
            if (in_array($k, $this->allowedFields))
            {
                $arrItem[$k] = $v;
            }
        }

        // Geocode for osm (open street map)
        if ($arrRow['anform_lat'] !== '' && $arrRow['anform_lng'])
        {
            $this->Template->hasGeo = true;
            $this->Template->lat = $arrRow['anform_lat'];
            $this->Template->lng = $arrRow['anform_lng'];
        }

        $this->Template->item = $arrItem;

        // Closure for label
        $this->Template->getLabel = (function ($k) {
            if ($GLOBALS['TL_LANG']['tl_famulatur_angebot'][$k][0] != '')
            {
                return $GLOBALS['TL_LANG']['tl_famulatur_angebot'][$k][0];
            }
            else
            {
                return $k;
            }
        });

        // Closure for data
        $this->Template->getValue = (function ($k) use ($arrRow) {
            if (isset($arrRow[$k]))
            {
                return $arrRow[$k];
            }
            else
            {
                return '';
            }
        });
    }
}

class_alias(ModuleAngebotReader::class, 'ModuleAngebotReader');
