<?php

namespace NyroDev\NyroCmsBundle\Services;

use NyroDev\NyroCmsBundle\Event\UrlGenerationEvent;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentHandler;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Model\Sharable;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Services\AbstractService as nyroDevAbstractService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use NyroDev\UtilityBundle\Services\ShareService;

class NyroCmsService extends nyroDevAbstractService
{
    protected $handlers = array();

    public function getHandler(ContentHandler $contentHandler)
    {
        if (!isset($this->handlers[$contentHandler->getId()])) {
            $class = $contentHandler->getClass();
            if (!class_exists($class)) {
                throw new \RuntimeException($class.' not found when trying to create handler.');
            }

            if (0 === strpos(get_class($contentHandler), 'Proxies')) {
                $contentHandler = $this->get(DbAbstractService::class)->getContentHandlerRepository()->find($contentHandler->getId());
            }

            $this->handlers[$contentHandler->getId()] = new $class($contentHandler, $this->container);
        }

        return $this->handlers[$contentHandler->getId()];
    }

    protected $routeConfig;

    public function setRouteConfig($routeConfig)
    {
        $this->routeConfig = $routeConfig;
    }

    public function getRouteConfig()
    {
        return $this->routeConfig;
    }

    protected $activeIds = array();

    public function setActiveIds($activeIds)
    {
        $this->activeIds = $activeIds;
    }

    public function getActiveIds()
    {
        return $this->activeIds;
    }

    protected $rootContent;

    public function setRootContent(Content $content)
    {
        $this->rootContent = $content;
    }

    public function getRootContent()
    {
        return $this->rootContent;
    }

    protected $contentRoots = array();

    /**
     * @param type $id
     *
     * @return \NyroDev\NyroCmsBundle\Entity\Content
     */
    public function getContentRoot($id)
    {
        if (!isset($this->contentRoots[$id])) {
            $this->contentRoots[$id] = $this->get(DbAbstractService::class)->getContentRepository()->find($id);
        }

        return $this->contentRoots[$id];
    }

    public function getUrlFor($object, $absolute = false, array $prm = array(), $parent = null)
    {
        $routeCfg = $this->getRouteFor($object, $prm, $parent);

        return $routeCfg['route'] ? $this->generateUrl($routeCfg['route'], $routeCfg['prm'], $absolute || $routeCfg['absolute']) : '#';
    }

    public function getRouteFor($object, array $prm = array(), $parent = null)
    {
        $routeName = null;
        $absolute = false;

        if ($object instanceof Content) {
            $root = $this->getContentRoot($object->getRoot());
            if ($root->getId() == $object->getId()) {
                $routeName = $root->getHandler().'_homepage';
            } else {
                $routeName = $root->getHandler().'_content';
                if (isset($prm['handler']) && $prm['handler']) {
                    $routeName .= '_handler';
                }
                $prm = array_merge($prm, array(
                    'url' => trim($object->getUrl(), '/'),
                ));
            }
        } elseif ($object instanceof ContentSpec) {
            $parent = is_null($parent) ? $object->getParent() : $parent;
            if (!$this->getHandler($object->getContentHandler())->hasContentSpecUrl()) {
                return $this->getRouteFor($parent, $prm);
            }

            $root = $this->getContentRoot($parent->getRoot());
            $routeName = $root->getHandler().'_content_spec';
            if (isset($prm['handler']) && $prm['handler']) {
                $routeName .= '_handler';
            }

            $title = $object->getTitle();
            if ($this->disabledLocaleUrls($object->getTranslatableLocale())) {
                $curLoc = $object->getTranslatableLocale();

                $object->setTranslatableLocale($this->getDefaultLocale($object));
                $this->get(DbAbstractService::class)->refresh($object);
                $title = $object->getTitle();

                $object->setTranslatableLocale($curLoc);
                $this->get(DbAbstractService::class)->refresh($object);
            }

            $prm = array_merge($prm, array(
                'url' => trim($parent->getUrl(), '/'),
                'id' => $object->getId(),
                'title' => $this->get(NyrodevService::class)->urlify($title),
            ));
        }

        if ($routeName) {
            $event = new UrlGenerationEvent($routeName, $prm, $absolute, $object, $parent);
            $this->get('event_dispatcher')->dispatch(UrlGenerationEvent::OBJECT_URL, $event);
            $routeName = $event->getRouteName();
            $prm = $event->getRoutePrm();
            $absolute = $event->getAbsolute();
        }

        return array(
            'route' => $routeName,
            'prm' => $prm,
            'absolute' => $absolute,
        );
    }

