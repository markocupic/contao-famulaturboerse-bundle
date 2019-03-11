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
use Contao\CoreBundle\Security\Exception\LockedException;
use Contao\Environment;
use Contao\FrontendUser;
use Contao\Input;
use Contao\MemberModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Haste\Form\Form;
use Contao\FamulaturAngebotModel;
use Markocupic\Famulatur\Classes\FamulaturHelper;
use Patchwork\Utf8;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Contao\Module;
use Contao\BackendTemplate;
use Contao\Database;

/**
 * Class ModuleAngebotReader
 * @package Markocupic\Famulatur\Modules
 */
class ModuleAngebotReader extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_angebotReader';

    /**
     * @var
     */
    protected $angebotModel;

    /**
     * @var
     */
    protected $allowedFields;


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

        if (Input::get('items') !== '')
        {
            $objAngebot = FamulaturAngebotModel::findByIdOrAlias(Input::get('items'));
            if ($objAngebot !== null)
            {
                $this->angebotModel = $objAngebot;
            }
        }

        if (!$this->angebotModel)
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
        foreach($this->angebotModel->row() as $k => $v)
        {
            if(in_array($k, $this->allowedFields))
            {
                $arrItem[$k] = $v;
            }
        }
        $this->Template->item =  $arrItem;


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


    }

}

//class_alias(ModuleAngebotReader::class, 'ModuleAngebotReader');
