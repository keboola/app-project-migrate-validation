<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation\Tests;

use Generator;
use Keboola\ProjectMigrateValidation\Config;
use Keboola\ProjectMigrateValidation\ConfigDefinition;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /** @dataProvider kbcUrlDataProvider */
    public function testKbcUrl(array $configData): void
    {
        $config = new Config($configData, new ConfigDefinition());
        self::assertEquals('https://connection.keboola.com/', $config->getDestinationKbcUrl());
    }

    public function kbcUrlDataProvider(): Generator
    {
        yield 'baseUrl' =>[
            [
                'parameters' => [
                    'destinationKbcUrl' => 'https://connection.keboola.com/',
                    '#destinationKbcToken' => 'token',
                ],
            ],
        ];

        yield 'baseUrl2' =>[
            [
                'parameters' => [
                    'destinationKbcUrl' => 'https://connection.keboola.com',
                    '#destinationKbcToken' => 'token',
                ],
            ],
        ];

        yield 'urlWithPath' =>[
            [
                'parameters' => [
                    'destinationKbcUrl' => 'https://connection.keboola.com/admin/project/1',
                    '#destinationKbcToken' => 'token',
                ],
            ],
        ];

        yield 'urlWithoutScheme' =>[
            [
                'parameters' => [
                    'destinationKbcUrl' => 'connection.keboola.com',
                    '#destinationKbcToken' => 'token',
                ],
            ],
        ];
    }
}
