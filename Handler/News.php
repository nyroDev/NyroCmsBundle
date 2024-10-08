<?php

namespace NyroDev\NyroCmsBundle\Handler;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Services\NyroCmsService;
use NyroDev\UtilityBundle\Services\NyrodevService;

class News extends AbstractHandler
{
    public function hasIntro(): bool
    {
        return true;
    }

    public function hasMetas(): bool
    {
        return true;
    }

    public function hasOgs(): bool
    {
        return true;
    }

    public function getAllowedParams(): array
    {
        return [
            'page',
        ];
    }

    protected function _prepareView(Content $content, ?ContentSpec $handlerContent = null, ?string $handlerAction = null): array
    {
        $view = '@NyroDevNyroCms/Handler/news';
        $vars = [
            'content' => $content,
        ];

        $routCfg = $this->get(NyroCmsService::class)->getRouteFor($content);
        $route = $routCfg['route'];
        $routePrm = $routCfg['prm'];

        if ($handlerContent) {
            $view .= 'Show';
            $vars['news'] = $handlerContent;
            $vars['backUrl'] = $this->generateUrl($route, $routePrm);

            $route .= '_spec';
            $routePrm['id'] = $handlerContent->getId();
            $routePrm['title'] = $this->get(NyrodevService::class)->urlify($handlerContent->getTitle());
        } else {
            $page = $this->request->query->get('page', 1);
            $nbPerPage = $this->getParameter('handler_news_perpage', 6);
            $total = $this->getTotalContentSpec($content);
            $nbPages = ceil($total / $nbPerPage);

            if ($page > $nbPages) {
                $page = $nbPages;
            }
            if ($page < 1) {
                $page = 1;
            }

            $pager = new \NyroDev\UtilityBundle\Utility\Pager($this->get(NyrodevService::class), $route, $routePrm, $total, $page, $nbPerPage);

            $results = $this->getContentSpecs($content, $pager->getStart(), $nbPerPage);

            $vars['results'] = $results;
            $vars['pager'] = $pager;
        }

        return [
            'view' => $view.'.html.php',
            'vars' => $vars,
        ];
    }

    public function getFeatured(Content $content, int $nb = 2, bool $forceNb = true)
    {
        $results = $this->getContentSpecs($content, 0, $nb, ['featured' => 1]);

        $count = count($results);
        if ($forceNb && $count < $nb) {
            $results = array_merge($results, $this->getContentSpecs($content, 0, $nb - $count, ['!featured' => 1]));
        }

        return $results;
    }

    public function hasHome(): bool
    {
        return true;
    }

    protected function _prepareHomeView(Content $content): array
    {
        return [
            'view' => '@NyroDevNyroCms/Handler/newsHome.html.php',
            'vars' => [
                'news' => $this->getFeatured($content, 4),
                'handlerContent' => $content,
            ],
        ];
    }
}
