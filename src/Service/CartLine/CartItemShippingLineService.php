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
use Mollie\Service\LanguageService;
use mollie\src\Utility\RoundingUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartItemShippingLineService
{
    /* @var LanguageService */
    private $languageService;

    /* @var RoundingUtility */
    private $roundingUtility;

    public function __construct(LanguageService $languageService, RoundingUtility $roundingUtility)
    {
        $this->languageService = $languageService;
        $this->roundingUtility = $roundingUtility;
    }

    /**
     * @param float $roundedShippingCost
     * @param array $cartSummary
     * @param array $orderLines
     *
     * @return array
     */
    public function addShippingLine(float $roundedShippingCost, array $cartSummary, array $orderLines): array
    {
        if ($this->roundingUtility->round($roundedShippingCost, 2) > 0) {
            $shippingVatRate = $this->roundingUtility->round(($cartSummary['total_shipping'] - $cartSummary['total_shipping_tax_exc']) / $cartSummary['total_shipping_tax_exc'] * 100, Config::API_ROUNDING_PRECISION);

            $orderLines['shipping'] = [
                [
                    'name' => $this->languageService->lang('Shipping'),
                    'quantity' => 1,
                    'unitPrice' => $this->roundingUtility->round($roundedShippingCost, Config::API_ROUNDING_PRECISION),
                    'totalAmount' => $this->roundingUtility->round($roundedShippingCost, Config::API_ROUNDING_PRECISION),
                    'vatAmount' => $this->roundingUtility->round($roundedShippingCost * $shippingVatRate / ($shippingVatRate + 100), Config::API_ROUNDING_PRECISION),
                    'vatRate' => $shippingVatRate,
                ],
            ];
        }

        return $orderLines;
    }
}
