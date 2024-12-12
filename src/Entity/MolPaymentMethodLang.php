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
if (!defined('_PS_VERSION_')) {
    exit;
}

class MolPaymentMethodLang extends ObjectModel
{
    /** @var int */
    public $id;

    /** @var string name of the payment method */
    public $id_method;

    /** @var int */
    public $id_lang;

    /** @var int */
    public $id_shop;

    /** @var string payment title */
    public $text;

    /**
     * Definition of the ObjectModel
     */
    public static $definition = [
        'table' => 'mol_payment_method_lang',
        'primary' => 'id',
        'fields' => [
            'id_method' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
                'size' => 64,
            ],
            'id_lang' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
                'size' => 11,
            ],
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
                'size' => 11,
            ],
            'text' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 64,
            ],
        ],
    ];
}
