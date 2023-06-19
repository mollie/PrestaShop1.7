<?php

namespace Mollie\Verification;

use Mollie\Repository\PaymentMethodRepositoryInterface;

class IsPaymentInformationAvailable
{
    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    public function __construct(PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function verify(int $orderId): bool
    {
        return $this->hasPaymentInformation($orderId);
    }

    private function hasPaymentInformation(int $orderId): bool
    {
        $payment = $this->paymentMethodRepository->getPaymentBy('order_id', (int) $orderId);

        if (empty($payment)) {
            return false;
        }

        if (empty($payment['transaction_id'])) {
            return false;
        }

        return true;
    }
}
