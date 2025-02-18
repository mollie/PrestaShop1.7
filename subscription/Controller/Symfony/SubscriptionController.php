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

use Exception;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Shop;
use Mollie\Config\Config;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Subscription\Exception\SubscriptionApiException;
use Mollie\Subscription\Filters\SubscriptionFilters;
use Mollie\Subscription\Grid\SubscriptionGridDefinitionFactory;
use Mollie\Subscription\Handler\SubscriptionCancellationHandler;
use Mollie\Subscription\Handler\UpdateSubscriptionCarrierHandler;
use Mollie\Utility\PsVersionUtility;
use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use PrestaShop\PrestaShop\Core\Grid\GridFactoryInterface;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionController extends AbstractSymfonyController
{
    private const FILE_NAME = 'SubscriptionController';

    /** @var ?ContainerInterface */
    protected $container;

    /** @var ?Environment */
    public $twig;

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
    public function indexAction(SubscriptionFilters $filters, Request $request)
    {
        /** @var Shop $shop */
        $shop = $this->module->getService(Shop::class);

        if ($shop->getContext() !== \Shop::CONTEXT_SHOP) {
            if (!$this->get('session')->getFlashBag()->has('error')) {
                $this->addFlash('error', $this->module->l('Select the shop that you want to configure'));
            }

            return $this->render('@PrestaShop/Admin/layout.html.twig');
        }

        /** @var GridFactoryInterface $currencyGridFactory */
        $currencyGridFactory = $this->module->getService('subscription_grid_factory');
        $currencyGrid = $currencyGridFactory->getGrid($filters);

        if (PsVersionUtility::isPsVersionGreaterOrEqualTo(_PS_VERSION_, '1.7.8.0')) {
            $formHandler = $this->get('subscription_options_form_handler')->getForm();
        } else {
            $formHandler = $this->get('subscription_options_form_handler_deprecated')->getForm();
        }

        return new Response(
            $this->renderTwig('@Modules/mollie/views/templates/admin/Subscription/subscriptions-grid.html.twig', [
                'currencyGrid' => $this->presentGrid($currencyGrid),
                'enableSidebar' => true,
                'subscriptionOptionsForm' => $formHandler->createView(),
            ])
        );
    }

    /**
     * @AdminSecurity("is_granted('create', request.get('_legacy_controller'))")
     */
    public function submitOptionsAction(Request $request): RedirectResponse
    {
        if (PsVersionUtility::isPsVersionGreaterOrEqualTo(_PS_VERSION_, '1.7.8.0')) {
            /** @var FormHandlerInterface $formHandler */
            $formHandler = $this->get('subscription_options_form_handler');
        } else {
            /** @var FormHandlerInterface $formHandler */
            $formHandler = $this->get('subscription_options_form_handler_deprecated');
        }

        $form = $formHandler->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash(
                'error',
                $this->module->l('Failed to save options. Try again or contact support.', self::FILE_NAME)
            );

            return $this->redirectToRoute('admin_subscription_index');
        }

        // NOTE: By default getting was throwing silented error
        $carrier = $form->getData()['carrier'] ?? $form->getData()['subscription_options']['carrier'];

        $this->updateSubscriptionCarrier($carrier);

        $formHandler->save($form->getData());

        $this->addFlash(
            'success',
            $this->module->l('Options saved successfully.', self::FILE_NAME)
        );

        return $this->redirectToRoute('admin_subscription_index');
    }

    private function updateSubscriptionCarrier(int $newCarrierId): void
    {
        /** @var ConfigurationAdapter $configuration */
        $configuration = $this->module->getService(ConfigurationAdapter::class);
        $oldCarrierId = $configuration->get(Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID);

        if (empty($oldCarrierId) || empty($newCarrierId)) {
            $this->addFlash(
                'error',
                $this->module->l('Carrier not found', self::FILE_NAME)
            );
        }

        /** @var UpdateSubscriptionCarrierHandler $subscriptionCarrierUpdateHandler */
        $subscriptionCarrierUpdateHandler = $this->module->getService(UpdateSubscriptionCarrierHandler::class);

        /** @var PrestaLoggerInterface $logger */
        $logger = $this->module->getService(PrestaLoggerInterface::class);

        $failedSubscriptionOrderIdsToUpdate = $subscriptionCarrierUpdateHandler->run($newCarrierId);

        if (!empty($failedSubscriptionOrderIdsToUpdate)) {
            $logger->error('Failed to update subscription carrier for all orders.', [
                'failed_subscription_order_ids' => json_encode($failedSubscriptionOrderIdsToUpdate),
            ]);
        }
    }

    /**
     * Provides filters functionality.
     *
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     */
    public function searchAction(Request $request): RedirectResponse
    {
        $responseBuilder = $this->get('prestashop.bundle.grid.response_builder');

        return $responseBuilder->buildSearchResponse(
            $this->get(SubscriptionGridDefinitionFactory::class),
            $request,
            SubscriptionGridDefinitionFactory::GRID_ID,
            'admin_subscription_index'
        );
    }

    /**
     * @AdminSecurity("is_granted('delete', request.get('_legacy_controller'))", redirectRoute="admin_subscription_index")
     */
    public function cancelAction(int $subscriptionId): RedirectResponse
    {
        /** @var SubscriptionCancellationHandler $subscriptionCancellationHandler */
        $subscriptionCancellationHandler = $this->module->getService(SubscriptionCancellationHandler::class);

        try {
            $subscriptionCancellationHandler->handle($subscriptionId);
        } catch (SubscriptionApiException $e) {
            $this->addFlash('error', $this->getErrorMessage($e));

            return $this->redirectToRoute('admin_subscription_index');
        }

        $this->addFlash(
            'success',
            $this->module->l('Successfully canceled', self::FILE_NAME)
        );

        return $this->redirectToRoute('admin_subscription_index');
    }

    private function getErrorMessage(Exception $e): string
    {
        $errors = [];

        if ($e instanceof SubscriptionApiException) {
            $errors[SubscriptionApiException::class] = [
                SubscriptionApiException::CANCELLATION_FAILED => $this->module->l('Failed to cancel subscription', self::FILE_NAME),
            ];
        }

        return $this->getErrorMessageForException($e, $errors);
    }

    private function renderTwig($name, $args)
    {
        if (!$this->twig) {
            return $this->render($name, $args);
        }

        return $this->twig->render($name, $args);
    }
}
