<?php

namespace NyroDev\NyroCmsBundle\Services;

use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Services\AbstractService as NyroDevAbstractService;
use NyroDev\UtilityBundle\Services\Traits\TwigServiceableTrait;

class TooltipService extends NyroDevAbstractService
{
    use TwigServiceableTrait;

    public function __construct(
        private readonly DbAbstractService $dbService,
        private readonly AdminService $adminService,
    ) {
    }

    public function renderIdent(string $ident): ?string
    {
        $tooltip = $this->dbService->getTooltipRepository()->findOneByIdent($ident);
        if (!$tooltip) {
            return null;
        }

        $editUrl = null;
        if ($this->adminService->isSuperAdmin()) {
            $editUrl = $this->adminService->generateUrl('nyrocms_admin_data_tooltip_edit', ['id' => $tooltip->getId()]);
        }

        return $this->renderContent($tooltip->getContent(), $editUrl);
    }

    public function renderContent(string $content, ?string $editUrl): ?string
    {
        return $this->getTwig()->render('@NyroDevNyroCms/AdminTpl/_tooltip.html.php', [
            'content' => nl2br($content),
            'editUrl' => $editUrl,
        ]);
    }
}
