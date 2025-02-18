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

namespace Mollie\Subscription\Form\Options;

use Module;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface as OldTranslatorInterface; // Older Symfony translator
use Symfony\Contracts\Translation\TranslatorInterface as NewTranslatorInterface; // Newer Symfony translator

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionOptionsType extends TranslatorAwareType
{
    /** @var FormChoiceProviderInterface */
    private $carrierOptionProvider;
    /** @var Module */
    private $module;

    /** @var NewTranslatorInterface|OldTranslatorInterface */
    /* @phpstan-ignore-next-lines */
    private $translator;

    public function __construct(
        $translator,
        array $locales,
        FormChoiceProviderInterface $carrierOptionProvider,
        Module $module
    ) {
        parent::__construct($translator, $locales);

        $this->carrierOptionProvider = $carrierOptionProvider;
        $this->module = $module;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enable_subscriptions', SwitchType::class, [
                'required' => true,
            ])
            ->add('carrier', ChoiceType::class, [
                'required' => true,
                'choices' => $this->carrierOptionProvider->getChoices(),
                // TODO migrate to modern translation system
                'placeholder' => $this->module->l('Choose your carrier'),
            ]);
    }
}
