<?php

namespace NyroDev\NyroCmsBundle\Services;

use Composer\Autoload\ClassLoader;
use NyroDev\NyroCmsBundle\Event\CmsFoundClassesEvent;
use NyroDev\NyroCmsBundle\Event\UrlGenerationEvent;
use NyroDev\NyroCmsBundle\Handler\AbstractHandler;
use NyroDev\NyroCmsBundle\Model\Composable;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentHandler;
use NyroDev\NyroCmsBundle\Model\ContentRootable;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Routing\NyroCmsLoader;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\PhpTemplateBundle\Helper\TagRendererHelper;
use NyroDev\UtilityBundle\Services\AbstractService as NyroDevAbstractService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use NyroDev\UtilityBundle\Services\Traits\MailerInterfaceServiceableTrait;
use NyroDev\UtilityBundle\Services\Traits\TwigServiceableTrait;
use RuntimeException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\ErrorHandler\DebugClassLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Service\Attribute\Required;

class NyroCmsService extends NyroDevAbstractService
{
    use TwigServiceableTrait;
    use MailerInterfaceServiceableTrait;
    public const ICON_PATH = 'bundles/nyrodevnyrocms/images/nyroCms.svg';

    protected ?NyroCmsLoader $routeLoader = null;
    protected ?TagRendererHelper $tagRendereHelper = null;

    protected array $handlers = [];

