<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

declare(strict_types=1);

use Mollie\Logger\Logger;
use Mollie\Logger\LoggerInterface;
use Mollie\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMollieSettingsController extends ModuleAdminController
{
    const FILE_NAME = 'AdminMollieSettingsController';

    /** @var Mollie */
    public $module;

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    private function initCloudSyncAndPsAccounts(): void
    {
        $mboInstaller = new Prestashop\ModuleLibMboInstaller\DependencyBuilder($this->module);

        if (!$mboInstaller->areDependenciesMet()) {
            $dependencies = $mboInstaller->handleDependencies();
            $this->context->smarty->assign('dependencies', $dependencies);

            $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/dependency_builder.tpl');

            return;
        }

        $this->context->smarty->assign('module_dir', $this->module->getPathUri());
        $moduleManager = PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder::getInstance()->build();

        /** @var Logger $logger * */
        $logger = $this->module->getService(LoggerInterface::class);

        try {
            $accountsFacade = $this->module->getService('Mollie.PsAccountsFacade');
            $accountsService = $accountsFacade->getPsAccountsService();
        } catch (PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException $e) {
            $logger->error(sprintf('%s - Failed to initiate ps_accounts', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            try {
                $accountsInstaller = $this->module->getService('Mollie.PsAccountsInstaller');
                $accountsInstaller->install();
                $accountsFacade = $this->module->getService('Mollie.PsAccountsFacade');
                $accountsService = $accountsFacade->getPsAccountsService();
            } catch (Exception $e) {
                $this->context->controller->errors[] = $e->getMessage();

                $logger->error(sprintf('%s - Failed to install ps_accounts', self::FILE_NAME), [
                    'exceptions' => ExceptionUtility::getExceptions($e),
                ]);

                return;
            }
        }

        try {
            Media::addJsDef([
                'contextPsAccounts' => $accountsFacade->getPsAccountsPresenter()
                    ->present($this->module->name),
            ]);

            // Retrieve Account CDN
            $this->context->smarty->assign('urlAccountsCdn', $accountsService->getAccountsCdn());
        } catch (Exception $e) {
            $this->context->controller->errors[] = $e->getMessage();

            $logger->error(sprintf('%s - Failed to load ps accounts CDN', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);
        }

        if ($moduleManager->isInstalled('ps_eventbus')) {
            $eventbusModule = \Module::getInstanceByName('ps_eventbus');
            if ($eventbusModule && version_compare($eventbusModule->version, '1.9.0', '>=')) {
                /** @phpstan-ignore-next-line PHPStan does not recognize the event bus module, so it doesn't know it has getService function */
                $eventbusPresenterService = $eventbusModule->getService('PrestaShop\Module\PsEventbus\Service\PresenterService');

                $this->context->smarty->assign('urlCloudsync', 'https://assets.prestashop3.com/ext/cloudsync-merchant-sync-consent/latest/cloudsync-cdc.js');
                $this->addJs($this->module->getPathUri() . '/views/js/admin/cloudsync.js');
                Media::addJsDef([
                    'contextPsEventbus' => $eventbusPresenterService->expose($this->module, ['info', 'modules', 'themes']),
                ]);
            }
        }

        $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/cloudsync.tpl');

        return;
    }

    public function postProcess()
    {
        /** @var \Mollie\Service\Content\TemplateParserInterface $templateParser */
        $templateParser = $this->module->getService(\Mollie\Service\Content\TemplateParserInterface::class);

        /** @var Logger $logger * */
        $logger = $this->module->getService(LoggerInterface::class);

        $this->content = $templateParser->parseTemplate(
            $this->context->smarty,
            $this->module->getService(\Mollie\Builder\Content\LogoInfoBlock::class),
            $this->module->getLocalPath() . 'views/templates/admin/logo.tpl'
        );

        try {
            $this->initCloudSyncAndPsAccounts();
        } catch (Exception $e) {
            $logger->error('Failed to initiate cloud sync and ps accounts', [
                'context' => [],
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);
        }

        /** @var \Mollie\Repository\ModuleRepository $moduleRepository */
        $moduleRepository = $this->module->getService(\Mollie\Repository\ModuleRepository::class);
        $moduleDatabaseVersion = $moduleRepository->getModuleDatabaseVersion($this->module->name);
        $needsUpgrade = Tools::version_compare($this->module->version, $moduleDatabaseVersion, '>');
        if ($needsUpgrade) {
            $this->context->controller->errors[] = $this->module->l('Please upgrade Mollie module');

            return;
        }

        $isShopContext = Shop::getContext() === Shop::CONTEXT_SHOP;

        if (!$isShopContext) {
            $this->context->controller->errors[] = $this->module->l('Select the shop that you want to configure');

            return;
        }

        $isSubmitted = (bool) Tools::isSubmit("submit{$this->module->name}");

        /* @phpstan-ignore-next-line */
        if (false === Configuration::get(Mollie\Config\Config::MOLLIE_STATUS_AWAITING) && !$isSubmitted) {
            $this->context->controller->errors[] = $this->module->l('Select an order status for \"Status for Awaiting payments\" in the \"Advanced settings\" tab');
        }

        $errors = [];

        if (Tools::isSubmit("submit{$this->module->name}")) {
            /** @var \Mollie\Service\SettingsSaveService $saveSettingsService */
            $saveSettingsService = $this->module->getService(\Mollie\Service\SettingsSaveService::class);
            $resultMessages = $saveSettingsService->saveSettings($errors);
            if (!empty($errors)) {
                $this->context->controller->errors = $resultMessages;
            } else {
                $this->context->controller->confirmations = $resultMessages;
            }
        }

        Media::addJsDef([
            'description_message' => addslashes($this->module->l('Enter a description')),
            'min_amount_message' => addslashes($this->module->l('You have entered incorrect min amount')),
            'max_amount_message' => addslashes($this->module->l('You have entered incorrect max amount')),

            'payment_api' => addslashes(Mollie\Config\Config::MOLLIE_PAYMENTS_API),
            'ajaxUrl' => addslashes($this->context->link->getAdminLink('AdminMollieAjax')),
        ]);

        /* Custom logo JS vars*/
        Media::addJsDef([
            'image_size_message' => addslashes($this->module->l('Upload an image %s%x%s1%')),
            'not_valid_file_message' => addslashes($this->module->l('Invalid file: %s%')),
        ]);

        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/method_countries.js');
        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/validation.js');
        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/admin/settings.js');
        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/admin/custom_logo.js');
        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/admin/upgrade_notice.js');
        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/admin/api_key_test.js');
        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/admin/init_mollie_account.js');
        $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/mollie.css');
        $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/admin/logo_input.css');

        /** @var \Mollie\Builder\Content\UpdateMessageInfoBlock $updateMessageInfoBlock */
        $updateMessageInfoBlock = $this->module->getService(\Mollie\Builder\Content\UpdateMessageInfoBlock::class);
        $updateMessageInfoBlockData = $updateMessageInfoBlock->setAddons(false);

        $html = $templateParser->parseTemplate(
            $this->context->smarty,
            $updateMessageInfoBlockData,
            $this->module->getLocalPath() . 'views/templates/admin/updateMessage.tpl'
        );

        /** @var \Mollie\Builder\Content\BaseInfoBlock $baseInfoBlock */
        $baseInfoBlock = $this->module->getService(\Mollie\Builder\Content\BaseInfoBlock::class);
        $this->context->smarty->assign($baseInfoBlock->buildParams());

        /** @var \Mollie\Builder\FormBuilder $settingsFormBuilder */
        $settingsFormBuilder = $this->module->getService(\Mollie\Builder\FormBuilder::class);

        try {
            $html .= $settingsFormBuilder->buildSettingsForm();
        } catch (PrestaShopDatabaseException $e) {
            $errorHandler = \Mollie\Handler\ErrorHandler\ErrorHandler::getInstance();
            $errorHandler->handle($e, $e->getCode(), false);
            $this->context->controller->errors[] = $this->module->l('The database tables are missing. Reset the module.');
        }

        $this->content .= $html;
    }
}
