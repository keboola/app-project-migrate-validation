<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation\Tests;

use Keboola\ProjectMigrateValidation\GoodDataWriterClientV2;
use Keboola\ProjectMigrateValidation\Validate;
use Keboola\StorageApi\Options\Components\ListComponentConfigurationsOptions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Keboola\StorageApi\Components;

class ValidateTest extends TestCase
{

    /**
     * @dataProvider runWithoutErrorProvider
     * @param array $components
     * @param array $transformations
     * @param array $writers
     * @param array $expectedResults
     * @throws \ReflectionException
     */
    public function testRun(array $components, array $transformations, array $writers, array $expectedResults): void
    {
        /** @var Components|MockObject $componentsMock */
        $componentsMock = $this->createMock(Components::class);

        /** @var GoodDataWriterClientV2|MockObject $goodDataWriterMock */
        $goodDataWriterMock = $this->createMock(GoodDataWriterClientV2::class);

        $componentsMock->expects($this->once())
            ->method('listComponents')
            ->willReturn($components);

        $componentsMock->expects($this->once())
            ->method('listComponentConfigurations')
            ->with(
                (new ListComponentConfigurationsOptions())->setComponentId('transformation')
            )
            ->willReturn($transformations);


        $goodDataWriterMock->expects($this->once())
            ->method('getWriters')
            ->willReturn($writers);


        $validate = new Validate($componentsMock, $goodDataWriterMock);
        $results = $validate->run();
        $this->assertEquals($expectedResults, $results);
    }

    public function runWithoutErrorProvider(): array
    {
        return [
            'empty' => [
                [],
                [],
                [],
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
                // writers
                [
                    [
                        'id' => 'martin',
                        'status' => 'ready',
                        'project' => [
                            'id' => '234',
                            'pid' => 'xrz',
                            'active' => true,
                            'main' => true,
                            'authToken' => 'keboola_demo',
                        ],
                    ],
                    [
                        'id' => 'custom',
                        'status' => 'ready',
                        'project' => [
                            'id' => '234',
                            'pid' => 'xrz',
                            'active' => true,
                            'main' => true,
                            'authToken' => 'keboola_production',
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
                // writers
                [
                    [
                        'id' => 'martin',
                        'status' => 'ready',
                        'project' => [
                            'id' => '234',
                            'pid' => 'xrz',
                            'active' => true,
                            'main' => true,
                            'authToken' => 'keboola_demo',
                        ],
                    ],
                    [
                        'id' => 'custom',
                        'status' => 'ready',
                        'project' => [
                            'id' => '234',
                            'pid' => 'xrz',
                            'active' => true,
                            'main' => true,
                            'authToken' => 'SZVXXX',
                        ],
                    ],
                ],
                // result
                [
                    '2 configurations of legacy restbox component found',
                    '1 mysql transformation(s) found',
                    '1 redshift transformation(s) found',
                    'GoodData writer custom is using custom auth token: SZVXXX',
                ],
            ],
        ];
    }
}
