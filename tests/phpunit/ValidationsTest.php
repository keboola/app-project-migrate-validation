<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation\Tests;

use Keboola\ProjectMigrateValidation\Validate;
use PHPUnit\Framework\TestCase;

class ValidationsTest extends TestCase
{

    /**
     * @dataProvider transformationsBackendProvider
     * @param array $transformations
     * @param string $backendType
     * @param array $expectedResult
     */
    public function testTransformationsBackends(
        array $transformations,
        string $backendType,
        array $expectedResult
    ): void {
        $this->assertEquals($expectedResult, Validate::checkBackendTransformations($backendType, $transformations));
    }

    public function transformationsBackendProvider(): array
    {
        return [
            [
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
                                    'backend' => 'mysql',
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
                'mysql',
                [
                    '1 mysql transformation(s) found',
                ],
            ],
        ];
    }

    /**
     * @dataProvider legacyComponentsProvider
     * @param array $components
     * @param array $expectedResult
     */
    public function testLegacyComponents(array $components, array $expectedResult): void
    {
        $this->assertEquals($expectedResult, Validate::checkLegacyComponents($components));
    }

    public function legacyComponentsProvider(): array
    {
        return [
            [
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
                [
                    '2 configurations of legacy restbox component found',
                ],
            ],
        ];
    }
}
