<?php

namespace NyroDev\NyroCmsBundle\Handler;

use DateTime;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentHandler;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Repository\ContentRepositoryInterface;
use NyroDev\NyroCmsBundle\Repository\ContentSpecRepositoryInterface;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Controller\AbstractAdminController;
use NyroDev\UtilityBundle\Form\Type\TinymceType;
use NyroDev\UtilityBundle\Model\Sharable;
use NyroDev\UtilityBundle\Services\ImageService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractHandler
{
    public const TEMPLATE_INDICATOR = 'HANDLER_INDICATOR';

    /**
     * @var ContentHandler
     */
    protected $contentHandler;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContentHandler $contentHandler, ContainerInterface $container)
    {
        $this->contentHandler = $contentHandler;
        $this->container = $container;
    }

    public function getAdminRouteName()
    {
        return 'nyrocms_admin_handler_contents';
    }

    public function getAdminRoutePrm()
    {
        return [
            'chid' => $this->contentHandler->getId(),
        ];
    }

    public function getOtherAdminRoutes()
    {
        return;
    }

    public function hasAdminMenuLink()
    {
        return true;
    }

    public function hasAdminTreeLink()
    {
        return true;
    }

    public function useDateSpec()
    {
        return false;
    }

    public function orderField()
    {
        return 'position';
    }

    public function isReversePositionOrder()
    {
        return true;
    }

    public function hasIntro()
    {
        return false;
    }

    public function isIntroRequired()
    {
        return false;
    }

    public function hasFeatured()
    {
        return true;
    }

    public function hasStateInvisible()
    {
        return true;
    }

    public function hasValidDates()
    {
        return true;
    }

    public function hasMetas()
    {
        return false;
    }

    public function hasOgs()
    {
        return false;
    }

    public function needTranslations()
    {
        return true;
    }

    public function hasMoveActions()
    {
        return true;
    }

    public function hasComposer()
    {
        return true;
    }

    public function hasContentSpecUrl()
    {
        return true;
    }

    public function hasHome()
    {
        return false;
    }

    protected function getFormFields($action)
    {
        return [];
    }

    protected function hasContentSpecificContent()
    {
        return false;
    }

    public function getAllowedParams()
    {
        return [];
    }

    public function getSitemapXmlUrls(Content $content)
    {
        return [];
    }

    public function getSitemapUrls(Content $content)
    {
        return [];
    }

    /**
     * Get an application parameter.
     *
     * @param string $parameter
     *
     * @return mixed
     */
    public function getParameter($parameter, $default = null)
    {
        $value = $this->container->hasParameter($parameter) ? $this->container->getParameter($parameter) : null;

        return !is_null($value) ? $value : $default;
    }

    /**
     * Gets a service by id.
     *
     * @param string $id The service id
     *
     * @return object The service
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * Get the translation for a given keyword.
     *
     * @param string $key        Translation key
     * @param array  $parameters Parameters to replace
     * @param string $domain     Translation domain
     * @param string $locale     Local to use
     *
     * @return string The translation
     */
    public function trans($key, array $parameters = [], $domain = 'messages', $locale = null)
    {
        return $this->get('translator')->trans($key, $parameters, $domain, $locale);
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string $route      The name of the route
     * @param mixed  $parameters An array of parameters
     * @param bool   $absolute   Whether to generate an absolute URL
     *
     * @return string The generated URL
     */
    public function generateUrl($route, $parameters = [], $absolute = false)
    {
        return $this->container->get(NyrodevService::class)->generateUrl($route, $parameters, $absolute);
    }

    /**
     * @return ContentRepositoryInterface
     */
    public function getContentRepo()
    {
        return $this->get(DbAbstractService::class)->getContentRepository();
    }

    /**
     * @return ContentSpecRepositoryInterface
     */
    public function getContentSpecRespository()
    {
        return $this->get(DbAbstractService::class)->getContentSpecRepository();
    }

    protected $contents = [];

    /**
     * Get content by id.
     *
     * @param int $id
     *
     * @return Content
     */
    public function getContentById($id)
    {
        if (!isset($this->contents[$id])) {
            $this->contents[$id] = $this->getContentRepo()->find($id);
        }

        return $this->contents[$id];
    }

    public function formClb($action, ContentSpec $row, FormBuilder $form, array $langs = [], array $translations = [])
    {
        $after = $this->hasValidDates() ? 'validEnd' : 'state';
        $content = $this->hasComposer() ? $row->getData() : $row->getContent();
        $translationsContent = [];
        $fieldTr = $this->hasComposer() ? 'data' : 'content';
        foreach ($translations as $lg => $trs) {
            $translationsContent[$lg] = [];
            foreach ($trs as $field => $tr) {
                if ($field == $fieldTr) {
                    $translationsContent[$lg] = json_decode($tr->getContent(), true);
                }
            }
        }

        foreach ($this->getFormFields($action) as $k => $cfg) {
            $type = $cfg['type'];
            unset($cfg['type']);
            $translatable = false;
            if (isset($cfg['translatable'])) {
                $translatable = $cfg['translatable'];
                unset($cfg['translatable']);
            }
            $cfg['mapped'] = false;
            if (isset($content[$k])) {
                $cfg['data'] = $content[$k];
                if (DateType::class == $type || DateTimeType::class == $type) {
                    $cfg['data'] = new DateTime($cfg['data']['date']);
                }
            }
            if (FileType::class == $type && isset($cfg['data'])) {
                unset($cfg['data']);
            }
            if (!isset($cfg['position'])) {
                $cfg['position'] = ['after' => $after];
                $after = $k;
            }

            $form->add($k, $type, $cfg);

            if ($this->needTranslations() && $translatable && count($langs)) {
                foreach ($langs as $lg => $lang) {
                    $curCfg = $cfg;
                    $fieldName = 'lang_'.$lg.'_'.$k;
                    $curCfg['position']['after'] = $after;
                    $data = isset($translationsContent[$lg]) && isset($translationsContent[$lg][$k]) ? $translationsContent[$lg][$k] : null;
                    if ($data && 'date' == $type && !is_object($data)) {
                        $data = new DateTime($data['date']);
                    }
                    if (FileType::class == $type) {
                        $data = null;
                        if (isset($curCfg['showDelete']) && $curCfg['showDelete']) {
                            $cfg['showDelete'] = $cfg['showDelete'].'_'.$lg;
                        }
                    }
                    $form->add($fieldName, $type, array_merge($curCfg, [
                        'label' => $curCfg['label'].' '.strtoupper($lg),
                        'data' => $data,
                    ]));
                    $after = $fieldName;
                }
            }
        }
    }

    /**
     * Get the upload directory.
     *
     * @return string
     */
    public function getUploadRootDir()
    {
        return $this->get(NyrodevService::class)->getKernel()->getProjectDir().'/public/'.$this->getUploadDir();
    }

    /**
     * Get the upload directory web name.
     *
     * @return string
     */
    public function getUploadDir()
    {
        return 'uploads/contentHandler/'.$this->contentHandler->getId();
    }

    public function flushClb($action, ContentSpec $row, Form $form)
    {
        $newContents = $newContentTexts = [];
        foreach ($this->getFormFields($action) as $k => $cfg) {
            $data = $form->get($k)->getData();
            if (FileType::class == $cfg['type']) {
                if (isset($cfg['showDelete']) && $cfg['showDelete'] && $this->get(NyrodevService::class)->getRequest()->get($cfg['showDelete'])) {
                    $this->deleteFileClb($row, $k);
                }
                $newContents[$k] = $this->handleFileUpload($k, $data, $action, $row);
            } else {
                $newContents[$k] = $data;
                if (TextType::class == $cfg['type'] ||
                    TextareaType::class == $cfg['type'] ||
                    ChoiceType::class == $cfg['type']) {
                    $newContentTexts[] = $data;
                } elseif (TinymceType::class === $cfg['type']) {
                    $newContentTexts[] = html_entity_decode(strip_tags($data));
                }
            }
        }

        if ($this->hasComposer()) {
            $row->setData($newContents);
        } else {
            $row->setContent($newContents);
            $row->setContentText(implode("\n", array_filter($newContentTexts)));
        }
    }

    public function afterFlushClb($response, $action, $row)
    {
    }

    public function flushLangClb($action, ContentSpec $row, Form $form, $lg)
    {
        $newContents = $newContentTexts = [];
        if (AbstractAdminController::ADD == $action) {
            if ($this->hasComposer()) {
                $row->setData([]);
            } else {
                $row->setContent([]);
            }
        }

        foreach ($this->getFormFields($action) as $k => $cfg) {
            $data = $dataLg = $form->get($k)->getData();
            $fieldName = $k;
            if (isset($cfg['translatable']) && $cfg['translatable']) {
                $fieldName = 'lang_'.$lg.'_'.$k;
                $dataLg = $form->get($fieldName)->getData();
                if ($dataLg) {
                    $data = $dataLg;
                }
            }
            if (FileType::class == $cfg['type']) {
                if (isset($cfg['translatable']) && $cfg['translatable'] && isset($cfg['showDelete']) && $cfg['showDelete']) {
                    $deleteIdent = $cfg['showDelete'].'_'.$lg;
                    if ($this->get(NyrodevService::class)->getRequest()->get($deleteIdent)) {
                        $this->deleteFileClb($row, $fieldName);
                    }
                }
                $newContents[$k] = $this->handleFileUpload($k, $dataLg, $action, $row, $fieldName);
            } else {
                $newContents[$k] = $data;
                if (TextType::class == $cfg['type'] ||
                    TextareaType::class == $cfg['type'] ||
                    ChoiceType::class == $cfg['type']) {
                    $newContentTexts[] = $data;
                } elseif (TinymceType::class === $cfg['type']) {
                    $newContentTexts[] = html_entity_decode(strip_tags($data));
                }
            }
        }

        if ($this->hasComposer()) {
            $row->setData($newContents);
        } else {
            $row->setContent($newContents);
            $row->setContentText(implode("\n", array_filter($newContentTexts)));
        }
    }

    protected $fileUploaded = [];

    protected function handleFileUpload($field, $data, $action, ContentSpec $row, $fieldForm = null)
    {
        $fieldForm = is_null($fieldForm) ? $field : $fieldForm;
        if (!isset($this->fileUploaded[$fieldForm])) {
            $this->fileUploaded[$fieldForm] = $this->hasComposer() ? $row->getInData($field) : $row->getInContent($field);
            /* @var $data UploadedFile */
            if ($data) {
                // We have a file upload, handle it
                $rootDir = $this->getUploadRootDir();

                $fs = new Filesystem();
                if (!$fs->exists($rootDir)) {
                    $fs->mkdir($rootDir);
                }

                // Remove current files
                $this->deleteFileClb($row, $field);

                // Transfer new File
                $destPath = $this->get(NyrodevService::class)->getUniqFileName($rootDir, $data->getClientOriginalName());
                $data->move($rootDir, $destPath);

                $this->fileUploaded[$fieldForm] = $destPath;
            }
        }

        return $this->fileUploaded[$fieldForm];
    }

    public function deleteClb(ContentSpec $row)
    {
        foreach ($this->getFormFields(AbstractAdminController::ADD) as $k => $cfg) {
            if (FileType::class == $cfg['type']) {
                $this->deleteFileClb($row, $k);
            }
        }
    }

    protected function deleteFileClb(ContentSpec $row, $field)
    {
        $file = $this->hasComposer() ? $row->getInData($field) : $row->getInContent($field);
        if ($file) {
            $fs = new Filesystem();
            $filePath = $this->getUploadRootDir().'/'.$file;
            if ($fs->exists($filePath)) {
                $fs->remove($filePath);
                $this->get(ImageService::class)->removeCache($filePath);
            }
            $this->hasComposer() ? $row->setInData($field, null) : $row->getInContent($field, null);
        }
    }

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var bool
     */
    protected $isAdmin = false;

    public function init(Request $request = null, $isAdmin = false)
    {
        $this->request = $request;
        $this->isAdmin = $isAdmin;
    }

    protected $contentSpec = [];

    /**
     * @param int     $id
     * @param Content $content
     * @param int     $state
     *
     * @return ContentSpec
     */
    public function getContentSpec($id, $locale = null, Content $content = null, $state = ContentSpec::STATE_ACTIVE)
    {
        if (!isset($this->contentSpec[$id])) {
            $this->contentSpec[$id] = $this->getContentSpecRespository()
                                        ->getOneOrNullForHandler($this->contentHandler->getId(), $state, $this->hasContentSpecificContent() ? $content : null, [
                                            'id' => $id,
                                        ]);

            if ($this->contentSpec[$id] && $locale) {
                $this->contentSpec[$id]->setTranslatableLocale($locale);
                $this->get(DbAbstractService::class)->refresh($this->contentSpec[$id]);
            }
        }

        return $this->contentSpec[$id];
    }

    public function getContentSpecs(Content $content = null, $start = null, $limit = null, array $where = [], $state = ContentSpec::STATE_ACTIVE)
    {
        return $this->getContentSpecRespository()
                        ->getForHandler($this->contentHandler->getId(), $state, $this->hasContentSpecificContent() ? $content : null, $where, [$this->orderField() => $this->isReversePositionOrder() ? 'DESC' : 'ASC'], $start, $limit);
    }

    public function getTotalContentSpec(Content $content = null, array $where = [], $state = ContentSpec::STATE_ACTIVE)
    {
        return $this->getContentSpecRespository()
                        ->countForHandler($this->contentHandler->getId(), $state, $this->hasContentSpecificContent() ? $content : null, $where);
    }

    public function isWrapped()
    {
        return false;
    }

    public function isWrappedAs()
    {
        return false;
    }

    protected $sharable;

    protected function setSharable(Sharable $sharable)
    {
        $this->sharable = $sharable;
    }

    public function getSharable()
    {
        return $this->sharable;
    }

    protected $preparedView;

    public function prepareView(Content $content, ContentSpec $handlerContent = null, $handlerAction = null)
    {
        if (is_null($this->preparedView)) {
            $this->preparedView = $this->_prepareView($content, $handlerContent, $handlerAction);
        }

        return $this->preparedView;
    }

    abstract protected function _prepareView(Content $content, ContentSpec $handlerContent = null, $handlerAction = null);

    protected $preparedHomeView;

    public function prepareHomeView(Content $content)
    {
        if (is_null($this->preparedHomeView)) {
            $this->preparedHomeView = $this->_prepareHomeView($content);
        }

        return $this->preparedHomeView;
    }

    protected function _prepareHomeView(Content $content)
    {
        return [];
    }
}
