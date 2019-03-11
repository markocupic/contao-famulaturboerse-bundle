<?php
/**
 * Created by PhpStorm.
 * User: Marko
 * Date: 08.03.2019
 * Time: 09:33
 */

namespace Markocupic\Famulatur\Modules;


use Contao\Config;
use Contao\Controller;
use Contao\Environment;
use Contao\Input;
use Contao\PageModel;
use Haste\Form\Form;
use Contao\FamulaturAngebotModel;
use Haste\Util\Url;
use Patchwork\Utf8;
use Contao\Module;
use Contao\BackendTemplate;
use Contao\Database;

/**
 * Class ModuleAngebotList
 * @package Markocupic\Famulatur\Modules
 */
class ModuleAngebotList extends Module
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
     * Display a login form
     *
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
        //$this->generateZipcodeTable();

        return parent::generate();
    }


    protected function compile()
    {
        global $objPage;


        $this->generateFilterForm();

        $this->Template->form = $this->form;


        // Build query
        $arrCol = array('id>?');
        $arrVal = array(0);
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

        // Get Model
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
                        $href = $objFormPage->getAbsoluteUrl(Config::get('useAutoItem') ? '/%s' : '/items/%s');
                        $arrRow['readerHref'] = sprintf($href, $arrRow['alias']);
                    }
                }

                $arrRows[] = $arrRow;
            }

            if (count($arrRows) > 0)
            {
                $this->Template->countRows = count($arrRows);
            }

        }


        // Generate sortBy urls
        $this->Template->urlSortByPlz = $this->generateSortingUrl('anform_plz');
        $this->Template->urlSortByStadt = $this->generateSortingUrl('anform_stadt');
        $this->Template->urlSortByKurzbeschreibung = $this->generateSortingUrl('anform_kurzbeschreibung');
        $this->Template->urlSortByFachrichtung = $this->generateSortingUrl('anform_richtung');

        $this->Template->rows = $arrRows;

    }

    protected function generateFilterForm()
    {

        Controller::loadDataContainer('tl_famulatur_angebot');

        // Create the form
        $objForm = $this->createForm('form-famulatur-listing-filter');

        // Add hidden fields REQUEST_TOKEN & FORM_SUBMIT
        //$objForm->addContaoHiddenFields();
        $objForm->addSubmitFormField('submit', 'Filter anwenden');


        $objForm->addFormField('anform_filter_postal', array(
            'label'     => $GLOBALS['TL_LANG']['MISC']['anform_filter_postal'],
            'inputType' => 'text',
            'eval'      => array('rgxp' => 'natural', 'minlength' => 5, 'maxlength' => 5, 'mandatory' => false)
        ));

        $objForm->addFormField('anform_filter_umkreis', array(
            'label'     => $GLOBALS['TL_LANG']['MISC']['anform_filter_umkreis'],
            'inputType' => 'select',
            'options'   => array(
                10    => '10 km',
                25    => '25 km',
                50    => '50 km',
                100   => '100 km',
                1000  => '1000 km',
                10000 => '10000 km'


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

        // Validate and save
        if ($objForm->validate())
        {
            $blnHasError = false;

            if (!$blnHasError)
            {
                // No validation
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
     * @return array
     */
    protected function getValidZipcodesFromOpenGeo()
    {

        $arrConfig = array(
            'dbHost'     => Config::get('openGeoDbHost'),
            'dbPort'     => Config::get('openGeoDbPort'),
            'dbUser'     => Config::get('openGeoDbUser'),
            'dbPass'     => Config::get('openGeoDbPassword'),
            'dbDatabase' => Config::get('openGeoDbDatabase')
        );

        $arrZip = array();


        $objDb = Database::getInstance($arrConfig)->prepare('SELECT * FROM zipcode')->execute();
        while ($objDb->next())
        {
            $arrZip[] = $objDb->zipcode;
        }

        return $arrZip;
    }


    /**
     * @param $intUmkreis
     * @param $strZip
     * @return array
     */
    protected function filterByUmkreis($intUmkreis, $strZip)
    {

        $sql = 'SELECT ' .
            'dest.zc_zip, ' .
            'dest.zc_location_name, ' .
            'ACOS( ' .
            '     SIN(RADIANS(src.zc_lat)) * SIN(RADIANS(dest.zc_lat)) ' .
            '     + COS(RADIANS(src.zc_lat)) * COS(RADIANS(dest.zc_lat)) ' .
            '     * COS(RADIANS(src.zc_lon) - RADIANS(dest.zc_lon)) ' .
            '     ) * 6380 AS distance ' .
            'FROM zip_coordinates dest ' .
            'CROSS JOIN zip_coordinates src ' .
            'WHERE src.zc_id IN (SELECT zc_id FROM zip_coordinates WHERE zc_zip LIKE "' . urldecode($strZip) . '%")' .
            'AND dest.zc_id <> src.zc_id ' . 'HAVING distance < ' . $intUmkreis . ' ' .
            'ORDER BY distance';

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


    /**
     * @see http://opengeodb.org/wiki/OpenGeoDB_-_Umkreissuche
     * @see Download http://www.fa-technik.adfc.de/code/opengeodb/
     * @see http://www.lichtblau-it.de/downloads
     */
    protected function generateZipcodeTable()
    {

        $arrConfig = array(
            'dbHost'     => Config::get('openGeoDbHost'),
            'dbPort'     => Config::get('openGeoDbPort'),
            'dbUser'     => Config::get('openGeoDbUser'),
            'dbPass'     => Config::get('openGeoDbPassword'),
            'dbDatabase' => Config::get('openGeoDbDatabase')
        );

        $createTableSQL = 'CREATE TABLE zip_coordinates (
            zc_id INT NOT NULL auto_increment PRIMARY KEY,
            zc_loc_id INT NOT NULL ,
            zc_zip VARCHAR( 10 ) NOT NULL ,
            zc_location_name VARCHAR( 255 ) NOT NULL ,
            zc_lat DOUBLE NOT NULL ,
            zc_lon DOUBLE NOT NULL)';
        Database::getInstance($arrConfig)->prepare($createTableSQL)->execute();


        $objDb = Database::getInstance($arrConfig)->prepare('SELECT * FROM zipcode')->execute();
        while ($objDb->next())
        {
            $set = array(
                'zc_loc_id' => $objDb->city_id,
                'zc_zip'    => $objDb->zipcode,
            );
            Database::getInstance($arrConfig)->prepare('INSERT INTO zip_coordinates %s')->set($set)->execute();
        }

        $objDb = Database::getInstance($arrConfig)->prepare('SELECT * FROM zip_coordinates')->execute();
        while ($objDb->next())
        {
            $objDb2 = Database::getInstance($arrConfig)->prepare('SELECT * FROM city WHERE id=?')->execute($objDb->zc_loc_id);
            $set = array(
                'zc_location_name' => $objDb2->name,
                'zc_lat'           => $objDb2->lat,
                'zc_lon'           => $objDb2->lng
            );
            Database::getInstance($arrConfig)->prepare('UPDATE zip_coordinates %s WHERE zc_id=?')->set($set)->execute($objDb->zc_id);
        }
        return;
    }


}

//class_alias(ModuleAngebotList::class, 'ModuleAngebotList');
