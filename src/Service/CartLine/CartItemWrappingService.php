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
use Mollie\Utility\CalculationUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartItemWrappingService
{
    /**
     * @var LanguageService
     */
    private $languageService;

    /* @var RoundingUtility */
    private $roundingUtility;

    public function __construct(LanguageService $languageService, RoundingUtility $roundingUtility)
    {
        $this->languageService = $languageService;
        $this->roundingUtility = $roundingUtility;
    }

    /**
     * @param float $wrappingPrice
     * @param array $cartSummary
     * @param int $vatRatePrecision
     * @param array $orderLines
     *
     * @return array
     */
    public function addWrappingLine(float $wrappingPrice, array $cartSummary, int $vatRatePrecision, array $orderLines): array
    {
        if (round($wrappingPrice, 2) > 0) {
            $wrappingVatRate = $this->roundingUtility->round(
                CalculationUtility::getActualVatRate(
                    $cartSummary['total_wrapping'],
                    $cartSummary['total_wrapping_tax_exc']
                ),
                $vatRatePrecision
            );

            $orderLines['wrapping'] = [
                [
                    'name' => $this->languageService->lang('Gift wrapping'),
                    'quantity' => 1,
                    'unitPrice' => $this->roundingUtility->round($wrappingPrice, Config::API_ROUNDING_PRECISION),
                    'totalAmount' => $this->roundingUtility->round($wrappingPrice, Config::API_ROUNDING_PRECISION),
                    'vatAmount' => $this->roundingUtility->round($wrappingPrice * $wrappingVatRate / ($wrappingVatRate + 100), Config::API_ROUNDING_PRECISION),
                    'vatRate' => $wrappingVatRate,
                ],
            ];
        }

        return $orderLines;
    }
}
