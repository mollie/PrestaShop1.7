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

use Mollie\Adapter\Context;
use Mollie\Config\Config;
use Mollie\Service\VoucherService;
use Mollie\Utility\CartPriceUtility;
use Mollie\Utility\NumberUtility;
use Mollie\Utility\TextFormatUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartItemsService
{
    /**
     * @var Context
     */
    private $context;
    /**
     * @var VoucherService
     */
    private $voucherService;

    public function __construct(Context $context, VoucherService $voucherService)
    {
        $this->context = $context;
        $this->voucherService = $voucherService;
    }

    /**
     * @param array $giftProducts
     * @param string $selectedVoucherCategory
     * @param float $remaining
     *
     * @return array
     */
    public function createProductLines(array $cartItems, array $giftProducts, array $orderLines, string $selectedVoucherCategory, float $remaining): array
    {
        foreach ($cartItems as $cartItem) {
            // Get the rounded total w/ tax
            $roundedTotalWithTax = round($cartItem['total_wt'], Config::API_ROUNDING_PRECISION);

            // Skip if no qty
            $quantity = (int) $cartItem['cart_quantity'];
            if ($quantity <= 0 || $cartItem['price_wt'] <= 0) {
                continue;
            }

            // Generate the product hash
            $idProduct = TextFormatUtility::formatNumber($cartItem['id_product'], 0);
            $idProductAttribute = TextFormatUtility::formatNumber($cartItem['id_product_attribute'], 0);
            $idCustomization = TextFormatUtility::formatNumber($cartItem['id_customization'], 0);

            $productHash = "{$idProduct}¤{$idProductAttribute}¤{$idCustomization}";

            foreach ($giftProducts as $gift_product) {
                if ($gift_product['id_product'] === $cartItem['id_product']) {
                    $quantity = NumberUtility::minus($quantity, $gift_product['cart_quantity']);

                    $productHashGift = "{$idProduct}¤{$idProductAttribute}¤{$idCustomization}gift";
                    $orderLines[$productHashGift][] = [
                        'name' => $cartItem['name'],
                        'sku' => $productHashGift,
                        'targetVat' => (float) $cartItem['rate'],
                        'quantity' => $gift_product['cart_quantity'],
                        'unitPrice' => 0,
                        'totalAmount' => 0,
                        'category' => '',
                        'product_url' => $this->context->getProductLink($cartItem['id_product']),
                        'image_url' => $this->context->getImageLink($cartItem['link_rewrite'], $cartItem['id_image']),
                    ];
                    continue;
                }
            }

            if ((int) $quantity <= 0) {
                continue;
            }

            // Try to spread this product evenly and account for rounding differences on the order line
            $orderLines[$productHash][] = [
                'name' => $cartItem['name'],
                'sku' => $productHash,
                'targetVat' => (float) $cartItem['rate'],
                'quantity' => $quantity,
                'unitPrice' => round($cartItem['price_wt'], Config::API_ROUNDING_PRECISION),
                'totalAmount' => (float) $roundedTotalWithTax,
                'category' => $this->voucherService->getVoucherCategory($cartItem, $selectedVoucherCategory),
                'product_url' => $this->context->getProductLink($cartItem['id_product']),
                'image_url' => $this->context->getImageLink($cartItem['link_rewrite'], $cartItem['id_image']),
            ];
            $remaining -= $roundedTotalWithTax;
        }

        return [$orderLines, $remaining];
    }

    /**
     * Spread the cart line amount evenly.
     *
     * Optionally split into multiple lines in case of rounding inaccuracies
     *
     * @param array[] $cartLineGroup Cart Line Group WITHOUT VAT details (except target VAT rate)
     * @param float $newTotal
     *
     * @return array[]
     *
     * @since 3.2.2
     * @since 3.3.3 Omits VAT details
     */
    public static function spreadCartLineGroup($cartLineGroup, $newTotal)
    {
        $newTotal = round($newTotal, Config::API_ROUNDING_PRECISION);
        $quantity = array_sum(array_column($cartLineGroup, 'quantity'));
        $newCartLineGroup = [];
        $spread = CartPriceUtility::spreadAmountEvenly($newTotal, $quantity);

        foreach ($spread as $unitPrice => $qty) {
            $newCartLineGroup[] = [
                'name' => $cartLineGroup[0]['name'],
                'quantity' => $qty,
                'unitPrice' => (float) $unitPrice,
                'totalAmount' => (float) $unitPrice * $qty,
                'sku' => $cartLineGroup[0]['sku'] ?? '',
                'targetVat' => $cartLineGroup[0]['targetVat'],
                'category' => $cartLineGroup[0]['category'],
            ];
        }

        return $newCartLineGroup;
    }
}
