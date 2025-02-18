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

declare(strict_types=1);

namespace Mollie\Logger;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Exception\NotImplementedException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @deprecated use LoggerInterface instead
 */
class PrestaLogger implements PrestaLoggerInterface
{
    /** @var ConfigurationAdapter */
    private $configuration;

    public function __construct(ConfigurationAdapter $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string|\Stringable $message
     *
     * @deprecated use LoggerInterface instead
     */
    public function emergency($message, array $context = []): void
    {
        $this->logWithPrestaShop('emergency', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     *
     * @deprecated use LoggerInterface instead
     */
    public function alert($message, array $context = []): void
    {
        $this->logWithPrestaShop('alert', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     *
     * @deprecated use LoggerInterface instead
     */
    public function critical($message, array $context = []): void
    {
        $this->logWithPrestaShop('critical', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     *
     * @deprecated use LoggerInterface instead
     */
    public function error($message, array $context = []): void
    {
        if ((int) $this->configuration->get(Config::MOLLIE_DEBUG_LOG) === Config::DEBUG_LOG_NONE) {
            return;
        }

        $uniqueMessage = sprintf('Log ID (%s) | %s', uniqid('', true), $message);

        \PrestaShopLogger::addLog(
            $this->getMessageWithContext($uniqueMessage, $context),
            3
        );
    }

    /**
     * @param string|\Stringable $message
     *
     * @deprecated use LoggerInterface instead
     */
    public function warning($message, array $context = []): void
    {
        $this->logWithPrestaShop('warning', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     *
     * @deprecated use LoggerInterface instead
     */
    public function notice($message, array $context = []): void
    {
        $this->logWithPrestaShop('notice', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     *
     * @deprecated use LoggerInterface instead
     */
    public function info($message, array $context = []): void
    {
        if ((int) $this->configuration->get(Config::MOLLIE_DEBUG_LOG) !== Config::DEBUG_LOG_ALL) {
            return;
        }

        $uniqueMessage = sprintf('Log ID (%s) | %s', uniqid('', true), $message);

        \PrestaShopLogger::addLog(
            $this->getMessageWithContext($uniqueMessage, $context)
        );
    }

    /**
     * @param string|\Stringable $message
     *
     * @deprecated use LoggerInterface instead
     */
    public function debug($message, array $context = []): void
    {
        $this->info($message, $context);
    }

    /**
     * @param string|\Stringable $message
     *
     * @deprecated use LoggerInterface instead
     */
    public function log($level, $message, array $context = []): void
    {
        throw new NotImplementedException('not implemented method');
    }

    private function getMessageWithContext(string $message, array $context = []): string
    {
        $content = json_encode($context);

        return "{$message} . context: {$content}";
    }

    /**
     * Handle logging logic with PrestaShopLogger.
     *
     * @param string|\Stringable $message
     *
     * @deprecated use LoggerInterface instead
     */
    private function logWithPrestaShop(string $level, $message, array $context): void
    {
        throw new NotImplementedException("Method {$level} not implemented.");
    }
}
