<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation;

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

    public static function checkGoodDataWritersTokens(): void
    {
    }
}
