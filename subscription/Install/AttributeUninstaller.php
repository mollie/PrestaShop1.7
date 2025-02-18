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

namespace Mollie\Subscription\Install;

use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\ProductAttributeAdapter;
use Mollie\Logger\LoggerInterface;
use Mollie\Subscription\Config\Config;
use PrestaShopException;
use Psr\Log\LogLevel;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AttributeUninstaller extends AbstractUninstaller
{
    private const FILE_NAME = 'AttributeUninstaller';

    /** @var ConfigurationAdapter */
    private $configuration;

    /** @var Mollie */
    private $module;

    /** @var LoggerInterface */
    private $logger;
    /** @var ProductAttributeAdapter */
    private $productAttributeAdapter;

    public function __construct(
        LoggerInterface $logger,
        ConfigurationAdapter $configuration,
        Mollie $module,
        ProductAttributeAdapter $productAttributeAdapter
    ) {
        $this->configuration = $configuration;
        $this->module = $module;
        $this->logger = $logger;
        $this->productAttributeAdapter = $productAttributeAdapter;
    }

    public function uninstall(): bool
    {
        try {
            foreach (Config::getSubscriptionAttributeOptions() as $attributeName => $attributeConfigKey) {
                $attribute = $this->productAttributeAdapter->getProductAttribute((int) $this->configuration->get($attributeConfigKey));
                $attribute->delete();
            }

            $attributeGroup = new \AttributeGroup((int) $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_GROUP));
            $attributeGroup->delete();
        } catch (PrestaShopException $e) {
            $this->errors[] = $this->module->l('Failed to delete attributes', self::FILE_NAME);
            $this->logger->log(LogLevel::ERROR, $e->getMessage());

            return false;
        }

        return true;
    }
}
