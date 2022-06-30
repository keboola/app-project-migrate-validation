<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation;

use Keboola\Component\Config\BaseConfig;

class Config extends BaseConfig
{
    public function getDestinationKbcUrl(): string
    {
        $url = $this->getStringValue(['parameters', 'destinationKbcUrl']);
        if (substr($url, 0, 4) !== 'http') {
            $url = 'https://' . $url;
        }
        $urlParts = parse_url($url);
        return sprintf('%s://%s/', $urlParts['scheme'], $urlParts['host']);
    }

    public function getDestinationKbcToken(): string
    {
        return $this->getStringValue(['parameters', '#destinationKbcToken']);
    }
}
