<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation;

use Keboola\Component\Config\BaseConfigDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class ConfigDefinition extends BaseConfigDefinition
{
    protected function getParametersDefinition(): ArrayNodeDefinition
    {
        $parametersNode = parent::getParametersDefinition();
        // @formatter:off
        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->children()
            ->scalarNode('destinationKbcUrl')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->scalarNode('#destinationKbcToken')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
        ;
        // @formatter:on
        return $parametersNode;
    }
}
