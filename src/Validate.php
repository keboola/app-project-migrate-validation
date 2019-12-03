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
        'gooddata-writer',
    ];

    public const KEBOOLA_GOODDATA_WRITER = 'keboola.gooddata-writer';

    /** @var Components */
    private $componentsApi;

    public function __construct(Components $componentsApi)
    {
        $this->componentsApi = $componentsApi;
    }

    public function run(): array
    {
        $results = [];

        $components = $this->componentsApi->listComponents();
        $results = array_merge(
            $results,
            self::checkLegacyComponents($components)
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
            self::checkGoodDataWriter($components)
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
}
