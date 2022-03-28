<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation;

use Keboola\Component\Config\BaseConfig;

class Config extends BaseConfig
{
    public function getDestinationKbcUrl(): string
    {
        return $this->getStringValue(['parameters', 'destinationKbcUrl']);
    }

    public function getDestinationKbcToken(): string
    {
        return $this->getStringValue(['parameters', '#destinationKbcToken']);
    }
}
