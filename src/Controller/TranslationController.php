<?php
/**
 * TranslationController.php - Main Controller
 *
 * Main Controller Translation Module
 *
 * @category Controller
 * @package Translation
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

namespace OnePlace\Translation\Controller;

use Application\Controller\CoreEntityController;
use Application\Model\CoreEntityModel;
use OnePlace\Translation\Model\Translation;
use OnePlace\Translation\Model\TranslationTable;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;
use Gettext\Translations;
use Gettext\Loader\PoLoader;
use Gettext\Generator\MoGenerator;

class TranslationController extends CoreEntityController {
    /**
     * Active languages
     *
     * @var array $aLanguages
     *@since 1.0.0
     */
    public static $aLanguages = [];

    /**
     * TranslationController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param TranslationTable $oTableGateway
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter,TranslationTable $oTableGateway,$oServiceManager) {
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'translation-single';
        TranslationController::$aLanguages = ['en_US','de_DE'];
        parent::__construct($oDbAdapter,$oTableGateway,$oServiceManager);

        if($oTableGateway) {
            # Attach TableGateway to Entity Models
            if(!isset(CoreEntityModel::$aEntityTables[$this->sSingleForm])) {
                CoreEntityModel::$aEntityTables[$this->sSingleForm] = $oTableGateway;
            }
        }
    }

    /**
     * Translation Index
     *
     * @since 1.0.0
     * @return ViewModel - View Object with Data from Controller
     */
    public function indexAction() {
        return $this->generateIndexView('translation');
    }

    /**
     * Translation Add Form
     *
     * @since 1.0.0
     * @return ViewModel - View Object with Data from Controller
     */
    public function addAction() {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('translation');

        # Add Links for Breadcrumb
        $this->layout()->aNavLinks = [
            (object)['label'=>'Translations','href'=>'/translation'],
            (object)['label'=>'Add Translation'],
        ];

        # Check license
        if(!$this->checkLicense('translation')) {
            $this->flashMessenger()->addErrorMessage('You have no active license for translation');
            $this->redirect()->toRoute('home');
        }

        # Get Request to decide wether to save or display form
        $oRequest = $this->getRequest();

        # Display Add Form
        if(!$oRequest->isPost()) {
            # Add Buttons for breadcrumb
            $this->setViewButtons('translation-single');

            # Load Tabs for View Form
            $this->setViewTabs($this->sSingleForm);

            # Load Fields for View Form
            $this->setFormFields($this->sSingleForm);

            # Log Performance in DB
            $aMeasureEnd = getrusage();
            $this->logPerfomance('translation-add',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

            return new ViewModel([
                'sFormName' => $this->sSingleForm,
            ]);
        }

        # Get and validate Form Data
        $aFormData = $this->parseFormData($_REQUEST);

        # Save Add Form
        $oTranslation = new Translation(CoreController::$oDbAdapter);
        $oTranslation->exchangeArray($aFormData);
        $iTranslationID = $this->oTableGateway->saveSingle($oTranslation);
        $oTranslation = $this->oTableGateway->getSingle($iTranslationID);

        # Save Multiselect
        $this->updateMultiSelectFields($_REQUEST,$oTranslation,'translation-single');

        # Re-generate language files
        foreach(TranslationController::$aLanguages as $sLang) {
            $this->generateLanguageFiles($sLang);
        }

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('translation-save',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        # Display Success Message and View New Translation
        $this->flashMessenger()->addSuccessMessage('Translation successfully created');
        return $this->redirect()->toRoute('translation',['action'=>'view','id'=>$iTranslationID]);
    }

    /**
     * Translation Edit Form
     *
     * @since 1.0.0
     * @return ViewModel - View Object with Data from Controller
     */
    public function editAction() {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('translation');

        # Add Links for Breadcrumb
        $this->layout()->aNavLinks = [
            (object)['label'=>'Translations','href'=>'/translation'],
            (object)['label'=>'Edit Translation'],
        ];

        # Check license
        if(!$this->checkLicense('translation')) {
            $this->flashMessenger()->addErrorMessage('You have no active license for translation');
            $this->redirect()->toRoute('home');
        }

        # Get Request to decide wether to save or display form
        $oRequest = $this->getRequest();

        # Display Edit Form
        if(!$oRequest->isPost()) {

            # Get Translation ID from URL
            $iTranslationID = $this->params()->fromRoute('id', 0);

            # Try to get Translation
            try {
                $oTranslation = $this->oTableGateway->getSingle($iTranslationID);
            } catch (\RuntimeException $e) {
                echo 'Translation Not found';
                return false;
            }

            # Attach Translation Entity to Layout
            $this->setViewEntity($oTranslation);

            # Add Buttons for breadcrumb
            $this->setViewButtons('translation-single');

            # Load Tabs for View Form
            $this->setViewTabs($this->sSingleForm);

            # Load Fields for View Form
            $this->setFormFields($this->sSingleForm);

            # Log Performance in DB
            $aMeasureEnd = getrusage();
            $this->logPerfomance('translation-edit',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

            return new ViewModel([
                'sFormName' => $this->sSingleForm,
                'oTranslation' => $oTranslation,
            ]);
        }

        $iTranslationID = $oRequest->getPost('Item_ID');
        $oTranslation = $this->oTableGateway->getSingle($iTranslationID);

        # Update Translation with Form Data
        $oTranslation = $this->attachFormData($_REQUEST,$oTranslation);

        # Save Translation
        $iTranslationID = $this->oTableGateway->saveSingle($oTranslation);

        $this->layout('layout/json');

        # Parse Form Data
        $aFormData = $this->parseFormData($_REQUEST);

        # Save Multiselect
        $this->updateMultiSelectFields($aFormData,$oTranslation,'translation-single');

        # Re-generate language files
        foreach(TranslationController::$aLanguages as $sLang) {
            $this->generateLanguageFiles($sLang);
        }

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('translation-save',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        # Display Success Message and View New User
        $this->flashMessenger()->addSuccessMessage('Translation successfully saved');
        return $this->redirect()->toRoute('translation',['action'=>'view','id'=>$iTranslationID]);
    }

    /**
     * Translation View Form
     *
     * @since 1.0.0
     * @return ViewModel - View Object with Data from Controller
     */
    public function viewAction() {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('translation');

        # Check license
        if(!$this->checkLicense('translation')) {
            $this->flashMessenger()->addErrorMessage('You have no active license for translation');
            $this->redirect()->toRoute('home');
        }

        # Get Translation ID from URL
        $iTranslationID = $this->params()->fromRoute('id', 0);

        # Try to get Translation
        try {
            $oTranslation = $this->oTableGateway->getSingle($iTranslationID);
        } catch (\RuntimeException $e) {
            echo 'Translation Not found';
            return false;
        }

        # Attach Translation Entity to Layout
        $this->setViewEntity($oTranslation);

        # Add Buttons for breadcrumb
        $this->setViewButtons('translation-view');

        # Load Tabs for View Form
        $this->setViewTabs($this->sSingleForm);

        # Load Fields for View Form
        $this->setFormFields($this->sSingleForm);

        # Add Links for Breadcrumb
        $this->layout()->aNavLinks = [
            (object)['label'=>'Translations','href'=>'/translation'],
            (object)['label'=>$oTranslation->getLabel()],
        ];

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('translation-view',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        return new ViewModel([
            'sFormName'=>$this->sSingleForm,
            'oTranslation'=>$oTranslation,
        ]);
    }

    /**
     * Generate PO and MO files for selected language
     *
     * @param string $sLang
     * @return bool false no viewfile
     * @since 1.0.0
     */
    public function generateLanguageFiles($sLang = 'en_US') {
        $this->layout('layout/json');

        //echo 'gen files for '.$sLang;

        $this->createpofile($sLang);

        //Load a PO file
        $poLoader = new PoLoader();

        if(!file_exists(__DIR__.'/../../language/'.$sLang.'.po')) {
            file_put_contents(__DIR__.'/../../language/'.$sLang.'.po',"msgid \"\"\nmsgstr \"\"\n");
        }

        $translations = $poLoader->loadFile(__DIR__.'/../../language/'.$sLang.'.po');

        //Save to MO file
        $moGenerator = new MoGenerator();

        $moGenerator->generateFile($translations, __DIR__.'/../../language/'.$sLang.'.mo');

        //Or return as a string
        //$content = $moGenerator->generateString($translations);
        //var_dump($content);
        //file_put_contents(__DIR__.'/../../language/'.$sLang.'.mo', $content);

        return false;
    }

    /**
     * Create PO File for Language
     *
     * @param $sLang language
     * @since 1.0.0
     */
    private function createpofile($sLang) {

        # begin po file
        $sFile = "msgid \"\"\nmsgstr \"\"\n";

        # languages are translation categories so we need category tag
        $oTag = CoreController::$aCoreTables['core-tag']->select(['tag_key'=>'category']);
        if(count($oTag)) {
            $oTag = $oTag->current();

            $oEntityTag = CoreController::$aCoreTables['core-entity-tag']->select(['tag_idfs'=>$oTag->Tag_ID,'entity_form_idfs'=>'translation-single','tag_value'=>$sLang]);
            if(count($oEntityTag) > 0) {
                # load language (entity tag)
                $oEntityTag = $oEntityTag->current();

                # add translations to file
                $aTranslations = $this->oTableGateway->fetchAll(false,['language_idfs'=>$oEntityTag->Entitytag_ID]);
                if(count($aTranslations) > 0) {
                    foreach($aTranslations as $oTrans) {
                        $sFile .= "\nmsgid \"".$oTrans->label."\"";
                        $sFile .= "\nmsgstr \"".$oTrans->translation."\"";
                        $sFile .= "\n";
                    }
                }

                $sFile .= "\n";

                # save po file
                file_put_contents(__DIR__.'/../../language/'.$sLang.'.po',$sFile);
            }
        }
    }
}