    public function getDateFormOptions()
    {
        return array(
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'attr' => array(
                'class' => 'datepicker',
            ),
        );
    }

    public function getDateTimeFormOptions($stepMinutes = 5)
    {
        return array(
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy HH:mm',
            'attr' => array(
                'class' => 'datepicker datetimepicker',
                'data-stepminute' => $stepMinutes,
            ),
        );
    }

    public function sendEmail($to, $subject, $content, $from = null, $locale = null, Content $dbContent = null)
    {
        $response = $this->get('templating')->renderResponse($this->getParameter('nyrocms.email.global_template'), array(
            'stylesTemplate' => $this->getParameter('nyrocms.email.styles_template'),
            'bodyTemplate' => $this->getParameter('nyrocms.email.body_template'),
            'subject' => $subject,
            'locale' => $locale ? $locale : $this->getLocale(),
            'content' => $content,
            'dbContent' => $dbContent,
        ));
        $html = $response->getContent();
        $text = $this->get(NyrodevService::class)->html2text($html);

        if (!$from) {
            $from = $this->getParameter('noreply_email');
        }

        $msg = $this->get('mailer')->createMessage()
                    ->setTo($to)
                    ->setSubject($subject)
                    ->setFrom($from)
                    ->setBody($text)
                    ->addPart($html, 'text/html');

        return $this->get('mailer')->send($msg);
    }

    public function getLocale()
    {
        return $this->getRequest()->getLocale();
    }

    public function getDefaultLocale($rootContent = null)
    {
        $isContentRootable = false;
        if ($rootContent) {
            $isContentRootable = $rootContent instanceof \NyroDev\NyroCmsBundle\Model\ContentRootable;
            if ($isContentRootable) {
                $rootContent = $rootContent->getVeryParent();
            }
        }
        if ($isContentRootable && $rootContent && $rootContent->getLocales()) {
            $tmp = explode('|', $rootContent->getLocales());

            return $tmp[0];
        } else {
            return $this->getParameter('locale');
        }
    }

    public function getLocales($rootContent = null, $asString = false)
    {
        $isContentRootable = false;
        if ($rootContent) {
            $isContentRootable = $rootContent instanceof \NyroDev\NyroCmsBundle\Model\ContentRootable;
            if ($isContentRootable) {
                $rootContent = $rootContent->getVeryParent();
            }
        }
        $locales = $isContentRootable && $rootContent && $rootContent->getLocales() ? $rootContent->getLocales() : $this->getParameter('locales');

        return $asString ? $locales : explode('|', $locales);
    }

    public function getLocaleNames($rootContent = null, $prefixTranslation = null)
    {
        $names = $this->container->getParameter('localeNames');
        $ret = array();
        foreach ($this->getLocales($rootContent) as $locale) {
            if (isset($names[$locale])) {
                $ret[$locale] = $prefixTranslation ? $this->trans($prefixTranslation.'.'.$locale) : $names[$locale];
            }
        }

        return $ret;
    }

    protected $pathInfoObject;

    public function setPathInfoObject($object)
    {
        $this->pathInfoObject = $object;
    }

    protected $pathInfoSearch;

    public function setPathInfoSearch($search)
    {
        $this->pathInfoSearch = $search;
    }

    public function getPathInfo()
    {
        $request = $this->getRequest();

        return array(
            'route' => $request->get('_route'),
            'routePrm' => $request->get('_route_params'),
            'object' => $this->pathInfoObject,
        );
    }

