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

namespace Mollie\Service;

use Cart;
use CartRule;
use Context;
use Mollie;
use Mollie\Handler\CartRule\CartRuleQuantityChangeHandlerInterface;
use Mollie\Repository\PaymentMethodRepository;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentReturnService
{
    const PENDING = 1;
    const DONE = 2;
    const FILE_NAME = 'PaymentReturnService';

    /**
     * @var Mollie
     */
    private $module;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var PaymentMethodRepository
     */
    private $paymentMethodRepository;

    /**
     * @var RepeatOrderLinkFactory
     */
    private $orderLinkFactory;

    /**
     * @var TransactionService
     */
    private $transactionService;

    /**
     * @var CartRuleQuantityChangeHandlerInterface
     */
    private $cartRuleQuantityChangeHandlerInterface;

    public function __construct(
        Mollie $module,
        PaymentMethodRepository $paymentMethodRepository,
        RepeatOrderLinkFactory $orderLinkFactory,
        TransactionService $transactionService,
        CartRuleQuantityChangeHandlerInterface $cartRuleQuantityChangeHandlerInterface
    ) {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->orderLinkFactory = $orderLinkFactory;
        $this->transactionService = $transactionService;
        $this->cartRuleQuantityChangeHandlerInterface = $cartRuleQuantityChangeHandlerInterface;
    }

    public function handleStatus(Order $order, $transaction, $status)
    {
        $cart = new Cart($order->id_cart);

        /* @phpstan-ignore-next-line */
        $cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);
        $this->cartRuleQuantityChangeHandlerInterface->handle($cart, $cartRules);

        return $this->getStatusResponse($transaction, $status, $cart->id, $cart->secure_key);
    }

    public function handlePendingStatus(Order $order, $transaction)
    {
        $cart = new Cart($order->id_cart);
        $status = static::PENDING;

        /* @phpstan-ignore-next-line */
        $cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);
        $this->cartRuleQuantityChangeHandlerInterface->handle($cart, $cartRules);

        return $this->getStatusResponse($transaction, $status, $cart->id, $cart->secure_key);
    }

    public function handlePaidStatus(Order $order, $transaction)
    {
        $cart = new Cart($order->id_cart);
        $status = static::DONE;

        /* @phpstan-ignore-next-line */
        $cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);
        $this->cartRuleQuantityChangeHandlerInterface->handle($cart, $cartRules);

        return $this->getStatusResponse($transaction, $status, $cart->id, $cart->secure_key);
    }

    public function handleAuthorizedStatus(Order $order, $transaction)
    {
        $cart = new Cart($order->id_cart);
        $status = static::DONE;

        /* @phpstan-ignore-next-line */
        $cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);
        $this->cartRuleQuantityChangeHandlerInterface->handle($cart, $cartRules);

        return $this->getStatusResponse($transaction, $status, $cart->id, $cart->secure_key);
    }

    public function handleFailedStatus($transaction)
    {
        $orderLink = $this->orderLinkFactory->getLink();

        return [
            'success' => true,
            'status' => static::DONE,
            'response' => json_encode($transaction),
            'href' => $orderLink,
        ];
    }

    public function handleTestPendingStatus()
    {
        $orderLink = $this->orderLinkFactory->getLink();

        return [
            'success' => true,
            'status' => static::DONE,
            'href' => $orderLink,
        ];
    }

    private function getStatusResponse($transaction, $status, $cartId, $cartSecureKey)
    {
        /* @phpstan-ignore-next-line */
        $orderId = (int) Order::getIdByCartId((int) $cartId);

        $successUrl = $this->context->link->getPageLink(
            'order-confirmation',
            true,
            null,
            [
                'id_cart' => (int) $cartId,
                'id_module' => (int) $this->module->id,
                'id_order' => $orderId,
                'key' => $cartSecureKey,
            ]
        );

        return [
            'success' => true,
            'status' => $status,
            'response' => json_encode($transaction),
            'href' => $successUrl,
        ];
    }
}
