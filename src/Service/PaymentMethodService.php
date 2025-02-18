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

use Address;
use Cart;
use Configuration;
use Country;
use Currency;
use Customer;
use MolCustomer;
use Mollie;
use Mollie\Adapter\CartAdapter;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Context;
use Mollie\Adapter\Shop;
use Mollie\Api\Resources\BaseCollection;
use Mollie\Api\Resources\MethodCollection;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Api\Types\SequenceType;
use Mollie\Config\Config;
use Mollie\DTO\Object\Amount;
use Mollie\DTO\Object\Company;
use Mollie\DTO\Object\Payment;
use Mollie\DTO\OrderData;
use Mollie\DTO\PaymentData;
use Mollie\Exception\OrderCreationException;
use Mollie\Logger\LoggerInterface;
use Mollie\Provider\CreditCardLogoProvider;
use Mollie\Provider\OrderTotal\OrderTotalProviderInterface;
use Mollie\Provider\PaymentFeeProviderInterface;
use Mollie\Provider\PhoneNumberProviderInterface;
use Mollie\Repository\GenderRepositoryInterface;
use Mollie\Repository\PaymentMethodLangRepositoryInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidationInterface;
use Mollie\Service\PaymentMethod\PaymentMethodSortProviderInterface;
use Mollie\Subscription\Validator\SubscriptionOrderValidator;
use Mollie\Utility\CustomLogoUtility;
use Mollie\Utility\EnvironmentUtility;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\HashUtility;
use Mollie\Utility\LocaleUtility;
use Mollie\Utility\SecureKeyUtility;
use Mollie\Utility\TextFormatUtility;
use MolPaymentMethod;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentMethodService
{
    const FILE_NAME = 'PaymentMethodService';
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Mollie
     */
    private $module;

    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $methodRepository;

    /**
     * @var CartLinesService
     */
    private $cartLinesService;

    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @var CreditCardLogoProvider
     */
    private $creditCardLogoProvider;

    private $paymentMethodSortProvider;

    private $phoneNumberProvider;

    /**
     * @var PaymentMethodRestrictionValidationInterface
     */
    private $paymentMethodRestrictionValidation;

    /**
     * @var Shop
     */
    private $shop;
    /** @var SubscriptionOrderValidator */
    private $subscriptionOrder;
    /** @var CartAdapter */
    private $cartAdapter;
    /** @var ConfigurationAdapter */
    private $configurationAdapter;
    private $genderRepository;
    /** @var PaymentFeeProviderInterface */
    private $paymentFeeProvider;
    /** @var Context */
    private $context;
    /** @var OrderTotalProviderInterface */
    private $orderTotalProvider;
    /** @var PaymentMethodLangRepositoryInterface */
    private $paymentMethodLangRepository;

    public function __construct(
        Mollie $module,
        PaymentMethodRepositoryInterface $methodRepository,
        CartLinesService $cartLinesService,
        CustomerService $customerService,
        CreditCardLogoProvider $creditCardLogoProvider,
        PaymentMethodSortProviderInterface $paymentMethodSortProvider,
        PhoneNumberProviderInterface $phoneNumberProvider,
        PaymentMethodRestrictionValidationInterface $paymentMethodRestrictionValidation,
        Shop $shop,
        SubscriptionOrderValidator $subscriptionOrder,
        CartAdapter $cartAdapter,
        ConfigurationAdapter $configurationAdapter,
        GenderRepositoryInterface $genderRepository,
        PaymentFeeProviderInterface $paymentFeeProvider,
        Context $context,
        OrderTotalProviderInterface $orderTotalProvider,
        PaymentMethodLangRepositoryInterface $paymentMethodLangRepository,
        LoggerInterface $logger
    ) {
        $this->module = $module;
        $this->methodRepository = $methodRepository;
        $this->cartLinesService = $cartLinesService;
        $this->customerService = $customerService;
        $this->creditCardLogoProvider = $creditCardLogoProvider;
        $this->paymentMethodSortProvider = $paymentMethodSortProvider;
        $this->phoneNumberProvider = $phoneNumberProvider;
        $this->paymentMethodRestrictionValidation = $paymentMethodRestrictionValidation;
        $this->shop = $shop;
        $this->subscriptionOrder = $subscriptionOrder;
        $this->cartAdapter = $cartAdapter;
        $this->configurationAdapter = $configurationAdapter;
        $this->genderRepository = $genderRepository;
        $this->paymentFeeProvider = $paymentFeeProvider;
        $this->context = $context;
        $this->orderTotalProvider = $orderTotalProvider;
        $this->paymentMethodLangRepository = $paymentMethodLangRepository;
        $this->logger = $logger;
    }

    public function savePaymentMethod($method)
    {
        $shopId = $this->shop->getShop()->id;
        $environment = Tools::getValue(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
        $paymentId = $this->methodRepository->getPaymentMethodIdByMethodId($method['id'], $environment, $shopId);
        $paymentMethod = new MolPaymentMethod();
        if ($paymentId) {
            $paymentMethod = new MolPaymentMethod((int) $paymentId);
        }
        $paymentMethod->id_method = $method['id'];
        $paymentMethod->method_name = $method['name'];
        $paymentMethod->enabled = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_ENABLED . $method['id']);
        $paymentMethod->method = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_API . $method['id']);
        $paymentMethod->description = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_DESCRIPTION . $method['id']);
        $paymentMethod->is_countries_applicable = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_APPLICABLE_COUNTRIES . $method['id']);
        $paymentMethod->minimal_order_value = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_MINIMUM_ORDER_VALUE . $method['id']);
        $paymentMethod->max_order_value = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_MAX_ORDER_VALUE . $method['id']);
        $paymentMethod->surcharge = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_TYPE . $method['id']);
        $paymentMethod->surcharge_fixed_amount_tax_excl = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_TAX_EXCL . $method['id']);
        $paymentMethod->tax_rules_group_id = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_TAX_RULES_GROUP_ID . $method['id']);
        $paymentMethod->surcharge_percentage = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_PERCENTAGE . $method['id']);
        $paymentMethod->surcharge_limit = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_LIMIT . $method['id']) === ''
            ? (float) Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_MAX_AMOUNT . $method['id'])
            : Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_LIMIT . $method['id']);
        $paymentMethod->images_json = json_encode($method['image']);
        $paymentMethod->live_environment = $environment;
        $paymentMethod->id_shop = $shopId;
        $paymentMethod->min_amount = (float) Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_MIN_AMOUNT . $method['id']);
        $paymentMethod->max_amount = (float) Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_MAX_AMOUNT . $method['id']);

        $paymentMethod->save();

        foreach (Tools::getValue(Config::MOLLIE_METHOD_TITLE . $method['id']) as $idLang => $title) {
            $this->paymentMethodLangRepository->savePaymentTitleTranslation($method['id'], $idLang, $title, $this->context->getShopId());
        }

        return $paymentMethod;
    }

    /**
     * Get payment methods to show on the checkout.
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.0.0
     * @since 3.4.0 public
     *
     * @public ✓ This method is part of the public API
     */
    public function getMethodsForCheckout()
    {
        $apiKey = EnvironmentUtility::getApiKey();
        if (!$apiKey || $this->module->getApiClient() === null) {
            return [];
        }
        /* @phpstan-ignore-next-line */
        if (false === Configuration::get(Config::MOLLIE_STATUS_AWAITING)) {
            return [];
        }
        $apiEnvironment = Configuration::get(Config::MOLLIE_ENVIRONMENT);
        $methods = $this->methodRepository->getMethodsForCheckout($apiEnvironment, $this->shop->getShop()->id) ?: [];

        $isSubscriptionOrder = $this->subscriptionOrder->validate($this->cartAdapter->getCart());
        $sequenceType = $isSubscriptionOrder ? SequenceType::SEQUENCETYPE_FIRST : null;

        try {
            $mollieMethods = $this->getSupportedMollieMethods($sequenceType);
        } catch (\Exception $e) {
            return [];
        }

        $methods = $this->removeNotSupportedMethods($methods, $mollieMethods);

        foreach ($methods as $index => $method) {
            /** @var MolPaymentMethod|null $paymentMethod */
            $paymentMethod = $this->methodRepository->findOneBy(['id_payment_method' => (int) $method['id_payment_method']]);

            if (!$paymentMethod || !$this->paymentMethodRestrictionValidation->isPaymentMethodValid($paymentMethod)) {
                unset($methods[$index]);
                continue;
            }

            $image = json_decode($method['images_json'], true);
            $methods[$index]['image'] = $image;
            if (CustomLogoUtility::isCustomLogoEnabled($method['id_method'])) {
                if ($this->creditCardLogoProvider->logoExists()) {
                    $methods[$index]['image']['custom_logo'] = $this->creditCardLogoProvider->getLogoPathUri();
                }
            }
        }

        $methods = $this->paymentMethodSortProvider->getSortedInAscendingWayForCheckout($methods);

        return $methods;
    }

    /**
     * Get payment data.
     *
     * @param float|string $amount
     * @param string $currency
     * @param string $method
     * @param int|Cart $cartId
     * @param string $secureKey
     * @param string $orderReference
     * @param string $cardToken
     *
     * @return PaymentData|OrderData
     *
     * @since 3.3.0 Order reference
     */
    public function getPaymentData(
        $amount,
        $currency,
        $method,
        $cartId,
        $secureKey,
        MolPaymentMethod $molPaymentMethod,
        $orderReference,
        $cardToken = '',
        $saveCard = true,
        $useSavedCard = false,
        string $applePayToken = ''
    ) {
        $totalAmount = TextFormatUtility::formatNumber($amount, 2);
        $cart = new Cart($cartId);
        $customer = new Customer($cart->id_customer);

        $paymentFeeData = $this->paymentFeeProvider->getPaymentFee($molPaymentMethod, $totalAmount);

        if ($paymentFeeData->isActive()) {
            $totalAmount += $paymentFeeData->getPaymentFeeTaxIncl();
        }

        $currency = (string) ($currency ? Tools::strtoupper($currency) : 'EUR');
        $value = (float) TextFormatUtility::formatNumber($totalAmount, 2);
        $amountObj = new Amount($currency, $value);

        $key = SecureKeyUtility::generateReturnKey(
            $customer->id,
            $cartId,
            $this->module->name
        );
        $redirectUrl = $this->context->getModuleLink(
            'mollie',
            'return',
            [
                'cart_id' => $cartId,
                'utm_nooverride' => 1,
                'rand' => time(),
                'key' => $key,
                'customerId' => $customer->id,
                'order_number' => $orderReference,
            ],
            true
        );

        $webhookUrl = $this->context->getModuleLink(
            'mollie',
            'webhook',
            [
                'security_token' => HashUtility::hash($cart->secure_key),
            ],
            true
        );

        $metaData = [
            'cart_id' => $cartId,
            'order_reference' => $orderReference,
            'secure_key' => $key,
        ];

        if (Mollie\Config\Config::MOLLIE_ORDERS_API !== $molPaymentMethod->method) {
            $paymentData = new PaymentData($amountObj, $orderReference, $redirectUrl, $webhookUrl);

            $paymentData->setMetadata($metaData);

            $paymentData->setLocale($this->getLocale($molPaymentMethod->method));
            $paymentData->setMethod($molPaymentMethod->id_method);

            $paymentData->setDescription($orderReference);
            $paymentData->setEmail($customer->email);

            if (isset($cart->id_address_invoice)) {
                $billingAddress = new Address((int) $cart->id_address_invoice);
                $paymentData->setBillingAddress($billingAddress);
            }
            if (isset($cart->id_address_delivery)) {
                $shippingAddress = new Address((int) $cart->id_address_delivery);
                $paymentData->setShippingAddress($shippingAddress);
            }

            if ($cardToken) {
                $paymentData->setCardToken($cardToken);
            }

            if (PaymentMethod::BANKTRANSFER === $method) {
                $paymentData->setLocale(LocaleUtility::getWebShopLocale());
            }

            if ($molPaymentMethod->id_method === PaymentMethod::APPLEPAY && $applePayToken) {
                $paymentData->setApplePayToken($applePayToken);
            }

            if ($this->subscriptionOrder->validate(new Cart($cartId))) {
                $paymentData->setSubscriptionOrder(true);

                $molCustomer = $this->getCustomerInfo($cart->id_customer, true, false);

                // TODO handle this - throw exception or add log message.
                $paymentData->setCustomerId('');

                if ($molCustomer) {
                    $paymentData->setCustomerId($molCustomer->customer_id);
                }

                $paymentData->setSequenceType(SequenceType::SEQUENCETYPE_FIRST);
            }

            $isCreditCardPayment = PaymentMethod::CREDITCARD === $molPaymentMethod->id_method;
            if (!$isCreditCardPayment) {
                return $paymentData;
            }

            if ($molPaymentMethod->id_method === PaymentMethod::CREDITCARD) {
                $molCustomer = $this->handleCustomerInfo($cart->id_customer, $saveCard, $useSavedCard);
                if ($molCustomer) {
                    $paymentData->setCustomerId($molCustomer->customer_id);
                }
            }

            return $paymentData;
        }

        if (Mollie\Config\Config::MOLLIE_ORDERS_API === $molPaymentMethod->method) {
            $orderData = new OrderData($amountObj, $redirectUrl, $webhookUrl);

            if (isset($cart->id_address_invoice)) {
                $billingAddress = new Address((int) $cart->id_address_invoice);

                if (!empty($billingAddress->vat_number) && !empty($customer->siret)) {
                    $company = new Company();
                    $company->setVatNumber($billingAddress->vat_number);
                    $company->setRegistrationNumber($customer->siret);
                }

                $orderData->setBillingAddress($billingAddress);
                $orderData->setBillingPhoneNumber($this->phoneNumberProvider->getFromAddress($billingAddress));
            }
            if (isset($cart->id_address_delivery)) {
                $shippingAddress = new Address((int) $cart->id_address_delivery);

                $orderData->setShippingAddress($shippingAddress);
                $orderData->setDeliveryPhoneNumber($this->phoneNumberProvider->getFromAddress($shippingAddress));
            }

            $orderData->setOrderNumber($orderReference);
            $orderData->setLocale($this->getLocale($molPaymentMethod->method));
            $orderData->setEmail($customer->email);

            /** @var \Gender|null $gender */
            $gender = $this->genderRepository->findOneBy(['id_gender' => $customer->id_gender]);

            if (!empty($gender) && isset($gender->name[$cart->id_lang])) {
                $orderData->setTitle((string) $gender->name[$cart->id_lang]);
            }

            $orderData->setMethod($molPaymentMethod->id_method);
            $orderData->setMetadata($metaData);

            if (!empty($customer->birthday) && $customer->birthday !== '0000-00-00') {
                $orderData->setConsumerDateOfBirth((string) $customer->birthday);
            }

            $currency = new Currency($cart->id_currency);
            $selectedVoucherCategory = Configuration::get(Config::MOLLIE_VOUCHER_CATEGORY);

            try {
                $orderData->setLines(
                    $this->cartLinesService->getCartLines(
                        $amount,
                        $paymentFeeData,
                        $currency->iso_code,
                        $cart->getSummaryDetails(),
                        $cart->getTotalShippingCost(null, true),
                        $cart->getProducts(),
                        (bool) Configuration::get('PS_GIFT_WRAPPING'),
                        $selectedVoucherCategory
                    )
                );
            } catch (\Exception $e) {
                $this->logger->error(sprintf('%s - Unable to set order lines', self::FILE_NAME), [
                    'exceptions' => ExceptionUtility::getExceptions($e),
                ]);

                return $orderData;
            }

            $payment = new Payment();

            if (!empty($cardToken)) {
                $payment->setCardToken($cardToken);
            }

            $payment->setWebhookUrl($this->context->getModuleLink(
                'mollie',
                'webhook',
                [
                    'security_token' => HashUtility::hash($cart->secure_key),
                ],
                true
            ));

            if ($molPaymentMethod->id_method === PaymentMethod::CREDITCARD) {
                $molCustomer = $this->handleCustomerInfo($cart->id_customer, $saveCard, $useSavedCard);
                if ($molCustomer && !empty($molCustomer->customer_id)) {
                    $payment->setCustomerId($molCustomer->customer_id);
                }
            }

            if ($molPaymentMethod->id_method === PaymentMethod::APPLEPAY && !empty($applePayToken)) {
                $payment->setApplePayPaymentToken($applePayToken);
            }

            if (isset($company)) {
                $payment->setCompany($company);
            }

            $orderData->setPayment($payment);

            return $orderData;
        }

        // TODO handle no return option - throw exception
    }

    private function getLocale($method)
    {
        // Send webshop locale
        if ((Mollie\Config\Config::MOLLIE_PAYMENTS_API === $method
                && Mollie\Config\Config::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE === Configuration::get(Mollie\Config\Config::MOLLIE_PAYMENTSCREEN_LOCALE))
            || Mollie\Config\Config::MOLLIE_ORDERS_API === $method
        ) {
            $locale = LocaleUtility::getWebShopLocale();
            if (preg_match(
                '/^[a-z]{2}(?:[\-_][A-Z]{2})?$/iu',
                $locale
            )) {
                return $locale;
            }
        }
    }

    private function isCustomerSaveEnabled(bool $isSingleClickPaymentEnabled, $saveCard = true)
    {
        return $isSingleClickPaymentEnabled && $saveCard;
    }

    private function removeNotSupportedMethods($methods, $mollieMethods)
    {
        foreach ($methods as $key => $method) {
            $valid = false;
            foreach ($mollieMethods as $mollieMethod) {
                if ($method['id_method'] === $mollieMethod->id) {
                    $valid = true;
                    $methods[$key]['method_name'] = $mollieMethod->description;
                    continue;
                }
            }
            if (!$valid) {
                unset($methods[$key]);
            }
        }

        return $methods;
    }

    private function getSupportedMollieMethods(?string $sequenceType = null): array
    {
        $address = new Address($this->context->getInvoiceAddressId());
        $country = new Country($address->id_country);

        $cartAmount = $this->orderTotalProvider->getOrderTotal();

        /** @var BaseCollection|MethodCollection $methods */
        $methods = $this->module->getApiClient()->methods->allActive(
            [
                'resource' => 'orders',
                'includeWallets' => 'applepay',
                'locale' => $this->context->getLanguageLocale(),
                'billingCountry' => $country->iso_code,
                'amount' => [
                    'value' => (string) TextFormatUtility::formatNumber($cartAmount, 2),
                    'currency' => $this->context->getCurrencyIso(),
                ],
                'sequenceType' => $sequenceType,
            ]
        );

        return $methods->getArrayCopy();
    }

    public function handleCustomerInfo(int $customerId, bool $saveCard, bool $useSavedCard): ?MolCustomer
    {
        $isSingleClickPaymentEnabled = (bool) (int) $this->configurationAdapter->get(Config::MOLLIE_SINGLE_CLICK_PAYMENT);
        if (!$this->isCustomerSaveEnabled($isSingleClickPaymentEnabled)) {
            return null;
        }

        return $this->getCustomerInfo($customerId, $saveCard, $useSavedCard);
    }

    public function getCustomerInfo(int $customerId, bool $saveCard, bool $useSavedCard): ?MolCustomer
    {
        if ($saveCard) {
            $apiCustomer = $this->customerService->processCustomerCreation($customerId);
        } elseif ($useSavedCard) {
            $apiCustomer = $this->customerService->getCustomer($customerId);
        } else {
            return null;
        }

        return $apiCustomer;
    }

    public function getPaymentMethod($apiPayment): MolPaymentMethod
    {
        $transactionMethod = $apiPayment->method;

        switch ($apiPayment->resource) {
            case Config::MOLLIE_API_STATUS_PAYMENT:
                if (!isset($apiPayment->details->wallet)) {
                    break;
                }
                $transactionMethod = $apiPayment->details->wallet;
                break;
            case Config::MOLLIE_API_STATUS_ORDER:
                foreach ($apiPayment->payments() as $payment) {
                    if (!isset($payment->details->wallet)) {
                        continue;
                    }
                    $transactionMethod = $payment->details->wallet;
                }
                break;
            default:
                throw new OrderCreationException('Missing order resource information', OrderCreationException::ORDER_RESOURSE_IS_MISSING);
        }
        $environment = (int) Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);

        return new MolPaymentMethod(
            $this->methodRepository->getPaymentMethodIdByMethodId($transactionMethod, $environment)
        );
    }
}
