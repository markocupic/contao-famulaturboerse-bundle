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
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Haste\Form\Form;
use Contao\FamulaturAngebotModel;
use Markocupic\Famulatur\Classes\FamulaturHelper;
use Patchwork\Utf8;
use Markocupic\Famulatur\Classes\FamulaturModule;
use Contao\BackendTemplate;
use Contao\Database;

/**
 * Class ModuleAngebotForm
 * @package Markocupic\Famulatur\Modules
 */
class ModuleAngebotForm extends FamulaturModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_angebotForm';

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

        // Get Angebot Model from parent class
        $this->getFamulaturAngebotModel();

        // Allow module to logged in users only
        if ($this->getMemberModel() === null)
        {
            $this->allowForm = false;
            $this->arrMessages[] = $GLOBALS['TL_LANG']['MISC']['allowFamulaturAngebotFormToLoggedInUsersOnly'];
        }

        // Do only allow users to edit their own items
        if ($this->objMember !== null)
        {
            if (($objAngebotModel = $this->objFamulaturAngebot) !== null)
            {
                if ($objAngebotModel->fd_member !== $this->objMember->id)
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

        // Call addMessagesTorTemplate form parent class
        $this->addMessagesToTemplate();
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
        if ($this->objFamulaturAngebot !== null)
        {
            $objForm->bindModel($this->objFamulaturAngebot);
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
                if ($this->objFamulaturAngebot === null)
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
                        // System log
                        System::log(sprintf('A new entry "tl_famulatur_angebot.id=%s" has been created.', $insertId), __METHOD__, TL_GENERAL);

                        if (($objFamulaturAngebot = FamulaturAngebotModel::findByPk($insertId)) !== null)
                        {
                            $this->objFamulaturAngebot = $objFamulaturAngebot;
                        }

                        foreach (static::$allowedFields as $strField)
                        {
                            if ($objForm->hasFormField($strField))
                            {
                                $this->objFamulaturAngebot->{$strField} = $objForm->getWidget($strField)->value;
                            }
                        }

                        // Call hooks
                        foreach ($GLOBALS['TL_HOOKS']['onInsertFamulaturAngebot'] as $callback)
                        {
                            System::importStatic($callback[0])->{$callback[1]}($this->objFamulaturAngebot, $this->objMember, $objForm, $this);
                        }
                    }
                }

                // Create new version
                $objVersions = new Versions('tl_famulatur_angebot', $this->objFamulaturAngebot->id);
                $objVersions->initialize();

                if ($this->objFamulaturAngebot->alias == '')
                {
                    $this->objFamulaturAngebot->alias = FamulaturHelper::generateAlias('', $this->objFamulaturAngebot->id);
                }

                $this->objFamulaturAngebot->tstamp = time();
                $this->objFamulaturAngebot->ip = Environment::get('ip');

                $this->objFamulaturAngebot->fd_member_group = $this->objMember->groups;

                // Save model
                $this->objFamulaturAngebot->save();

                // System log
                System::log(sprintf('UPDATE tl_famulatur_angebot where id=%s.', $this->objFamulaturAngebot->id), __METHOD__, TL_GENERAL);

                // Update lat and lng !!! Place this call after $this->objFamulaturAngebot->save()
                FamulaturHelper::updateLatAndLng($this->objFamulaturAngebot->id);

                // Call hooks
                foreach ($GLOBALS['TL_HOOKS']['onUpdateFamulaturAngebot'] as $callback)
                {
                    System::importStatic($callback[0])->{$callback[1]}($this->objFamulaturAngebot, $this->objMember, $objForm, $this);
                }

                // Create version
                $objVersions->create();

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
}

class_alias(ModuleAngebotForm::class, 'ModuleAngebotForm');
