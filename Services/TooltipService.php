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
    ) {
    }

    public function renderIdent(string $ident): ?string
    {
        $tooltip = $this->dbService->getTooltipRepository()->findOneByIdent($ident);
        if (!$tooltip) {
            return null;
        }

        return $this->renderContent($tooltip->getContent());
    }

    public function renderContent(string $content): ?string
    {
        return $this->getTwig()->render('@NyroDevNyroCms/AdminTpl/_tooltip.html.php', [
            'content' => nl2br($content),
        ]);
    }
}
