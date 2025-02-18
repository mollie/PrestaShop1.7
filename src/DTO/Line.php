<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

namespace Mollie\DTO;

use JsonSerializable;
use Mollie\DTO\Object\Amount;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Line implements JsonSerializable
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $sku;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $productUrl;

    /**
     * @var string
     */
    private $imageUrl;

    /**
     * @var array
     */
    private $metaData;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var string
     */
    private $vatRate;

    /**
     * @var Amount
     */
    private $unitPrice;

    /**
     * @var Amount
     */
    private $totalPrice;

    /**
     * @var Amount|null
     */
    private $discountAmount = null;

    /**
     * @var Amount
     */
    private $vatAmount;

    /**
     * @var string
     */
    private $category;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Line
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     *
     * @return Line
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Line
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductUrl()
    {
        return $this->productUrl;
    }

    /**
     * @param string $productUrl
     *
     * @return Line
     */
    public function setProductUrl($productUrl)
    {
        $this->productUrl = $productUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     *
     * @return Line
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @return array
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * @param array $metaData
     *
     * @return Line
     */
    public function setMetaData($metaData)
    {
        $this->metaData = $metaData;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return Line
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getVatRate()
    {
        return $this->vatRate;
    }

    /**
     * @param string $vatRate
     *
     * @return Line
     */
    public function setVatRate($vatRate)
    {
        $this->vatRate = $vatRate;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @param Amount $unitPrice
     *
     * @return Line
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * @param Amount $totalPrice
     *
     * @return Line
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * @return Amount|null
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @param Amount $discountAmount
     *
     * @return Line
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getVatAmount()
    {
        return $this->vatAmount;
    }

    /**
     * @param Amount $vatAmount
     *
     * @return Line
     */
    public function setVatAmount($vatAmount)
    {
        $this->vatAmount = $vatAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     *
     * @return Line
     */
    public function setCategory(string $category): Line
    {
        $this->category = $category;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'sku' => $this->getSku(),
            'name' => $this->getName(),
            'productUrl' => $this->getProductUrl(),
            'imageUrl' => $this->getImageUrl(),
            'metadata' => $this->getMetaData(),
            'quantity' => $this->getQuantity(),
            'vatRate' => $this->getVatRate(),
            'category' => $this->getCategory(),
            'unitPrice' => [
                'currency' => $this->getUnitPrice()->getCurrency(),
                'value' => $this->getUnitPrice()->getValue(),
            ],
            'totalAmount' => [
                'currency' => $this->getTotalPrice()->getCurrency(),
                'value' => $this->getTotalPrice()->getValue(),
            ],
            'discountAmount' => $this->getDiscountAmount() ?
                [
                    'currency' => $this->getDiscountAmount()->getCurrency(),
                    'value' => $this->getDiscountAmount()->getValue(),
                ]
                :
                [],
            'vatAmount' => [
                'currency' => $this->getVatAmount()->getCurrency(),
                'value' => $this->getVatAmount()->getValue(),
            ],
        ];
    }
}
