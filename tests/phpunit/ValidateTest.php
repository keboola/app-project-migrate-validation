<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation\Tests;

use Keboola\ProjectMigrateValidation\Validate;
use Keboola\StorageApi\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidateTest extends TestCase
{

    /**
     * @dataProvider runWithoutErrorProvider
     */
    public function testRun(
        array $components,
        array $transformations,
        array $verifyToken,
        array $expectedResults
    ): void {
        /** @var MockObject $sourceClient */
        $sourceClient = $this->createMock(Client::class);
        $sourceClient
            ->expects($this->exactly(2))
            ->method('apiGet')
            ->withConsecutive(
                ['components?include='],
                ['components/transformation/configs?'],
            )
            ->willReturnOnConsecutiveCalls(
                $components,
                $transformations
            )
        ;

        $sourceClient
            ->expects($this->exactly(2))
            ->method('verifyToken')
            ->willReturn($verifyToken['source']);

        /** @var MockObject $destinationClient */
        $destinationClient = $this->createMock(Client::class);

        $destinationClient
            ->expects($this->exactly(2))
            ->method('verifyToken')
            ->willReturn($verifyToken['destination']);

        /** @var Client $sourceClient */
        /** @var Client $destinationClient */
        $validate = new Validate($sourceClient, $destinationClient);
        $results = $validate->run();
        $this->assertEquals($expectedResults, $results);
    }

    public function runWithoutErrorProvider(): array
    {
        return [
            'empty-components' => [
                [],
                [],
                [
                    'source' => [
                        'owner' => [
                            'features' => [
                                'queuev2',
                            ],
                            'hasMysql' => false,
                            'hasSynapse' => false,
                            'hasRedshift' => false,
                            'hasSnowflake' => true,
                            'hasExasol' => false,
                            'hasTeradata' => false,
                        ],
                    ],
                    'destination' =>[
                        'owner' => [
                            'features' => [
                                'queuev2',
                            ],
                            'hasMysql' => false,
                            'hasSynapse' => false,
                            'hasRedshift' => false,
                            'hasSnowflake' => true,
                            'hasExasol' => false,
                            'hasTeradata' => false,
                        ],
                    ],
                ],
                [],
            ],
            'ok' => [
                // components
                [
                    [
                        'id' => 'keboola.ex-db-mysql',
                        'name' => 'restbox',
                        'configurations' => [
                            [
                                'id' => '123',
                                'name' => 'xyz',
                            ],
                            [
                                'id' => '1234',
                                'name' => 'abc',
                            ],
                        ],
                    ],
                    [
                        'id' => 'blueskydigital.wr-sftp-webdav',
                        'name' => 'first',
                        'configurations' => [
                            [
                                'id' => '576624',
                                'name' => 'webdav',
                            ],
                        ],
                    ],
                ],
                // transformations
                [
                    [
                        'id' => 'abc',
                        'rows' => [
                            [
                                'id' => '234',
                                'configuration' => [],
                            ],
                            [
                                'id' => '235',
                                'configuration' => [
                                    'backend' => 'snowflake',
                                ],
                            ],
                            [
                                'id' => '236',
                                'configuration' => [
                                    'backend' => 'snowflake',
                                ],
                            ],
                            [
                                'id' => '237',
                                'configuration' => [
                                    'backend' => 'snowflake',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'source' => [
                        'owner' => [
                            'features' => [
                                'queuev2',
                            ],
                            'hasMysql' => false,
                            'hasSynapse' => false,
                            'hasRedshift' => false,
                            'hasSnowflake' => true,
                            'hasExasol' => false,
                            'hasTeradata' => false,
                        ],
                    ],
                    'destination' =>[
                        'owner' => [
                            'features' => [
                                'queuev2',
                            ],
                            'hasMysql' => false,
                            'hasSynapse' => false,
                            'hasRedshift' => false,
                            'hasSnowflake' => true,
                            'hasExasol' => false,
                            'hasTeradata' => false,
                        ],
                    ],
                ],
                // result
                [],

            ],
            'not-valid' => [
                // components
                [
                    [
                        'id' => 'restbox',
                        'name' => 'restbox',
                        'configurations' => [
                            [
                                'id' => '123',
                                'name' => 'xyz',
                            ],
                            [
                                'id' => '1234',
                                'name' => 'abc',
                            ],
                        ],
                    ],
                    [
                        'id' => 'blueskydigital.wr-sftp-webdav',
                        'name' => 'first',
                        'configurations' => [
                            [
                                'id' => '576624',
                                'name' => 'webdav',
                            ],
                        ],
                    ],
                    [
                        'id' => 'gooddata-writer',
                        'name' => 'first',
                        'configurations' => [
                            [
                                'id' => '576624',
                                'name' => 'webdav',
                            ],
                        ],
                    ],
                    [
                        'id' => 'keboola.gooddata-writer',
                        'name' => 'writer',
                        'configurations' => [
                            [
                                'id' => '12345656',
                                'name' => 'example',
                            ],
                        ],
                    ],
                ],
                // transformations
                [
                    [
                        'id' => 'abc',
                        'rows' => [
                            [
                                'id' => '234',
                                'configuration' => [],
                            ],
                            [
                                'id' => '235',
                                'configuration' => [
                                    'backend' => 'mysql',
                                ],
                            ],
                            [
                                'id' => '236',
                                'configuration' => [
                                    'backend' => 'snowflake',
                                ],
                            ],
                            [
                                'id' => '237',
                                'configuration' => [
                                    'backend' => 'redshift',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'source' => [
                        'owner' => [
                            'features' => [],
                            'hasMysql' => false,
                            'hasSynapse' => false,
                            'hasRedshift' => false,
                            'hasSnowflake' => true,
                            'hasExasol' => false,
                            'hasTeradata' => false,
                        ],
                    ],
                    'destination' =>[
                        'owner' => [
                            'features' => [],
                            'hasMysql' => false,
                            'hasSynapse' => true,
                            'hasRedshift' => false,
                            'hasSnowflake' => false,
                            'hasExasol' => false,
                            'hasTeradata' => false,
                        ],
                    ],
                ],
                // result
                [
                    '2 configurations of legacy restbox component found',
                    '1 configurations of legacy gooddata-writer component found',
                    '1 mysql transformation(s) found',
                    '1 redshift transformation(s) found',
                    '1 configuration(s) of GoodData writer found',
                    'Source project hasn\'t "Queue v2" feature.',
                    'Destination project hasn\'t "Queue v2" feature.',
                    'Synapse backend isn\'t same on source (OFF) and destination (ON) projects.',
                    'Snowflake backend isn\'t same on source (ON) and destination (OFF) projects.',
                ],
            ],
        ];
    }
}
