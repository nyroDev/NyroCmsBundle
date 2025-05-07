<?php

namespace NyroDev\NyroCmsBundle\Repository;

use NyroDev\NyroCmsBundle\Model\Composable;
use NyroDev\NyroCmsBundle\Model\Template;

interface TemplateRepositoryInterface
{
    public function getAvailableTemplatesFor(Composable $row): array;

    public function getTemplateDefaultFor(Composable $row): ?Template;
}
