<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation\Tests;

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
     * @param array $expectedResults
     * @throws \ReflectionException
     */
    public function testRun(array $components, array $transformations, array $expectedResults): void
    {
        /** @var Components|MockObject $componentsMock */
        $componentsMock = $this->createMock(Components::class);

        $componentsMock->expects($this->once())
            ->method('listComponents')
            ->willReturn($components);

        $componentsMock->expects($this->once())
            ->method('listComponentConfigurations')
            ->with(
                (new ListComponentConfigurationsOptions())->setComponentId('transformation')
            )
            ->willReturn($transformations);

        $validate = new Validate($componentsMock);
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
                // result
                [
                    '2 configurations of legacy restbox component found',
                    '1 configurations of legacy gooddata-writer component found',
                    '1 mysql transformation(s) found',
                    '1 redshift transformation(s) found',
                    '1 configuration(s) of GoodData writer found',
                ],
            ],
        ];
    }
}
