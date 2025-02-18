<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 *
 * @category   Mollie
 *
 * @see       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Handler\Order;

use Cart;
use Configuration;
use Mollie;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Order as MollieOrderAlias;
use Mollie\Api\Resources\Payment as MolliePaymentAlias;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Mollie\DTO\Line;
use Mollie\DTO\OrderData;
use Mollie\DTO\PaymentData;
use Mollie\Exception\FailedToProvidePaymentFeeException;
use Mollie\Exception\OrderCreationException;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Provider\PaymentFeeProviderInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\OrderStatusService;
use Mollie\Service\PaymentMethodService;
use Mollie\Subscription\Handler\SubscriptionCreationHandler;
use Mollie\Subscription\Validator\SubscriptionOrderValidator;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\NumberUtility;
use Mollie\Utility\TextGeneratorUtility;
use MolPaymentMethod;
use Order;
use PrestaShop\Decimal\Number;

if (!defined('_PS_VERSION_')) {
    exit;
}

// TODO: refactor
class OrderCreationHandler
{
    const FILE_NAME = 'OrderCreationHandler';
    /**
     * @var Mollie
     */
    private $module;
    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepository;
    /**
     * @var PaymentMethodService
     */
    private $paymentMethodService;
    /** @var OrderPaymentFeeHandler */
    private $orderPaymentFeeHandler;
    /** @var OrderStatusService */
    private $orderStatusService;
    /** @var SubscriptionCreationHandler */
    private $recurringOrderCreation;
    /** @var SubscriptionOrderValidator */
    private $subscriptionOrder;
    /** @var PaymentFeeProviderInterface */
    private $paymentFeeProvider;
    /** @var PrestaLoggerInterface */
    private $logger;

    public function __construct(
        Mollie $module,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        PaymentMethodService $paymentMethodService,
        OrderPaymentFeeHandler $orderPaymentFeeHandler,
        OrderStatusService $orderStatusService,
        SubscriptionCreationHandler $recurringOrderCreation,
        SubscriptionOrderValidator $subscriptionOrder,
        PaymentFeeProviderInterface $paymentFeeProvider,
        PrestaLoggerInterface $logger
    ) {
        $this->module = $module;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentMethodService = $paymentMethodService;
        $this->orderPaymentFeeHandler = $orderPaymentFeeHandler;
        $this->orderStatusService = $orderStatusService;
        $this->recurringOrderCreation = $recurringOrderCreation;
        $this->subscriptionOrder = $subscriptionOrder;
        $this->paymentFeeProvider = $paymentFeeProvider;
        $this->logger = $logger;
    }

    /**
     * @param MollieOrderAlias|MolliePaymentAlias $apiPayment
     *
     * @throws FailedToProvidePaymentFeeException
     * @throws ApiException
     * @throws OrderCreationException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function createOrder($apiPayment, int $cartId, bool $isAuthorizablePayment = false): int
    {
        $orderStatus = $isAuthorizablePayment ?
            (int) Config::getStatuses()[PaymentStatus::STATUS_AUTHORIZED] :
            (int) Config::getStatuses()[PaymentStatus::STATUS_PAID];

        $cart = new Cart($cartId);

        $originalAmount = $cart->getOrderTotal(
            true,
            Cart::BOTH
        );

        $paymentMethod = $this->paymentMethodService->getPaymentMethod($apiPayment);

        $paymentFeeData = $this->paymentFeeProvider->getPaymentFee($paymentMethod, (float) $originalAmount);

        if (Order::getIdByCartId((int) $cartId)) {
            return 0;
        }

        if (!$paymentFeeData->isActive()) {
            $this->module->validateOrder(
                (int) $cartId,
                $orderStatus,
                (float) $apiPayment->amount->value,
                $paymentMethod->method_name,
                null,
                ['transaction_id' => $apiPayment->id],
                null,
                false,
                $cart->secure_key
            );

            /* @phpstan-ignore-next-line */
            $orderId = (int) Order::getIdByCartId((int) $cartId);

            $this->createRecurringOrderEntity(new Order($orderId), $paymentMethod->id_method);

