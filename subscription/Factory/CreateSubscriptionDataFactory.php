<?php

declare(strict_types=1);

namespace Mollie\Subscription\Factory;

use Mollie;
use Mollie\Adapter\Link;
use Mollie\Repository\MolCustomerRepository;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Subscription\DTO\CreateSubscriptionData as SubscriptionDataDTO;
use Mollie\Subscription\DTO\Object\Amount;
use Mollie\Subscription\Provider\SubscriptionDescriptionProvider;
use Mollie\Subscription\Provider\SubscriptionIntervalProvider;
use Mollie\Subscription\Repository\CombinationRepository;
use Mollie\Subscription\Repository\CurrencyRepository as CurrencyAdapter;
use Mollie\Subscription\Validator\SubscriptionProductValidator;
use Mollie\Utility\SecureKeyUtility;
use Order;

class CreateSubscriptionDataFactory
{
    /** @var MolCustomerRepository */
    private $customerRepository;

    /** @var SubscriptionIntervalProvider */
    private $subscriptionInterval;

    /** @var SubscriptionDescriptionProvider */
    private $subscriptionDescription;

    /** @var CurrencyAdapter */
    private $currencyAdapter;

    /** @var CombinationRepository */
    private $combination;

    /** @var PaymentMethodRepositoryInterface */
    private $methodRepository;
    /** @var Link */
    private $link;
    /** @var Mollie */
    private $module;
    /** @var SubscriptionProductValidator */
    private $subscriptionProductValidator;

    public function __construct(
        MolCustomerRepository $customerRepository,
        SubscriptionIntervalProvider $subscriptionInterval,
        SubscriptionDescriptionProvider $subscriptionDescription,
        CurrencyAdapter $currencyAdapter,
        CombinationRepository $combination,
        PaymentMethodRepositoryInterface $methodRepository,
        Link $link,
        Mollie $module,
        SubscriptionProductValidator $subscriptionProductValidator
    ) {
        $this->customerRepository = $customerRepository;
        $this->subscriptionInterval = $subscriptionInterval;
        $this->subscriptionDescription = $subscriptionDescription;
        $this->currencyAdapter = $currencyAdapter;
        $this->combination = $combination;
        $this->methodRepository = $methodRepository;
        $this->link = $link;
        $this->module = $module;
        $this->subscriptionProductValidator = $subscriptionProductValidator;
    }

    public function build(Order $order): SubscriptionDataDTO
    {
        $customer = $order->getCustomer();
        /** @var \MolCustomer $molCustomer */
        //todo: will need to improve mollie module logic to have shop id or card it so that multishop doesn't break
        $molCustomer = $this->customerRepository->findOneBy(['email' => $customer->email]);

        $products = $order->getCartProducts();
        $subscriptionProduct = [];

        // TODO add shipping option that is selected in BO

        foreach ($products as $product) {
            if (!$this->subscriptionProductValidator->validate((int) $product['id_product_attribute'])) {
                continue;
            }

            $subscriptionProduct = $product;

            break;
        }

        $combination = $this->combination->getById((int) $subscriptionProduct['id_product_attribute']);
        $interval = $this->subscriptionInterval->getSubscriptionInterval($combination);

        $currency = $this->currencyAdapter->getById((int) $order->id_currency);
        $description = $this->subscriptionDescription->getSubscriptionDescription($order);

        // TODO if we decide to include shipping, add it here (new cart instance is needed)

        $orderAmount = new Amount((float) $subscriptionProduct['total_price_tax_incl'], $currency->iso_code);
        $subscriptionData = new SubscriptionDataDTO(
            $molCustomer->customer_id,
            $orderAmount,
            $interval,
            $description
        );

        $subscriptionData->setWebhookUrl($this->link->getModuleLink(
            'mollie',
            'subscriptionWebhook'
        ));

        $key = SecureKeyUtility::generateReturnKey(
            $order->id_customer,
            $order->id_cart,
            $this->module->name
        );

        $subscriptionData->setMetaData(
            [
                'secure_key' => $key,
            ]
        );

        // todo: check for solution what to do when mandate is missing
        $payment = $this->methodRepository->getPaymentBy('cart_id', $order->id_cart);
        $subscriptionData->setMandateId($payment['mandate_id']);

        return $subscriptionData;
    }
}
