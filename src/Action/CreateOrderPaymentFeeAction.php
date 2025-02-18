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

namespace Mollie\Action;

use Exception;
use Mollie\DTO\CreateOrderPaymentFeeActionData;
use Mollie\Exception\CouldNotCreateOrderPaymentFee;
use Mollie\Logger\LoggerInterface;
use Mollie\Utility\ExceptionUtility;
use MolOrderPaymentFee;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CreateOrderPaymentFeeAction
{
    const FILE_NAME = 'CreateOrderPaymentFeeAction';

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws CouldNotCreateOrderPaymentFee
     */
    public function run(CreateOrderPaymentFeeActionData $data): void
    {
        try {
            $molOrderPaymentFee = new MolOrderPaymentFee();

            $molOrderPaymentFee->id_cart = $data->getCartId();
            $molOrderPaymentFee->id_order = $data->getOrderId();
            $molOrderPaymentFee->fee_tax_incl = $data->getPaymentFeeTaxIncl();
            $molOrderPaymentFee->fee_tax_excl = $data->getPaymentFeeTaxExcl();

            $molOrderPaymentFee->save();
        } catch (Exception $exception) {
            $this->logger->error(sprintf('%s - Could not create order payment fee', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            throw CouldNotCreateOrderPaymentFee::failedToInsertOrderPaymentFee($exception);
        }
    }
}
