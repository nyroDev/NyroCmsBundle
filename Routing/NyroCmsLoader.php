<?php

namespace NyroDev\NyroCmsBundle\Routing;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\NyroCmsBundle\Services\NyroCmsService;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class NyroCmsLoader extends Loader
{
    private $loaded = [];

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function load($resource, $type = null)
    {
        $typeCfg = array_flip(explode('_', $type));

        $res = explode('@', $resource);
        if (isset($this->loaded[$res[0]])) {
            throw new \RuntimeException('Do not add the "nyrocms" with "'.$res[0].'" loader twice');
        }

        $rootContent = $this->container->get(DbAbstractService::class)->getContentRepository()->findOneBy([
            'level' => 0,
            'handler' => $res[0],
        ]);
        if (!$rootContent) {
            throw new \RuntimeException('No root content found with handler "'.$res[0].'"');
        }
        /* @var $rootContent \NyroDev\NyroCmsBundle\Model\Content */

        $env = $this->container->getParameter('kernel.environment');
        $routes = new RouteCollection();

        $locale = $this->container->get(NyroCmsService::class)->getDefaultLocale($rootContent);
        $locales = $this->container->get(NyroCmsService::class)->getLocales($rootContent, true);

        $prefixUrlLocale = '/{_locale}';
        $hasOnly1Locale = $locale === $locales && !isset($typeCfg['forceLang']);
        if ($hasOnly1Locale) {
            $prefixUrlLocale = null;
        }

        $routeHandlerPath = $this->container->get(NyroCmsService::class)->getParameter('nyrocms.route_handler_path');
        if ($routeHandlerPath) {
            $routeHandlerPath.= '/';
        }

        if (isset($typeCfg['homepage'])) {
            $routes->add('_homepage', new Route(
                    '/',
                    ['_controller' => $res[1].':index', '_locale' => $locale, '_config' => $res[0]],
                    [],
                    [],
                    $rootContent->getHost()
                )
            );
        }

        $routes->add($res[0].'_homepage_noLocale', new Route(
                '/'.(isset($typeCfg['forceLang']) ? $locale.'/' : ''),
                ['_controller' => $res[1].':index', '_locale' => $locale, '_config' => $res[0]],
                [],
                [],
                $rootContent->getHost()
            )
        );

        if ($rootContent->getXmlSitemap()) {
            if (!$hasOnly1Locale) {
                $routes->add($res[0].'_sitemap_xml_index', new Route(
                        '/sitemap.{_format}',
                        ['_controller' => $res[1].':sitemapIndexXml', '_config' => $res[0]],
                        ['_format' => 'xml'],
                        [],
                        $rootContent->getHost()
                    )
                );
            }
            $routes->add($res[0].'_sitemapXml', new Route(
                    $prefixUrlLocale.'/sitemap.{_format}',
                    ['_controller' => $res[1].':sitemapXml', '_locale' => $locale, '_config' => $res[0]],
                    ['_locale' => $locales, '_format' => 'xml'],
                    [],
                    $rootContent->getHost()
                )
            );
        }

        $routes->add($res[0].'_homepage', new Route(
                $prefixUrlLocale.'/',
                ['_controller' => $res[1].':index', '_locale' => $locale, '_config' => $res[0]],
                ['_locale' => $locales],
                [],
                $rootContent->getHost()
            )
        );
        $routes->add($res[0].'_search', new Route(
                $prefixUrlLocale.'/search',
                ['_controller' => $res[1].':search', '_locale' => $locale, '_config' => $res[0]],
                ['_locale' => $locales],
                [],
                $rootContent->getHost()
            )
        );
        $routes->add($res[0].'_content_spec_handler', new Route(
                $prefixUrlLocale.'/{url}/{id}/{title}/'.$routeHandlerPath.'{handler}',
                ['_controller' => $res[1].':contentSpec', '_locale' => $locale, '_config' => $res[0]],
                ['_locale' => $locales, 'url' => '[^/]+', 'id' => '\d+', 'handler' => '.+'],
                [],
                $rootContent->getHost()
            )
        );
        $routes->add($res[0].'_content_spec', new Route(
                $prefixUrlLocale.'/{url}/{id}/{title}',
                ['_controller' => $res[1].':contentSpec', '_locale' => $locale, '_config' => $res[0]],
                ['_locale' => $locales, 'url' => '[^/]+', 'id' => '\d+'],
                [],
                $rootContent->getHost()
            )
        );
        $routes->add($res[0].'_content_handler', new Route(
                $prefixUrlLocale.'/{url}/'.$routeHandlerPath.'{handler}',
                ['_controller' => $res[1].':content', '_locale' => $locale, '_config' => $res[0]],
                ['_locale' => $locales, 'url' => '[^/]+', 'handler' => '.+'],
                [],
                $rootContent->getHost()
            )
        );
        $routes->add($res[0].'_content', new Route(
                $prefixUrlLocale.'/{url}',
                ['_controller' => $res[1].':content', '_locale' => $locale, '_config' => $res[0]],
                ['_locale' => $locales, 'url' => 'dev' === $env ? '^(?!_wdt|_profiler|_error.).+' : '.+'],
                [],
                $rootContent->getHost()
            )
        );

        $this->loaded[$res[0]] = true;

        return $routes;
    }

    public function findMatchingController(Content $content)
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

    public function supports($resource, $type = null)
    {
        return 'nyrocms' === substr($type, 0, 7);
    }
}