            return $orderId;
        }

        $cartPrice = NumberUtility::plus($originalAmount, $paymentFeeData->getPaymentFeeTaxIncl());
        $priceDifference = NumberUtility::minus($cartPrice, $apiPayment->amount->value);

        if (abs($priceDifference) !== 0.00) {
            if ($apiPayment->resource === Config::MOLLIE_API_STATUS_ORDER) {
                $apiPayment->refundAll();
            } else {
                $apiPayment->refund([
                    'amount' => [
                        'currency' => (string) $apiPayment->amount->currency,
                        'value' => $apiPayment->amount->value,
                    ],
                ]);
            }

            $this->paymentMethodRepository->updatePaymentReason($apiPayment->id, Config::WRONG_AMOUNT_REASON);

            $this->logger->error(sprintf('%s - Wrong cart amount while creating order', self::FILE_NAME), [
                'cart_id' => $cartId,
                'cart_amount' => $cartPrice,
                'api_payment_amount' => $apiPayment->amount->value,
                'price_diff' => $priceDifference,
            ]);

            throw new \Exception('Wrong cart amount');
        }

        $this->module->validateOrder(
            (int) $cartId,
            (int) Configuration::get(Mollie\Config\Config::MOLLIE_STATUS_AWAITING),
            (float) $apiPayment->amount->value,
            $paymentMethod->method_name,
            null,
            ['transaction_id' => $apiPayment->id],
            null,
            false,
            $cart->secure_key
        );

        /* @phpstan-ignore-next-line */
        $orderId = (int) Order::getIdByCartId((int) $cartId);

        $this->orderPaymentFeeHandler->addOrderPaymentFee($orderId, $apiPayment);

        $this->orderStatusService->setOrderStatus($orderId, $orderStatus);

        $this->createRecurringOrderEntity(new Order($orderId), $paymentMethod->id_method);

        return $orderId;
    }

    /**
     * @param PaymentData|OrderData $paymentData
     *
     * @return OrderData|PaymentData
     */
    public function createBankTransferOrder($paymentData, Cart $cart)
    {
        /** @var PaymentMethodRepositoryInterface $paymentMethodRepository */
        $paymentMethodRepository = $this->module->getService(PaymentMethodRepositoryInterface::class);
        $this->module->validateOrder(
            (int) $cart->id,
            (int) Configuration::get(Config::MOLLIE_STATUS_OPEN),
            (float) $paymentData->getAmount()->getValue(),
            isset(Config::$methods[$paymentData->getMethod()]) ? Config::$methods[$paymentData->getMethod()] : $this->module->name,
            null,
            [],
            null,
            false,
            $cart->secure_key
        );

        $orderId = Order::getIdByCartId($cart->id);
        $order = new Order($orderId);

        $environment = (int) Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
        $paymentMethodId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId($paymentData->getMethod(), $environment);
        $paymentMethodObj = new MolPaymentMethod((int) $paymentMethodId);
        $orderNumber = TextGeneratorUtility::generateDescriptionFromCart($paymentMethodObj->description, $order->id);

        if ($paymentData instanceof PaymentData) {
            $paymentData->setDescription($orderNumber);
        } elseif ($paymentData instanceof OrderData) {
            $paymentData->setOrderNumber($orderNumber);
        }

        $originalAmount = $cart->getOrderTotal(
            true,
            Cart::BOTH
        );
        $paymentFee = 0;

        if ($paymentData instanceof PaymentData) {
            $environment = (int) Configuration::get(Config::MOLLIE_ENVIRONMENT);
            $paymentMethod = new MolPaymentMethod(
                $paymentMethodRepository->getPaymentMethodIdByMethodId($paymentData->getMethod(), $environment)
            );

            $paymentFeeData = $this->paymentFeeProvider->getPaymentFee($paymentMethod, (float) $originalAmount);

            $paymentFee = $paymentFeeData->getPaymentFeeTaxIncl();
        } else {
            /** @var Line $line */
            foreach ($paymentData->getLines() as $line) {
                if ($line->getSku() === Config::PAYMENT_FEE_SKU) {
                    $paymentFee = $line->getUnitPrice()->getValue();
                }
            }
        }

        if (!$paymentFee) {
            return $paymentData;
        }

        $order->total_paid_tax_excl = (float) (new Number((string) $order->total_paid_tax_excl))->plus((new Number((string) $paymentFee)))->toPrecision(2);
        $order->total_paid_tax_incl = (float) (new Number((string) $order->total_paid_tax_incl))->plus((new Number((string) $paymentFee)))->toPrecision(2);
        $order->total_paid = (float) $paymentData->getAmount()->getValue();
        $order->total_paid_real = (float) $paymentData->getAmount()->getValue();
        $order->update();

        return $paymentData;
    }

    private function createRecurringOrderEntity(Order $order, string $method): void
    {
        $cart = new Cart($order->id_cart);

        if (!$this->subscriptionOrder->validate($cart)) {
            return;
        }

        try {
            $this->recurringOrderCreation->handle($order, $method);
        } catch (\Throwable $exception) {
            $this->logger->error(sprintf('%s - Failed to create recurring order', self::FILE_NAME), [
                    'exceptions' => ExceptionUtility::getExceptions($exception),
                ]
            );
        }
    }
}
