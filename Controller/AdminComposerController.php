<?php

namespace NyroDev\NyroCmsBundle\Controller;

use Exception;
use NyroDev\NyroCmsBundle\Handler\AbstractHandler;
use NyroDev\NyroCmsBundle\Model\Composable;
use NyroDev\NyroCmsBundle\Model\ComposableContentSummary;
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
        $composerService->initComposerFor($row, $lang);
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
        $themes = $canChangeTheme ? $composerService->getThemes($row->getParent() ? $row->getParent() : $row) : [];

        if ($request->isMethod('post')) {
            if ($request->request->has('imageUpload') && $request->files->has('image')) {
                return $composerService->handleImageUpload($request);
            } elseif ($request->request->has('fileUpload') && $request->files->has('file')) {
                return $composerService->handleFileUpload($request);
            } elseif ($request->request->has('video')) {
                $ret = [];

                $url = $request->request->get('url');
                $constraints = [
                    new NotBlank(),
                    new EmbedUrl(),
                ];
                $errors = $this->get('nyrodev_form')->getValidator()->validate($url, $constraints);

                if (0 == count($errors)) {
                    $dataUrl = $this->get(EmbedService::class)->data($url);
                    $embedUrl = $dataUrl['urlEmbed'];
                    if ($request->request->get('autoplay')) {
                        $embedUrl .= (false === strpos($embedUrl, '?') ? '?' : '&').'autoplay=1';
                    }
                    $ret = [
                        'url' => $url,
                        'embed' => $embedUrl,
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

            $contentsKey = $request->request->all('contentsKey');
            $contentsType = $request->request->all('contentsType');
            $contentsId = $request->request->all('contentsId');
            $contentsDel = $request->request->all('contentsDel');
            $contents = $request->request->all('contents');

            $newContents = [];
            $newTexts = [$row->getTitle()];
            $firstImage = null;
            foreach ($contentsKey as $key) {
                if (isset($contentsType[$key]) && isset($contents[$key])) {
                    if (isset($contentsDel[$key]) && $contentsDel[$key]) {
                        // Delete this block
                        $composerService->deleteBlock($row, $contentsId[$key], $contentsType[$key], $contents[$key]);
                    } else {
                        $block = $composerService->getBlock($row, $contentsId[$key], $contentsType[$key], $contents[$key], true);
                        foreach ($block['texts'] as $t) {
                            if (AbstractHandler::TEMPLATE_INDICATOR != $t) {
                                $newTexts[] = html_entity_decode(strip_tags($t));
                            }
                        }
                        if (is_null($firstImage) && count($block['images']) && isset($block['images'][0])) {
                            $firstImage = $block['images'][0];
                        }
                        unset($block['texts']);
                        unset($block['images']);
                        unset($block['files']);
                        $newContents[] = $block;
                    }
                }
            }

            $row->setContent($newContents);
            if ($row instanceof ComposableContentSummary) {
                $row->setContentText(implode("\n", $newTexts));
                $row->setFirstImage($firstImage);
            }

            if ($templateId = $request->query->get('template')) {
                if (!isset($availableTemplates[$templateId])) {
                    throw new Exception('Template not found');
                }

                $composerService->applyTemplate($availableTemplates[$templateId], $row);
            }

            $this->get(DbAbstractService::class)->flush();

            $composerService->afterComposerEdition($row);

            return $this->redirect($url);
        } elseif ($request->query->has('block')) {
            if (!in_array($request->query->get('block'), $availableBlocks)) {
                throw $this->createNotFoundException();
            }

            $html = $composerService->renderNew($row, $request->query->get('block'), true);

            return new Response($html);
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

        return $this->render($this->get(ComposerService::class)->globalComposerTemplate($row), [
            'type' => $type,
            'id' => $id,
            'composerUrl' => $url,
            'row' => $row,
            'lang' => $lang,
            'langs' => $langs,
            'availableTemplates' => $availableTemplates,
            'availableBlocks' => $availableBlocks,
            'canChangeTheme' => $canChangeTheme,
            'canChangeLang' => $canChangeLang,
            'canChangeStructure' => $canChangeStructure,
            'canChangeMedia' => $canChangeMedia,
            'themes' => $themes,
        ]);
    }
}
