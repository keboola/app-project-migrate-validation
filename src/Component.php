<?php

declare(strict_types=1);

namespace Keboola\ProjectMigrateValidation;

use Keboola\Component\BaseComponent;
use Keboola\Component\UserException;
use Keboola\StorageApi\Client;
use Keboola\StorageApi\Components;

class Component extends BaseComponent
{
    public function run(): void
    {
        $storageClient = new Client([
            'url' => getenv('KBC_URL'),
            'token' => getenv('KBC_TOKEN'),
            'runId' => getenv('KBC_RUNID'),
        ]);

        $validate = new Validate(
            new Components($storageClient),
            GoodDataWriterClientV2::createFromStorageClient($storageClient)
        );

        $results = $validate->run();

        if (empty($results)) {
            $this->getLogger()->info('✔ Project is valid and can be migrated.');
            return;
        }

        foreach ($results as $resultMessage) {
            $this->getLogger()->info($resultMessage);
        }

        throw new UserException('Project cannot be migrated. Please resolve first validation issues listed below.');
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
