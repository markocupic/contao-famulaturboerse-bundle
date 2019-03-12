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
use Contao\Module;
use Contao\BackendTemplate;
use Contao\Database;

/**
 * Class ModuleAngebotForm
 * @package Markocupic\Famulatur\Modules
 */
class ModuleAngebotForm extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_angebotForm';

    /**
     * Flash type
     * @var string
     */
    protected $strFlashType = 'contao.FE.famulatur';

    /**
     * @var
     */
    protected $angebotModel;

    /**
     * @var
     */
    protected $objMember;

    /**
     * @var bool
     */
    protected $allowForm = true;

    /**
     * @var
     */
    protected $form;

    /**
     * @var
     */
    protected static $allowedFields;

    /**
     * @var array
     */
    protected $arrMessages = array();

    /**
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['angebotForm'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        static::$allowedFields = StringUtil::deserialize($this->formFields, true);

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

        // Allow module to logged in users only
        if (($objMember = $this->getMemberModel()) !== null)
        {
            $this->objMember = $objMember;
        }
        else
        {
            $this->allowForm = false;
            $this->arrMessages[] = $GLOBALS['TL_LANG']['MISC']['allowFamulaturAngebotFormToLoggedInUsersOnly'];
        }

        if (($objMember = $this->getMemberModel()) !== null)
        {
            if (($objAngebotModel = $this->getAngebotModel()) !== null)
            {
                if ($objAngebotModel->fd_member !== $objMember->id)
                {
                    $this->allowForm = false;
                    $this->arrMessages[] = $GLOBALS['TL_LANG']['MISC']['notAllowedUsingFamulaturAngebotForm'];
                }
            }
        }

        return parent::generate();
    }

    /**
     * @throws \Exception
     */
    protected function compile()
    {
        if ($this->allowForm)
        {
            // Handle the form
            $this->generateAngebotForm();
            $this->Template->form = $this->form;
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
     * @throws \Exception
     */
    protected function generateAngebotForm()
    {
        Controller::loadLanguageFile('tl_famulatur_angebot');

        // Create the form
        $objForm = $this->createForm('form-famulatur-angebot-form');

        // Add hidden fields REQUEST_TOKEN & FORM_SUBMIT
        $objForm->addContaoHiddenFields();
        $objForm->addSubmitFormField('submit', 'Formular senden');
        $objForm->addCaptchaFormField('captcha');

        // Bind model
        if ($this->getAngebotModel() !== null)
        {
            $objForm->bindModel($this->getAngebotModel());
        }

        // Get model
        $objForm->addFieldsFromDca('tl_famulatur_angebot', function (&$strField, &$arrDca) {
            if ($strField === 'fd_member' || $strField === 'alias' || $strField === 'id')
            {
                return false;
            }
            if (!in_array($strField, static::$allowedFields))
            {
                return false;
            }
            if (!isset($arrDca['inputType']))
            {
                return false;
            }
            if ($arrDca['inputType'] === 'checkboxWizard')
            {
                $arrDca['inputType'] = 'checkbox';
            }

            $arrFields[] = $strField;

            // You must return true, otherwise the field will be skipped.
            return true;
        });

        // Helper to build the form with all the fields
        //$this->generateFormCode($objForm);

        // Validate and save
        if ($objForm->validate())
        {
            $blnHasError = false;

            if (!$blnHasError)
            {
                if ($this->getAngebotModel() === null)
                {
                    $set = array(
                        'date'      => time(),
                        'fd_member' => $this->objMember->id,
                        'published' => $this->autoPublishOnInsert,
                    );

                    $objInsertStmt = Database::getInstance()->prepare('INSERT INTO tl_famulatur_angebot %s')->set($set)->execute();
                    if ($objInsertStmt->affectedRows)
                    {
                        $insertId = $objInsertStmt->insertId;

                        $this->setAngebotModel(FamulaturAngebotModel::findByPk($insertId));

                        foreach (static::$allowedFields as $strField)
                        {
                            if ($objForm->hasFormField($strField))
                            {
                                $this->angebotModel->{$strField} = $objForm->getWidget($strField)->value;
                            }
                        }

                        // Call hooks
                        foreach ($GLOBALS['TL_HOOKS']['onInsertFamulaturAngebot'] as $callback)
                        {
                            System::importStatic($callback[0])->{$callback[1]}($this->angebotModel, $this->objMember, $objForm, $this);
                        }
                    }
                }

                if ($this->angebotModel->alias == '')
                {
                    $this->angebotModel->alias = FamulaturHelper::generateAlias('', $this->angebotModel->id);
                }

                $this->angebotModel->tstamp = time();
                $this->angebotModel->ip = Environment::get('ip');

                $this->angebotModel->fd_member_group = $this->objMember->groups;

                // Save model
                $this->angebotModel->save();

                // Call hooks
                foreach ($GLOBALS['TL_HOOKS']['onUpdateFamulaturAngebot'] as $callback)
                {
                    System::importStatic($callback[0])->{$callback[1]}($this->angebotModel, $this->objMember, $objForm, $this);
                }

                // Set Message
                $errMsg = $GLOBALS['TL_LANG']['MISC']['famulaturFormSent'];
                $this->setFlashMessage($this->strFlashType, $errMsg);

                // Check whether there is a jumpTo page or reload
                if (($objPage = PageModel::findByPk($this->jumpTo)) instanceof PageModel)
                {
                    $this->jumpToOrReload($objPage->row());
                }
                $this->reload();
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
        $objForm = new Form($strId, 'POST', function ($objHaste) {
            return Input::post('FORM_SUBMIT') === $objHaste->getFormId();
        });

        $url = Environment::get('uri');
        $objForm->setFormActionFromUri($url);

        // Add hidden fields REQUEST_TOKEN & FORM_SUBMIT
        $objForm->addContaoHiddenFields();

        return $objForm;
    }

    /**
     * @return mixed
     */
    protected function getAngebotModel()
    {
        return $this->angebotModel;
    }

    /**
     * @param FamulaturAngebotModel $objModel
     */
    protected function setAngebotModel(FamulaturAngebotModel $objModel)
    {
        $this->angebotModel = $objModel;
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
     * @param $objForm
     */
    private function generateFormCode($objForm)
    {
        $arrFields = $objForm->getFormFields();
        $strHtml = '';
        foreach ($arrFields as $fieldname => $value)
        {
            $strHtml .= sprintf("
             %s = '%s'; ?>
      <?php if (%shasFormField(%s)): ?>
      <div data-input=\"%s\">
        <div class=\"%s\">
          <?= %sgetWidget(%s)->parse(); ?>
        </div>
      </div>
      <?php endif; ?>
      ", '<?php $field', $fieldname, '$this->form->', '$field', '<?= $field ?>', 'widget-<?= $field ?>', '$this->form->', '$field');
        }
        die($strHtml);
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

class_alias(ModuleAngebotForm::class, 'ModuleAngebotForm');