    public function __construct(
        private readonly NyrodevService $nyrodevService,
        private readonly DbAbstractService $dbService,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[Required]
    public function setRouteLoader(NyroCmsLoader $routeLoader): void
    {
        $this->routeLoader = $routeLoader;
    }

    #[Required]
    public function setTagRendereHelper(TagRendererHelper $tagRendereHelper): void
    {
        $this->tagRendereHelper = $tagRendereHelper;
    }

    public function getHandler(ContentHandler $contentHandler): AbstractHandler
    {
        if (!isset($this->handlers[$contentHandler->getId()])) {
            $class = $contentHandler->getClass();
            if (!class_exists($class)) {
                throw new RuntimeException($class.' not found when trying to create handler.');
            }

            if (0 === strpos(get_class($contentHandler), 'Proxies')) {
                $contentHandler = $this->dbService->getContentHandlerRepository()->find($contentHandler->getId());
            }

            $this->handlers[$contentHandler->getId()] = new $class($contentHandler, $this->container);
        }

        return $this->handlers[$contentHandler->getId()];
    }

    protected string|array $routeConfig = [];

    public function setRouteConfig(string|array $routeConfig): void
    {
        $this->routeConfig = $routeConfig;
    }

    public function getRouteConfig(): string|array
    {
        return $this->routeConfig;
    }

    protected array $activeIds = [];

    public function setActiveIds(array $activeIds): void
    {
        $this->activeIds = $activeIds;
    }

    public function getActiveIds(): array
    {
        return $this->activeIds;
    }

    protected ?Content $rootContent = null;

    public function setRootContent(Content $content): void
    {
        $this->rootContent = $content;
    }

    public function getRootContent(): ?Content
    {
        return $this->rootContent;
    }

    protected array $contentRoots = [];

    public function getContentRoot($id): ?Content
    {
        if (!isset($this->contentRoots[$id])) {
            $this->contentRoots[$id] = $this->dbService->getContentRepository()->find($id);
        }

        return $this->contentRoots[$id];
    }

    public function getUrlFor($object, bool $absolute = false, array $prm = [], $parent = null): string
    {
        $routeCfg = $this->getRouteFor($object, $prm, $parent);

        return $routeCfg['route'] ? $this->generateUrl($routeCfg['route'], $routeCfg['prm'], $absolute || $routeCfg['absolute']) : '#';
    }

    public function getRouteFor($object, array $prm = [], $parent = null): array
    {
        $routeName = null;
        $absolute = false;

        if ($object instanceof Content) {
            $root = $this->getContentRoot($object->getRoot());
            $rootHandler = $root->getHandler();
            if ($root->getDynamicHandler()) {
                $prm['dynamicHandler'] = $rootHandler;
                $rootHandler = $root->getDynamicHandler();
            }
            if ($root->getId() == $object->getId()) {
                $routeName = $rootHandler.'_homepage';
            } else {
                $routeName = $rootHandler.'_content';
                if (isset($prm['handler']) && $prm['handler']) {
                    $routeName .= '_handler';
                }
                $prm['url'] = trim($object->getUrl(), '/');
            }
        } elseif ($object instanceof ContentSpec) {
            $parent = is_null($parent) ? $object->getParent() : $parent;
            if (!$this->getHandler($object->getContentHandler())->hasContentSpecUrl()) {
                return $this->getRouteFor($parent, $prm);
            }

            $root = $this->getContentRoot($parent->getRoot());
            $rootHandler = $root->getHandler();
            if ($root->getDynamicHandler()) {
                $prm['dynamicHandler'] = $rootHandler;
                $rootHandler = $root->getDynamicHandler();
            }
            $routeName = $rootHandler.'_content_spec';
            if (isset($prm['handler']) && $prm['handler']) {
                $routeName .= '_handler';
            }

            $title = $object->getTitle();
            if ($this->disabledLocaleUrls($object->getTranslatableLocale())) {
                $curLoc = $object->getTranslatableLocale();

                $object->setTranslatableLocale($this->getDefaultLocale($object));
                $this->dbService->refresh($object);
                $title = $object->getTitle();

                $object->setTranslatableLocale($curLoc);
                $this->dbService->refresh($object);
            }

            $prm = array_merge($prm, [
                'url' => trim($parent->getUrl(), '/'),
                'id' => $object->getId(),
                'title' => $this->nyrodevService->urlify($title),
            ]);
        }

        if ($routeName) {
            $event = new UrlGenerationEvent($routeName, $prm, $absolute, $object, $parent);
            $this->eventDispatcher->dispatch($event, UrlGenerationEvent::OBJECT_URL);
            $routeName = $event->getRouteName();
            $prm = $event->getRoutePrm();
            $absolute = $event->getAbsolute();
        }

        return [
            'route' => $routeName,
            'prm' => $prm,
            'absolute' => $absolute,
        ];
    }

    public function getDateFormOptions(): array
    {
        return [
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'html5' => false,
            'attr' => [
                'class' => 'datepicker',
            ],
        ];
    }

    public function getDateTimeFormOptions(int $stepMinutes = 5): array
    {
        return [
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy HH:mm',
            'attr' => [
                'class' => 'datepicker datetimepicker',
                'data-stepminute' => $stepMinutes,
            ],
        ];
    }

    public function sendEmail(Email $email): void
    {
        if (!$email->getFrom()) {
            $email->from($this->getParameter('noreply_email'));
        }
        if ($email instanceof TemplatedEmail) {
            if (!$email->getLocale()) {
                $email->locale($this->getLocale());
            }
            $email->context(array_merge([
                'locale' => $email->getLocale(),
            ], $email->getContext()));
        }

        $forceTo = $this->getParameter('nyrocms.email.force_to');
        $forceCc = $this->getParameter('nyrocms.email.force_cc');
        $forceBcc = $this->getParameter('nyrocms.email.force_bcc');
        if (null !== $forceTo) {
            if ($forceTo) {
                $email->to($forceTo);
            } else {
                $email->to();
            }
            $email->cc();
            $email->bcc();
        }
        if (null !== $forceCc) {
            if ($forceCc) {
                $email->cc($forceCc);
            } else {
                $email->cc();
            }
        }
        if (null !== $forceBcc) {
            if ($forceBcc) {
                $email->bcc($forceBcc);
            } else {
                $email->bcc();
            }
        }

        $this->getMailerInterface()->send($email);
    }

    public function getLocale(): string
    {
        return $this->getRequest()?->getLocale() ?? $this->getDefaultLocale();
    }

    public function getDefaultLocale(?Composable $rootContent = null): string
    {
        $isContentRootable = false;
        if ($rootContent) {
            $isContentRootable = $rootContent instanceof ContentRootable;
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

    public function getLocales(?Composable $rootContent = null, bool $asString = false): string|array
    {
        $isContentRootable = false;
        if ($rootContent) {
            $isContentRootable = $rootContent instanceof ContentRootable;
            if ($isContentRootable) {
                $rootContent = $rootContent->getVeryParent();
            }
        }
        $locales = $isContentRootable && $rootContent && $rootContent->getLocales() ? $rootContent->getLocales() : $this->getParameter('locales');

        return $asString ? $locales : explode('|', $locales);
    }

    public function getLocaleNames(?Composable $rootContent = null, ?string $prefixTranslation = null): array
    {
        $names = $this->container->getParameter('localeNames');
        $ret = [];
        foreach ($this->getLocales($rootContent) as $locale) {
            if (isset($names[$locale])) {
                $ret[$locale] = $prefixTranslation ? $this->trans($prefixTranslation.'.'.$locale) : $names[$locale];
            }
        }

        return $ret;
    }

    protected mixed $pathInfoObject = null;

    public function setPathInfoObject(mixed $object): void
    {
        $this->pathInfoObject = $object;
    }

    protected ?string $pathInfoSearch = null;

    public function setPathInfoSearch(string $search): void
    {
        $this->pathInfoSearch = $search;
    }

    public function getPathInfo(): array
    {
        $request = $this->getRequest();

        return [
            'route' => $request->get('_route'),
            'routePrm' => $request->get('_route_params'),
            'object' => $this->pathInfoObject,
        ];
    }

    public function getLocalesUrl(array $pathInfo, bool $absolute = false, ?array $onlyLangs = null): array
    {
        $ret = [];
        $isObjectPage = isset($pathInfo['object']) && $pathInfo['object'];

        $rootContent = $this->getRootContent();
        $objectLocale = $isObjectPage ? $pathInfo['object'] : $rootContent;

        $routePrm = [];
        $prefixRoute = null;
        if ($rootContent) {
            $prefixRoute = $rootContent->getHandler();
            if ($rootContent->getDynamicHandler()) {
                $routePrm['dynamicHandler'] = $prefixRoute;
                $prefixRoute = $rootContent->getDynamicHandler();
            }
        }

        $defaultLocale = $this->getDefaultLocale($objectLocale);
        $locales = $this->getLocales($objectLocale);
        $curLocale = $this->getLocale();
        if ($onlyLangs && !is_array($onlyLangs)) {
            $onlyLangs = explode(',', $onlyLangs);
        }

        foreach ($locales as $locale) {
            if ($locale != $curLocale && ($locale == $defaultLocale || empty($onlyLangs) || in_array($locale, $onlyLangs))) {
                $prm = ['_locale' => $locale];
                $routeName = null;
                if (!$pathInfo['route']) {
                    $routeName = $prefixRoute.'_homepage_noLocale';
                } elseif ($pathInfo['route'] == $prefixRoute.'_homepage_noLocale' && $curLocale == $defaultLocale) {
                    $routeName = $prefixRoute.'_homepage';
                    $routePrm = array_merge($pathInfo['routePrm'], $routePrm, $prm);
                } elseif ($pathInfo['route'] == $prefixRoute.'_homepage' && $locale == $defaultLocale) {
                    $routeName = $prefixRoute.'_homepage_noLocale';
                } elseif ($this->pathInfoSearch && preg_match('/_search$/', $pathInfo['route'])) {
                    $routeName = $pathInfo['route'];
                    $routePrm = array_merge($pathInfo['routePrm'], $routePrm, $prm, ['q' => $this->pathInfoSearch]);
                } elseif ($isObjectPage) {
                    $pathInfo['object']->setTranslatableLocale($locale);
                    $this->dbService->refresh($pathInfo['object']);
                    $ret[$locale] = $this->getUrlFor($pathInfo['object'], $absolute, array_merge($pathInfo['routePrm'], $routePrm, $prm));
                } else {
                    $routeName = $pathInfo['route'];
                    $routePrm = array_merge($pathInfo['routePrm'], $routePrm, $prm);
                }
                if (!is_null($routeName)) {
                    $event = new UrlGenerationEvent($routeName, $routePrm, $absolute);
                    $this->eventDispatcher->dispatch($event, UrlGenerationEvent::LOCALES_URL);
                    $ret[$locale] = $this->generateUrl($event->getRouteName(), $event->getRoutePrm(), $event->getAbsolute());
                }
            }
        }

        if ($isObjectPage) {
            $pathInfo['object']->setTranslatableLocale($curLocale);
            $this->dbService->refresh($pathInfo['object']);
        }

        return $ret;
    }

    public function disabledLocaleUrls(string $locale): bool
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

    protected ?array $foundHandlers = null;

    public function getFoundHandlers(): array
    {
        if (is_null($this->foundHandlers)) {
            $this->foundHandlers = [];

            foreach ($this->getClassesInComposerClassMaps() as $class) {
                if (strpos($class, '\\Handler\\') && is_subclass_of($class, AbstractHandler::class, true)) {
                    $this->foundHandlers[] = '\\'.$class;
                }
            }
            sort($this->foundHandlers);

            $event = new CmsFoundClassesEvent($this->foundHandlers);
            $this->eventDispatcher->dispatch($event, CmsFoundClassesEvent::HANDLER);
            $this->foundHandlers = $event->foundClasses;
        }

        return $this->foundHandlers;
    }

    protected ?array $foundComposables = null;

    public function getFoundComposables(): array
    {
        if (is_null($this->foundComposables)) {
            $this->foundComposables = [];

            foreach ($this->getClassesInComposerClassMaps() as $class) {
                if (strpos($class, '\\Entity\\') && in_array(Composable::class, class_implements($class))) {
                    $this->foundComposables[] = '\\'.$class;
                }
            }

            sort($this->foundComposables);

            $event = new CmsFoundClassesEvent($this->foundComposables);
            $this->eventDispatcher->dispatch($event, CmsFoundClassesEvent::COMPOSABLE);
            $this->foundComposables = $event->foundClasses;
        }

        return $this->foundComposables;
    }

    private ?array $classesInComposerClassMaps = null;

    // From Symfony\Component\HttpKernel\DependencyInjection\AddAnnotatedClassesToCachePass::getClassesInComposerClassMaps
    private function getClassesInComposerClassMaps(): array
    {
        if ($this->classesInComposerClassMaps) {
            return $this->classesInComposerClassMaps;
        }

        $this->classesInComposerClassMaps = [];

        foreach (spl_autoload_functions() as $function) {
            if (!\is_array($function)) {
                continue;
            }

            if ($function[0] instanceof DebugClassLoader) {
                $function = $function[0]->getClassLoader();
            }

            if (\is_array($function) && $function[0] instanceof ClassLoader) {
                $this->classesInComposerClassMaps += array_filter($function[0]->getClassMap());
            }
        }

        $this->classesInComposerClassMaps = array_keys($this->classesInComposerClassMaps);

        return $this->classesInComposerClassMaps;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (
            !$request
            || $request->server->get('APP_DEBUG')
            || 'html' !== $request->getRequestFormat()
        ) {
            return;
        }

        // We're in prod HTML
        $code = null;
        if ($event->getThrowable() instanceof HttpExceptionInterface) {
            $code = $event->getThrowable()->getStatusCode();
        }
        if (!$code) {
            $code = 404;
        }
        $repo = $this->dbService->getContentRepository();

        // @todo define rootContent regarding URL
        $rootContent = null;

        $errorMenu = $repo->findOneByMenuOption('_error', $rootContent);
        if ($errorMenu) {
            $response = $this->forwardTo($request, $event->getKernel(), $errorMenu, $code);
            if ($response) {
                $event->setResponse($response);

                return;
            }
        }
    }

    protected function forwardTo(Request $request, HttpKernelInterface $kernel, Content $content, int $code): ?Response
    {
        $controller = $this->routeLoader->findMatchingController($content);
        if (!$controller) {
            return null;
        }

        $this->tagRendereHelper->reset();
        $subRequest = $request->duplicate([], null, [
            '_controller' => $controller.'::directContent',
            'content' => $content,
        ]);

        $response = $kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $response->setStatusCode($code);

        return $response;
    }
}
