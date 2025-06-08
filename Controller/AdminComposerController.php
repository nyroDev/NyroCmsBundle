<?php

namespace NyroDev\NyroCmsBundle\Controller;

use Exception;
use NyroDev\NyroCmsBundle\Model\Composable;
use NyroDev\NyroCmsBundle\Model\ComposableHandler;
use NyroDev\NyroCmsBundle\Model\ComposableTranslatable;
use NyroDev\NyroCmsBundle\Services\AdminService;
use NyroDev\NyroCmsBundle\Services\ComposerService;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\NyroCmsBundle\Services\NyroCmsService;
use NyroDev\UtilityBundle\Services\EmbedService;
use NyroDev\UtilityBundle\Validator\Constraints\EmbedUrl;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminComposerController extends AbstractAdminController
{
    public function composerAction(Request $request, string $type, string $id, ?string $lang = null): Response
    {
        $row = $this->get(DbAbstractService::class)->getRepository($type)->find($id);
        if (!$row || !($row instanceof Composable)) {
            throw $this->createNotFoundException();
        }

        if (!$this->get(AdminService::class)->canAdmin($row)) {
            throw $this->createAccessDeniedException();
        }

        /* @var $row Composable */
        $locale = $this->get(NyroCmsService::class)->getDefaultLocale($row);
        if (!$lang) {
            $lang = $locale;
        }

        $composerService = $this->get(ComposerService::class);
        $canChangeLang = $composerService->canChangeLang($row);
        $sameLangStructure = $composerService->isSameLangStructure($row);
        $sameLangMedia = $composerService->isSameLangMedia($row);
        $canChangeTheme = $composerService->canChangeTheme($row);
        $availableTemplates = $composerService->getAvailableTemplates($row);

        $isDefaultLocale = true;
        $langs = $this->get(NyroCmsService::class)->getLocaleNames($row);
        if ($canChangeLang) {
            if ($lang != $locale) {
                $isDefaultLocale = false;
                $row->setTranslatableLocale($lang);
                $this->get(DbAbstractService::class)->refresh($row);
                unset($langs[$lang]);
            } else {
                unset($langs[$locale]);
            }
        }

        $canChangeStructure = $isDefaultLocale || !$sameLangStructure;
        $canChangeMedia = $isDefaultLocale || !$sameLangMedia;

        $url = $this->generateUrl('nyrocms_admin_composer', array_filter(['type' => $type, 'id' => $id, 'lang' => $lang]));
        /* @var $composerService ComposerService */
        $availableBlocks = $composerService->getAvailableBlocks($row);
        $availableItems = $composerService->getAvailableItems($row);
        $themes = $canChangeTheme ? $composerService->getThemes($row->getParent() ? $row->getParent() : $row) : [];

        if ($request->isMethod('post')) {
            if ($request->request->has('videoUrl')) {
                $ret = [];

                $url = $request->request->get('videoUrl');
                $constraints = [
                    new NotBlank(),
                    new EmbedUrl(),
                ];
                $errors = $this->get('nyrodev_form')->getValidator()->validate($url, $constraints);

                if (0 == count($errors)) {
                    $dataUrl = $this->get(EmbedService::class)->data($url);
                    $embedUrl = $dataUrl['urlEmbed'];
                    $autoplay = $request->request->getBoolean('autoplay');
                    if ($autoplay) {
                        $embedUrl .= (false === strpos($embedUrl, '?') ? '?' : '&').'autoplay=1';
                    }
                    $ret = [
                        'url' => $url,
                        'src' => $embedUrl,
                        'autoplay' => $autoplay,
                        'data' => $dataUrl,
                    ];
                } else {
                    $tmp = [];
                    foreach ($errors as $err) {
                        $tmp[] = $err->getMessage();
                    }
                    $ret['err'] = implode(', ', $tmp);
                }

                return new JsonResponse($ret);
            } elseif ($request->request->has('iframeUrl')) {
                $ret = [];

                $url = $request->request->get('iframeUrl');
                $constraints = [
                    new NotBlank(),
                    new EmbedUrl([
                        'type' => '',
                    ]),
                ];
                $errors = $this->get('nyrodev_form')->getValidator()->validate($url, $constraints);

                if (0 == count($errors)) {
                    $dataUrl = $this->get(EmbedService::class)->data($url);
                    $embedUrl = $dataUrl['urlEmbed'];
                    $ret = [
                        'url' => $url,
                        'src' => $embedUrl,
                        'data' => $dataUrl,
                    ];
                } else {
                    $tmp = [];
                    foreach ($errors as $err) {
                        $tmp[] = $err->getMessage();
                    }
                    $ret['err'] = implode(', ', $tmp);
                }

                return new JsonResponse($ret);
            }

            if ($canChangeTheme && $request->request->has('theme')) {
                $row->setTheme($request->request->get('theme'));
            }

            $newContents = array_map(function ($val) {
                return json_decode($val, true);
            }, $request->request->all('content'));

            $composerService->applyContent($row, $newContents);

            if ($templateId = $request->request->get('template')) {
                if (!isset($availableTemplates[$templateId])) {
                    throw new Exception('Template not found');
                }

                $composerService->applyTemplate($availableTemplates[$templateId], $row);

                return new Response($composerService->render($row, true));
            }

            $this->get(DbAbstractService::class)->flush();

            $composerService->afterComposerEdition($row);

            return $this->redirect($url);
        }

        if ($row instanceof ComposableHandler && $row->getContentHandler()) {
            $handler = $this->get(NyroCmsService::class)->getHandler($row->getContentHandler());
            $handler->init($request, true);
            $contentHandler = $handler->prepareView($row);
            if ($contentHandler instanceof Response) {
                return $contentHandler;
            }

            // Fix bug when there is some fetch in prepareView
            if ($lang && $row instanceof ComposableTranslatable && $row->getTranslatableLocale() != $lang) {
                $row->setTranslatableLocale($lang);
                $this->get(DbAbstractService::class)->refresh($row);
            }
        }

        return $this->render($composerService->composerTemplate($row), [
            'type' => $type,
            'id' => $id,
            'composerUrl' => $url,
            'row' => $row,
            'lang' => $lang,
            'langs' => $langs,
            'availableTemplates' => array_values($availableTemplates),
            'availableBlocks' => $availableBlocks,
            'availableItems' => $availableItems,
            'canChangeTheme' => $canChangeTheme,
            'canChangeLang' => $canChangeLang,
            'canChangeStructure' => $canChangeStructure,
            'canChangeMedia' => $canChangeMedia,
            'themes' => $themes,
            'uiTranslations' => $this->get('translator')->getCatalogue()->all('nyroComposer'),
        ]);
    }
}
