<?php

namespace Mollie\Tests\Unit\Collector\ApplePayDirect;

use Cart;
use Mollie\Collector\ApplePayDirect\OrderTotalCollector;
use Mollie\DTO\ApplePay\Carrier\Carrier as AppleCarrier;
use Mollie\DTO\PaymentFeeData;
use Mollie\Service\OrderFeeService;
use PHPUnit\Framework\TestCase;

class OrderTotalCollectorTest extends TestCase
{
    /**
     * @dataProvider  getCarriersDataProvider
     */
    public function testGetOrderTotals($carriers, $expectedResult): void
    {
        $paymentFeeData = $this->createMock(PaymentFeeData::class);
        $paymentFeeData->method('getPaymentFeeTaxIncl')->willReturn(0.5);

        $orderFeeServiceMock = $this->createMock(OrderFeeService::class);
        $orderFeeServiceMock->method('getPaymentFee')->willReturn($paymentFeeData);

        $cart = $this->createMock(Cart::class);
        $cart->method('getOrderTotal')->willReturn(1.95);

        $orderTotalCollector = new OrderTotalCollector($orderFeeServiceMock);
        $orderTotals = $orderTotalCollector->getOrderTotals($carriers, $cart);

        $this->assertEquals($expectedResult, $orderTotals);
    }

    public function getCarriersDataProvider(): array
    {
        return [
            'basic case' => [
                'carriers' => [
                    new AppleCarrier('testName', 'test delay', 1, 0.54),
                ],
                'expectedResult' => [
                    [
                        'type' => 'final',
                        'label' => 'testName',
                        'amount' => 2.45,
                        'amountWithoutFee' => 1.95,
                    ],
                ],
            ],
            'no carriers' => [
                'carriers' => [],
                'expectedResult' => [],
            ],
            'multiple carriers' => [
                'carriers' => [
                    new AppleCarrier('testName1', 'test delay1', 1, 0.54),
                    new AppleCarrier('testName2', 'test delay2', 2, 0),
                ],
                'expectedResult' => [
                    [
                        'type' => 'final',
                        'label' => 'testName1',
                        'amount' => 2.45,
                        'amountWithoutFee' => 1.95,
                    ],
                    [
                        'type' => 'final',
                        'label' => 'testName2',
                        'amount' => 2.45,
                        'amountWithoutFee' => 1.95,
                    ],
                ],
            ],
        ];
    }
}
