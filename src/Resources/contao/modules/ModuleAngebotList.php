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
use Contao\Environment;
use Contao\Input;
use Contao\PageModel;
use Contao\Pagination;
use Haste\Form\Form;
use Contao\FamulaturAngebotModel;
use Haste\Util\Url;
use Markocupic\Famulatur\Classes\FamulaturHelper;
use Markocupic\Famulatur\Classes\FamulaturModule;
use Patchwork\Utf8;
use Contao\BackendTemplate;
use Contao\Database;
use Contao\CoreBundle\Exception\PageNotFoundException;

/**
 * Class ModuleAngebotList
 * @package Markocupic\Famulatur\Modules
 */
class ModuleAngebotList extends FamulaturModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_angebotList';

    /**
     * @var
     */
    protected $form;

    /**
     * @var array
     */
    protected $arrFilterFields = array('anform_filter_postal', 'anform_filter_umkreis', 'anform_filter_fachrichtung');

    /**
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['angebotList'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     *
     */
    protected function compile()
    {
        // Generate filter form
        $this->generateFilterForm();
        $this->Template->form = $this->form;

        // Reset form url
        global $objPage;
        $objPageModel = PageModel::findByPk($objPage->id);
        if ($objPageModel !== null)
        {
            $this->Template->resetFormUrl = $objPageModel->getFrontendUrl();
        }

        // Build query
        $arrCol = array('id>?');
        $arrVal = array(0);

        // Show featured only
        if ($this->showFeaturedOnly)
        {
            $arrCol[] = 'tl_famulatur_angebot.featured=?';
            $arrVal[] = '1';
        }

        foreach ($this->arrFilterFields as $strFieldname)
        {
            if (strlen(Input::get($strFieldname)))
            {
                if ($strFieldname === 'anform_filter_fachrichtung')
                {
                    $arrCol[] = 'tl_famulatur_angebot.anform_richtung=?';
                    $arrVal[] = Input::get($strFieldname);
                }
                if ($strFieldname === 'anform_filter_postal' && Input::get('anform_filter_umkreis') == '')
                {
                    $arrCol[] = 'tl_famulatur_angebot.anform_plz=?';
                    $arrVal[] = Input::get($strFieldname);
                }
            }
        }

        // Defaults
        $arrOptions = array('order' => 'tl_famulatur_angebot.anform_plz ASC');

        // Get sortBy from url
        if (Input::get('sortBy'))
        {
            if (Input::get('sortDirection') === 'desc')
            {
                $direction = 'desc';
            }
            else
            {
                $direction = 'asc';
            }
            $arrOptions['order'] = sprintf('tl_famulatur_angebot.%s %s', Input::get('sortBy'), $direction);
        }

        // Get model
        $objListing = FamulaturAngebotModel::findBy(
            $arrCol,
            $arrVal,
            $arrOptions
        );
        if ($objListing !== null)
        {
            // Launch Umkreissuche
            if (Input::get('anform_filter_umkreis') != '' && strlen(Input::get('anform_filter_postal')) === 5)
            {
                $arrZips = $this->filterByUmkreis(Input::get('anform_filter_umkreis'), Input::get('anform_filter_postal'));
            }
            $arrValidZips = $this->getValidZipcodesFromOpenGeo();

            $arrRows = array();
            while ($objListing->next())
            {
                // Umkreissuche
                if (isset($arrZips))
                {
                    if (!in_array($objListing->anform_plz, $arrZips))
                    {
                        continue;
                    }
                }

                $arrRow = $objListing->row();

                // Check for valid zipcodes
                if (!in_array($objListing->anform_plz, $arrValidZips))
                {
                    $arrRow['invalidZip'] = true;
                }

                // Get the readerJumpTo URL
                if (isset($this->readerJumpTo))
                {
                    $objFormPage = PageModel::findWithDetails($this->readerJumpTo);
                    if ($objFormPage !== null)
                    {
                        $href = ampersand($objFormPage->getFrontendUrl(Config::get('useAutoItem') ? '/%s' : '/items/%s'));
                        $arrRow['readerHref'] = sprintf($href, $arrRow['alias']);
                    }
                }

                $arrRows[] = $arrRow;
            }
            $total = count($arrRows);
            if ($total > 0)
            {
                $this->Template->countRows = $total;
            }

            // Paginate the result
            if ($this->perPage > 0)
            {
                // Get the current page
                $id = 'page_g' . $this->id;
                $page = Input::get($id) ?? 1;

                // Do not index or cache the page if the page number is outside the range
                if ($page < 1 || $page > max(ceil($total / $this->perPage), 1))
                {
                    throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
                }

                // Set limit and offset
                $offset = ($page - 1) * $this->perPage;
                $limit = min($this->perPage + $offset, $total);

                $objPagination = new Pagination($total, $this->perPage, Config::get('maxPaginationLinks'), $id);
                $this->Template->pagination = $objPagination->generate("\n  ");

                $arrRows = array_slice($arrRows, $offset, $limit);
            }
        }

        // Generate sortBy urls
        $this->Template->urlSortByPlz = $this->generateSortingUrl('anform_plz');
        $this->Template->urlSortByStadt = $this->generateSortingUrl('anform_stadt');
        $this->Template->urlSortByKurzbeschreibung = $this->generateSortingUrl('anform_kurzbeschreibung');
        $this->Template->urlSortByFachrichtung = $this->generateSortingUrl('anform_richtung');

        $this->Template->rows = $arrRows;

        $this->addMessagesToTemplate();
    }

    /**
     *
     */
    protected function generateFilterForm()
    {
        Controller::loadDataContainer('tl_famulatur_angebot');

        // Create the form
        $objForm = $this->createForm('form-famulatur-listing-filter');

        $objForm->addSubmitFormField('submit', 'Filter anwenden');

        $objForm->addFormField('anform_filter_postal', array(
            'label'     => $GLOBALS['TL_LANG']['MISC']['anform_filter_postal'],
            'inputType' => 'text',
            'eval'      => array('rgxp' => 'germanpostal', 'minlength' => 5, 'maxlength' => 5, 'mandatory' => false)
        ));

        $objForm->addFormField('anform_filter_umkreis', array(
            'label'     => $GLOBALS['TL_LANG']['MISC']['anform_filter_umkreis'],
            'inputType' => 'select',
            'options'   => array(
                10  => '10 km',
                25  => '25 km',
                50  => '50 km',
                75  => '75 km',
                100 => '100 km'
            ),
            'eval'      => array('includeBlankOption' => true, 'blankOptionLabel' => 'keine Umkreissuche', 'mandatory' => false)
        ));

        $objForm->addFormField('anform_filter_fachrichtung', array(
            'label'     => $GLOBALS['TL_LANG']['MISC']['anform_filter_fachrichtung'],
            'inputType' => 'select',
            'options'   => $GLOBALS['TL_DCA']['tl_famulatur_angebot']['fields']['anform_richtung']['options'],
            'eval'      => array('includeBlankOption' => true, 'blankOptionLabel' => 'keine Fachrichtung', 'mandatory' => false)
        ));

        foreach ($this->arrFilterFields as $strField)
        {
            $objForm->getWidget($strField)->value = Input::get($strField);
        }

        // Set value to ''
        if (Input::get('anform_filter_postal') == '')
        {
            $objForm->getWidget('anform_filter_umkreis')->value = '';
        }

        if (strlen(Input::get('anform_filter_postal')))
        {
            if (!FamulaturHelper::isValidGermanPostalCode(Input::get('anform_filter_postal')))
            {
                $this->arrMessages[] = sprintf($GLOBALS['TL_LANG']['ERR']['invalidPostalCode'], 'Postleitzahl');
            }
        }

        $this->form = $objForm;
    }

    /**
     * @param $strId
     * @return Form
     */
    protected function createForm($strId)
    {
        $objForm = new Form($strId, 'GET', function ($objHaste) {
            return Input::post('FORM_SUBMIT') === $objHaste->getFormId();
        });

        $url = Environment::get('uri');
        $objForm->setFormActionFromUri($url);

        return $objForm;
    }

    /**
     * @param $key
     * @return string
     */
    protected function generateSortingUrl($key)
    {
        $url = Url::removeQueryString(array('sortDirection=asc'));
        $url = Url::removeQueryString(array('sortDirection=desc'));

        if (!strlen(Input::get('sortDirection')) || Input::get('sortDirection') === 'asc')
        {
            $url = Url::addQueryString('sortDirection=desc', $url);
        }
        else
        {
            $url = Url::addQueryString('sortDirection=asc', $url);
        }

        $url = Url::removeQueryString(array('sortBy=anform_plz'), $url);
        $url = Url::removeQueryString(array('sortBy=anform_stadt'), $url);
        $url = Url::removeQueryString(array('sortBy=anform_kurzbeschreibung'), $url);
        $url = Url::removeQueryString(array('sortBy=anform_richtung'), $url);
        $url = Url::addQueryString('sortBy=' . $key, $url);

        return $url;
    }

    /**
     * @see https://github.com/ratopi/opengeodb/wiki/OpenGeoDB_-_Umkreissuche
     * @param $intUmkreis
     * @param $strZip
     * @return array
     */
    protected function filterByUmkreis($intUmkreis, $strZip)
    {
        $sql = 'SELECT ' .
            'dest.zc_zip, ' .
            'dest.zc_location_name, ' .
            'ACOS( SIN(RADIANS(src.zc_lat)) * SIN(RADIANS(dest.zc_lat)) + COS(RADIANS(src.zc_lat)) * COS(RADIANS(dest.zc_lat)) * COS(RADIANS(src.zc_lon) - RADIANS(dest.zc_lon)) ) * 6380 AS distance ' .
            'FROM zip_coordinates dest CROSS JOIN zip_coordinates src ' .
            'WHERE src.zc_id = (SELECT zc_id FROM zip_coordinates WHERE zc_zip LIKE "%' . urldecode($strZip) . '" ' .
            'LIMIT 0,1) ' .
            'HAVING distance <= ' . urldecode($intUmkreis) . ' ORDER BY distance';

        $arrConfig = array(
            'dbHost'     => Config::get('openGeoDbHost'),
            'dbPort'     => Config::get('openGeoDbPort'),
            'dbUser'     => Config::get('openGeoDbUser'),
            'dbPass'     => Config::get('openGeoDbPassword'),
            'dbDatabase' => Config::get('openGeoDbDatabase')
        );

        $arrZip = array();

        try
        {
            $objDb = Database::getInstance($arrConfig)->prepare($sql)->execute();
            while ($objDb->next())
            {
                if ($objDb->zc_zip != '')
                {
                    $arrZip[] = $objDb->zc_zip;
                }
            }
        } catch (Exception $e)
        {
            echo 'Could not connect to opengeo database. Please check credentials in the Backend Settings of the Contao backend.' . $e->getMessage(), "\n";
        }

        return array_unique($arrZip);
    }
}

class_alias(ModuleAngebotList::class, 'ModuleAngebotList');
