<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation;

use Keboola\Writer\GoodData\Client;

use Keboola\StorageApi\Client as StorageClient;

class GoodDataWriterClientV2
{
    /** @var Client  */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public static function getKeboolaServiceUrl(array $services, string $serviceId): string
    {
        $foundServices = array_values(array_filter($services, function ($service) use ($serviceId) {
            return $service['id'] === $serviceId;
        }));
        if (empty($foundServices)) {
            throw new \Exception(sprintf('%s service not found', $serviceId));
        }
        return $foundServices[0]['url'];
    }

    public static function createFromStorageClient(StorageClient $sapiClient): self
    {
        $services =  $sapiClient->indexAction()['services'];
        $baseUrl = self::getKeboolaServiceUrl(
            $services,
            'syrup'
        );
        $goodDataWriterClient = Client::factory([
            'url' => sprintf("%s/gooddata-writer", $baseUrl),
            'token' => $sapiClient->getTokenString(),
            'runId' => $sapiClient->getRunId(),
        ]);
        return new self($goodDataWriterClient);
    }

    public function getWriters(): array
    {
        return [];
    }

    public function dummy(): array
    {
        $request = $this->client->get('v2?include=project');
        return $this->client->send($request)->json();
    }

}
