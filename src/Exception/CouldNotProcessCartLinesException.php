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

namespace Mollie\Exception;

use Exception;
use Mollie\Exception\Code\ExceptionCode;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CouldNotProcessCartLinesException extends \Exception
{
    public static function failedToRoundAmount(Exception $exception): self
    {
        return new self(
            'Failed to round amount.',
            ExceptionCode::SERVICE_FAILED_TO_ROUND_AMOUNT,
            $exception
        );
    }

    public static function failedToFillProductLinesWithRemainingData(Exception $e)
    {
        return new self(
            'Failed to fill product lines with remaining data.',
            ExceptionCode::SERVICE_FAILED_TO_FILL_PRODUCT_LINES_WITH_REMAINING_DATA,
            $e
        );
    }

    public static function failedToAddShippingLine(Exception $e)
    {
        return new self(
            'Failed to add shipping line.',
            ExceptionCode::SERVICE_FAILED_TO_ADD_SHIPPING_LINE,
            $e
        );
    }

    public static function failedToAddWrappingLine(Exception $e)
    {
        return new self(
            'Failed to add wrapping line.',
            ExceptionCode::SERVICE_FAILED_TO_ADD_WRAPPING_LINE,
            $e
        );
    }

    public static function failedToAddPaymentFee(Exception $e)
    {
        return new self(
            'Failed to add payment fee.',
            ExceptionCode::SERVICE_FAILED_TO_ADD_PAYMENT_FEE,
            $e
        );
    }

    public static function failedToUngroupLines(Exception $e)
    {
        return new self(
            'Failed to ungroup lines.',
            ExceptionCode::SERVICE_FAILED_TO_UNGROUP_LINES,
            $e
        );
    }

    public static function failedConvertToLineArray(Exception $e)
    {
        return new self(
            'Failed to convert to line array.',
            ExceptionCode::SERVICE_FAILED_TO_CONVERT_TO_LINE_ARRAY,
            $e
        );
    }
}
