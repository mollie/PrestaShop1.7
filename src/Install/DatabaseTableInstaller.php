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

namespace Mollie\Install;

use Db;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class DatabaseTableInstaller implements InstallerInterface
{
    public function install()
    {
        $commands = $this->getCommands();

        foreach ($commands as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return $this->alterTableCommands();
    }

    /**
     * @return array
     */
    private function getCommands()
    {
        $sql = [];

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mollie_payments` (
				`transaction_id`  VARCHAR(64)  NOT NULL PRIMARY KEY,
				`cart_id`         INT(64),
				`order_id`        INT(64),
				`order_reference` VARCHAR(191),
				`mandate_id`      VARCHAR(64),
				`method`          VARCHAR(128) NOT NULL,
				`bank_status`     VARCHAR(64)  NOT NULL,
				`reason`          VARCHAR(64),
				`created_at`      DATETIME     NOT NULL,
				`updated_at`      DATETIME     DEFAULT NULL,
				 INDEX (cart_id, order_reference)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_country` (
				`id_mol_country`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_method`       VARCHAR(64),
				`id_country`      INT(64),
				`all_countries` tinyint
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_payment_method` (
				`id_payment_method`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_method`  VARCHAR(64) NOT NULL,
				`method_name`  VARCHAR(64) NOT NULL,
				`enabled`       TINYINT(1),
				`method` VARCHAR(64),
				`description` VARCHAR(255),
				`is_countries_applicable` TINYINT(1),
				`minimal_order_value` decimal(20,6),
				`max_order_value` decimal(20,6),
				`surcharge` INT(10),
				`surcharge_fixed_amount_tax_excl` decimal(20,6),
				`tax_rules_group_id` INT(10),
				`surcharge_percentage` decimal(20,6),
				`surcharge_limit` decimal(20,6),
				`images_json` TEXT,
				`min_amount` decimal(20,6),
				`max_amount` decimal(20,6),
				`live_environment` TINYINT(1),
				`position` INT(10),
			    `id_shop` INT(64) DEFAULT 1
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_order_payment_fee` (
				`id_mol_order_payment_fee`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_cart` INT(64) NOT NULL,
				`id_order` INT(64) NOT NULL,
				`fee_tax_incl` decimal(20,6) NOT NULL,
				`fee_tax_excl` decimal(20,6) NOT NULL
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_carrier_information` (
				`id_mol_carrier_information`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_carrier` INT(64) NOT NULL,
				`url_source` VARCHAR(64) NOT NULL,
				`custom_url` VARCHAR(255)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_excluded_country` (
				`id_mol_country`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_method`       VARCHAR(64),
				`id_country`      INT(64),
				`all_countries` tinyint
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_pending_order_cart` (
                `id_mol_pending_order_cart`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `order_id` INT(64) NOT NULL,
                `cart_id` INT(64) NOT NULL
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
        ';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_customer` (
                        `id_mol_customer`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
                        `customer_id` VARCHAR(64) NOT NULL,
                        `name` VARCHAR(64) NOT NULL,
                        `email` VARCHAR(64) NOT NULL,
                        `created_at` VARCHAR(64) NOT NULL
                    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
        ';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_pending_order_cart_rule` (
                `id_mol_pending_order_cart_rule` INT(64) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `id_order` VARCHAR(64) NOT NULL,
                `id_cart_rule` VARCHAR(64) NOT NULL,
                `name` VARCHAR(64) NOT NULL,
                `value_tax_incl` decimal(20,6) NOT NULL,
                `value_tax_excl` decimal(20,6) NOT NULL,
                `free_shipping` TINYINT(1) NOT NULL,
                `id_order_invoice` INT(64) NOT NULL
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
        ';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_logs` (
                `id_mollie_log` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `id_log` INT(11),
                `id_shop` INT(11),
                `request` TEXT,
                `response` TEXT,
                `context` TEXT,
                `date_add` DATETIME NOT NULL,
                INDEX (`id_log`),
                INDEX (`id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_payment_method_lang` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `id_method` VARCHAR(64) NOT NULL,
                `id_lang` INT(11),
                `id_shop` INT(11),
                `text` VARCHAR(64), 
                INDEX (`id_method`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return $sql;
    }

    private function alterTableCommands(): bool
    {
        $query = '
            SELECT COUNT(*) > 0 AS count
            FROM information_schema.columns
            WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND table_name = "' . _DB_PREFIX_ . 'mollie_payments" AND column_name = "mandate_id";
        ';

        /* only run if it doesn't exist */
        if (Db::getInstance()->getValue($query)) {
            return true;
        }

        $query = '
            ALTER TABLE ' . _DB_PREFIX_ . 'mollie_payments
            ADD COLUMN mandate_id VARCHAR(64);
        ';

        if (!Db::getInstance()->execute($query)) {
            return false;
        }

        return true;
    }
}
