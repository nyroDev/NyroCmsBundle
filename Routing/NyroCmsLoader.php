<?php

namespace NyroDev\NyroCmsBundle\Routing;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\NyroCmsBundle\Services\NyroCmsService;
use NyroDev\UtilityBundle\Services\Traits\ContainerInterfaceServiceableTrait;
use RuntimeException;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class NyroCmsLoader extends Loader
{
    use ContainerInterfaceServiceableTrait;

    private array $loaded = [];

    public function load(mixed $resource, ?string $type = null): mixed
    {
        if (!is_array($resource)) {
            throw new RuntimeException('The resource for a nyroCms routing must be an array.');
        }

        $required = ['handler', 'controller'];
        foreach ($required as $key) {
            if (!isset($resource[$key])) {
                throw new RuntimeException('The resource for a nyroCms routing must have the "'.$key.'" key.');
            }
        }

        if (isset($this->loaded[$resource['handler']])) {
            throw new RuntimeException('Do not add the "nyrocms" with handler "'.$resource['handler'].'" loader twice.');
        }

        /** @var NyroCmsService $nyroCms */
        $nyroCms = $this->container->get(NyroCmsService::class);

        $rootContentHandler = $resource['handler'];
        if (isset($resource['dynamic'])) {
            if (!is_array($resource['dynamic'])) {
                throw new RuntimeException('The resource for a nyroCms routing must have the "dynamic" key as an array.');
            }
            if (!isset($resource['dynamic']['rootHandler'])) {
                throw new RuntimeException('The resource for a nyroCms routing must have the "rootHandler" inside the "dynamic" array.');
            }
            $rootContentHandler = $resource['dynamic']['rootHandler'];
        }

        $rootContent = $this->container->get(DbAbstractService::class)->getContentRepository()->findOneBy([
            'level' => 0,
            'handler' => $rootContentHandler,
        ]);
        if (!$rootContent) {
            throw new RuntimeException('No root content found with handler "'.$rootContentHandler.'"');
        }
        /* @var $rootContent \NyroDev\NyroCmsBundle\Model\Content */

        $host = $rootContent->getHost();
        $xmlSitemap = $rootContent->getXmlSitemap();
        $path = '/';
        $priority = 0;

        $locale = $nyroCms->getDefaultLocale($rootContent);
        $locales = $nyroCms->getLocales($rootContent, true);

        if (isset($resource['dynamic'])) {
            if (isset($resource['dynamic']['host'])) {
                if (!str_contains($resource['dynamic']['host'], '{dynamicHandler}')) {
                    throw new RuntimeException('The dynamic host resource for a nyroCms routing must contains {dynamicHandler} /.');
                }
                $host = $resource['dynamic']['host'];
            }
            if (isset($resource['dynamic']['path'])) {
                if (!str_contains($resource['dynamic']['path'], '{dynamicHandler}')) {
                    throw new RuntimeException('The dynamic path resource for a nyroCms routing must contains {dynamicHandler} /.');
                }
                if (!str_starts_with($resource['dynamic']['path'], '/')) {
                    throw new RuntimeException('The dynamic path resource for a nyroCms routing must starts with a /.');
                }
                if (!str_ends_with($resource['dynamic']['path'], '/')) {
                    throw new RuntimeException('The dynamic path resource for a nyroCms routing must ends with a /.');
                }
                $path = $resource['dynamic']['path'];
            }
            if (isset($resource['dynamic']['xmlSitemap'])) {
                $xmlSitemap = $resource['dynamic']['xmlSitemap'];
            }
            $priority = 1;
        }

        $env = $this->container->getParameter('kernel.environment');
        $routes = new RouteCollection();

        $prefixUrlLocale = $path.'{_locale}/';
        $hasOnly1Locale = $locale === $locales && !isset($resource['forceLang']);
        if ($hasOnly1Locale) {
            $prefixUrlLocale = $path;
        }

        $routeHandlerPath = $nyroCms->getParameter('nyrocms.route_handler_path');
        if ($routeHandlerPath) {
            $routeHandlerPath .= '/';
        }

        if (isset($resource['homepage'])) {
            if (isset($this->loaded['_hompage'])) {
                throw new RuntimeException('Do not add the "nyrocms" with homepage twice.');
            }
            $this->loaded['_hompage'] = true;
            $routes->add('_homepage', new Route(
                $path,
                ['_controller' => $resource['controller'].'::index', '_locale' => $locale, '_config' => $resource['handler']],
                [],
                [],
                $host
            ), $priority);
        }

        $routes->add($resource['handler'].'_homepage_noLocale', new Route(
            $path.(isset($resource['forceLang']) ? $locale.'/' : ''),
            ['_controller' => $resource['controller'].'::index', '_locale' => $locale, '_config' => $resource['handler']],
            [],
            [],
            $host
        ), $priority);

        if ($xmlSitemap) {
            if (!$hasOnly1Locale) {
                $routes->add($resource['handler'].'_sitemap_xml_index', new Route(
                    $path.'sitemap.{_format}',
                    ['_controller' => $resource['controller'].'::sitemapIndexXml', '_config' => $resource['handler']],
                    ['_format' => 'xml'],
                    [],
                    $host
                ), $priority);
            }
            $routes->add($resource['handler'].'_sitemapXml', new Route(
                $prefixUrlLocale.'sitemap.{_format}',
                ['_controller' => $resource['controller'].'::sitemapXml', '_locale' => $locale, '_config' => $resource['handler']],
                ['_locale' => $locales, '_format' => 'xml'],
                [],
                $host
            ), $priority);
        }

        $routes->add($resource['handler'].'_homepage', new Route(
            $prefixUrlLocale,
            ['_controller' => $resource['controller'].'::index', '_locale' => $locale, '_config' => $resource['handler']],
            ['_locale' => $locales],
            [],
            $host
        ), $priority);
        $routes->add($resource['handler'].'_search', new Route(
            $prefixUrlLocale.'search',
            ['_controller' => $resource['controller'].'::search', '_locale' => $locale, '_config' => $resource['handler']],
            ['_locale' => $locales],
            [],
            $host
        ), $priority);
        $routes->add($resource['handler'].'_content_spec_handler', new Route(
            $prefixUrlLocale.'{url}/{id}/{title}/'.$routeHandlerPath.'{handler}',
            ['_controller' => $resource['controller'].'::contentSpec', '_locale' => $locale, '_config' => $resource['handler']],
            ['_locale' => $locales, 'url' => '[^/]+', 'id' => '\d+', 'handler' => '.+'],
            [],
            $host
        ), $priority);
        $routes->add($resource['handler'].'_content_spec', new Route(
            $prefixUrlLocale.'{url}/{id}/{title}',
            ['_controller' => $resource['controller'].'::contentSpec', '_locale' => $locale, '_config' => $resource['handler']],
            ['_locale' => $locales, 'url' => '[^/]+', 'id' => '\d+'],
            [],
            $host
        ), $priority);
        $routes->add($resource['handler'].'_content_handler', new Route(
            $prefixUrlLocale.'{url}/'.$routeHandlerPath.'{handler}',
            ['_controller' => $resource['controller'].'::content', '_locale' => $locale, '_config' => $resource['handler']],
            ['_locale' => $locales, 'url' => '[^/]+', 'handler' => '.+'],
            [],
            $host
        ), $priority);
        $routes->add($resource['handler'].'_content', new Route(
            $prefixUrlLocale.'{url}',
            ['_controller' => $resource['controller'].'::content', '_locale' => $locale, '_config' => $resource['handler']],
            ['_locale' => $locales, 'url' => 'dev' === $env ? '^(?!_wdt|_profiler|_error.).+' : '.+'],
            [],
            $host
        ), $priority);

        $this->loaded[$resource['handler']] = true;

        return $routes;
    }

    public function findMatchingController(Content $content): ?string
    {
        $handler = $content->getVeryParent()->getHandler();

        $resources = $this->container->get(NyroCmsService::class)->getParameter('nyrocms.route_resources');
        foreach ($resources as $resource) {
            $res = explode('@', $resource);
            if ($res[0] === $handler) {
                return $res[1];
            }
        }

        return null;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'nyrocms' === $type;
    }
}
