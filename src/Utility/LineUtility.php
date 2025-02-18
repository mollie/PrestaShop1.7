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

namespace mollie\src\Utility;

use Mollie\Config\Config;
use Mollie\DTO\Line;
use Mollie\DTO\Object\Amount;
use Mollie\Utility\TextFormatUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class LineUtility
{
    /**
     * @param string $currencyIsoCode
     * @param string $currencyIsoCode
     *
     * @return array
     */
    public function convertToLineArray(array $newItems, $currencyIsoCode): array
    {
        $roundingPrecision = CONFIG::API_ROUNDING_PRECISION;
        foreach ($newItems as $index => $item) {
            $line = new Line();
            $line->setName($item['name'] ?: $item['sku']);
            $line->setQuantity((int) $item['quantity']);
            $line->setSku(isset($item['sku']) ? $item['sku'] : '');

            $currency = strtoupper(strtolower($currencyIsoCode));

            if (isset($item['discount'])) {
                $line->setDiscountAmount(new Amount(
                        $currency,
                        TextFormatUtility::formatNumber($item['discount'], $roundingPrecision, '.', '')
                    )
                );
            }

            $line->setUnitPrice(new Amount(
                $currency,
                TextFormatUtility::formatNumber($item['unitPrice'], $roundingPrecision, '.', '')
            ));

            $line->setTotalPrice(new Amount(
                $currency,
                TextFormatUtility::formatNumber($item['totalAmount'], $roundingPrecision, '.', '')
            ));

            $line->setVatAmount(new Amount(
                $currency,
                TextFormatUtility::formatNumber($item['vatAmount'], $roundingPrecision, '.', '')
            ));

            if (isset($item['category'])) {
                $line->setCategory($item['category']);
            }

            $line->setVatRate(TextFormatUtility::formatNumber($item['vatRate'], $roundingPrecision, '.', ''));
            $line->setProductUrl($item['product_url'] ?? null);
            $line->setImageUrl($item['image_url'] ?? null);

            $newItems[$index] = $line;
        }

        return $newItems;
    }
}
