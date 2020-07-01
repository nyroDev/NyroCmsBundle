<?php

namespace NyroDev\NyroCmsBundle\Controller;

use NyroDev\NyroCmsBundle\Handler\Sitemap;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Routing\NyroCmsLoader;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\NyroCmsBundle\Services\NyroCmsService;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ErrorController as BaseErrorController;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Twig\Environment;

class ExceptionController extends BaseErrorController
{
    protected $nyroCms;

    public function __construct(Environment $twig, bool $debug, NyroCmsService $nyroCms, NyroCmsLoader $routeLoader)
    {
        parent::__construct($twig, $debug);
        $this->nyroCms = $nyroCms;
        $this->routeLoader = $routeLoader;
    }

    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
    {
        $showException = $request->attributes->get('showException', $this->debug); // As opposed to an additional parameter, this maintains BC
        $code = $exception->getStatusCode();

        if (!$showException && 'html' === $request->getRequestFormat()) {
            // Search for CMS error page or sitemap
            $repo = $this->nyroCms->get(DbAbstractService::class)->getContentRepository();

            // @todo define rootContent regarding URL
            $rootContent = null;

            $errorMenu = $repo->findOneByMenuOption('_error', $rootContent);
            if ($errorMenu) {
                $response = $this->forwardTo($request, $errorMenu, $code);
                if ($response) {
                    return $response;
                }
            }

            $sitemapHandler = $repo->findOneByContentHandlerClass(Sitemap::class, $rootContent);
            if ($sitemapHandler) {
                $response = $this->forwardTo($request, $sitemapHandler, $code);
                if ($response) {
                    return $response;
                }
            }
        }

        return parent::showAction($request, $exception, $logger);
    }

    protected function forwardTo(Request $request, Content $content, $code)
    {
        $controller = $this->routeLoader->findMatchingController($content);
        if (!$controller) {
            return null;
        }

        $subRequest = $request->duplicate([], null, [
            '_controller' => $controller.'::directContent',
            'content' => $content,
        ]);

        $response = $this->nyroCms->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $response->setStatusCode($code);

        return $response;
    }
}
