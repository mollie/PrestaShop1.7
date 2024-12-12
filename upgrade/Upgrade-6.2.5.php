<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_2_5(Mollie $module): bool
{
    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_payment_method_lang` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_method` VARCHAR(64),
            `id_lang` INT(11),
            `id_shop` INT(11),
            `text` VARCHAR(64) NOT NULL,
            INDEX (`id_method`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

    $result = Db::getInstance()->execute($sql);

    if(!$result) {
        return false;
    }

    try {
        updatePaymentMethodTitles();
    } catch (Exception $e) {
        return false;
    }

    $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'mol_payment_method` DROP COLUMN `title`;';

    return Db::getInstance()->execute($sql);
}

function updatePaymentMethodTitles() {
    $sql = 'SELECT `id_method`, `title` FROM `' . _DB_PREFIX_ . 'mol_payment_method`';

    $methodsList = \Db::getInstance()->executeS($sql);

    foreach ($methodsList as $method) {
        if (empty($method['title'])) {
            continue;
        }

        insertNewTitlesIntoDatabase($method);
    }
}

function insertNewTitlesIntoDatabase($method) {
    foreach (\Shop::getCompleteListOfShopsID() as $idShop) {
        foreach (\Language::getLanguages() as $idLang) {
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'mol_payment_method_lang` (`id_method`, `id_lang`, `id_shop`, `text`)
                    VALUES ("' . pSQL($method['id_method']) . '", ' . (int)$idLang['id_lang'] . ', ' . (int)$idShop . ', "' . pSQL($method['title']) . '")';

            \Db::getInstance()->execute($sql);
        }
    }
}
