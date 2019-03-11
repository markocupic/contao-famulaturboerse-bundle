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
use Contao\FrontendUser;
use Contao\Input;
use Contao\MemberModel;
use Contao\PageModel;
use Contao\System;
use Contao\FamulaturAngebotModel;
use Patchwork\Utf8;
use Contao\Module;
use Contao\BackendTemplate;
use Contao\Database;

/**
 * Class ModuleAngebotDashboard
 * @package Markocupic\Famulatur\Modules
 */
class ModuleAngebotDashboard extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_angebotDashboard';

    /**
     * Flash type
     * @var string
     */
    protected $strFlashType = 'contao.FE.famulatur';

    /**
     * @var
     */
    protected $objMember;


    /**
     * @var array
     */
    protected $arrMessages = array();

    /**
     * @var bool
     */
    protected $allowListing = true;

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
            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['angebotDashboard'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }
        global $objPage;
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
            $oPage = PageModel::findWithDetails($objPage->id);
            $this->redirect($oPage->getAbsoluteUrl());
        }

        // Allow module to logged in users only
        if (($objMember = $this->getMemberModel()) !== null)
        {
            $this->objMember = $objMember;
        }
        else
        {
            $this->allowListing = false;
            $this->arrMessages[] = $GLOBALS['TL_LANG']['MISC']['notAllowedUsingFamulaturDashboard'];
        }

        return parent::generate();
    }


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

                // Get the jumpToFormHref URL
                if (isset($this->formAngebotJumpTo))
                {
                    $objFormPage = PageModel::findWithDetails($this->formAngebotJumpTo);
                    if ($objFormPage !== null)
                    {
                        $href = $objFormPage->getAbsoluteUrl(Config::get('useAutoItem') ? '/%s' : '/items/%s');
                        $arrRow['editItemHref'] = sprintf($href, $arrRow['alias']);
                    }
                }
                // Get the deleteItemHref URL
                if (isset($objPage->id))
                {
                    $objPageDelete = PageModel::findWithDetails($objPage->id);
                    if ($objPageDelete !== null)
                    {
                        $href = $objPageDelete->getAbsoluteUrl(Config::get('useAutoItem') ? '/%s' : '/items/%s') . '?action=delete';
                        $arrRow['deleteItemHref'] = sprintf($href, $arrRow['alias']);
                    }
                }
                // Get the readerJumpTo
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

            // Get the createNewHref URL
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

        // Handle Messages
        $session = System::getContainer()->get('session');
        // Get flash bag messages and merge them
        if ($session->isStarted() && $this->hasFlashMessage($this->strFlashType))
        {
            $this->arrMessages = array_merge($this->arrMessages, $this->getFlashMessage($this->strFlashType));
            $this->unsetFlashMessage($this->strFlashType);
        }

        // Add messages to template
        if (!empty($this->arrMessages))
        {
            $this->Template->hasMessages = true;
            $this->Template->messages = $this->arrMessages;
        }

    }


    /**
     * @return mixed
     */
    protected function getMemberModel()
    {
        if (FE_USER_LOGGED_IN)
        {
            if (($objMember = FrontendUser::getInstance()) !== null)
            {
                return MemberModel::findByPk($objMember->id);
            }
        }
        return null;
    }


    /**
     * @param $key
     * @param string $message
     */
    protected function setFlashMessage($key, $message = '')
    {
        if (!isset($_SESSION[$key]))
        {
            $_SESSION[$key] = array();

        }
        $_SESSION[$key][] = $message;
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function getFlashMessage($key)
    {
        if (!isset($_SESSION[$key]))
        {
            $_SESSION[$key] = array();

        }
        return $_SESSION[$key];
    }

    /**
     * @param $key
     */
    protected function unsetFlashMessage($key)
    {
        if (isset($_SESSION[$key]))
        {
            $_SESSION[$key] = null;
            unset($_SESSION[$key]);
        }
    }

    /**
     * @param $key
     * @return bool
     */
    protected function hasFlashMessage($key)
    {
        if (isset($_SESSION[$key]) && !empty($_SESSION[$key]))
        {
            return true;
        }
        return false;
    }
}

//class_alias(ModuleAngebotDashboard::class, 'ModuleAngebotDashboard');
