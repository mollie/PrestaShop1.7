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

namespace Mollie\Subscription\Controller\Symfony;

use Module;
use Mollie;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AbstractAdminController - an abstraction for all admin module controllers
 */
abstract class AbstractSymfonyController extends FrameworkBundleAdminController
{
    /** @var Mollie */
    protected $module;

    public function __construct()
    {
        /* @phpstan-ignore-next-line */
        $this->module = Module::getInstanceByName('mollie');
    }
}
