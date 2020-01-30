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

use Application\Controller\CoreController;
use Application\Model\CoreEntityModel;
use OnePlace\Translation\Model\Translation;
use OnePlace\Translation\Model\TranslationTable;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;

class TranslationController extends CoreController {
    /**
     * Translation Table Object
     *
     * @since 1.0.0
     */
    private $oTableGateway;

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
        # Set Layout based on users theme
        $this->setThemeBasedLayout('translation');

        # Check license
        if(!$this->checkLicense('translation')) {
            $this->flashMessenger()->addErrorMessage('You have no active license for translation');
            $this->redirect()->toRoute('home');
        }

        # Add Buttons for breadcrumb
        $this->setViewButtons('translation-index');

        # Set Table Rows for Index
        $this->setIndexColumns('translation-index');

        # Get Paginator
        $oPaginator = $this->oTableGateway->fetchAll(true);
        $iPage = (int) $this->params()->fromQuery('page', 1);
        $iPage = ($iPage < 1) ? 1 : $iPage;
        $oPaginator->setCurrentPageNumber($iPage);
        $oPaginator->setItemCountPerPage(3);

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('translation-index',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        return new ViewModel([
            'sTableName'=>'translation-index',
            'aItems'=>$oPaginator,
        ]);
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
        $oTranslation = new Translation($this->oDbAdapter);
        $oTranslation->exchangeArray($aFormData);
        $iTranslationID = $this->oTableGateway->saveSingle($oTranslation);
        $oTranslation = $this->oTableGateway->getSingle($iTranslationID);

        # Save Multiselect
        $this->updateMultiSelectFields($_REQUEST,$oTranslation,'translation-single');

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

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('translation-view',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        return new ViewModel([
            'sFormName'=>$this->sSingleForm,
            'oTranslation'=>$oTranslation,
        ]);
    }
}
