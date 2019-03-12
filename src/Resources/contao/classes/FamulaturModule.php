<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */

namespace Markocupic\Famulatur\Classes;

use Contao\FrontendUser;
use Contao\MemberModel;
use Contao\Module;
use Contao\Input;
use Contao\FamulaturAngebotModel;
use Contao\System;
use Contao\Config;
use Contao\Database;

/**
 * Class FamulaturModule
 * @package Markocupic\Famulatur\Classes
 */
abstract class FamulaturModule extends Module
{

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
     * @var
     */
    protected $objFamulaturAngebot;

    /**
     * @var array
     */
    protected $arrMessages = array();

    /**
     * @return array
     */
    protected function getValidZipcodesFromOpenGeo()
    {
        return FamulaturHelper::getValidZipcodesFromOpenGeo();
    }

    /**
     * @return MemberModel|null
     */
    protected function getMemberModel()
    {
        if (FE_USER_LOGGED_IN)
        {
            if (($objMember = FrontendUser::getInstance()) !== null)
            {
                $objMember = MemberModel::findByPk($objMember->id);
                if ($objMember !== null)
                {
                    $this->objMember = $objMember;
                    return $this->objMember;
                }
            }
        }
        return null;
    }

    /**
     * @return FamulaturAngebotModel|null
     */
    protected function getFamulaturAngebotModel()
    {
        if ($this->objFamulaturAngebot !== null)
        {
            return $this->objFamulaturAngebot;
        }

        if (Input::get('items') !== '')
        {
            $objAngebot = FamulaturAngebotModel::findByIdOrAlias(Input::get('items'));
            if ($objAngebot !== null)
            {
                $this->objFamulaturAngebot = $objAngebot;
                return $objAngebot;
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

    /**
     *
     */
    protected function addMessagesToTemplate()
    {
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
}

class_alias(FamulaturModule::class, 'FamulaturModule');
