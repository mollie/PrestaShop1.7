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

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Shop;
use Mollie\Config\Config;
use Mollie\Logger\LogFormatter;
use Mollie\Logger\Logger;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\MolLogRepositoryInterface;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\VersionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}
class AdminMollieLogsController extends ModuleAdminController
{
    /** @var Mollie */
    public $module;

    const FILE_NAME = 'AdminMollieLogsController';

    const LOG_INFORMATION_TYPE_REQUEST = 'request';
    const LOG_INFORMATION_TYPE_RESPONSE = 'response';
    const LOG_INFORMATION_TYPE_CONTEXT = 'context';

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'log';
        $this->className = 'PrestaShopLogger';
        $this->lang = false;
        $this->noLink = true;
        $this->allow_export = true;

        parent::__construct();

        $this->toolbar_btn = [];
        $this->fields_list = [
            'id_log' => [
                'title' => $this->module->l('ID', self::FILE_NAME),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ],
            'severity' => [
                'title' => $this->module->l('Severity (1-4)', self::FILE_NAME),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'callback' => 'printSeverityLevel',
            ],
            'message' => [
                'title' => $this->module->l('Message', self::FILE_NAME),
            ],
            'request' => [
                'title' => $this->module->l('Request', self::FILE_NAME),
                'align' => 'text-center',
                'callback' => 'printRequestButton',
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true,
            ],
            'response' => [
                'title' => $this->module->l('Response', self::FILE_NAME),
                'align' => 'text-center',
                'callback' => 'printResponseButton',
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true,
            ],
            'context' => [
                'title' => $this->module->l('Context', self::FILE_NAME),
                'align' => 'text-center',
                'callback' => 'printContextButton',
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true,
            ],
            'date_add' => [
                'title' => $this->module->l('Date', self::FILE_NAME),
                'align' => 'right',
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ],
        ];

        $this->_orderBy = 'id_log';
        $this->_orderWay = 'desc';

        $this->_select .= '
            REPLACE(a.`message`, "' . LogFormatter::MOLLIE_LOG_PREFIX . '", "") as message,
            ml.request, ml.response, ml.context
        ';

        $shopIdCheck = '';

        if (VersionUtility::isPsVersionGreaterOrEqualTo('1.7.8.0')) {
            $shopIdCheck = ' AND ml.id_shop = a.id_shop';
        }

