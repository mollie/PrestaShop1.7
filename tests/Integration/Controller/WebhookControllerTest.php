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

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Mollie\Service\TransactionService;
use Mollie\Tests\Integration\BaseTestCase;
use Prestashop\ModuleLibGuzzleAdapter\ClientFactory;

class WebhookControllerTest extends BaseTestCase
{
    /** @var Client */
    private $client;

    /** @var TransactionService */
    private $transactionService;

    /** @var Mollie */
    private $module;

    protected function setUp()
    {
        parent::setUp();

        $this->client = (new ClientFactory())->getClient([
            'base_uri' => __PS_BASE_URI__,
            'timeout' => 15,
        ]);
    }

    /**
     * @dataProvider webhookDataProvider
     */
    public function testUnsuccessfulWebhookResponse($url, $expectedStatusCode, $expectedResponseBody)
    {
        $result = $this->client->sendRequest(
            new Request('GET', $url)
        );

        $this->assertEquals($expectedStatusCode, $result->getStatusCode());
        $this->assertEquals($expectedResponseBody, $result->getBody()->getContents());
    }

    public function webhookDataProvider(): array
    {
        return [
            'No API Key' => [
                'url' => \Context::getContext()->link->getModuleLink('mollie', 'webhook'),
                'expectedStatusCode' => 401,
                'expectedResponseBody' => '{"success":false,"errors":["Unauthorized"],"data":[]}',
            ],
        ];
    }
}
