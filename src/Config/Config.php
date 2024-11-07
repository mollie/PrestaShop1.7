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

namespace Mollie\Config;

use Configuration;
use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Api\Types\RefundStatus;
use Mollie\Utility\EnvironmentUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Config
{
    const SEGMENT_KEY = 'x8qDW8mWIlcY9SXbMhKLoH7xYQ1cSxF2';

    const SENTRY_KEY = 'https://c2c43f02599847d682e0a1fb7843600f@o497594.ingest.sentry.io/5573860';

    const SENTRY_ENV = 'MISSING_ENV';

    /**
     * Default payment method availability.
     *
     * empty array is global availability
     *
     * @var array
     *
     * @since 3.3.2
     */
    public static $defaultMethodAvailability = [
        'creditcard' => [],
        'klarnapaylater' => ['nl', 'de', 'at', 'fi', 'be'],
        'klarnasliceit' => ['de', 'at', 'fi', 'nl'],
        'ideal' => ['nl'],
        'bancontact' => ['be'],
        'paypal' => [],
        'eps' => ['at'],
        'belfius' => ['be'],
        'inghomepay' => ['be'],
        'kbc' => ['be'],
        'sofort' => ['de', 'at', 'ch', 'pl', 'it', 'es', 'be', 'nl'],
        'giftcard' => ['nl'],
        'bitcoin' => [],
        'paysafecard' => [],
        'banktransfer' => [],
        'cartesbancaires' => ['fr'],
        'directdebit' => [
            'fi', 'at', 'pt', 'be', 'bg', 'es', 'hr', 'cy', 'cz', 'dk', 'ee', 'fr', 'gf', 'de', 'gi', 'gr', 'gp', 'gg', 'hu',
            'is', 'ie', 'im', 'it', 'je', 'lv', 'li', 'lt', 'lu', 'mt', 'mq', 'yt', 'mc', 'nl', 'no', 'pl', 're',
            'ro', 'bl', 'mf', 'pm', 'sm', 'sk', 'sl', 'se', 'ch', 'gb', 'uk',
        ],
        'mybank' => [],
        'trustly' => ['at', 'de', 'ee', 'es', 'fi', 'lt', 'lv', 'nl'],
        'satispay' => [
            'at', 'be', 'bg', 'hr', 'cy', 'cz', 'dk', 'ee', 'fi', 'fr', 'de', 'gr', 'hu', 'is', 'ie',
            'it', 'lv', 'lt', 'lu', 'mt', 'nl', 'no', 'pl', 'pt', 'ro', 'sk', 'si', 'es', 'se', 'ch',
            'li', 'gb', 'tr',
        ],
        'riverty' => ['nl', 'be', 'de', 'at'],
        'payconiq' => [],
        'paybybank' => ['at', 'be', 'cy', 'ee', 'fi', 'fr', 'de', 'gr', 'ie', 'it', 'lv', 'lt', 'lu', 'mt', 'nl', 'pt', 'sk', 'si', 'es', 'uk'],
    ];

    const SUPPORTED_PHP_VERSION = '5.6';
    const NOTICE = 1;
    const WARNING = 2;
    const ERROR = 3;
    const CRASH = 4;

    const NAME = 'mollie';

    const PAYMENTSCREEN_LOCALE_BROWSER_LOCALE = 'browser_locale';
    const PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE = 'website_locale';
    const DEFAULT_EMAIL_LANGUAGE_ISO_CODE = 'en';

    const LOGOS_BIG = 'big';
    const LOGOS_NORMAL = 'normal';
    const LOGOS_HIDE = 'hide';

    const METHODS_CONFIG = 'MOLLIE_METHODS_CONFIG';

    const ENVIRONMENT_TEST = 0;
    const ENVIRONMENT_LIVE = 1;

    const DEBUG_LOG_NONE = 0;
    const DEBUG_LOG_ERRORS = 1;
    const DEBUG_LOG_ALL = 2;

    const MOLLIE_ENV_CHANGED = 'MOLLIE_ENV_CHANGED';
    const MOLLIE_ENVIRONMENT = 'MOLLIE_ENVIRONMENT';
    const MOLLIE_API_KEY = 'MOLLIE_API_KEY';
    const MOLLIE_API_KEY_TEST = 'MOLLIE_API_KEY_TEST';
    const MOLLIE_API_KEY_TESTING_BUTTON = 'MOLLIE_API_KEY_TESTING_BUTTON';
    const MOLLIE_FORM_PAYMENT_OPTION_POSITION = 'payment_option_position';
    const MOLLIE_ACCOUNT_SWITCH = 'MOLLIE_ACCOUNT_SWITCH';
    const MOLLIE_PAYMENTSCREEN_LOCALE = 'MOLLIE_PAYMENTSCREEN_LOCALE';
    const MOLLIE_SEND_ORDER_CONFIRMATION = 'MOLLIE_SEND_ORDER_CONFIRMATION';
    const MOLLIE_IFRAME = [
        'sandbox' => 'MOLLIE_SANDBOX_IFRAME',
        'production' => 'MOLLIE_PRODUCTION_IFRAME',
    ];
    const MOLLIE_SINGLE_CLICK_PAYMENT = [
        'sandbox' => 'MOLLIE_SANDBOX_SINGLE_CLICK_PAYMENT',
        'production' => 'MOLLIE_PRODUCTION_SINGLE_CLICK_PAYMENT',
    ];

    const MOLLIE_IMAGES = 'MOLLIE_IMAGES';
    const MOLLIE_SHOW_RESEND_PAYMENT_LINK = 'MOLLIE_SHOW_RESEND_PAYMENT_LINK';

    const MOLLIE_CSS = 'MOLLIE_CSS';
    const MOLLIE_DEBUG_LOG = 'MOLLIE_DEBUG_LOG';
    const MOLLIE_METHOD_COUNTRIES = 'MOLLIE_METHOD_COUNTRIES';
    const MOLLIE_METHOD_COUNTRIES_DISPLAY = 'MOLLIE_METHOD_COUNTRIES_DISPLAY';
    const MOLLIE_DISPLAY_ERRORS = 'MOLLIE_DISPLAY_ERRORS';
    const MOLLIE_TRACKING_URLS = 'MOLLIE_TRACKING_URLS_';
    const MOLLIE_AUTO_SHIP_MAIN = 'MOLLIE_AS_MAIN';
    const MOLLIE_AUTO_SHIP_STATUSES = 'MOLLIE_AS_STATUSES';
    const MOLLIE_STATUS_OPEN = 'MOLLIE_STATUS_OPEN';
    const MOLLIE_STATUS_AWAITING = 'MOLLIE_STATUS_AWAITING';
    const MOLLIE_STATUS_PAID = 'MOLLIE_STATUS_PAID';
    const MOLLIE_STATUS_COMPLETED = 'MOLLIE_STATUS_COMPLETED';
    const MOLLIE_STATUS_CANCELED = 'MOLLIE_STATUS_CANCELED';
    const MOLLIE_STATUS_EXPIRED = 'MOLLIE_STATUS_EXPIRED';
    const MOLLIE_STATUS_PARTIAL_REFUND = 'MOLLIE_STATUS_PARTIAL_REFUND';
    const MOLLIE_STATUS_REFUNDED = 'MOLLIE_STATUS_REFUNDED';
    const MOLLIE_STATUS_SHIPPING = 'MOLLIE_STATUS_SHIPPING';
    const MOLLIE_MAIL_WHEN_SHIPPING = 'MOLLIE_MAIL_WHEN_SHIPPING';
    const MOLLIE_MAIL_WHEN_OPEN = 'MOLLIE_MAIL_WHEN_OPEN';
    const MOLLIE_MAIL_WHEN_AWAITING = 'MOLLIE_MAIL_WHEN_AWAITING';
    const MOLLIE_MAIL_WHEN_PAID = 'MOLLIE_MAIL_WHEN_PAID';
    const MOLLIE_MAIL_WHEN_COMPLETED = 'MOLLIE_MAIL_WHEN_COMPLETED';
    const MOLLIE_MAIL_WHEN_CANCELED = 'MOLLIE_MAIL_WHEN_CANCELED';
    const MOLLIE_MAIL_WHEN_EXPIRED = 'MOLLIE_MAIL_WHEN_EXPIRED';
    const MOLLIE_MAIL_WHEN_REFUNDED = 'MOLLIE_MAIL_WHEN_REFUNDED';
    const MOLLIE_MAIL_WHEN_CHARGEBACK = 'MOLLIE_MAIL_WHEN_CHARGEBACK';
    // NOTE: const below is needed to check in configuration table if email should be sent. IDE doesn't detect its usage
    const MOLLIE_MAIL_WHEN_PARTIAL_REFUND = 'MOLLIE_MAIL_WHEN_PARTIAL_REFUND';
    const PARTIAL_REFUND_CODE = 'partial_refund';
    const MOLLIE_STATUS_PARTIALLY_SHIPPED = 'MOLLIE_PARTIALLY_SHIPPED';
    const MOLLIE_STATUS_ORDER_COMPLETED = 'MOLLIE_STATUS_ORDER_COMPLETED';
    const MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_DEFAULT = 'MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_DEFAULT';
    const MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED = 'MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED';
    const MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED = 'MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED';
    const MOLLIE_STATUS_CHARGEBACK = 'MOLLIE_STATUS_CHARGEBACK';
    const MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS = 'MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS';
    const MOLLIE_APPLE_PAY_DIRECT_PRODUCT = 'MOLLIE_APPLE_PAY_DIRECT_PRODUCT';
    const MOLLIE_APPLE_PAY_DIRECT_CART = 'MOLLIE_APPLE_PAY_DIRECT_CART';

    const MOLLIE_APPLE_PAY_DIRECT_STYLE = 'MOLLIE_APPLE_PAY_DIRECT_STYLE';
    const MOLLIE_BANCONTACT_QR_CODE_ENABLED = 'MOLLIE_BANCONTACT_QR_CODE_ENABLED';

    const MOLLIE_CARRIER_URL_SOURCE = 'MOLLIE_CARRIER_URL_SOURCE_';
    const MOLLIE_CARRIER_CUSTOM_URL = 'MOLLIE_CARRIER_CUSTOM_URL_';

    const MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID = 'MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID';
    const MOLLIE_SUBSCRIPTION_ENABLED = 'MOLLIE_SUBSCRIPTION_ENABLED';

    const MOLLIE_METHOD_ENABLED = 'MOLLIE_METHOD_ENABLED_';
    const MOLLIE_METHOD_TITLE = 'MOLLIE_METHOD_TITLE_';
    const MOLLIE_METHOD_API = 'MOLLIE_METHOD_API_';
    const MOLLIE_METHOD_DESCRIPTION = 'MOLLIE_METHOD_DESCRIPTION_';
    const MOLLIE_METHOD_APPLICABLE_COUNTRIES = 'MOLLIE_METHOD_APPLICABLE_COUNTRIES_';
    const MOLLIE_METHOD_CERTAIN_COUNTRIES = 'MOLLIE_METHOD_CERTAIN_COUNTRIES_';
    const MOLLIE_METHOD_EXCLUDE_CERTAIN_COUNTRIES = 'MOLLIE_METHOD_EXCLUDE_CERTAIN_COUNTRIES_';
    const MOLLIE_METHOD_MINIMUM_ORDER_VALUE = 'MOLLIE_METHOD_MINIMUM_ORDER_VALUE_';
    const MOLLIE_METHOD_MAX_ORDER_VALUE = 'MOLLIE_METHOD_MAX_ORDER_VALUE_';
    const MOLLIE_METHOD_SURCHARGE_TYPE = 'MOLLIE_METHOD_SURCHARGE_TYPE_';
    const MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_TAX_INCL = 'MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_TAX_INCL_';
    const MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_TAX_EXCL = 'MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_TAX_EXCL_';
    const MOLLIE_METHOD_TAX_RULES_GROUP_ID = 'MOLLIE_METHOD_TAX_RULES_GROUP_ID_';
    const MOLLIE_METHOD_SURCHARGE_PERCENTAGE = 'MOLLIE_METHOD_SURCHARGE_PERCENTAGE_';
    const MOLLIE_METHOD_SURCHARGE_LIMIT = 'MOLLIE_METHOD_SURCHARGE_LIMIT_';
    const MOLLIE_METHOD_MIN_AMOUNT = 'MOLLIE_METHOD_MIN_AMOUNT_';
    const MOLLIE_METHOD_MAX_AMOUNT = 'MOLLIE_METHOD_MAX_AMOUNT_';

    const MOLLIE_CARRIER_NO_TRACKING_INFO = 'no_tracking_info';
    const MOLLIE_CARRIER_MODULE = 'module';
    const MOLLIE_CARRIER_CARRIER = 'carrier_url';
    const MOLLIE_CARRIER_CUSTOM = 'custom_url';

    const MOLLIE_API = 'MOLLIE_API';
    const MOLLIE_ORDERS_API = 'orders';
    const MOLLIE_PAYMENTS_API = 'payments';

    const MOLLIE_METHODS_LAST_CHECK = 'MOLLIE_METHOD_CHECK_UPD';
    const MOLLIE_METHODS_CHECK_INTERVAL = 86400; //daily check

    const API_ROUNDING_PRECISION = 2;
    const VAT_RATE_ROUNDING_PRECISION = 0;

    const STATUS_PAID_ON_BACKORDER = 'paid_backorder';
    const STATUS_PENDING_ON_BACKORDER = 'pending_backorder';
    const STATUS_ON_BACKORDER = 'on_backorder';
    const MOLLIE_AWAITING_PAYMENT = 'awaiting';
    const MOLLIE_CHARGEBACK = 'chargeback';
    const MOLLIE_OPEN_PAYMENT = 'open';
    const PRICE_DISPLAY_METHOD_NO_TAXES = '1';
    const APPLEPAY = 'applepay';
    const MOLLIE_COUNTRIES = 'country_';

    const STATUS_PS_OS_OUTOFSTOCK_PAID = 'PS_OS_OUTOFSTOCK_PAID';

    const FEE_NO_FEE = 0;
    const FEE_FIXED_FEE = 1;
    const FEE_PERCENTAGE = 2;
    const FEE_FIXED_FEE_AND_PERCENTAGE = 3;

    const MOLLIE_API_STATUS_PAYMENT = 'payment';
    const MOLLIE_API_STATUS_ORDER = 'order';

    const ORDER_CONF_MAIL_SEND_ON_CREATION = 0;
    const ORDER_CONF_MAIL_SEND_ON_PAID = 1;
    const ORDER_CONF_MAIL_SEND_ON_NEVER = 2;

    const NEW_ORDER_MAIL_SEND_ON_CREATION = 0;
    const NEW_ORDER_MAIL_SEND_ON_PAID = 1;
    const NEW_ORDER_MAIL_SEND_ON_NEVER = 2;

    const SHOW_RESENT_LINK = 'show';
    const HIDE_RESENT_LINK = 'hide';

    const CARTES_BANCAIRES = 'cartesbancaires';

    const MOLLIE_MODULE_UPGRADE_NOTICE_CLOSE_DATE = 'MOLLIE_MODULE_UPGRADE_NOTICE_CLOSE_DATE';
    const MODULE_UPGRADE_NOTICE_CLOSE_DURATION = 28;

    const MOLLIE_SHOW_CUSTOM_LOGO = 'MOLLIE_SHOW_CUSTOM_LOGO';

    const EMAIL_ALERTS_MODULE_NAME = 'ps_emailalerts';

    const MOLLIE_VOUCHER_CATEGORY = 'MOLLIE_VOUCHER_CATEGORY';

    const MOLLIE_METHOD_ID_KLARNA_PAY_LATER = 'klarnapaylater';
    const MOLLIE_METHOD_ID_KLARNA_SLICE_IT = 'klarnasliceit';
    const MOLLIE_METHOD_ID_APPLE_PAY = 'applepay';
    const MOLLIE_VOUCHER_METHOD_ID = 'voucher';
    const MOLLIE_in3_METHOD_ID = 'in3';
    const RIVERTY = 'riverty';

    const MOLLIE_VOUCHER_CATEGORY_NULL = 'null';
    const MOLLIE_VOUCHER_CATEGORY_MEAL = 'meal';
    const MOLLIE_VOUCHER_CATEGORY_GIFT = 'gift';
    const MOLLIE_VOUCHER_CATEGORY_ECO = 'eco';

    const MOLLIE_REFUND_STATUS_CANCELED = 'canceled';

    const MOLLIE_VOUCHER_FEATURE_ID = 'MOLLIE_VOUCHER_FEATURE_ID';
    const MOLLIE_VOUCHER_FEATURE = 'MOLLIE_VOUCHER_FEATURE_';
    const MOLLIE_VOUCHER_CATEGORIES = [
        self::MOLLIE_VOUCHER_CATEGORY_MEAL => 'meal',
        self::MOLLIE_VOUCHER_CATEGORY_GIFT => 'gift',
        self::MOLLIE_VOUCHER_CATEGORY_ECO => 'eco',
    ];
    const MOLLIE_VOUCHER_MINIMAL_AMOUNT = 1;
    const RESTORE_CART_BACKTRACE_MEMORIZATION_SERVICE = 'memo';
    const RESTORE_CART_BACKTRACE_RETURN_CONTROLLER = 'return';

    const AUTHORIZABLE_PAYMENTS = [
        PaymentMethod::KLARNA_PAY_LATER,
        PaymentMethod::KLARNA_SLICE_IT,
        PaymentMethod::KLARNA_PAY_NOW,
        PaymentMethod::KLARNA_ONE,
        PaymentMethod::BILLIE,
    ];

    const ORDER_API_ONLY_METHODS = [
        PaymentMethod::KLARNA_PAY_LATER,
        PaymentMethod::KLARNA_SLICE_IT,
        PaymentMethod::KLARNA_PAY_NOW,
        PaymentMethod::KLARNA_ONE,
        PaymentMethod::BILLIE,
        self::MOLLIE_VOUCHER_METHOD_ID,
        self::MOLLIE_in3_METHOD_ID,
        self::RIVERTY,
    ];

    const PAYMENT_API_ONLY_METHODS = [
    ];

    const ROUTE_RESEND_SECOND_CHANCE_PAYMENT_MESSAGE = 'mollie_module_admin_resend_payment_message';

    const PAYMENT_FEE_SKU = 'payment-fee-sku';
    const WRONG_AMOUNT_REASON = 'wrong amount';

    const APPLE_PAY_DIRECT_ORDER_CREATION_MAX_WAIT_RETRIES = 10;
    const BANCONTACT_ORDER_CREATION_MAX_WAIT_RETRIES = 600;

    public const LOCK_TIME_TO_LIVE = 60;

    /** @var array */
    public static $methods = [
        'banktransfer' => 'Bank',
        'belfius' => 'Belfius',
        'bitcoin' => 'Bitcoin',
        'cartesbancaires' => 'Cartes Bancaires',
        'creditcard' => 'Credit Card',
        'directdebit' => 'Direct Debit',
        'eps' => 'EPS',
        'giftcard' => 'Giftcard',
        'ideal' => 'iDEAL',
        'inghomepay' => 'ING Homepay',
        'kbc' => 'KBC',
        'bancontact' => 'Bancontact',
        'paypal' => 'PayPal',
        'paysafecard' => 'Paysafecard',
        'sofort' => 'Sofort Banking',
        'klarnapaylater' => 'Pay later.',
        'klarnasliceit' => 'Slice it.',
        'applepay' => 'Apple Pay',
        'mybank' => 'MyBank',
        'voucher' => 'Voucher',
        'klarnapaynow' => 'Klarna Pay now.',
        'in3' => 'in3',
        'billie' => 'Billie',
        'twint' => 'TWINT',
        'bancomat' => 'Bancomat',
        'alma' => 'Alma',
        'blik' => 'BLIK',
        'klarna' => 'Pay with Klarna.',
        'riverty' => 'Riverty',
        'payconiq' => 'Payconiq',
        'trustly' => 'Trustly',
        'satispay' => 'Satispay',
        'paybybank' => 'Pay by Bank',
    ];

    public const LOG_SEVERITY_LEVEL_INFORMATIVE = 1;
    public const LOG_SEVERITY_LEVEL_WARNING = 2;
    public const LOG_SEVERITY_LEVEL_ERROR = 3;
    public const LOG_SEVERITY_LEVEL_MAJOR = 4;

    const MOLLIE_BUTTON_ORDER_TOTAL_REFRESH = 'MOLLIE_BUTTON_ORDER_TOTAL_REFRESH';

    // TODO migrate functions below to separate service
    public static function getStatuses()
    {
        $isAuthorizablePaymentInvoiceOnStatusDefault = Configuration::get(Config::MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS) === Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_DEFAULT;

        return [
            self::MOLLIE_AWAITING_PAYMENT => Configuration::get(self::MOLLIE_STATUS_AWAITING),
            PaymentStatus::STATUS_PAID => Configuration::get(self::MOLLIE_STATUS_PAID),
            OrderStatus::STATUS_COMPLETED => Configuration::get(self::MOLLIE_STATUS_COMPLETED),
            PaymentStatus::STATUS_AUTHORIZED => $isAuthorizablePaymentInvoiceOnStatusDefault ?
                Configuration::get(self::MOLLIE_STATUS_PAID) : Configuration::get(self::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED),
            PaymentStatus::STATUS_CANCELED => Configuration::get(self::MOLLIE_STATUS_CANCELED),
            PaymentStatus::STATUS_EXPIRED => Configuration::get(self::MOLLIE_STATUS_EXPIRED),
            RefundStatus::STATUS_REFUNDED => Configuration::get(self::MOLLIE_STATUS_REFUNDED),
            PaymentStatus::STATUS_OPEN => Configuration::get(self::MOLLIE_STATUS_OPEN),
            PaymentStatus::STATUS_FAILED => Configuration::get(self::MOLLIE_STATUS_CANCELED),
            PaymentStatus::STATUS_PENDING => Configuration::get(self::MOLLIE_STATUS_AWAITING),
            OrderStatus::STATUS_SHIPPING => Configuration::get(self::MOLLIE_STATUS_SHIPPING),
            self::PARTIAL_REFUND_CODE => Configuration::get(self::MOLLIE_STATUS_PARTIAL_REFUND),
            OrderStatus::STATUS_CREATED => Configuration::get(self::MOLLIE_STATUS_AWAITING),
            self::STATUS_PAID_ON_BACKORDER => Configuration::get('PS_OS_OUTOFSTOCK_PAID'),
            self::STATUS_PENDING_ON_BACKORDER => Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'),
            self::STATUS_ON_BACKORDER => Configuration::get('PS_OS_OUTOFSTOCK'),
            self::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED => Configuration::get(self::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED),
            self::MOLLIE_CHARGEBACK => Configuration::get(self::MOLLIE_STATUS_CHARGEBACK),
        ];
    }

    public static function isTestMode()
    {
        $apiKey = EnvironmentUtility::getApiKey();
        if (0 === strpos($apiKey, 'test')) {
            return true;
        }

        return false;
    }

    public static function getMollieOrderStatuses()
    {
        return [
            self::MOLLIE_STATUS_PARTIALLY_SHIPPED,
            self::MOLLIE_STATUS_PARTIAL_REFUND,
            self::MOLLIE_STATUS_AWAITING,
            self::MOLLIE_STATUS_ORDER_COMPLETED,
            self::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED,
            self::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED,
            self::MOLLIE_STATUS_CHARGEBACK,
        ];
    }
}
