<?php

namespace NyroDev\NyroCmsBundle\Controller;

use NyroDev\NyroCmsBundle\Event\SitemapEvent;
use NyroDev\NyroCmsBundle\Handler\AbstractHandler;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Repository\ContentRepositoryInterface;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\NyroCmsBundle\Services\NyroCmsService;
use NyroDev\UtilityBundle\Controller\AbstractController as NyroDevAbstractController;
use NyroDev\UtilityBundle\Services\ImageService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use NyroDev\UtilityBundle\Services\ShareService;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends NyroDevAbstractController
{
    use Traits\SubscribedServiceTrait;

    abstract protected function getRootHandler(): string;

    public function getContentRepo(): ContentRepositoryInterface
    {
        return $this->get(DbAbstractService::class)->getContentRepository();
    }

    protected ?Content $rootContent = null;

    protected function getRootContent(): Content
    {
        if (is_null($this->rootContent)) {
            $this->rootContent = $this->getContentRepo()->findOneBy(['level' => 0, 'handler' => $this->getRootHandler()]);
            if (!$this->rootContent) {
                throw new RuntimeException('Cannot find rootContent "'.$this->getRootHandler().'"');
            }
        }

        return $this->rootContent;
    }

    protected function setGlobalRootContent(): void
    {
        $this->get(NyroCmsService::class)->setRootContent($this->getRootContent());
    }

    public function index(Request $request, string|array|null $_config = null): Response
    {
        $this->get(NyroCmsService::class)->setRouteConfig($_config);
        $this->setGlobalRootContent();

        return $this->handleIndex($request);
    }

    abstract protected function handleIndex(Request $request): Response;

    protected array $enabledStates = [
        Content::STATE_ACTIVE,
        Content::STATE_INVISIBLE,
    ];

    protected function getContentByUrl(string $url): ?Content
    {
        $url = '/'.$url;
        $root = $this->getRootContent();

        // try direct URL match
        $content = $this->getContentRepo()->findByUrl($url, $root->getId(), $this->enabledStates);
        if ($content) {
            return $content;
        }

        // try by old url
        $oldContents = $this->getContentRepo()->findByLog('url', $url);
        if (count($oldContents)) {
            foreach ($oldContents as $oldContent) {
                if ($oldContent->getRoot() == $root->getId() && in_array($oldContent->getState(), $this->enabledStates)) {
                    return $oldContent;
                }
            }
        }

        throw $this->createNotFoundException();
    }

    public function content(Request $request, string $url, ?string $handler = null, string|array|null $_config = null): Response
    {
        $this->get(NyroCmsService::class)->setRouteConfig($_config);
        $this->setGlobalRootContent();
        $content = $this->getContentByUrl($url);

        return $this->handleContent($request, $content, null, $handler);
    }

    public function contentSpec(Request $request, string $url, string $id, ?string $handler = null, string|array|null $_config = null): Response
    {
        $this->get(NyroCmsService::class)->setRouteConfig($_config);
        $this->setGlobalRootContent();
        $content = $this->getContentByUrl($url);

        $contentSpec = $this->get(DbAbstractService::class)->getContentSpecRepository()->findForAction($id, $content->getContentHandler()->getId(), $this->enabledStates);

        if (!$contentSpec) {
            throw $this->createNotFoundException();
        }

        return $this->handleContent($request, $content, $contentSpec, $handler);
    }

    public function directContent(Request $request, Content $content): Response
    {
        $this->setGlobalRootContent();

        return $this->handleContent($request, $content, null, null, true);
    }

    protected function getFirstTitles(): array
    {
        return [];
    }

    protected function getLastTitles(): array
    {
        return [];
    }

    protected function handleContent(Request $request, Content $content, ?ContentSpec $contentSpec = null, ?string $handlerAction = null, bool $ignoreRedirects = false): Response
    {
        $routePrm = [];
        if ($handlerAction) {
            $routePrm['handler'] = $handlerAction;
        }

        if (!$ignoreRedirects) {
            $redirect = null;

            if ($content->getGoUrl()) {
                $redirect = $this->redirect($content->getGoUrl());
            }

            if (!$redirect) {
                $redirect = $this->get(NyrodevService::class)->redirectIfNotUrl($this->get(NyroCmsService::class)->getUrlFor($contentSpec ? $contentSpec : $content, false, $routePrm), $this->getAllowedParams($content));
            }

            if ($redirect) {
                return $redirect;
            }
        }

        if (!$content->getContent() || 0 === count($content->getContent()) || $content->getRedirectToChildren()) {
            // No text content, search for the first sub content
            $subContents = $this->getContentRepo()->childrenForMenu($content, true);
            if (count($subContents)) {
                return $this->redirect($this->get(NyroCmsService::class)->getUrlFor($subContents[0]));
            }
        }

        $parents = $this->getContentRepo()->getPathForBreacrumb($content, $contentSpec ? false : true);

        $titles = $this->getFirstTitles();
        $activeIds = [];
        foreach ($parents as $parent) {
            $activeIds[$parent->getId()] = $parent->getId();
            $titles[] = $parent->getTitle();
        }

        $titles = array_merge($titles, $this->getLastTitles());

        $activeIds[$content->getId()] = $content->getId();

        $this->get(NyroCmsService::class)->setActiveIds($activeIds);
        $this->get(NyroCmsService::class)->setPathInfoObject($contentSpec ? $contentSpec : $content);

        $handler = null;
        if ($content->getContentHandler()) {
            $handler = $this->get(NyroCmsService::class)->getHandler($content->getContentHandler());
            $handler->init($request);
            $contentHandler = $handler->prepareView($content, $contentSpec, $handlerAction);
            if ($contentHandler instanceof Response) {
                return $contentHandler;
            }
        }

        $title = count($titles) ? implode(', ', $titles) : null;
        $description = $content->getSummary();
        $image = $content->getFirstImage();
        if ($contentSpec) {
            $title = $contentSpec->getTitle().' - '.$content->getTitle().', '.$title;
            $description = $contentSpec->getSummary();
            if ($contentSpec->getFirstImage()) {
                $image = $contentSpec->getFirstImage();
            }
        } else {
            $title = $content->getTitle().($title ? ', '.$title : null);
        }

        $this->setTitle($title);
        $this->setDescription($description);
        if ($image) {
            $this->setImage($this->get(ImageService::class)->resize($this->get(NyrodevService::class)->getPublicDirPath().$image, [
                'name' => 'share',
                'w' => 1000,
                'h' => null,
                'fit' => true,
                'quality' => 80,
            ]));
        }

        $this->get(ShareService::class)->setSharable($content, false);
        if ($contentSpec) {
            $this->get(ShareService::class)->setSharable($contentSpec, false);
        }
        if ($handler && $handler->getSharable()) {
            $this->get(ShareService::class)->setSharable($handler->getSharable(), false);
        }

        return $this->handleContentView($request, $content, $parents, $contentSpec, $handler, $handlerAction);
    }

    protected function getAllowedParams(Content $content): array
    {
        $ret = [];

        if ($content->getContentHandler()) {
            $handler = $this->get(NyroCmsService::class)->getHandler($content->getContentHandler());
            $ret = $handler->getAllowedParams();
        }

        return $ret;
    }

    abstract protected function handleContentView(Request $request, Content $content, array $parents = [], ?ContentSpec $contentSpec = null, ?AbstractHandler $handler = null, $handlerAction = null): Response;

    public function search(Request $request, string|array|null $_config = null): Response
    {
        $this->get(NyroCmsService::class)->setRouteConfig($_config);
        $this->setGlobalRootContent();
        $q = strip_tags($request->query->all('q'));

        $title = $this->trans('public.header.search');
        $results = [
            'total' => 0,
        ];
        if ($q) {
            $this->get(NyroCmsService::class)->setPathInfoSearch($q);
            $title = $this->trans('nyrocms.search.title', ['%q%' => $q]);
            $root = $this->getRootContent();
            $tmpQ = array_filter(array_map('trim', explode(' ', trim($q))));
            $query = $parameters = [];
            foreach ($tmpQ as $k => $v) {
                $query[] = '.contentText LIKE :text'.$k;
                $parameters['text'.$k] = '%'.$v.'%';
            }

            $results['contents'] = $this->getContentRepo()->search($tmpQ, $root->getId(), Content::STATE_ACTIVE);

            $total = count($results['contents']);
            $cts = [];
            $tmp = $this->getContentRepo()->findWithContentHandler($root->getId(), Content::STATE_ACTIVE);
            foreach ($tmp as $t) {
                $cts[$t->getContentHandler()->getId()] = $t;
            }

            $results['contentSpecs'] = [];
            if (count($cts)) {
                $tmpSpecs = $this->get(DbAbstractService::class)->getContentSpecRepository()->search($tmpQ, array_keys($cts), ContentSpec::STATE_ACTIVE);

                foreach ($tmpSpecs as $tmp) {
                    $chId = $tmp->getContentHandler()->getId();
                    if (!isset($results['contentSpecs'][$chId])) {
                        $results['contentSpecs'][$chId] = [
                            'content' => $cts[$chId],
                            'contentSpecs' => [],
                        ];
                    }
                    $results['contentSpecs'][$chId]['contentSpecs'][] = $tmp;
                    ++$total;
                }
            }

            $results['total'] = $total;
        }

        $this->setTitle($title);

        return $this->handleSearchView($request, $q, $results, $title);
    }

    abstract protected function handleSearchView(Request $request, string $q, array $results, string $title): Response;

    public function sitemapIndexXml(string|array|null $_config = null): Response
    {
        $this->get(NyroCmsService::class)->setRouteConfig($_config);
        $this->setGlobalRootContent();
        $urls = [];
        foreach ($this->get(NyroCmsService::class)->getLocales($this->getRootContent()) as $locale) {
            $urls[] = $this->get(NyrodevService::class)->generateUrl($this->getRootHandler().'_sitemapXml', ['_locale' => $locale, '_format' => 'xml'], true);
        }

        return $this->render('@NyroDevNyroCms/Default/sitemapIndex.xml.php', [
            'urls' => $urls,
        ]);
    }

    public function sitemapXml(Request $request, string|array|null $_config = null): Response
    {
        $this->get(NyroCmsService::class)->setRouteConfig($_config);
        $this->setGlobalRootContent();
        $urls = [
            $this->get(NyrodevService::class)->generateUrl($this->getRootHandler().'_homepage'.('fr' == $request->getLocale() ? '_noLocale' : ''), [], true),
        ];

        foreach ($this->getContentRepo()->childrenForMenu($this->getRootContent(), false) as $content) {
            if (!$content->getGoUrl() && !$content->getRedirectToChildren() && ($content->getContent() || $content->getContentHandler() || count($content->getChildren()) === 0)) {
                $urls[] = $this->get(NyroCmsService::class)->getUrlFor($content, true);
            }
            if ($content->getContentHandler()) {
                $contentHandler = $this->get(NyroCmsService::class)->getHandler($content->getContentHandler());
                if ($contentHandler->hasContentSpecUrl()) {
                    $contentSpecs = $this->get(DbAbstractService::class)->getContentSpecRepository()->getForHandler($content->getContentHandler()->getId(), ContentSpec::STATE_ACTIVE);
                    foreach ($contentSpecs as $contentSpec) {
                        $urls[] = $this->get(NyroCmsService::class)->getUrlFor($contentSpec, true, [], $content);
                    }
                }
                $urls = array_merge($urls, $contentHandler->getSitemapXmlUrls($content));
            }
        }

        $sitemapEvent = new SitemapEvent(
            $this->getRootContent(),
            $urls,
            true
        );
        $this->get('event_dispatcher')->dispatch($sitemapEvent, SitemapEvent::SITEMAP_EVENT);

        $response = new Response();
        $response->setPublic();
        $response->setSharedMaxAge(60 * 60);

        return $this->render('@NyroDevNyroCms/Default/sitemap.xml.php', [
            'urls' => $sitemapEvent->urls,
        ], $response);
    }

    protected function setTitle(string $title, bool $addDefault = true): void
    {
        $this->get(ShareService::class)->setTitle($this->get(NyrodevService::class)->inlineText($title).($addDefault ? ' - '.$this->trans(trim($this->getParameter('nyroDev_utility.share.title'))) : ''));
    }

    protected function setDescription(string $description): void
    {
        $this->get(ShareService::class)->setDescription($this->get(NyrodevService::class)->inlineText($description));
    }

    protected function setImage(?string $image = null): void
    {
        if ($image) {
            $this->get(ShareService::class)->setImage($image);
        }
    }
}
