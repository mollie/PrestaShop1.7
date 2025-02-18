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

namespace Mollie\Utility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ArrayUtility
{
    public static function getLastElement($array)
    {
        return end($array);
    }

    /**
     * @param array $lines
     *
     * @return array
     */
    public function ungroupLines(array $lines): array
    {
        $newItems = [];
        foreach ($lines as &$items) {
            foreach ($items as &$item) {
                $newItems[] = $item;
            }
        }

        return $newItems;
    }
}
