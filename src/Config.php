<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation;

use Keboola\Component\Config\BaseConfig;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Config extends BaseConfig
{
    public function getDestinationKbcUrl(): string
    {
        $url = $this->getStringValue(['parameters', 'destinationKbcUrl']);
        if (substr($url, 0, 4) !== 'http') {
            $url = 'https://' . $url;
        }
        $urlParts = parse_url($url);
        foreach (['scheme', 'host'] as $item) {
            if (empty($urlParts[$item])) {
                throw new InvalidConfigurationException(
                    sprintf('Missing "%s", in the destination URL address', $item)
                );
            }
        }
        return sprintf('%s://%s/', $urlParts['scheme'], $urlParts['host']);
    }

    public function getDestinationKbcToken(): string
    {
        return $this->getStringValue(['parameters', '#destinationKbcToken']);
    }
}
