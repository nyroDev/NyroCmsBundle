<?php

namespace NyroDev\NyroCmsBundle\Handler;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Services\NyroCmsService;

class Sitemap extends AbstractHandler
{
    protected function _prepareView(Content $content, ContentSpec $handlerContent = null, ?string $handlerAction = null): array
    {
        $root = $this->getContentById($content->getRoot());

        return [
            'view' => '@NyroDevNyroCms/Handler/sitemap.html.php',
            'vars' => [
                'content' => $content,
                'contents' => $this->getHierarchy($root),
                'isRoot' => true,
            ],
        ];
    }

    protected function getHierarchy(Content $content): array
    {
        $ret = [];

        foreach ($this->getContentRepo()->childrenForMenu($content, true) as $sub) {
            $contents = $this->getHierarchy($sub);

            if ($sub->getContentHandler()) {
                $contentHandler = $this->get(NyroCmsService::class)->getHandler($sub->getContentHandler());
                $contents = array_merge($contents, $contentHandler->getSitemapUrls($sub));
            }

            $ret[] = [
                'content' => $sub,
                'contents' => $contents,
            ];
        }

        return $ret;
    }
}
