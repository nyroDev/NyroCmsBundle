<?php

namespace NyroDev\NyroCmsBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class NyrocmsLoader extends Loader {
	
    private $loaded = array();
	
    /**
     * @var ContainerInterface
     */
    protected $container;
	
	public function __construct($container) {
		$this->container = $container;
	}
	
	public function load($resource, $type = null) {
		$res = explode('@', $resource);
        if (isset($this->loaded[$res[0]]))
            throw new \RuntimeException('Do not add the "nyrocms" with "'.$res[0].'" loader twice');
		
		$rootContent = $this->container->get('nyrocms_db')->getContentRepository()->findOneBy(array(
			'level'=>0,
			'handler'=>$res[0]
		));
		if (!$rootContent)
			throw new \RuntimeException('No root content found with handler "'.$res[0].'"');
		/* @var $rootContent \NyroDev\NyroCmsBundle\Model\Content */
		
		$routes = new RouteCollection();
		
		$locale = $this->container->get('nyrocms')->getDefaultLocale();
		$locales = implode('|', $this->container->get('nyrocms')->getLocales());
		
		$routes->add($res[0].'_homepage_noLocale', new Route(
				'/',
				array('_controller'=>$res[1].':index', '_locale'=>$locale),
				array(),
				array(),
				$rootContent->getHost()
			)
		);
		
		$routes->add($res[0].'_homepage', new Route(
				'/{_locale}/',
				array('_controller'=>$res[1].':index', '_locale'=>$locale),
				array('_locale'=>$locales),
				array(),
				$rootContent->getHost()
			)
		);
		$routes->add($res[0].'_search', new Route(
				'/{_locale}/search',
				array('_controller'=>$res[1].':search', '_locale'=>$locale),
				array('_locale'=>$locales),
				array(),
				$rootContent->getHost()
			)
		);
		$routes->add($res[0].'_content_spec_handler', new Route(
				'/{_locale}/{url}/{id}/{title}/handler/{handler}',
				array('_controller'=>$res[1].':contentSpec', '_locale'=>$locale),
				array('_locale'=>$locales, 'url'=>'.+', 'id'=>'\d+', 'handler'=>'.+'),
				array(),
				$rootContent->getHost()
			)
		);
		$routes->add($res[0].'_content_spec', new Route(
				'/{_locale}/{url}/{id}/{title}',
				array('_controller'=>$res[1].':contentSpec', '_locale'=>$locale),
				array('_locale'=>$locales, 'url'=>'.+', 'id'=>'\d+'),
				array(),
				$rootContent->getHost()
			)
		);
		$routes->add($res[0].'_content_handler', new Route(
				'/{_locale}/{url}/handler/{handler}',
				array('_controller'=>$res[1].':content', '_locale'=>$locale),
				array('_locale'=>$locales, 'url'=>'.+', 'handler'=>'.+'),
				array(),
				$rootContent->getHost()
			)
		);
		$routes->add($res[0].'_content', new Route(
				'/{_locale}/{url}',
				array('_controller'=>$res[1].':content', '_locale'=>$locale),
				array('_locale'=>$locales, 'url'=>'.+'),
				array(),
				$rootContent->getHost()
			)
		);
		
		if ($rootContent->getXmlSitemap()) {
			$routes->add($res[0].'_sitemap_xml_index', new Route(
						'/sitemap.{_format}',
						array('_controller'=>$res[1].':sitemapIndexXml'),
						array('_format'=>'xml'),
						array(),
						$rootContent->getHost()
					)
				);
			$routes->add($res[0].'_sitemapXml', new Route(
						'/{_locale}/sitemap.{_format}',
						array('_controller'=>$res[1].':sitemapXml', '_locale'=>$locale),
						array('_locale'=>$locales, '_format'=>'xml'),
						array(),
						$rootContent->getHost()
					)
				);
		}
		
        $this->loaded[$res[0]] = true;

        return $routes;
	}

	public function supports($resource, $type = null) {
		return 'nyrocms' === $type;
	}

}