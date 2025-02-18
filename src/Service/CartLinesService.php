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

use Mollie\Config\Config;
use Mollie\DTO\PaymentFeeData;
use Mollie\Exception\CouldNotProcessCartLinesException;
use Mollie\Service\CartLine\CartItemDiscountService;
use Mollie\Service\CartLine\CartItemProductLinesService;
use Mollie\Service\CartLine\CartItemShippingLineService;
use Mollie\Service\CartLine\CartItemsService;
use Mollie\Service\CartLine\CartItemWrappingService;
use mollie\src\Service\CartLine\CartItemPaymentFeeService;
use mollie\src\Utility\LineUtility;
use mollie\src\Utility\RoundingUtility;
use Mollie\Utility\ArrayUtility;
use Mollie\Utility\CalculationUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartLinesService
{
    /* @var CartItemsService */
    private $cartItemsService;

    /* @var CartItemDiscountService */
    private $cartItemDiscountService;

    /* @var CartItemShippingLineService */
    private $cartItemShippingLineService;

    /* @var CartItemWrappingService */
    private $cartItemWrappingService;

    /* @var CartItemProductLinesService */
    private $cartItemProductLinesService;

    /* @var CartItemPaymentFeeService */
    private $cartItemPaymentFeeService;

    /* @var LineUtility */
    private $lineUtility;

    /* @var RoundingUtility */
    private $roundingUtility;

    /* @var ArrayUtility */
    private $arrayUtility;

    public function __construct(
        CartItemsService $cartItemsService,
        CartItemDiscountService $cartItemDiscountService,
        CartItemShippingLineService $cartItemShippingLineService,
        CartItemWrappingService $cartItemWrappingService,
        CartItemProductLinesService $cartItemProductLinesService,
        CartItemPaymentFeeService $cartItemPaymentFeeService,
        LineUtility $lineUtility,
        RoundingUtility $roundingUtility,
        ArrayUtility $arrayUtility
    ) {
        $this->cartItemsService = $cartItemsService;
        $this->cartItemDiscountService = $cartItemDiscountService;
        $this->cartItemShippingLineService = $cartItemShippingLineService;
        $this->cartItemWrappingService = $cartItemWrappingService;
        $this->cartItemProductLinesService = $cartItemProductLinesService;
        $this->cartItemPaymentFeeService = $cartItemPaymentFeeService;
        $this->lineUtility = $lineUtility;
        $this->roundingUtility = $roundingUtility;
        $this->arrayUtility = $arrayUtility;
    }

    /**
     * @param float $amount
     * @param PaymentFeeData $paymentFeeData
     * @param string $currencyIsoCode
     * @param array $cartSummary
     * @param float $shippingCost
     * @param array $cartItems
     * @param bool $psGiftWrapping
     * @param string $selectedVoucherCategory
     *
     * @return array
     *
     * @throws CouldNotProcessCartLinesException
     */
    public function getCartLines(
        float $amount,
        PaymentFeeData $paymentFeeData,
        string $currencyIsoCode,
        array $cartSummary,
        float $shippingCost,
        array $cartItems,
        bool $psGiftWrapping,
        string $selectedVoucherCategory
    ): array {
        $totalPrice = $this->roundingUtility->round($amount, Config::API_ROUNDING_PRECISION);
        $roundedShippingCost = $this->roundingUtility->round($shippingCost, Config::API_ROUNDING_PRECISION);

        foreach ($cartSummary['discounts'] as $discount) {
            if ($discount['free_shipping']) {
                $roundedShippingCost = 0;
            }
        }

        $wrappingPrice = $psGiftWrapping ? $this->roundingUtility->round($cartSummary['total_wrapping'], Config::API_ROUNDING_PRECISION) : 0;

        $remainingAmount = $this->roundingUtility->round(
            CalculationUtility::getCartRemainingPrice((float) $totalPrice, (float) $roundedShippingCost, (float) $wrappingPrice),
            Config::API_ROUNDING_PRECISION
        );

        $orderLines = [];
        list($orderLines, $remainingAmount) = $this->cartItemsService->createProductLines($cartItems, $cartSummary['gift_products'], $orderLines, $selectedVoucherCategory, $remainingAmount);

        $totalDiscounts = $cartSummary['total_discounts'] ?? 0;
        list($orderLines, $remainingAmount) = $this->cartItemDiscountService->addDiscountsToProductLines($totalDiscounts, $orderLines, $remainingAmount);

        try {
            $orderLines = $this->roundingUtility->compositeRoundingInaccuracies($remainingAmount, $orderLines);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedToRoundAmount($e);
        }

        try {
            $orderLines = $this->cartItemProductLinesService->fillProductLinesWithRemainingData($orderLines, Config::VAT_RATE_ROUNDING_PRECISION);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedToFillProductLinesWithRemainingData($e);
        }

        try {
            $orderLines = $this->cartItemShippingLineService->addShippingLine($roundedShippingCost, $cartSummary, $orderLines);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedToAddShippingLine($e);
        }

        try {
            $orderLines = $this->cartItemWrappingService->addWrappingLine($wrappingPrice, $cartSummary, Config::VAT_RATE_ROUNDING_PRECISION, $orderLines);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedToAddWrappingLine($e);
        }

        try {
            $orderLines = $this->cartItemPaymentFeeService->addPaymentFeeLine($paymentFeeData, $orderLines);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedToAddPaymentFee($e);
        }

        try {
            $newItems = $this->arrayUtility->ungroupLines($orderLines);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedToUngroupLines($e);
        }

        try {
            $lines = $this->lineUtility->convertToLineArray($newItems, $currencyIsoCode);
        } catch (\Exception $e) {
            throw CouldNotProcessCartLinesException::failedConvertToLineArray($e);
        }

        return $lines;
    }
}
