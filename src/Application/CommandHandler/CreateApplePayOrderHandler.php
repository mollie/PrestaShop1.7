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

namespace Mollie\Application\CommandHandler;

use Address;
use Cart;
use Configuration;
use Country;
use Currency;
use Mollie;
use Mollie\Adapter\Link;
use Mollie\Application\Command\CreateApplePayOrder;
use Mollie\Config\Config;
use Mollie\DTO\ApplePay\ShippingContent;
use Mollie\Exception\OrderCreationException;
use Mollie\Exception\RetryOverException;
use Mollie\Handler\RetryHandlerInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\MollieOrderCreationService;
use Mollie\Service\PaymentMethodService;
use Mollie\Utility\OrderNumberUtility;
use MolPaymentMethod;
use Order;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class CreateApplePayOrderHandler
{
    const FILE_NAME = 'CreateApplePayOrderHandler';

    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepository;
    /**
     * @var PaymentMethodService
     */
    private $paymentMethodService;
    /**
     * @var MollieOrderCreationService
     */
    private $mollieOrderCreationService;
    /**
     * @var Link
     */
    private $link;
    /**
     * @var Mollie
     */
    private $module;
    /** @var RetryHandlerInterface */
    private $retryHandler;

    public function __construct(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        PaymentMethodService $paymentMethodService,
        MollieOrderCreationService $mollieOrderCreationService,
        Link $link,
        Mollie $module,
        RetryHandlerInterface $retryHandler
    ) {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentMethodService = $paymentMethodService;
        $this->mollieOrderCreationService = $mollieOrderCreationService;
        $this->link = $link;
        $this->module = $module;
        $this->retryHandler = $retryHandler;
    }

    public function handle(CreateApplePayOrder $command): array
    {
        $cart = new Cart($command->getCartId());
        $this->updateCardInfo($cart->id_address_delivery, $command->getOrder()->getShippingContent());
        $this->updateCardInfo($cart->id_address_invoice, $command->getOrder()->getBillingContent());
        $this->updateCustomer($cart->id_customer, $command->getOrder()->getShippingContent());

        try {
            $apiPayment = $this->createMollieTransaction($cart, $command->getCardToken());
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'STATUS_FAILURE',
                'errors' => [
                    [
                        'code' => 'unknown',
                        'contactField' => null,
                        'message' => $this->module->l('Failed to create mollie transaction.', self::FILE_NAME),
                    ],
                ],
            ];
        }

        // we need to wait for webhook to create the order. That's why we wait here for few seconds and check if order is created
        $proc = function () use ($command) {
            $orderId = Order::getIdByCartId($command->getCartId());
            /* @phpstan-ignore-next-line */
            if (!$orderId) {
                throw new OrderCreationException('Order was not created in webhook', OrderCreationException::ORDER_IS_NOT_CREATED);
            }

            return new Order($orderId);
        };

        try {
            $order = $this->retryHandler->retry(
                $proc,
                [
                    'max' => Config::APPLE_PAY_DIRECT_ORDER_CREATION_MAX_WAIT_RETRIES,
                    'accepted_exception' => OrderCreationException::class,
                ]
            );
        } catch (RetryOverException $e) {
            return [
                'success' => false,
                'status' => 'STATUS_FAILURE',
                'errors' => [
                    [
                        'code' => 'unknown',
                        'contactField' => null,
                        'message' => $this->module->l('Couldn\'t find order by cart.', self::FILE_NAME),
                    ],
                ],
            ];
        }

        $this->deleteAddress($order->id_address_delivery);
        $this->deleteAddress($order->id_address_invoice);

        $successUrl = $this->link->getPageLink(
            'order-confirmation',
            true,
            null,
            [
                'id_cart' => (int) $cart->id,
                'id_module' => (int) $this->module->id,
                'id_order' => Order::getIdByCartId($cart->id),
                'key' => $cart->secure_key,
            ]
        );

        return [
            'success' => true,
            'successUrl' => $successUrl,
            'responseToApple' => ['status' => 0],
        ];
    }

    private function updateCardInfo(int $addressId, ShippingContent $shippingContent)
    {
        $address = new Address($addressId);
        $address->firstname = $shippingContent->getGivenName();
        $address->lastname = $shippingContent->getFamilyName();
        $address->city = $shippingContent->getLocality();
        $address->country = $shippingContent->getCountry();
        $address->id_country = Country::getByIso($shippingContent->getCountryCode());
        $address->postcode = $shippingContent->getPostalCode();
        if (isset($shippingContent->getAddressLines()[0])) {
            $address->address1 = $shippingContent->getAddressLines()[0];
        }
        if (isset($shippingContent->getAddressLines()[1])) {
            $address->address2 = $shippingContent->getAddressLines()[1];
        }

        $address->update();
    }

    private function updateCustomer(int $customerId, ShippingContent $shippingContent)
    {
        $customer = new \Customer($customerId);
        $customer->firstname = $shippingContent->getGivenName();
        $customer->lastname = $shippingContent->getFamilyName();
        $customer->email = $shippingContent->getEmailAddress();

        $customer->update();
    }

    private function createMollieTransaction(Cart $cart, string $cardToken)
    {
        $currency = new Currency($cart->id_currency);
        $paymentMethodId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId(Config::APPLEPAY, Configuration::get(Config::MOLLIE_ENVIRONMENT));

        $paymentMethodObj = new MolPaymentMethod((int) $paymentMethodId);

        $paymentData = $this->paymentMethodService->getPaymentData(
            $cart->getOrderTotal(true, Cart::BOTH, null, $cart->id_carrier),
            Tools::strtoupper($currency->iso_code),
            Config::APPLEPAY,
            (int) $cart->id,
            $cart->secure_key,
            $paymentMethodObj,
            OrderNumberUtility::generateOrderNumber($cart->id),
            '',
            false,
            false,
            $cardToken
        );

        return $this->mollieOrderCreationService->createMollieApplePayDirectOrder($paymentData, $paymentMethodObj);
    }

    private function deleteAddress(int $addressId)
    {
        $address = new Address($addressId);
        $address->deleted = true;
        $address->update();
    }
}