    public function getLocalesUrl($pathInfo, $absolute = false, $onlyLangs = null)
    {
        $ret = array();
        $isObjectPage = isset($pathInfo['object']) && $pathInfo['object'];

        $rootContent = $this->getRootContent();
        $objectLocale = $isObjectPage ? $pathInfo['object'] : $rootContent;

        $prefixRoute = $rootContent ? $rootContent->getHandler() : null;

        $defaultLocale = $this->getDefaultLocale($objectLocale);
        $locales = $this->getLocales($objectLocale);
        $curLocale = $this->getLocale();
        if ($onlyLangs && !is_array($onlyLangs)) {
            $onlyLangs = explode(',', $onlyLangs);
        }

        foreach ($locales as $locale) {
            if ($locale != $curLocale && ($locale == $defaultLocale || empty($onlyLangs) || in_array($locale, $onlyLangs))) {
                $prm = array('_locale' => $locale);
                $routeName = null;
                $routePrm = array();
                if (!$pathInfo['route']) {
                    $routeName = $prefixRoute.'_homepage_noLocale';
                    $routePrm = array();
                } elseif ($pathInfo['route'] == $prefixRoute.'_homepage_noLocale' && $curLocale == $defaultLocale) {
                    $routeName = $prefixRoute.'_homepage';
                    $routePrm = array_merge($pathInfo['routePrm'], $prm);
                } elseif ($pathInfo['route'] == $prefixRoute.'_homepage' && $locale == $defaultLocale) {
                    $routeName = $prefixRoute.'_homepage_noLocale';
                    $routePrm = array();
                } elseif ($this->pathInfoSearch && preg_match('/_search$/', $pathInfo['route'])) {
                    $routeName = $pathInfo['route'];
                    $routePrm = array_merge($pathInfo['routePrm'], $prm, array('q' => $this->pathInfoSearch));
                } elseif ($isObjectPage) {
                    $pathInfo['object']->setTranslatableLocale($locale);
                    $this->get(DbAbstractService::class)->refresh($pathInfo['object']);
                    $ret[$locale] = $this->getUrlFor($pathInfo['object'], $absolute, array_merge($pathInfo['routePrm'], $prm));
                } else {
                    $routeName = $pathInfo['route'];
                    $routePrm = array_merge($pathInfo['routePrm'], $prm);
                }
                if (!is_null($routeName)) {
                    $event = new UrlGenerationEvent($routeName, $routePrm, $absolute);
                    $this->get('event_dispatcher')->dispatch(UrlGenerationEvent::LOCALES_URL, $event);
                    $ret[$locale] = $this->generateUrl($event->getRouteName(), $event->getRoutePrm(), $event->getAbsolute());
                }
            }
        }

        if ($isObjectPage) {
            $pathInfo['object']->setTranslatableLocale($curLocale);
            $this->get(DbAbstractService::class)->refresh($pathInfo['object']);
        }

        return $ret;
    }

    public function disabledLocaleUrls($locale)
    {
        $ret = false;
        $disabled = $this->getParameter('nyrocms.disabled_locale_urls');
        if (is_array($disabled)) {
            $ret = in_array($locale, $disabled);
        } elseif ($disabled) {
            $ret = true;
        }

        return $ret;
    }

    protected $foundHandlers;

    public function getFoundHandlers()
    {
        if (is_null($this->foundHandlers)) {
            $this->foundHandlers = array();
            dump($GLOBALS['loader']);
            if (isset($GLOBALS['loader']) && $GLOBALS['loader'] instanceof \Composer\Autoload\ClassLoader) {
                $classes = array_keys($GLOBALS['loader']->getClassMap());
                foreach ($classes as $class) {
                    if (strpos($class, '\\Handler\\') && is_subclass_of($class, \NyroDev\NyroCmsBundle\Handler\AbstractHandler::class, true)) {
                        $this->foundHandlers[] = '\\'.$class;
                    }
                }
                sort($this->foundHandlers);
            }
        }

        return $this->foundHandlers;
    }

    public function inlineText($text)
    {
        return preg_replace('/\s\s+/', ' ', preg_replace('/\s/', ' ', trim($text, " \t\n\r\0\x0B:·-")));
    }

    public function setSharableContent(Sharable $sharable)
    {
        $shareService = $this->get(ShareService::class);
        $shareService->setTitle($this->inlineText($sharable.''));

        if ($sharable->getMetaTitle()) {
            $shareService->setTitle($this->inlineText($sharable->getMetaTitle()));
        }
        if ($sharable->getMetaDescription()) {
            $shareService->setDescription($this->inlineText($sharable->getMetaDescription()));
        }
        if ($sharable->getMetaKeywords()) {
            $shareService->setKeywords($this->inlineText($sharable->getMetaKeywords()));
        }
        if ($sharable->getOgTitle()) {
            $shareService->set('og:title', $this->inlineText($sharable->getOgTitle()), true);
            $shareService->set('twitter:title', $this->inlineText($sharable->getOgTitle()));
        }
        if ($sharable->getOgDescription()) {
            $shareService->set('og:description', $this->inlineText($sharable->getOgDescription()), true);
            $shareService->set('twitter:description', $this->inlineText($sharable->getOgDescription()));
        }
        if ($sharable->getOgImageFile()) {
            $shareService->setImage($sharable->getWebPath('ogImage'));
        }
    }
}