        $this->_join .= ' JOIN ' . _DB_PREFIX_ . 'mol_logs ml ON (ml.id_log = a.id_log' . $shopIdCheck . ' AND a.object_type = "' . pSQL(Logger::LOG_OBJECT_TYPE) . '")';
        $this->_use_found_rows = false;
        $this->list_no_link = true;
    }

    /**
     * @return false|string
     *
     * @throws SmartyException
     */
    public function displaySeverityInformation()
    {
        return $this->context->smarty->fetch(
            "{$this->module->getLocalPath()}views/templates/admin/logs/severity_levels.tpl"
        );
    }

    /**
     * @throws SmartyException
     */
    public function initContent(): void
    {
        // NOTE: we cannot add new logs here.
        if (isset($this->toolbar_btn['new'])) {
            unset($this->toolbar_btn['new']);
        }

        $this->content .= $this->displaySeverityInformation();

        parent::initContent();
    }

    public function setMedia($isNewTheme = false): void
    {
        parent::setMedia($isNewTheme);

        Media::addJsDef([
            'mollie' => [
                'logsUrl' => Context::getContext()->link->getAdminLink(Mollie::ADMIN_MOLLIE_LOGS_CONTROLLER),
            ],
        ]);

        $this->addJS($this->module->getPathUri() . 'views/js/admin/logs/log.js', false);
        $this->addCss($this->module->getPathUri() . 'views/css/admin/logs/log.css');
    }

    /**
     * @param string $request
     * @param array $data
     *
     * @return false|string
     *
     * @throws SmartyException
     */
    public function printRequestButton(string $request, array $data)
    {
        return $this->getDisplayButton($data['id_log'], $request, self::LOG_INFORMATION_TYPE_REQUEST);
    }

    /**
     * @param string $response
     * @param array $data
     *
     * @return false|string
     *
     * @throws SmartyException
     */
    public function printResponseButton(string $response, array $data)
    {
        return $this->getDisplayButton($data['id_log'], $response, self::LOG_INFORMATION_TYPE_RESPONSE);
    }

    /**
     * @param string $context
     * @param array $data
     *
     * @return false|string
     *
     * @throws SmartyException
     */
    public function printContextButton(string $context, array $data)
    {
        return $this->getDisplayButton($data['id_log'], $context, self::LOG_INFORMATION_TYPE_CONTEXT);
    }

    /**
     * @param int $level
     *
     * @return false|string
     *
     * @throws SmartyException
     */
    public function printSeverityLevel(int $level)
    {
        $this->context->smarty->assign([
            'log_severity_level' => $level,
            'log_severity_level_informative' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE :
                Config::LOG_SEVERITY_LEVEL_INFORMATIVE,
            'log_severity_level_warning' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_WARNING') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_WARNING :
                Config::LOG_SEVERITY_LEVEL_WARNING,
            'log_severity_level_error' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR :
                Config::LOG_SEVERITY_LEVEL_ERROR,
            'log_severity_level_major' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_MAJOR') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_MAJOR :
                Config::LOG_SEVERITY_LEVEL_MAJOR,
        ]);

        return $this->context->smarty->fetch(
            "{$this->module->getLocalPath()}views/templates/admin/logs/severity_level_column.tpl"
        );
    }

    /**
     * @param int $logId
     * @param string $data
     * @param string $logInformationType
     *
     * @return false|string
     *
     * @throws SmartyException
     */
    public function getDisplayButton(int $logId, string $data, string $logInformationType)
    {
        $unserializedData = json_decode($data);

        if (empty($unserializedData)) {
            return '--';
        }

        $this->context->smarty->assign([
            'log_id' => $logId,
            'log_information_type' => $logInformationType,
        ]);

        return $this->context->smarty->fetch(
            "{$this->module->getLocalPath()}views/templates/admin/logs/log_modal.tpl"
        );
    }

    public function displayAjaxGetLog()
    {
        /** @var \Mollie\Adapter\ToolsAdapter $tools */
        $tools = $this->module->getService(\Mollie\Adapter\ToolsAdapter::class);

        /** @var MolLogRepositoryInterface $logRepository */
        $logRepository = $this->module->getService(MolLogRepositoryInterface::class);

        /** @var Shop $shopContext */
        $shopContext = $this->module->getService(Shop::class);

        $logId = $tools->getValueAsInt('log_id');

        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);
        $log = null;

        try {
            /** @var \MolLog|null $log */
            $log = $logRepository->findOneBy([
                'id_log' => $logId,
                'id_shop' => $shopContext->getShop()->id,
            ]);
        } catch (\Exception $exception) {
            $logger->error(sprintf('%s - Failed to find log', self::FILE_NAME), [
                'context' => [
                    'id_log' => $logId,
                    'id_shop' => $shopContext->getShop()->id,
                ],
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            $this->ajaxResponse(json_encode([
                'error' => true,
                'message' => $this->module->l('Failed to find log.', self::FILE_NAME),
            ]));
        }

        if (!$log) {
            $logger->error(sprintf('%s - No log information found.', self::FILE_NAME), [
                'context' => [
                    'id_log' => $logId,
                    'id_shop' => $shopContext->getShop()->id,
                ],
            ]);

            $this->ajaxResponse(json_encode([
                'error' => true,
                'message' => $this->module->l('No log information found.', self::FILE_NAME),
            ]));
        }

        $this->ajaxResponse(json_encode([
            'error' => false,
            'log' => [
                self::LOG_INFORMATION_TYPE_REQUEST => $log->request,
                self::LOG_INFORMATION_TYPE_RESPONSE => $log->response,
                self::LOG_INFORMATION_TYPE_CONTEXT => $log->context,
            ],
        ]));
    }

    /**
     * @param string|false|null $value
     * @param null $controller
     * @param null $method
     *
     * @return void
     *
     * @throws \PrestaShopException
     */
    protected function ajaxResponse($value = null, $controller = null, $method = null): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        try {
            $this->ajaxRender($value, $controller, $method);
        } catch (\Exception $exception) {
            $logger->error(sprintf('%s - Could not return ajax response', self::FILE_NAME), [
                'context' => [
                    'response' => json_encode($value ?: []),
                ],
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);
        }

        exit;
    }

    public function processExport($textDelimiter = '"')
    {
        // clean buffer
        if (ob_get_level() && ob_get_length() > 0) {
            ob_clean();
        }

        header('Content-type: text/csv');
        header('Content-Type: application/force-download; charset=UTF-8');
        header('Cache-Control: no-store, no-cache');
        header('Content-disposition: attachment; filename="' . $this->table . '_' . date('Y-m-d_His') . '.csv"');

        $fd = fopen('php://output', 'wb');

        /** @var ConfigurationAdapter $configuration */
        $configuration = $this->module->getService(ConfigurationAdapter::class);

        /** @var Mollie\Adapter\Context $context */
        $context = $this->module->getService(Mollie\Adapter\Context::class);

        $storeInfo = [
            'PrestaShop Version' => _PS_VERSION_,
            'PHP Version' => phpversion(),
            'Module Version' => $this->module->version,
            'MySQL Version' => \Db::getInstance()->getVersion(),
            'Shop URL' => $context->getShopDomain(),
            'Shop Name' => $context->getShopName(),
        ];

        $moduleConfigurations = [
            'Environment' => $configuration->get(Config::MOLLIE_ENVIRONMENT) ? 'Production' : 'Sandbox',
            'Components' => $configuration->get(Config::MOLLIE_IFRAME),
            'OCP' => $configuration->get(Config::MOLLIE_SINGLE_CLICK_PAYMENT),
            'Locale Webshop' => $configuration->get(Config::MOLLIE_PAYMENTSCREEN_LOCALE),
            'Subscriptions enabled' => $configuration->get(Config::MOLLIE_SUBSCRIPTION_ENABLED),
        ];

        $psSettings = [
            'Default country' => $configuration->get('PS_COUNTRY_DEFAULT'),
            'Default currency' => $configuration->get('PS_CURRENCY_DEFAULT'),
            'Default language' => $configuration->get('PS_LANG_DEFAULT'),
            'Round mode' => $configuration->get('PS_PRICE_ROUND_MODE'),
            'Round type' => $configuration->get('PS_ROUND_TYPE'),
            'Current theme name' => $context->getShopThemeName(),
            'PHP memory limit' => ini_get('memory_limit'),
        ];

        $moduleConfigurationsInfo = "**Module configurations:**\n";
        foreach ($moduleConfigurations as $key => $value) {
            $moduleConfigurationsInfo .= "- $key: $value\n";
        }

        $psSettingsInfo = "**Prestashop settings:**\n";
        foreach ($psSettings as $key => $value) {
            $psSettingsInfo .= "- $key: $value\n";
        }

        fputcsv($fd, array_keys($storeInfo), ';', $textDelimiter);
        fputcsv($fd, $storeInfo, ';', $textDelimiter);
        fputcsv($fd, [], ';', $textDelimiter);

        fputcsv($fd, [$moduleConfigurationsInfo], ';', $textDelimiter);
        fputcsv($fd, [$psSettingsInfo], ';', $textDelimiter);

        $query = new \DbQuery();

        $query
            ->select('ml.id_log, l.severity, l.message, ml.request, ml.response, ml.context, ml.date_add')
            ->from('mol_logs', 'ml')
            ->leftJoin('log', 'l', 'ml.id_log = l.id_log')
            ->orderBy('ml.id_log DESC')
            ->limit(1000);

        $result = \Db::getInstance()->executeS($query);

        $firstRow = $result[0];
        $headers = [];

        foreach ($firstRow as $key => $value) {
            $headers[] = strtoupper($key);
        }

        $fd = fopen('php://output', 'wb');

        fputcsv($fd, $headers, ';', $textDelimiter);

        $content = !empty($result) ? $result : [];

        foreach ($content as $row) {
            $rowValues = [];
            foreach ($row as $key => $value) {
                $rowValues[] = $value;
            }

            fputcsv($fd, $rowValues, ';', $textDelimiter);
        }

        @fclose($fd);
        exit;
    }
}
