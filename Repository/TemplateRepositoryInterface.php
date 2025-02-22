<?php

namespace NyroDev\NyroCmsBundle\Repository;

use NyroDev\NyroCmsBundle\Model\Composable;

interface TemplateRepositoryInterface
{
    public function getAvailableTemplatesFor(Composable $row): array;
}
