<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation;

use Keboola\Writer\GoodData\Client;

class GoodDataWriterClientV2
{
    /** @var Client  */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getWriters(): array
    {
        $request = $this->client->get('v2?include=project');
        return $this->client->send($request)->json();
    }
}
