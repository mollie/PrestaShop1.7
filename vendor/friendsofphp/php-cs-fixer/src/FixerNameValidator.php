<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace MolliePrefix\PhpCsFixer;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class FixerNameValidator
{
    /**
     * @param string $name
     * @param bool   $isCustom
     *
     * @return bool
     */
    public function isValid($name, $isCustom)
    {
        if (!$isCustom) {
            return 1 === \MolliePrefix\PhpCsFixer\Preg::match('/^[a-z][a-z0-9_]*$/', $name);
        }
        return 1 === \MolliePrefix\PhpCsFixer\Preg::match('/^[A-Z][a-zA-Z0-9]*\\/[a-z][a-z0-9_]*$/', $name);
    }
}
