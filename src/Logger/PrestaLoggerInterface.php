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

namespace Mollie\Logger;

use Psr\Log\LoggerInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @deprecated use LoggerInterface instead
 */
interface PrestaLoggerInterface extends LoggerInterface
{
}
