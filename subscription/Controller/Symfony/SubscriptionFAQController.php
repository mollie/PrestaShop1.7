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

declare(strict_types=1);

namespace Mollie\Subscription\Controller\Symfony;

use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionFAQController extends AbstractSymfonyController
{
    private const FILE_NAME = 'SubscriptionFAQController';

    /** @var ?ContainerInterface */
    protected $container;

    /** @var ?Environment */
    private $twig;

    public function __construct(
        ?ContainerInterface $container = null,
        ?Environment $twig = null
    ) {
        $this->container = $container;
        $this->twig = $twig;

        parent::__construct();
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     *
     * @return Response
     */
    public function indexAction()
    {
        if (!$this->twig) {
            return new Response(
                $this->render('@Modules/mollie/views/templates/admin/Subscription/subscriptions-faq.html.twig', $this->getFAQTexts())
            );
        }

        return new Response(
            $this->twig->render('@Modules/mollie/views/templates/admin/Subscription/subscriptions-faq.html.twig', $this->getFAQTexts())
        );
    }

    private function getFAQTexts(): array
    {
        return [
            'subscriptionCreationTittle' => $this->module->l('Subscription creation', self::FILE_NAME),
            'subscriptionCreation' => $this->module->l('To create a subscription option for a product variation, assign it a Mollie subscription attribute.', self::FILE_NAME),
            'importantInformationTittle' => $this->module->l('IMPORTANT points', self::FILE_NAME),
            'importantInformation' => $this->module->l('When you add Mollie subscription attributes, make sure you always include \'none\' as a fallback.', self::FILE_NAME),
            'carrierInformationTitle' => $this->module->l('IMPORTANT subscription carrier points', self::FILE_NAME),
            'carrierInformation1' => $this->module->l('Make sure to select default carrier for recurring orders in advanced settings.', self::FILE_NAME),
            'carrierInformation2' => $this->module->l('Carrier should cover all supported shop regions.', self::FILE_NAME),
            'carrierInformation3' => $this->module->l('Carrier cannot be changed after first subscription order is placed.', self::FILE_NAME),
            'carrierInformation4' => $this->module->l('Selected carrier pricing/weight settings or carrier selection in Mollie should not change. If they do, subscription orders must be cancelled and carrier re-selected in module settings.', self::FILE_NAME),
            'cartRuleTitle' => $this->module->l('Cart rules', self::FILE_NAME),
            'cartRule1' => $this->module->l('Do not use cart rules with subscription products as this will cause errors due to incorrect pricing.', self::FILE_NAME),
            'giftWrappingTitle' => $this->module->l('Gift wrapping', self::FILE_NAME),
            'giftWrapping1' => $this->module->l('Gift wrapping feature is not supported for subscription orders.', self::FILE_NAME),
            'subscriptionOrderLogicTitle' => $this->module->l('Recurring order creation', self::FILE_NAME),
            'recurringOrderCreation' => $this->module->l('Mollie for Prestashop automatically creates a new order when the previous order is paid for.', self::FILE_NAME),
            'recurringOrderPrice' => $this->module->l('Recurring orders always use the product price that was specified when the related subscription was created.', self::FILE_NAME),
            'recurringOrderAPIChanges' => $this->module->l('Recurring order will override the “Method” payment setting and will be using Mollie’s Payment API.', self::FILE_NAME),
        ];
    }
}
