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

namespace Mollie\DTO;

use Address;
use Country;
use JsonSerializable;
use Mollie\DTO\Object\Amount;
use Mollie\DTO\Object\Payment;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderData implements JsonSerializable
{
    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * @var string
     */
    private $webhookUrl;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $metadata;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $cardToken;

    /**
     * @var string
     */
    private $customerId;

    /**
     * @var Address
     */
    private $billingAddress;

    /**
     * @var Address
     */
    private $shippingAddress;

    /**
     * @var string
     */
    private $orderNumber;

    /**
     * @var string
     */
    private $email;

    /**
     * @var Line[]
     */
    private $lines;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var string
     */
    private $billingPhoneNumber;

    /**
     * @var string
     */
    private $deliveryPhoneNumber;

    /**
     * @var string
     */
    private $shippingStreetAndNumber;

    /**
     * @var string
     */
    private $billingStreetAndNumber;

    /**
     * @var string
     */
    private $sequenceType;

    /** @var ?string */
    private $consumerDateOfBirth;

    /** @var ?string */
    private $title;

    public function __construct(
        Amount $amount,
               $redirectUrl,
               $webhookUrl
    ) {
        $this->amount = $amount;
        $this->redirectUrl = $redirectUrl;
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * @return Amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param Amount $amount
     */
    public function setAmount(Amount $amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @param mixed $redirectUrl
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return mixed
     */
    public function getWebhookUrl()
    {
        return $this->webhookUrl;
    }

    /**
     * @param mixed $webhookUrl
     */
    public function setWebhookUrl($webhookUrl)
    {
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     */
    public function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     */
    public function setCustomerId(string $customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return Address
     */
    public function getBillingAddress(): Address
    {
        return $this->billingAddress;
    }

    /**
     * @param Address $billingAddress
     */
    public function setBillingAddress(Address $billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return Address
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @return mixed
     */
    public function getBillingPhoneNumber()
    {
        return $this->billingPhoneNumber;
    }

    /**
     * @param mixed $billingPhoneNumber
     *
     * @return self
     */
    public function setBillingPhoneNumber($billingPhoneNumber): OrderData
    {
        $this->billingPhoneNumber = $billingPhoneNumber;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeliveryPhoneNumber()
    {
        return $this->deliveryPhoneNumber;
    }

    /**
     * @param mixed $deliveryPhoneNumber
     *
     * @return self
     */
    public function setDeliveryPhoneNumber($deliveryPhoneNumber): OrderData
    {
        $this->deliveryPhoneNumber = $deliveryPhoneNumber;

        return $this;
    }

    /**
     * @param Address $shippingAddress
     */
    public function setShippingAddress(Address $shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber(string $orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return Line[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * @param Line[] $lines
     */
    public function setLines(array $lines)
    {
        $this->lines = $lines;
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }

    /**
     * @maps payment
     */
    public function setPayment(Payment $payment): void
    {
        $this->payment = $payment;
    }

    public function getConsumerDateOfBirth(): ?string
    {
        return $this->consumerDateOfBirth;
    }

    public function setConsumerDateOfBirth(string $consumerDateOfBirth): void
    {
        $this->consumerDateOfBirth = $consumerDateOfBirth;
    }

    public function getSequenceType(): string
    {
        return $this->sequenceType;
    }

    public function setSequenceType(string $sequenceType): void
    {
        $this->sequenceType = $sequenceType;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function jsonSerialize()
    {
        $lines = [];
        foreach ($this->getLines() as $line) {
            $lines[] = $line->jsonSerialize();
        }

        $result = [
            'amount' => [
                'currency' => $this->getAmount()->getCurrency(),
                'value' => (string) $this->getAmount()->getValue(),
            ],
            'billingAddress' => [
                'organizationName' => $this->cleanUpInput($this->getBillingAddress()->company),
                'streetAndNumber' => $this->cleanUpInput($this->getBillingAddress()->address1),
                'streetAdditional' => $this->cleanUpInput($this->getBillingAddress()->address2, null),
                'city' => $this->cleanUpInput($this->getBillingAddress()->city),
                'postalCode' => $this->cleanUpInput($this->getBillingAddress()->postcode),
                'country' => $this->cleanUpInput(Country::getIsoById($this->getBillingAddress()->id_country)),
                'givenName' => $this->cleanUpInput($this->getBillingAddress()->firstname),
                'familyName' => $this->cleanUpInput($this->getBillingAddress()->lastname),
                'email' => $this->cleanUpInput($this->getEmail()),
                'title' => $this->cleanUpInput($this->getTitle()),
                'phone' => $this->getBillingPhoneNumber(),
            ],
            'shippingAddress' => [
                'organizationName' => $this->cleanUpInput($this->getShippingAddress()->company),
                'streetAndNumber' => $this->cleanUpInput($this->getShippingAddress()->address1),
                'streetAdditional' => $this->cleanUpInput($this->getShippingAddress()->address2, null),
                'city' => $this->cleanUpInput($this->getShippingAddress()->city),
                'postalCode' => $this->cleanUpInput($this->getShippingAddress()->postcode),
                'country' => $this->cleanUpInput(Country::getIsoById($this->getShippingAddress()->id_country)),
                'givenName' => $this->cleanUpInput($this->getShippingAddress()->firstname),
                'familyName' => $this->cleanUpInput($this->getShippingAddress()->lastname),
                'email' => $this->cleanUpInput($this->getEmail()),
                'title' => $this->cleanUpInput($this->getTitle()),
                'phone' => $this->getDeliveryPhoneNumber(),
            ],
            'redirectUrl' => $this->getRedirectUrl(),
            'webhookUrl' => $this->getWebhookUrl(),
            'method' => $this->getMethod(),
            'metadata' => $this->getMetadata(),
            'locale' => $this->getLocale(),
            'orderNumber' => $this->getOrderNumber(),
            'lines' => $lines,
            'payment' => $this->getPayment()->jsonSerialize(),
            'consumerDateOfBirth' => $this->getConsumerDateOfBirth(),
        ];

        if ($this->billingPhoneNumber) {
            $result['billingAddress']['phone'] = $this->billingPhoneNumber;
        }

        if ($this->deliveryPhoneNumber) {
            $result['shippingAddress']['phone'] = $this->deliveryPhoneNumber;
        }

        if ($this->sequenceType) {
            $result['sequenceType'] = $this->sequenceType;
        }

        return array_filter($result, static function ($val) {
            return $val !== null && $val !== '';
        });
    }

    private function cleanUpInput($input, $defaultValue = 'N/A')
    {
        if (empty($input)) {
            return $defaultValue;
        }

        if (ctype_space($input)) {
            return $defaultValue;
        }
        $input = ltrim($input);

        return substr($input, 0, 100);
    }
}
