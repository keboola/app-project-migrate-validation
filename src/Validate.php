<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation;

use Keboola\StorageApi\Client;
use Keboola\StorageApi\Components;
use Keboola\StorageApi\Options\Components\ListComponentConfigurationsOptions;

class Validate
{
    public const LEGACY_COMPONENTS = [
        'wr-google-drive',
        'table-importer',
        'ex-salesforce',
        'ag-forecastio',
        'ex-youtube',
        'ex-zendesk',
        'restbox',
        'rt-lucky-guess',
        'gooddata-writer',
    ];

    public const KEBOOLA_GOODDATA_WRITER = 'keboola.gooddata-writer';

    private Client $sourceClient;

    private Client $destinationClient;

    public function __construct(Client $sourceClient, Client $destinationClient)
    {
        $this->sourceClient = $sourceClient;
        $this->destinationClient = $destinationClient;
    }

    public function run(): array
    {
        $results = [];

        $sourceComponentsApi = new Components($this->sourceClient);

        $components = $sourceComponentsApi->listComponents();
        $transformations = $sourceComponentsApi->listComponentConfigurations(
            (new ListComponentConfigurationsOptions())->setComponentId('transformation')
        );

        $results = array_merge(
            $results,
            self::checkLegacyComponents($components),
            self::checkBackendTransformations('mysql', $transformations),
            self::checkBackendTransformations('redshift', $transformations),
            self::checkGoodDataWriter($components),
            self::checkProjectsQueue($this->sourceClient, $this->destinationClient),
            self::checkProjectsBackend($this->sourceClient, $this->destinationClient)
        );

        return $results;
    }

    public static function checkLegacyComponents(array $components): array
    {
        $results = [];
        foreach ($components as $component) {
            if (!in_array($component['id'], self::LEGACY_COMPONENTS)) {
                continue;
            }
            $results[] = sprintf(
                '%d configurations of legacy %s component found',
                count($component['configurations']),
                $component['id']
            );
        }
        return $results;
    }

    public static function checkBackendTransformations(string $backendType, array $configs): array
    {
        $transformations = [];
        foreach ($configs as $config) {
            foreach ($config['rows'] as $row) {
                if (!isset($row['configuration']['backend'])) {
                    continue;
                }

                if ($row['configuration']['backend'] !== $backendType) {
                    continue;
                }
                $transformations[] = $row;
            }
        }

        if (empty($transformations)) {
            return [];
        }

        return [
            sprintf(
                '%d %s transformation(s) found',
                count($transformations),
                $backendType
            ),
        ];
    }

    public static function checkGoodDataWriter(array $components): array
    {
        foreach ($components as $component) {
            if ($component['id'] === self::KEBOOLA_GOODDATA_WRITER) {
                return [sprintf(
                    '%d configuration(s) of GoodData writer found',
                    count($component['configurations'])
                )];
            }
        }
        return [];
    }

    private static function checkProjectsQueue(Client $sourceClient, Client $destinationClient): array
    {
        $result = [];
        foreach (['source' => $sourceClient, 'destination' => $destinationClient] as $key => $client) {
            if (!in_array('queuev2', $client->indexAction()['features'])) {
                $result[] = sprintf(
                    '%s project hasn\'t "Queue v2" feature.',
                    ucfirst($key)
                );
            }
        }
        return $result;
    }

    private static function checkProjectsBackend(Client $sourceClient, Client $destinationClient): array
    {
        $result = [];
        $sourceVerifyToken = $sourceClient->verifyToken();
        $destinationVerifyToken = $destinationClient->verifyToken();

        foreach (['hasMysql', 'hasSynapse', 'hasRedshift', 'hasSnowflake', 'hasExasol', 'hasTeradata'] as $backend) {
            if ($sourceVerifyToken['owner'][$backend] !== $destinationVerifyToken['owner'][$backend]) {
                $result[] = sprintf(
                    '%s backend isn\'t same on source (%s) and destination (%s) projects.',
                    substr($backend, 3),
                    $sourceVerifyToken['owner'][$backend] === true ? 'ON' : 'OFF',
                    $destinationVerifyToken['owner'][$backend] === true ? 'ON' : 'OFF'
                );
            }
        }

        return $result;
    }
}
