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

use Mollie\Application\Command\CreateApplePayOrder;
use Mollie\Application\Command\RequestApplePayPaymentSession;
use Mollie\Application\Command\UpdateApplePayShippingContact;
use Mollie\Application\Command\UpdateApplePayShippingMethod;
use Mollie\Application\CommandHandler\CreateApplePayOrderHandler;
use Mollie\Application\CommandHandler\RequestApplePayPaymentSessionHandler;
use Mollie\Application\CommandHandler\UpdateApplePayShippingContactHandler;
use Mollie\Application\CommandHandler\UpdateApplePayShippingMethodHandler;
use Mollie\Builder\ApplePayDirect\ApplePayOrderBuilder;
use Mollie\Builder\ApplePayDirect\ApplePayProductBuilder;
use Mollie\Exception\FailedToProvidePaymentFeeException;
use Mollie\Logger\Logger;
use Mollie\Logger\LoggerInterface;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\OrderRecoverUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieApplePayDirectAjaxModuleFrontController extends ModuleFrontController
{
    private const FILE_NAME = 'applePayDirectAjax';
    /** @var Mollie */
    public $module;

    public function postProcess()
    {
        /** @var Logger $logger * */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        $action = Tools::getValue('action');
        switch ($action) {
            case 'mollie_apple_pay_validation':
                $this->getApplePaySession();
                // no break
            case 'mollie_apple_pay_update_shipping_contact':
                $this->updateAppleShippingContact();
                // no break
            case 'mollie_apple_pay_update_shipping_method':
                $this->updateShippingMethod();
                // no break
            case 'mollie_apple_pay_create_order':
                $this->createApplePayOrder();
                // no break
            case 'mollie_apple_pay_get_total_price':
                $this->getTotalApplePayCartPrice();
        }

        $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));
    }

    private function getApplePaySession()
    {
        $cartId = (int) Tools::getValue('cartId');
        $validationUrl = Tools::getValue('validationUrl');
        /** @var RequestApplePayPaymentSessionHandler $handler */
        $handler = $this->module->getService(RequestApplePayPaymentSessionHandler::class);

        $command = new RequestApplePayPaymentSession(
            $validationUrl,
            (int) $this->context->currency->id,
            (int) $this->context->language->id,
            $cartId
        );
        $response = $handler->handle($command);

        $this->ajaxRender(json_encode($response));
    }

    private function updateShippingMethod()
    {
        /** @var UpdateApplePayShippingMethodHandler $handler */
        $handler = $this->module->getService(UpdateApplePayShippingMethodHandler::class);
        $shippingMethodDetails = Tools::getValue('shippingMethod');

        $command = new UpdateApplePayShippingMethod(
            (int) $shippingMethodDetails['identifier'],
            (int) Tools::getValue('cartId')
        );
        $response = $handler->handle($command);

        $this->ajaxRender(json_encode($response));
    }

    private function updateAppleShippingContact()
    {
        /** @var UpdateApplePayShippingContactHandler $handler */
        $handler = $this->module->getService(UpdateApplePayShippingContactHandler::class);
        /** @var ApplePayProductBuilder $productBuilder */
        $productBuilder = $this->module->getService(ApplePayProductBuilder::class);
        /** @var Logger $logger * */
        $logger = $this->module->getService(LoggerInterface::class);

        $simplifiedContent = Tools::getValue('simplifiedContact');
        $cartId = (int) Tools::getValue('cartId');
        $customerId = (int) Tools::getValue('customerId');
        if (Tools::getIsset('products')) {
            $products = Tools::getValue('products');
        } else {
            $products = $this->getWantedCartProducts($cartId);
        }

        $command = new UpdateApplePayShippingContact(
            $productBuilder->build($products),
            $cartId,
            $simplifiedContent['postalCode'],
            $simplifiedContent['countryCode'],
            $simplifiedContent['country'],
            $simplifiedContent['locality'],
            $customerId
        );

        try {
            $result = $handler->handle($command);
        } catch (FailedToProvidePaymentFeeException $e) {
            $logger->error(sprintf('%s - Failed to find apple pay address.', self::FILE_NAME), [
                'context' => [
                    'cartId' => $cartId,
                    'customerId' => $customerId,
                ],
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            $result = [
                'success' => false,
                'message' => $this->module->l('Failed to find address. Please try again. CartId ' . $cartId, self::FILE_NAME),
            ];
        }

        $this->ajaxRender(json_encode($result));
    }

    private function createApplePayOrder()
    {
        $cartId = (int) Tools::getValue('cartId');
        $cart = new Cart($cartId);

        $products = $this->getWantedCartProducts($cartId);
        /** @var CreateApplePayOrderHandler $handler */
        $handler = $this->module->getService(CreateApplePayOrderHandler::class);
        /** @var ApplePayOrderBuilder $applePayProductBuilder */
        $applePayProductBuilder = $this->module->getService(ApplePayOrderBuilder::class);

        $shippingContent = Tools::getValue('shippingContact');
        $billingContent = Tools::getValue('billingContact');
        $applePayOrderBuilder = $applePayProductBuilder->build($products, $shippingContent, $billingContent);

        $command = new CreateApplePayOrder(
            $cartId,
            $applePayOrderBuilder,
            json_encode(Tools::getValue('token'))
        );
        $response = $handler->handle($command);
        if (!$response['success']) {
            $this->ajaxRender(json_encode($response));
        }

        //we need to recover created order with customer settings so that we can show order confirmation page
        OrderRecoverUtility::recoverCreatedOrder($this->context, $cart->id_customer);

        $this->ajaxRender(json_encode($response));
    }

    private function getTotalApplePayCartPrice()
    {
        $cartId = Tools::getValue('cartId');
        $cart = new Cart($cartId);

        $this->ajaxRender(json_encode(
            [
                'total' => $cart->getOrderTotal(),
            ]
        ));
    }

    private function getWantedCartProducts(int $cartId)
    {
        $cart = new Cart($cartId);

        $products = [];
        foreach ($cart->getProducts() as $product) {
            $products[] = [
                'id_product' => $product['id_product'],
                'id_product_attribute' => $product['id_product_attribute'],
                'id_customization' => $product['id_customization'],
                'quantity_wanted' => $product['cart_quantity'],
            ];
        }

        return $products;
    }
}
