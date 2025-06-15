<?php

namespace NyroDev\NyroCmsBundle\Twig;

use NyroDev\NyroCmsBundle\Services\AdminService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class IconExtension extends AbstractExtension
{
    public function __construct(
        private readonly AdminService $adminService,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('nyrocms_icon', [$this->adminService, 'getIcon']),
        ];
    }
}
