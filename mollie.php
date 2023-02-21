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
use Mollie\Adapter\ProductAttributeAdapter;
use Mollie\Config\Config;
use Mollie\Config\Env;
use Mollie\Provider\ProfileIdProviderInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\ServiceProvider\LeagueServiceContainerProvider;
use Mollie\Subscription\Exception\ProductValidationException;
use Mollie\Subscription\Exception\SubscriptionProductValidationException;
use Mollie\Subscription\Install\AttributeInstaller;
use Mollie\Subscription\Install\DatabaseTableInstaller;
use Mollie\Subscription\Install\HookInstaller;
use Mollie\Subscription\Install\Installer;
use Mollie\Subscription\Logger\NullLogger;
use Mollie\Subscription\Repository\LanguageRepository as LanguageAdapter;
use Mollie\Subscription\Validator\CanProductBeAddedToCartValidator;
use Mollie\Utility\PsVersionUtility;

require_once __DIR__ . '/vendor/autoload.php';

class Mollie extends PaymentModule
{
    const DISABLE_CACHE = true;

    /** @var \Mollie\Api\MollieApiClient|null */
    private $api = null;

    /** @var string */
    public static $selectedApi;

    /** @var bool Indicates whether the Smarty cache has been cleared during updates */
    public static $cacheCleared;

    // The Addons version does not include the GitHub updater
    const ADDONS = false;

    const SUPPORTED_PHP_VERSION = '70080';

    const ADMIN_MOLLIE_PARENT_CONTROLLER = 'AdminMollieModuleParent';
    const ADMIN_MOLLIE_CONTROLLER = 'AdminMollieModule';
    const ADMIN_MOLLIE_AJAX_CONTROLLER = 'AdminMollieAjax';
    /** @var LeagueServiceContainerProvider */
    private $containerProvider;

    /**
     * Mollie constructor.
     */
    public function __construct()
    {
        $this->name = 'mollie';
        $this->tab = 'payments_gateways';
        $this->version = '5.4.0';
        $this->author = 'Mollie B.V.';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->module_key = 'a48b2f8918358bcbe6436414f48d8915';

        parent::__construct();

        $this->ps_versions_compliancy = ['min' => '1.7.6', 'max' => _PS_VERSION_];
        $this->displayName = $this->l('Mollie');
        $this->description = $this->l('Mollie Payments');

        $this->loadEnv();
        new \Mollie\Handler\ErrorHandler\ErrorHandler($this, new Env());
    }

    /**
     * Gets service that is defined by module container.
     *
     * @param string $serviceName
     * @returns mixed
     */
    public function getService(string $serviceName)
    {
        if ($this->containerProvider === null) {
            $this->containerProvider = new LeagueServiceContainerProvider();
        }

        return $this->containerProvider->getService($serviceName);
    }

    public function getApiClient()
    {
        if (!$this->api) {
            $this->setApiKey();
        }

        return $this->api;
    }

    private function loadEnv()
    {
        if (!class_exists('\Dotenv\Dotenv')) {
            return;
        }

        if (file_exists(_PS_MODULE_DIR_ . 'mollie/.env')) {
            $dotenv = \Dotenv\Dotenv::create(_PS_MODULE_DIR_ . 'mollie/', '.env');
            /* @phpstan-ignore-next-line */
            $dotenv->load();

            return;
        }
        if (file_exists(_PS_MODULE_DIR_ . 'mollie/.env.dist')) {
            $dotenv = \Dotenv\Dotenv::create(_PS_MODULE_DIR_ . 'mollie/', '.env.dist');
            /* @phpstan-ignore-next-line */
            $dotenv->load();
        }
    }

