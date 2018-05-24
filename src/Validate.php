<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation;

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
    ];

    public const KEBOOLA_GOOD_DATA_TOKENS = [
        'keboola_production',
        'keboola_demo',
    ];

    /** @var Components */
    private $componentsApi;

    /** @var GoodDataWriterClientV2 */
    private $goodDataWriterClient;

    public function __construct(
        Components $componentsApi,
        GoodDataWriterClientV2 $goodDataWriterClient
    ) {
        $this->componentsApi = $componentsApi;
        $this->goodDataWriterClient = $goodDataWriterClient;
    }

    public function run()
    {
        $results = [];

        $results = array_merge(
            $results,
            self::checkLegacyComponents($this->componentsApi->listComponents())
        );

        $transformations = $this->componentsApi->listComponentConfigurations(
            (new ListComponentConfigurationsOptions())->setComponentId('transformation')
        );
        $results = array_merge(
            $results,
            self::checkBackendTransformations('mysql', $transformations)
        );
        $results = array_merge(
            $results,
            self::checkBackendTransformations('redshift', $transformations)
        );

        $results = array_merge(
            $results,
            self::checkGoodDataWritersTokens($this->goodDataWriterClient->getWriters())
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

    public static function checkGoodDataWritersTokens(array $writers): array
    {
        $results = [];
        foreach ($writers as $writer) {
            if (in_array($writer['project']['authToken'], self::KEBOOLA_GOOD_DATA_TOKENS)) {
                continue;
            }
            $results[] = sprintf(
                'GoodData writer %s is using custom auth token: %s',
                $writer['id'],
                $writer['project']['authToken']
            );
        }
        return $results;
    }
}
