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

namespace Mollie\Grid\Row\AccessibilityChecker;

use Mollie\Repository\PaymentMethodRepositoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\AccessibilityChecker\AccessibilityCheckerInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Checks if second chance email option can be visible in order list.
 */
final class SecondChanceAccessibilityChecker implements AccessibilityCheckerInterface
{
    /** @var PaymentMethodRepositoryInterface */
    public $paymentMethodRepository;

    public function __construct(PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(array $record): bool
    {
        $molPayment = $this->paymentMethodRepository->getPaymentBy('cart_id', (int) \Cart::getCartIdByOrderId($record['id_order']));

        if (!$molPayment) {
            return false;
        }
        if (\Mollie\Utility\MollieStatusUtility::isPaymentFinished($molPayment['bank_status'])) {
            return false;
        }

        return !empty($record['transaction_id']);
    }
}
