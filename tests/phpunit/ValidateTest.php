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
     * @param array $components
     * @param array $transformations
     * @param array $expectedResults
     * @throws \ReflectionException
     */
    public function testRun(array $components, array $transformations, array $features, array $expectedResults): void
    {
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
            ->method('indexAction')
            ->with(null)
            ->willReturn(['features' => $features['source']]);

        /** @var MockObject $destinationClient */
        $destinationClient = $this->createMock(Client::class);
        $destinationClient
            ->method('indexAction')
            ->with(null)
            ->willReturn(['features' => $features['destination']]);

        /** @var Client $sourceClient */
        /** @var Client $destinationClient */
        $validate = new Validate($sourceClient, $destinationClient);
        $results = $validate->run();
        $this->assertEquals($expectedResults, $results);
    }

    public function runWithoutErrorProvider(): array
    {
        return [
            'empty' => [
                [],
                [],
                // features
                [
                    'source' => ['queuev2'],
                    'destination' =>['queuev2'],
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
                // features
                [
                    'source' => ['queuev2'],
                    'destination' =>['queuev2'],
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
                // features
                [
                    'source' => [],
                    'destination' =>[],
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

                ],
            ],
        ];
    }
}
