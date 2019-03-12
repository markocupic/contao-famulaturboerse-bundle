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
use Contao\Input;
use Contao\PageModel;
use Contao\FamulaturAngebotModel;
use Patchwork\Utf8;
use Markocupic\Famulatur\Classes\FamulaturModule;
use Contao\BackendTemplate;
use Contao\Database;

/**
 * Class ModuleAngebotDashboard
 * @package Markocupic\Famulatur\Modules
 */
class ModuleAngebotDashboard extends FamulaturModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_angebotDashboard';

    /**
     * @var bool
     */
    protected $allowListing = true;

    /**
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['angebotDashboard'][0]) . ' ###';
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

        // Delete item
        if (Input::get('action') === 'delete')
        {
            $objAngebot = FamulaturAngebotModel::findByIdOrAlias(Input::get('items'));
            if ($objAngebot !== null)
            {
                $objAngebot->delete();
                $strMsg = $GLOBALS['TL_LANG']['MISC']['deletedAngebot'];
                $this->setFlashMessage($this->strFlashType, $strMsg);
            }

            // Redirect back
            global $objPage;
            $oPage = PageModel::findWithDetails($objPage->id);
            $this->redirect($oPage->getAbsoluteUrl());
        }

        // Allow module to logged in users only
        if ($this->getMemberModel() === null)
        {
            $this->allowListing = false;
            $this->arrMessages[] = $GLOBALS['TL_LANG']['MISC']['notAllowedUsingFamulaturDashboard'];
        }

        return parent::generate();
    }

    /**
     *
     */
    protected function compile()
    {
        global $objPage;
        if ($this->allowListing)
        {
            $this->Template->allowListing = true;

            $arrRows = [];
            $objListing = Database::getInstance()->prepare('SELECT * FROM tl_famulatur_angebot WHERE fd_member=? ORDER BY date DESC')->execute($this->objMember->id);
            $this->Template->countRows = $objListing->numRows;
            while ($objListing->next())
            {
                $arrRow = $objListing->row();

                // Get the jumpToFormHref
                if (isset($this->formAngebotJumpTo))
                {
                    $objFormPage = PageModel::findWithDetails($this->formAngebotJumpTo);
                    if ($objFormPage !== null)
                    {
                        $href = $objFormPage->getAbsoluteUrl(Config::get('useAutoItem') ? '/%s' : '/items/%s');
                        $arrRow['editItemHref'] = sprintf($href, $arrRow['alias']);
                    }
                }
                // Get the deleteItemHref
                if (isset($objPage->id))
                {
                    $objPageDelete = PageModel::findWithDetails($objPage->id);
                    if ($objPageDelete !== null)
                    {
                        $href = $objPageDelete->getAbsoluteUrl(Config::get('useAutoItem') ? '/%s' : '/items/%s') . '?action=delete';
                        $arrRow['deleteItemHref'] = sprintf($href, $arrRow['alias']);
                    }
                }
                // Get the readerItemHref
                if (isset($objPage->id))
                {
                    $objPageReader = PageModel::findWithDetails($this->readerJumpTo);
                    if ($objPageReader !== null)
                    {
                        $href = $objPageReader->getAbsoluteUrl(Config::get('useAutoItem') ? '/%s' : '/items/%s');
                        $arrRow['readerItemHref'] = sprintf($href, $arrRow['alias']);
                    }
                }

                $arrRows[] = $arrRow;
            }

            // Get the createNewHref
            if (isset($this->formAngebotJumpTo))
            {
                $objFormPage = PageModel::findWithDetails($this->formAngebotJumpTo);
                if ($objFormPage !== null)
                {
                    $this->Template->createNewHref = $objFormPage->getAbsoluteUrl();
                }
            }

            $this->Template->rows = $arrRows;
        }

        // Call addMessagesTorTemplate form parent class
        $this->addMessagesToTemplate();
    }
}

class_alias(ModuleAngebotDashboard::class, 'ModuleAngebotDashboard');
