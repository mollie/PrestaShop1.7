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

use Mollie\Api\Types\PaymentMethod;
use Mollie\Exception\OrderCreationException;
use Mollie\Handler\Order\OrderCreationHandler;
use Mollie\Logger\Logger;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ExceptionService;
use Mollie\Service\MollieOrderCreationService;
use Mollie\Service\PaymentMethodService;
use Mollie\Subscription\Validator\SubscriptionOrderValidator;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\OrderNumberUtility;

if (!defined('_PS_VERSION_')) {
    return;
}

require_once dirname(__FILE__) . '/../../mollie.php';

/**
 * Class MolliePaymentModuleFrontController.
 *
 * @property Context $context
 * @property Mollie $module
 */
class MolliePaymentModuleFrontController extends ModuleFrontController
{
    private const FILE_NAME = 'payment';

    /** @var bool */
    public $ssl = true;

    /** @var bool */
    public $display_column_left = false;

    /** @var bool */
    public $display_column_right = false;

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function initContent()
    {
        parent::initContent();

        /** @var Logger $logger * */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        /** @var Cart $cart */
        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $this->context->smarty->assign('link', $this->context->link);

        if (!$this->validate(
            $cart,
            $customer
        )) {
            /** @var Mollie\Service\LanguageService $langService */
            $langService = $this->module->getService(Mollie\Service\LanguageService::class);
            $this->errors[] = $langService->lang('This payment method is not available.');
            $this->setTemplate('error.tpl');

            return;
        }

        $method = Tools::getValue('method');

        $originalAmount = $cart->getOrderTotal(
            true,
            Cart::BOTH
        );
        $amount = $originalAmount;
        if (!$amount) {
            Tools::redirectLink('index.php');
        }

        /** @var PaymentMethodRepositoryInterface $paymentMethodRepository */
        $paymentMethodRepository = $this->module->getService(PaymentMethodRepositoryInterface::class);
        /** @var PaymentMethodService $transactionService */
        $transactionService = $this->module->getService(PaymentMethodService::class);
        /** @var MollieOrderCreationService $mollieOrderCreationService */
        $mollieOrderCreationService = $this->module->getService(MollieOrderCreationService::class);
        /** @var SubscriptionOrderValidator $subscriptionOrderValidator */
        $subscriptionOrderValidator = $this->module->getService(SubscriptionOrderValidator::class);

        $environment = (int) Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
        $paymentMethodId = $paymentMethodRepository->getPaymentMethodIdByMethodId($method, $environment);
        $paymentMethodObj = new MolPaymentMethod((int) $paymentMethodId);

        /* if its subscription order only payment API is allowed */
        if ($subscriptionOrderValidator->validate($cart)) {
            $paymentMethodObj->method = Mollie\Config\Config::MOLLIE_PAYMENTS_API;
        }

        $orderNumber = OrderNumberUtility::generateOrderNumber($cart->id);

        $paymentData = $transactionService->getPaymentData(
            $amount,
            Tools::strtoupper($this->context->currency->iso_code),
            $method,
            (int) $cart->id,
            $customer->secure_key,
            $paymentMethodObj,
            $orderNumber,
            Tools::getValue('cardToken'),
            Tools::getValue('saveCard'),
            Tools::getValue('useSavedCard')
        );

        if ($method === PaymentMethod::BANKTRANSFER) {
            /** @var OrderCreationHandler $orderCreationHandler */
            $orderCreationHandler = $this->module->getService(OrderCreationHandler::class);
            $paymentData = $orderCreationHandler->createBankTransferOrder($paymentData, $cart);
        }

        try {
            $apiPayment = $mollieOrderCreationService->createMollieOrder($paymentData, $paymentMethodObj);
        } catch (OrderCreationException $e) {
            $this->setTemplate('error.tpl');

            if (Configuration::get(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS)) {
                $message = 'Cart Dump: ' . $e->getMessage() . ' json: ' . json_encode($paymentData, JSON_PRETTY_PRINT);
            } else {
                /** @var ExceptionService $exceptionService */
                $exceptionService = $this->module->getService(ExceptionService::class);
                $message = $exceptionService->getErrorMessageForException($e);
            }
            $this->errors[] = $message;

            $logger->error('An error occurred when creating mollie payment', [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            return false;
        } catch (PrestaShopException $e) {
            $this->setTemplate('error.tpl');
            $this->errors[] = Configuration::get(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS)
                ? $e->getMessage() . ' Cart Dump: ' . json_encode($paymentData, JSON_PRETTY_PRINT)
                : $this->module->l('An error occurred when creating your payment. Contact customer support.', self::FILE_NAME);

            $logger->error('An error occurred when creating payment', [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            return false;
        }

        if (!$apiPayment) {
            return;
        }

        try {
            if ($method === PaymentMethod::BANKTRANSFER) {
                $orderId = Order::getIdByCartId($cart->id);
                $order = new Order($orderId);
                $paymentMethodRepository->addOpenStatusPayment(
                    $cart->id,
                    $apiPayment->method,
                    $apiPayment->id,
                    $order->id,
                    $order->reference
                );
            }
        } catch (Exception $e) {
            $this->setTemplate('error.tpl');
            $this->errors[] = $this->module->l('Failed to save order information.', self::FILE_NAME);

            $logger->error('Failed to save order information.', [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            return false;
        }

        $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));

        // Go to payment url
        if (null !== $apiPayment->getCheckoutUrl()) {
            Tools::redirect($apiPayment->getCheckoutUrl());
        } else {
            Tools::redirect($apiPayment->redirectUrl);
        }
    }

    /**
     * Checks if this payment option is still available
     * May redirect the user to a more appropriate page.
     *
     * @param Cart $cart
     * @param Customer $customer
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function validate($cart, $customer)
    {
        if (!$cart->id_customer || !$cart->id_address_delivery || !$cart->id_address_invoice || !$this->module->active) {
            // We be like: how did you even get here?
            Tools::redirect(Context::getContext()->link->getPageLink('index', true));

            return false;
        }

        $authorized = false;

        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] === $this->module->name) {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            return false;
        }

        if (!Validate::isLoadedObject($customer)) {
            return false;
        }

        return true;
    }

    /**
     * Prepend module path if PS version >= 1.7.
     *
     * @param string $template
     * @param array $params
     * @param string|null $locale
     *
     * @throws PrestaShopException
     *
     * @since 3.3.2
     */
    public function setTemplate($template, $params = [], $locale = null)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $template = "module:mollie/views/templates/front/17_{$template}";
        }

        /* @phpstan-ignore-next-line */
        parent::setTemplate($template, $params, $locale);
    }
}