    /**
     * Installs the Mollie Payments Module.
     *
     * @return bool
     */
    public function install()
    {
        if (!$this->isPhpVersionCompliant()) {
            $this->_errors[] = $this->l('You\'re using an outdated PHP version. Upgrade your PHP version to use this module. The Mollie module supports versions PHP 7.0.8 and higher.');

            return false;
        }

        if (!parent::install()) {
            $this->_errors[] = $this->l('Unable to install module');

            return false;
        }

        $installer = new \Mollie\Install\Installer(
            $this,
            new \Mollie\Service\OrderStateImageService(),
            new \Mollie\Install\DatabaseTableInstaller(),
            new \Mollie\Tracker\Segment(
                new \Mollie\Adapter\Shop(),
                new \Mollie\Adapter\Language(),
                new \Mollie\Config\Env()
            ),
            new \Mollie\Adapter\ConfigurationAdapter()
        );
        if (!$installer->install()) {
            $this->_errors = array_merge($this->_errors, $installer->getErrors());

            return false;
        }

        $subscriptionInstaller = new Installer(
            new DatabaseTableInstaller(),
            new AttributeInstaller(new NullLogger(), new ConfigurationAdapter(), $this, new LanguageAdapter(), new ProductAttributeAdapter()),
            new HookInstaller($this)
        );

        if (!$subscriptionInstaller->install()) {
            $this->_errors = array_merge($this->_errors, $subscriptionInstaller->getErrors());
            parent::uninstall();

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        /** @var \Mollie\Install\Uninstall $uninstall */
        $uninstall = $this->getService(\Mollie\Install\Uninstall::class);
        if (!$uninstall->uninstall()) {
            $this->_errors[] = $uninstall->getErrors();

            return false;
        }

        return parent::uninstall();
    }

    public function enable($force_all = false)
    {
        if (!$this->isPhpVersionCompliant()) {
            $this->_errors[] = $this->l('You\'re using an outdated PHP version. Upgrade your PHP version to use this module. The Mollie module supports versions PHP 7.0.8 and higher.');

            return false;
        }

        return parent::enable($force_all);
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getContent()
    {
        if (Tools::getValue('ajax')) {
            header('Content-Type: application/json;charset=UTF-8');

            if (!method_exists($this, 'displayAjax' . Tools::ucfirst(Tools::getValue('action')))) {
                exit(json_encode([
                    'success' => false,
                ]));
            }
            exit(json_encode($this->{'displayAjax' . Tools::ucfirst(Tools::getValue('action'))}()));
        }

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminMollieSettings'));
    }

    /**
     * @param string $str
     *
     * @return string
     *
     * @deprecated
     */
    public function lang($str)
    {
        /** @var Mollie\Service\LanguageService $langService */
        $langService = $this->getService(Mollie\Service\LanguageService::class);
        $lang = $langService->getLang();
        if (array_key_exists($str, $lang)) {
            return $lang[$str];
        }

        return $str;
    }

    public function hookDisplayHeader(array $params)
    {
        if ($this->context->controller->php_self !== 'order') {
            return;
        }

        $apiClient = $this->getApiClient();
        if (!$apiClient) {
            return;
        }
        /** @var ProfileIdProviderInterface $profileIdProvider */
        $profileIdProvider = $this->getService(ProfileIdProviderInterface::class);

        Media::addJsDef([
            'profileId' => $profileIdProvider->getProfileId($apiClient),
            'isoCode' => $this->context->language->locale,
            'isTestMode' => \Mollie\Config\Config::isTestMode(),
        ]);
        $this->context->controller->registerJavascript(
            'mollie_iframe_js',
            'https://js.mollie.com/v1/mollie.js',
            ['server' => 'remote', 'position' => 'bottom', 'priority' => 150]
        );
        $this->context->controller->addJS("{$this->_path}views/js/front/mollie_iframe.js");
        $this->context->controller->addJS("{$this->_path}views/js/front/mollie_single_click.js");
        $this->context->controller->addJS("{$this->_path}views/js/front/bancontact/qr_code.js");
        $this->context->controller->addCSS($this->getPathUri() . 'views/css/front/bancontact_qr_code.css');

        Media::addJsDef([
            'ajaxUrl' => $this->context->link->getModuleLink('mollie', 'ajax'),
            'bancontactAjaxUrl' => $this->context->link->getModuleLink('mollie', 'bancontactAjax'),
        ]);
        $this->context->controller->addJS("{$this->_path}views/js/front/mollie_error_handle.js");
        $this->context->controller->addCSS("{$this->_path}views/css/mollie_iframe.css");
        if (Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) {
            $this->context->controller->addJS($this->getPathUri() . 'views/js/apple_payment.js');
        }
        $this->context->smarty->assign([
            'custom_css' => Configuration::get(Mollie\Config\Config::MOLLIE_CSS),
        ]);

        $this->context->controller->addJS("{$this->_path}views/js/front/payment_fee.js");

        return $this->display(__FILE__, 'views/templates/front/custom_css.tpl');
    }

    /**
     * @throws PrestaShopException
     */
    public function hookActionFrontControllerSetMedia($params)
    {
        /** @var \Mollie\Service\ErrorDisplayService $errorDisplayService */
        $errorDisplayService = $this->getService(\Mollie\Service\ErrorDisplayService::class);
        /** @var PaymentMethodRepositoryInterface $methodRepository */
        $methodRepository = $this->getService(PaymentMethodRepositoryInterface::class);

        $isCartController = $this->context->controller instanceof CartControllerCore;
        if ($isCartController) {
            $errorDisplayService->showCookieError('mollie_payment_canceled_error');
        }
        /** @var ?MolPaymentMethod $paymentMethod */
        $paymentMethod = $methodRepository->findOneBy(
            [
                'id_method' => Config::MOLLIE_METHOD_ID_APPLE_PAY,
                'live_environment' => Configuration::get(Config::MOLLIE_ENVIRONMENT),
            ]
        );

        $isProductController = $this->context->controller instanceof ProductControllerCore;
        if ($isProductController) {
            $this->context->controller->addJS("{$this->_path}views/js/front/subscription/product.js");
            $this->context->controller->addJqueryPlugin('growl');
            Media::addJsDef([
                'mollieSubAjaxUrl' => $this->context->link->getModuleLink('mollie', 'ajax'),
            ]);
        }
        if (!$paymentMethod || !$paymentMethod->enabled) {
            return;
        }

        $isApplePayEnabled = Configuration::get(Config::MOLLIE_APPLE_PAY_DIRECT);
        if ($isApplePayEnabled) {
            $controller = $this->context->controller;
            if ($controller instanceof ProductControllerCore || $controller instanceof CartControllerCore) {
                Media::addJsDef([
                    'countryCode' => $this->context->country->iso_code,
                    'currencyCode' => $this->context->currency->iso_code,
                    'totalLabel' => $this->context->shop->name,
                    'customerId' => $this->context->customer->id ?? 0,
                    'ajaxUrl' => $this->context->link->getModuleLink('mollie', 'applePayDirectAjax'),
                    'cartId' => $this->context->cart->id,
                    'applePayButtonStyle' => (int) Configuration::get(Config::MOLLIE_APPLE_PAY_DIRECT_STYLE),
                ]);
                $this->context->controller->addCSS($this->getPathUri() . 'views/css/front/apple_pay_direct.css');

                if ($controller instanceof ProductControllerCore) {
                    $this->context->controller->addJS($this->getPathUri() . 'views/js/front/applePayDirect/applePayDirectProduct.js');
                }
                if ($controller instanceof CartControllerCore) {
                    $this->context->controller->addJS($this->getPathUri() . 'views/js/front/applePayDirect/applePayDirectCart.js');
                }
            }
        }
    }

    /**
     * Add custom JS && CSS to admin controllers.
     */
    public function hookActionAdminControllerSetMedia()
    {
        $currentController = Tools::getValue('controller');

        if ('AdminOrders' === $currentController) {
            Media::addJsDef([
                'mollieHookAjaxUrl' => $this->context->link->getAdminLink('AdminMollieAjax'),
            ]);
            $this->context->controller->addCSS($this->getPathUri() . 'views/css/admin/order-list.css');
            $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/order_list.js');

            if (Tools::isSubmit('addorder') || version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
                Media::addJsDef([
                    'molliePendingStatus' => Configuration::get(\Mollie\Config\Config::MOLLIE_STATUS_AWAITING),
                    'isPsVersion177' => version_compare(_PS_VERSION_, '1.7.7.0', '>='),
                ]);
                $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/order_add.js');
            }
        }

        $moduleName = Tools::getValue('configure');

        // We are on module configuration page
        if ($this->name === $moduleName && 'AdminMollieSettings' === $currentController) {
            $this->context->controller->addJqueryPlugin('sortable');
            $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/payment_methods.js');
            $this->context->controller->addCSS($this->getPathUri() . 'views/css/admin/payment_methods.css');
        }
    }

    /**
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->getPathUri() . 'views/css/admin/menu.css');

        $html = '';
        if ($this->context->controller->controller_name === 'AdminOrders') {
            $this->context->smarty->assign([
                'mollieProcessUrl' => $this->context->link->getAdminLink('AdminModules', true) . '&configure=mollie&ajax=1',
                'mollieCheckMethods' => Mollie\Utility\TimeUtility::getCurrentTimeStamp() > ((int) Configuration::get(Mollie\Config\Config::MOLLIE_METHODS_LAST_CHECK) + Mollie\Config\Config::MOLLIE_METHODS_CHECK_INTERVAL),
            ]);
            $html .= $this->display(__FILE__, 'views/templates/admin/ordergrid.tpl');
            if (Tools::isSubmit('addorder') || version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
                $html .= $this->display($this->getPathUri(), 'views/templates/admin/email_checkbox.tpl');
            }
        }

        return $html;
    }

    /**
     * @param array $params Hook parameters
     *
     * @return string|bool Hook HTML
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayAdminOrder($params)
    {
        /** @var \Mollie\Repository\PaymentMethodRepository $paymentMethodRepo */
        $paymentMethodRepo = $this->getService(PaymentMethodRepositoryInterface::class);

        /** @var \Mollie\Service\ShipmentServiceInterface $shipmentService */
        $shipmentService = $this->getService(\Mollie\Service\ShipmentService::class);

        $cartId = Cart::getCartIdByOrderId((int) $params['id_order']);
        $transaction = $paymentMethodRepo->getPaymentBy('cart_id', (string) $cartId);
        if (empty($transaction)) {
            return false;
        }
        $currencies = [];
        foreach (Currency::getCurrencies() as $currency) {
            $currencies[Tools::strtoupper($currency['iso_code'])] = [
                'name' => $currency['name'],
                'iso_code' => Tools::strtoupper($currency['iso_code']),
                'sign' => $currency['sign'],
                'blank' => (bool) isset($currency['blank']) ? $currency['blank'] : true,
                'format' => (int) $currency['format'],
                'decimals' => (bool) isset($currency['decimals']) ? $currency['decimals'] : true,
            ];
        }

        $order = new Order($params['id_order']);
        $this->context->smarty->assign([
            'ajaxEndpoint' => $this->context->link->getAdminLink('AdminModules', true) . '&configure=mollie&ajax=1&action=MollieOrderInfo',
            'transactionId' => $transaction['transaction_id'],
            'currencies' => $currencies,
            'tracking' => $shipmentService->getShipmentInformation($order->reference),
            'publicPath' => __PS_BASE_URI__ . 'modules/' . basename(__FILE__, '.php') . '/views/js/dist/',
            'webPackChunks' => \Mollie\Utility\UrlPathUtility::getWebpackChunks('app'),
            'errorDisplay' => Configuration::get(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS),
        ]);

        return $this->display(__FILE__, 'order_info.tpl');
    }

    /**
     * @param array $params
     *
     * @return array|null
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookPaymentOptions($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            return [];
        }
        $paymentOptions = [];

        /** @var PaymentMethodRepositoryInterface $paymentMethodRepository */
        $paymentMethodRepository = $this->getService(PaymentMethodRepositoryInterface::class);

        /** @var \Mollie\Handler\PaymentOption\PaymentOptionHandlerInterface $paymentOptionsHandler */
        $paymentOptionsHandler = $this->getService(\Mollie\Handler\PaymentOption\PaymentOptionHandlerInterface::class);

        /** @var \Mollie\Service\PaymentMethodService $paymentMethodService */
        $paymentMethodService = $this->getService(\Mollie\Service\PaymentMethodService::class);

        $methods = $paymentMethodService->getMethodsForCheckout();

        foreach ($methods as $method) {
            /** @var MolPaymentMethod|null $paymentMethod */
            $paymentMethod = $paymentMethodRepository->findOneBy(['id_payment_method' => (int) $method['id_payment_method']]);

            if (!$paymentMethod) {
                continue;
            }
            $paymentMethod->method_name = $method['method_name'];
            $paymentOptions[] = $paymentOptionsHandler->handle($paymentMethod);
        }

        return $paymentOptions;
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayOrderConfirmation()
    {
        /** @var \Mollie\Repository\PaymentMethodRepository $paymentMethodRepo */
        $paymentMethodRepo = $this->getService(\Mollie\Repository\PaymentMethodRepository::class);
        $payment = $paymentMethodRepo->getPaymentBy('cart_id', (string) Tools::getValue('id_cart'));
        if (!$payment) {
            return '';
        }
        $isPaid = \Mollie\Api\Types\PaymentStatus::STATUS_PAID == $payment['bank_status'];
        $isAuthorized = \Mollie\Api\Types\PaymentStatus::STATUS_AUTHORIZED == $payment['bank_status'];
        if (($isPaid || $isAuthorized)) {
            $this->context->smarty->assign('okMessage', $this->l('Thank you. We received your payment.'));

            return $this->display(__FILE__, 'ok.tpl');
        }

        return '';
    }

    /**
     * @return array
     *
     * @since 3.3.0
     */
    public function displayAjaxMollieOrderInfo()
    {
        header('Content-Type: application/json;charset=UTF-8');

        /** @var \Mollie\Service\MollieOrderInfoService $orderInfoService */
        $orderInfoService = $this->getService(\Mollie\Service\MollieOrderInfoService::class);

        $input = @json_decode(Tools::file_get_contents('php://input'), true);

        return $orderInfoService->displayMollieOrderInfo($input);
    }

    /**
     * actionOrderStatusUpdate hook.
     *
     * @param array $params
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.0
     */
    public function hookActionOrderStatusUpdate($params = [])
    {
        if (!isset($params['newOrderStatus']) || !isset($params['id_order'])) {
            return;
        }

        if ($params['newOrderStatus'] instanceof OrderState) {
            $orderStatus = $params['newOrderStatus'];
        } else {
            $orderStatus = new OrderState((int) $params['newOrderStatus']);
        }
        $order = new Order($params['id_order']);

        if (!Validate::isLoadedObject($orderStatus)) {
            return;
        }

        if (!Validate::isLoadedObject($order)) {
            return;
        }

        $idOrder = $params['id_order'];
        $order = new Order($idOrder);
        $checkStatuses = [];
        if (Configuration::get(Mollie\Config\Config::MOLLIE_AUTO_SHIP_STATUSES)) {
            $checkStatuses = @json_decode(Configuration::get(Mollie\Config\Config::MOLLIE_AUTO_SHIP_STATUSES));
        }
        if (!is_array($checkStatuses)) {
            $checkStatuses = [];
        }
        if (!(Configuration::get(Mollie\Config\Config::MOLLIE_AUTO_SHIP_MAIN) && in_array($orderStatus->id, $checkStatuses))
        ) {
            return;
        }

        /** @var \Mollie\Handler\Shipment\ShipmentSenderHandlerInterface $shipmentSenderHandler */
        $shipmentSenderHandler = $this->getService(
            Mollie\Handler\Shipment\ShipmentSenderHandlerInterface::class
        );

        if (!$this->getApiClient()) {
            return;
        }
        try {
            $shipmentSenderHandler->handleShipmentSender($this->getApiClient(), $order, $orderStatus);
        } catch (Exception $e) {
            //todo: we logg error in handleShipment
        }
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    public function hookActionEmailSendBefore($params)
    {
        if (!isset($params['cart']->id)) {
            return true;
        }

        $cart = new Cart($params['cart']->id);
        $orderId = Order::getOrderByCartId($cart->id);
        $order = new Order($orderId);
        if ($order->module !== $this->name) {
            return true;
        }
        /** @var \Mollie\Validator\OrderConfMailValidator $orderConfMailValidator */
        $orderConfMailValidator = $this->getService(\Mollie\Validator\OrderConfMailValidator::class);

        /** @var string $template */
        $template = $params['template'];

        if ('order_conf' === $template ||
            'account' === $template ||
            'backoffice_order' === $template ||
            'contact_form' === $template ||
            'credit_slip' === $template ||
            'in_transit' === $template ||
            'order_changed' === $template ||
            'order_merchant_comment' === $template ||
            'order_return_state' === $template ||
            'cheque' === $template ||
            'payment' === $template ||
            'preparation' === $template ||
            'shipped' === $template ||
            'order_canceled' === $template ||
            'payment_error' === $template ||
            'outofstock' === $template ||
            'bankwire' === $template ||
            'refund' === $template) {
            $orderId = Order::getOrderByCartId($cart->id);
            $order = new Order($orderId);
            if (!Validate::isLoadedObject($order)) {
                return true;
            }
            try {
                /** @var \Mollie\Repository\OrderFeeRepository $orderFeeRepo */
                $orderFeeRepo = $this->getService(\Mollie\Repository\OrderFeeRepository::class);
                $orderFeeId = $orderFeeRepo->getOrderFeeIdByCartId($cart->id);
                $orderFee = new MolOrderFee($orderFeeId);
            } catch (Exception $e) {
                PrestaShopLogger::addLog(__METHOD__ . ' said: ' . $e->getMessage(), Mollie\Config\Config::CRASH);

                return true;
            }
            if ($orderFee->order_fee) {
                $params['templateVars']['{payment_fee}'] = Tools::displayPrice($orderFee->order_fee);
            } else {
                $params['templateVars']['{payment_fee}'] = Tools::displayPrice(0);
            }
        }

        if ('order_conf' === $template) {
            return $orderConfMailValidator->validate((int) $order->current_state);
        }

        return true;
    }

    public function hookDisplayPDFInvoice($params)
    {
        if (!isset($params['object'])) {
            return;
        }
        if (!$params['object'] instanceof OrderInvoice) {
            return;
        }

        /** @var \Mollie\Builder\InvoicePdfTemplateBuilder $invoiceTemplateBuilder */
        $invoiceTemplateBuilder = $this->getService(\Mollie\Builder\InvoicePdfTemplateBuilder::class);

        $templateParams = $invoiceTemplateBuilder
            ->setOrder($params['object']->getOrder())
            ->buildParams();

        if (empty($templateParams)) {
            return;
        }

        $this->context->smarty->assign($templateParams);

        return $this->context->smarty->fetch(
            $this->getLocalPath() . 'views/templates/admin/invoice_fee.tpl'
        );
    }

    /**
     * @return array
     */
    public function getTabs()
    {
        return [
            [
                'name' => $this->l('AJAX', __CLASS__),
                'class_name' => self::ADMIN_MOLLIE_AJAX_CONTROLLER,
                'parent_class_name' => self::ADMIN_MOLLIE_CONTROLLER,
                'module_tab' => true,
                'visible' => false,
            ],
            [
                'name' => 'parent',
                'class_name' => 'AdminMollieTabParent',
                'parent_class_name' => self::ADMIN_MOLLIE_CONTROLLER,
                'visible' => false,
            ],
            [
                'name' => 'Settings',
                'class_name' => 'AdminMollieSettings',
                'parent_class_name' => 'AdminMollieTabParent',
            ],
            [
                'name' => $this->l('Subscriptions'),
                'class_name' => 'AdminMollieSubscriptionOrdersParent',
                'parent_class_name' => self::ADMIN_MOLLIE_CONTROLLER,
            ],
            [
                'name' => $this->l('Subscriptions'),
                'class_name' => 'AdminMollieSubscriptionOrders',
                'parent_class_name' => 'AdminMollieTabParent',
            ],
            [
                'name' => $this->l('Subscription FAQ'),
                'class_name' => 'AdminMollieSubscriptionFAQParent',
                'parent_class_name' => self::ADMIN_MOLLIE_CONTROLLER,
                'module_tab' => true,
            ],
            [
                'name' => $this->l('Subscription FAQ'),
                'class_name' => 'AdminMollieSubscriptionFAQ',
                'parent_class_name' => 'AdminMollieTabParent',
                'module_tab' => true,
            ],
        ];
    }

    public function hookActionAdminOrdersListingFieldsModifier($params)
    {
        if (isset($params['select'])) {
            $params['select'] = rtrim($params['select'], ' ,') . ' ,mol.`transaction_id`';
        }
        if (isset($params['join'])) {
            $params['join'] .= ' LEFT JOIN `' . _DB_PREFIX_ . 'mollie_payments` mol ON mol.`cart_id` = a.`id_cart` AND mol.order_id > 0';
        }
        $params['fields']['order_id'] = [
            'title' => $this->l('Payment link'),
            'align' => 'text-center',
            'class' => 'fixed-width-xs',
            'orderby' => false,
            'search' => false,
            'remove_onclick' => true,
            'callback_object' => 'mollie',
            'callback' => 'resendOrderPaymentLink',
        ];
    }

    public function hookActionOrderGridDefinitionModifier(array $params)
    {
        if (\Configuration::get(\Mollie\Config\Config::MOLLIE_SHOW_RESEND_PAYMENT_LINK) === \Mollie\Config\Config::HIDE_RESENT_LINK) {
            return;
        }

        /** @var \Mollie\Grid\Definition\Modifier\OrderGridDefinitionModifier $orderGridDefinitionModifier */
        $orderGridDefinitionModifier = $this->getService(\Mollie\Grid\Definition\Modifier\OrderGridDefinitionModifier::class);
        $gridDefinition = $params['definition'];

        $orderGridDefinitionModifier->modify($gridDefinition);
    }

    public function hookActionOrderGridQueryBuilderModifier(array $params)
    {
        /** @var \Mollie\Grid\Query\Modifier\OrderGridQueryModifier $orderGridQueryModifier */
        $orderGridQueryModifier = $this->getService(\Mollie\Grid\Query\Modifier\OrderGridQueryModifier::class);
        $searchQueryBuilder = $params['search_query_builder'];

        $orderGridQueryModifier->modify($searchQueryBuilder);
    }

    public function hookActionValidateOrder($params)
    {
        if (!isset($this->context->controller) || 'admin' !== $this->context->controller->controller_type) {
            return;
        }
        $apiClient = $this->getApiClient();
        if (!$apiClient) {
            return;
        }

        //NOTE as mollie-email-send is only in manual order creation in backoffice this should work only when mollie payment is chosen.
        if (!empty(Tools::getValue('mollie-email-send')) &&
            $params['order']->module === $this->name
        ) {
            $cartId = $params['cart']->id;
            $totalPaid = strval($params['order']->total_paid);
            $currency = $params['currency']->iso_code;
            $customerKey = $params['customer']->secure_key;
            $orderReference = $params['order']->reference;
            $orderPayment = $params['order']->payment;
            $orderId = $params['order']->id;

            /** @var \Mollie\Service\PaymentMethodService $paymentMethodService */
            $paymentMethodService = $this->getService(\Mollie\Service\PaymentMethodService::class);
            $paymentMethodObj = new MolPaymentMethod();
            $paymentData = $paymentMethodService->getPaymentData(
                $totalPaid,
                $currency,
                '',
                null,
                $cartId,
                $customerKey,
                $paymentMethodObj,
                $orderReference
            );

            $newPayment = $apiClient->payments->create($paymentData->jsonSerialize());

            /** @var \Mollie\Repository\PaymentMethodRepository $paymentMethodRepository */
            $paymentMethodRepository = $this->getService(\Mollie\Repository\PaymentMethodRepository::class);
            $paymentMethodRepository->addOpenStatusPayment(
                $cartId,
                $orderPayment,
                $newPayment->id,
                $orderId,
                $orderReference
            );

            $sendMolliePaymentMail = Tools::getValue('mollie-email-send');
            if ('on' === $sendMolliePaymentMail) {
                /** @var \Mollie\Service\MolliePaymentMailService $molliePaymentMailService */
                $molliePaymentMailService = $this->getService(\Mollie\Service\MolliePaymentMailService::class);
                $molliePaymentMailService->sendSecondChanceMail($orderId);
            }
        }
    }

    public function hookActionObjectOrderPaymentAddAfter($params)
    {
        /** @var OrderPayment $orderPayment */
        $orderPayment = $params['object'];

        /** @var PaymentMethodRepositoryInterface $paymentMethodRepo */
        $paymentMethodRepo = $this->getService(PaymentMethodRepositoryInterface::class);

        $orders = Order::getByReference($orderPayment->order_reference);
        /** @var Order $order */
        $order = $orders->getFirst();
        if (!Validate::isLoadedObject($order)) {
            return;
        }
        $mollieOrder = $paymentMethodRepo->getPaymentBy('cart_id', $order->id_cart);
        if (!$mollieOrder) {
            return;
        }
        $orderPayment->payment_method = Config::$methods[$mollieOrder['method']];
        $orderPayment->update();
    }

    public function hookDisplayProductActions($params)
    {
        if (PsVersionUtility::isPsVersionHigherThen(_PS_VERSION_, '1.7.6.0')) {
            return $this->display(__FILE__, 'views/templates/front/apple_pay_direct.tpl');
        }
    }

    public function hookDisplayExpressCheckout($params)
    {
        return $this->display(__FILE__, 'views/templates/front/apple_pay_direct.tpl');
    }

    public function hookDisplayProductAdditionalInfo()
    {
        if (!PsVersionUtility::isPsVersionHigherThen(_PS_VERSION_, '1.7.6.0')) {
            return $this->display(__FILE__, 'views/templates/front/apple_pay_direct.tpl');
        }
    }

    public function hookActionCartUpdateQuantityBefore($params)
    {
        /** @var CanProductBeAddedToCartValidator $cartValidation */
        $cartValidation = $this->getService(CanProductBeAddedToCartValidator::class);

        try {
            $cartValidation->validate((int) $params['id_product_attribute']);
        } catch (ProductValidationException|SubscriptionProductValidationException $e) {
            $product = $this->makeProductNotOrderable($params['product']);

            $params['product'] = $product;
        }
    }

    /**
     * @return string
     */
    public function hookDisplayCustomerAccount()
    {
        $context = Context::getContext();
        $id_customer = $context->customer->id;

        $url = Context::getContext()->link->getModuleLink($this->name, 'subscriptions', [], true);

        $this->context->smarty->assign([
            'front_controller' => $url,
            'id_customer' => $id_customer,
        ]);

        return $this->display(dirname(__FILE__), '/views/templates/front/subscription/customerAccount.tpl');
    }

    /**
     * @param int $orderId
     *
     * @return string|bool
     *
     * @throws PrestaShopDatabaseException
     */
    public static function resendOrderPaymentLink($orderId)
    {
        /** @var Mollie $module */
        $module = Module::getInstanceByName('mollie');
        /** @var \Mollie\Repository\PaymentMethodRepository $molliePaymentRepo */
        $molliePaymentRepo = $module->getService(PaymentMethodRepositoryInterface::class);
        $molPayment = $molliePaymentRepo->getPaymentBy('cart_id', (string) Cart::getCartIdByOrderId($orderId));
        if (\Mollie\Utility\MollieStatusUtility::isPaymentFinished($molPayment['bank_status'])) {
            return false;
        }

        /** @var \Mollie\Presenter\OrderListActionBuilder $orderListActionBuilder */
        $orderListActionBuilder = $module->getService(\Mollie\Presenter\OrderListActionBuilder::class);

        return $orderListActionBuilder->buildOrderPaymentResendButton($orderId);
    }

    public function updateApiKey($shopId = null)
    {
        $this->setApiKey($shopId);
    }

    public function runUpgradeModule()
    {
        /** @var Mollie\Tracker\Segment $segment */
        $segment = $this->getService(Mollie\Tracker\Segment::class);

        $segment->setMessage('Mollie module upgrade');
        $segment->track();

        return parent::runUpgradeModule();
    }

    private function setApiKey($shopId = null)
    {
        /** @var \Mollie\Repository\ModuleRepository $moduleRepository */
        $moduleRepository = $this->getService(\Mollie\Repository\ModuleRepository::class);
        $moduleDatabaseVersion = $moduleRepository->getModuleDatabaseVersion($this->name);
        $needsUpgrade = Tools::version_compare($this->version, $moduleDatabaseVersion, '>');
        if ($needsUpgrade) {
            return;
        }

        /** @var \Mollie\Service\ApiKeyService $apiKeyService */
        $apiKeyService = $this->getService(\Mollie\Service\ApiKeyService::class);

        $environment = (int) Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
        $apiKeyConfig = \Mollie\Config\Config::ENVIRONMENT_LIVE === (int) $environment ?
            Mollie\Config\Config::MOLLIE_API_KEY : Mollie\Config\Config::MOLLIE_API_KEY_TEST;

        $apiKey = Configuration::get($apiKeyConfig, null, null, $shopId);

        if (!$apiKey) {
            return;
        }
        try {
            $this->api = $apiKeyService->setApiKey($apiKey, $this->version);
        } catch (\Mollie\Api\Exceptions\IncompatiblePlatform $e) {
            $errorHandler = \Mollie\Handler\ErrorHandler\ErrorHandler::getInstance();
            $errorHandler->handle($e, $e->getCode(), false);
            PrestaShopLogger::addLog(__METHOD__ . ' - System incompatible: ' . $e->getMessage(), Mollie\Config\Config::CRASH);
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            $errorHandler = \Mollie\Handler\ErrorHandler\ErrorHandler::getInstance();
            $errorHandler->handle($e, $e->getCode(), false);
            $this->warning = $this->l('Payment error:') . $e->getMessage();
            PrestaShopLogger::addLog(__METHOD__ . ' said: ' . $this->warning, Mollie\Config\Config::CRASH);
        } catch (\Exception $e) {
            $errorHandler = \Mollie\Handler\ErrorHandler\ErrorHandler::getInstance();
            $errorHandler->handle($e, $e->getCode(), false);
            PrestaShopLogger::addLog(__METHOD__ . ' - System incompatible: ' . $e->getMessage(), Mollie\Config\Config::CRASH);
        }
    }

    private function isPhpVersionCompliant()
    {
        return self::SUPPORTED_PHP_VERSION <= PHP_VERSION_ID;
    }

    private function makeProductNotOrderable($product): Product
    {
        if ($product instanceof Product) {
            $product->available_for_order = false;

            return $product;
        }

        $product['isOrderable'] = false;

        return $product;
    }
}
