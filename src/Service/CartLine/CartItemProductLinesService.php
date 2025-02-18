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

namespace Mollie\Service\CartLine;

use Mollie\Config\Config;
use mollie\src\Utility\RoundingUtility;
use Mollie\Utility\CalculationUtility;
use Mollie\Utility\NumberUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartItemProductLinesService
{
    /* @var RoundingUtility $roundingUtility */
    private $roundingUtility;

    public function __construct(RoundingUtility $roundingUtility)
    {
        $this->roundingUtility = $roundingUtility;
    }

    /**
     * @param int $vatRatePrecision
     *
     * @return array
     *
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public function fillProductLinesWithRemainingData(array $orderLines, int $vatRatePrecision): array
    {
        $roundingPrecision = CONFIG::API_ROUNDING_PRECISION;

        foreach ($orderLines as $productHash => $aItem) {
            $orderLines[$productHash] = array_map(function ($line) use ($roundingPrecision, $vatRatePrecision) {
                $quantity = (int) $line['quantity'];
                $targetVat = $line['targetVat'];
                $unitPrice = $line['unitPrice'];
                $unitPriceNoTax = $this->roundingUtility->round(CalculationUtility::getUnitPriceNoTax(
                    $line['unitPrice'],
                    $targetVat
                ),
                    $roundingPrecision
                );

                // Calculate VAT
                $totalAmount = $line['totalAmount'];
                $actualVatRate = 0;
                if ($unitPriceNoTax > 0) {
                    $actualVatRate = $this->roundingUtility->round(
                        $vatAmount = CalculationUtility::getActualVatRate($unitPrice, $unitPriceNoTax, $quantity),
                        $vatRatePrecision
                    );
                }
                $vatRateWithPercentages = NumberUtility::plus($actualVatRate, 100);
                $vatAmount = NumberUtility::times(
                    $totalAmount,
                    NumberUtility::divide($actualVatRate, $vatRateWithPercentages)
                );

                $newItem = [
                    'name' => $line['name'],
                    'category' => $line['category'],
                    'quantity' => (int) $quantity,
                    'unitPrice' => $this->roundingUtility->round($unitPrice, $roundingPrecision),
                    'totalAmount' => $this->roundingUtility->round($totalAmount, $roundingPrecision),
                    'vatRate' => $this->roundingUtility->round($actualVatRate, $roundingPrecision),
                    'vatAmount' => $this->roundingUtility->round($vatAmount, $roundingPrecision),
                    'product_url' => $line['product_url'] ?? null,
                    'image_url' => $line['image_url'] ?? null,
                ];
                if (isset($line['sku'])) {
                    $newItem['sku'] = $line['sku'];
                }

                return $newItem;
            }, $aItem);
        }

        return $orderLines;
    }
}
