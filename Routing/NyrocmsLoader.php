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
    private $loaded = array();

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

        $rootContent = $this->container->get(DbAbstractService::class)->getContentRepository()->findOneBy(array(
            'level' => 0,
            'handler' => $res[0],
        ));
        if (!$rootContent) {
            throw new \RuntimeException('No root content found with handler "'.$res[0].'"');
        }
        /* @var $rootContent \NyroDev\NyroCmsBundle\Model\Content */

        $routes = new RouteCollection();

        $locale = $this->container->get(NyroCmsService::class)->getDefaultLocale($rootContent);
        $locales = $this->container->get(NyroCmsService::class)->getLocales($rootContent, true);

        $prefixUrlLocale = '/{_locale}';
        $hasOnly1Locale = $locale === $locales && !isset($typeCfg['forceLang']);
        if ($hasOnly1Locale) {
            $prefixUrlLocale = null;
        }

        if (isset($typeCfg['homepage'])) {
            $routes->add('_homepage', new Route(
                    '/',
                    array('_controller' => $res[1].':index', '_locale' => $locale, '_config' => $res[0]),
                    array(),
                    array(),
                    $rootContent->getHost()
                )
            );
        }

        $routes->add($res[0].'_homepage_noLocale', new Route(
                '/'.(isset($typeCfg['forceLang']) ? $locale.'/' : ''),
                array('_controller' => $res[1].':index', '_locale' => $locale, '_config' => $res[0]),
                array(),
                array(),
                $rootContent->getHost()
            )
        );

        if ($rootContent->getXmlSitemap()) {
            if (!$hasOnly1Locale) {
                $routes->add($res[0].'_sitemap_xml_index', new Route(
                        '/sitemap.{_format}',
                        array('_controller' => $res[1].':sitemapIndexXml', '_config' => $res[0]),
                        array('_format' => 'xml'),
                        array(),
                        $rootContent->getHost()
                    )
                );
            }
            $routes->add($res[0].'_sitemapXml', new Route(
                    $prefixUrlLocale.'/sitemap.{_format}',
                    array('_controller' => $res[1].':sitemapXml', '_locale' => $locale, '_config' => $res[0]),
                    array('_locale' => $locales, '_format' => 'xml'),
                    array(),
                    $rootContent->getHost()
                )
            );
        }

        $routes->add($res[0].'_homepage', new Route(
                $prefixUrlLocale.'/',
                array('_controller' => $res[1].':index', '_locale' => $locale, '_config' => $res[0]),
                array('_locale' => $locales),
                array(),
                $rootContent->getHost()
            )
        );
        $routes->add($res[0].'_search', new Route(
                $prefixUrlLocale.'/search',
                array('_controller' => $res[1].':search', '_locale' => $locale, '_config' => $res[0]),
                array('_locale' => $locales),
                array(),
                $rootContent->getHost()
            )
        );
        $routes->add($res[0].'_content_spec_handler', new Route(
                $prefixUrlLocale.'/{url}/{id}/{title}/handler/{handler}',
                array('_controller' => $res[1].':contentSpec', '_locale' => $locale, '_config' => $res[0]),
                array('_locale' => $locales, 'url' => '.+', 'id' => '\d+', 'handler' => '.+'),
                array(),
                $rootContent->getHost()
            )
        );
        $routes->add($res[0].'_content_spec', new Route(
                $prefixUrlLocale.'/{url}/{id}/{title}',
                array('_controller' => $res[1].':contentSpec', '_locale' => $locale, '_config' => $res[0]),
                array('_locale' => $locales, 'url' => '.+', 'id' => '\d+'),
                array(),
                $rootContent->getHost()
            )
        );
        $routes->add($res[0].'_content_handler', new Route(
                $prefixUrlLocale.'/{url}/handler/{handler}',
                array('_controller' => $res[1].':content', '_locale' => $locale, '_config' => $res[0]),
                array('_locale' => $locales, 'url' => '.+', 'handler' => '.+'),
                array(),
                $rootContent->getHost()
            )
        );
        $routes->add($res[0].'_content', new Route(
                $prefixUrlLocale.'/{url}',
                array('_controller' => $res[1].':content', '_locale' => $locale, '_config' => $res[0]),
                array('_locale' => $locales, 'url' => '.+'),
                array(),
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
