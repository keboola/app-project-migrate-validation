<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation;

use Keboola\Component\BaseComponent;
use Keboola\Component\UserException;
use Keboola\StorageApi\Client;

class Component extends BaseComponent
{
    protected function run(): void
    {
        $sourceStorageClient = new Client([
            'url' => getenv('KBC_URL'),
            'token' => getenv('KBC_TOKEN'),
            'runId' => getenv('KBC_RUNID'),
        ]);

        $destinationStorageClient = new Client([
            'url' => $this->getConfig()->getDestinationKbcUrl(),
            'token' => $this->getConfig()->getDestinationKbcToken(),
        ]);

        $validate = new Validate(
            $sourceStorageClient,
            $destinationStorageClient
        );

        $results = $validate->run();

        if (empty($results)) {
            $this->getLogger()->info('✔ Project is valid and can be migrated.');
            return;
        }

        foreach ($results as $resultMessage) {
            $this->getLogger()->info($resultMessage);
        }

        throw new UserException('Project cannot be migrated. Please resolve validation issues listed below first.');
    }

    public function getConfig(): Config
    {
        /** @var Config $config */
        $config = parent::getConfig();
        return  $config;
    }

    protected function getConfigClass(): string
    {
        return Config::class;
    }

    protected function getConfigDefinitionClass(): string
    {
        return ConfigDefinition::class;
    }
}
