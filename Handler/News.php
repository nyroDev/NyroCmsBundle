<?php

namespace NyroDev\NyroCmsBundle\Handler;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;

class News extends AbstractHandler
{
    public function hasIntro()
    {
        return true;
    }

    public function hasMetas()
    {
        return true;
    }

    public function hasOgs()
    {
        return true;
    }

    public function getAllowedParams()
    {
        return array(
            'page',
        );
    }

    protected function _prepareView(Content $content, ContentSpec $handlerContent = null, $handlerAction = null)
    {
        $view = 'NyroDevNyroCmsBundle:Handler:news';
        $vars = array(
            'content' => $content,
        );

        $routCfg = $this->get('nyrocms')->getRouteFor($content);
        $route = $routCfg['route'];
        $routePrm = $routCfg['prm'];

        if ($handlerContent) {
            $view .= 'Show';
            $vars['news'] = $handlerContent;
            $vars['backUrl'] = $this->generateUrl($route, $routePrm);

            $route .= '_spec';
            $routePrm['id'] = $handlerContent->getId();
            $routePrm['title'] = $this->get('nyrodev')->urlify($handlerContent->getTitle());
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

            $pager = new \NyroDev\UtilityBundle\Utility\Pager($this->get('nyrodev'), $route, $routePrm, $total, $page, $nbPerPage);

            $results = $this->getContentSpecs($content, $pager->getStart(), $nbPerPage);

            $vars['results'] = $results;
            $vars['pager'] = $pager;
        }

        return array(
            'view' => $view.'.html.php',
            'vars' => $vars,
        );
    }

    public function getFeatured(Content $content, $nb = 2, $forceNb = true)
    {
        $results = $this->getContentSpecs($content, 0, $nb, array('featured' => 1));

        $count = count($results);
        if ($forceNb && $count < $nb) {
            $results = array_merge($results, $this->getContentSpecs($content, 0, $nb - $count, array('!featured' => 1)));
        }

        return $results;
    }

    public function hasHome()
    {
        return true;
    }

    protected function _prepareHomeView(Content $content)
    {
        return array(
            'view' => 'NyroDevNyroCmsBundle:handler:newsHome.html.php',
            'vars' => array(
                'news' => $this->getFeatured($content, 4),
                'handlerContent' => $content,
            ),
        );
    }
}
