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
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractHandler
{
    public const TEMPLATE_INDICATOR = 'HANDLER_INDICATOR';

    public function __construct(
        protected readonly ContentHandler $contentHandler,
        protected readonly ContainerInterface $container,
    ) {
    }

    public function getAdminRouteName(): string
    {
        return 'nyrocms_admin_handler_contents';
    }

    public function getAdminRoutePrm(): array
    {
        return [
            'chid' => $this->contentHandler->getId(),
        ];
    }

    public function getOtherAdminRoutes(): ?array
    {
        return null;
    }

    public function hasAdminMenuLink(): bool
    {
        return true;
    }

    public function hasAdminTreeLink(): bool
    {
        return true;
    }

    public function useDateSpec(): bool
    {
        return false;
    }

    public function orderField(): string
    {
        return 'position';
    }

    public function isReversePositionOrder(): bool
    {
        return true;
    }

    public function hasIntro(): bool
    {
        return false;
    }

    public function isIntroRequired(): bool
    {
        return false;
    }

    public function hasFeatured(): bool
    {
        return true;
    }

    public function hasStateInvisible(): bool
    {
        return true;
    }

    public function hasValidDates(): bool
    {
        return true;
    }

    public function hasMetas(): bool
    {
        return false;
    }

    public function hasOgs(): bool
    {
        return false;
    }

    public function needTranslations(): bool
    {
        return true;
    }

    public function hasMoveActions(): bool
    {
        return true;
    }

    public function hasComposer(): bool
    {
        return true;
    }

    public function hasContentSpecUrl(): bool
    {
        return true;
    }

    public function hasHome(): bool
    {
        return false;
    }

    protected function getFormFields(string $action): array
    {
        return [];
    }

    protected function hasContentSpecificContent(): bool
    {
        return false;
    }

    public function getAllowedParams(): array
    {
        return [];
    }

    public function getSitemapXmlUrls(Content $content): array
    {
        return [];
    }

    public function getSitemapUrls(Content $content): array
    {
        return [];
    }

    /**
     * Get an application parameter.
     */
    public function getParameter(string $parameter, mixed $default = null): mixed
    {
        $value = $this->container->hasParameter($parameter) ? $this->container->getParameter($parameter) : null;

        return !is_null($value) ? $value : $default;
    }

    /**
     * Gets a service by id.
     */
    public function get(string $id): mixed
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
     */
    public function trans(string $key, array $parameters = [], string $domain = 'messages', ?string $locale = null): string
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
    public function generateUrl(string $route, array $parameters = [], bool $absolute = false): string
    {
        return $this->container->get(NyrodevService::class)->generateUrl($route, $parameters, $absolute);
    }

    public function getContentRepo(): ContentRepositoryInterface
    {
        return $this->get(DbAbstractService::class)->getContentRepository();
    }

    public function getContentSpecRespository(): ContentSpecRepositoryInterface
    {
        return $this->get(DbAbstractService::class)->getContentSpecRepository();
    }

    protected array $contents = [];

    /**
     * Get content by id.
     *
     * @param int $id
     */
    public function getContentById($id): ?Content
    {
        if (!isset($this->contents[$id])) {
            $this->contents[$id] = $this->getContentRepo()->find($id);
        }

        return $this->contents[$id];
    }

    public function formClb(string $action, ContentSpec $row, FormBuilder $form, array $langs = [], array $translations = []): void
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
     */
    public function getUploadRootDir(): string
    {
        return $this->get(NyrodevService::class)->getKernel()->getProjectDir().'/public/'.$this->getUploadDir();
    }

    /**
     * Get the upload directory web name.
     */
    public function getUploadDir(): string
    {
        return 'uploads/contentHandler/'.$this->contentHandler->getId();
    }

    public function flushClb(string $action, ContentSpec $row, Form $form): void
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
                if (TextType::class == $cfg['type']
                    || TextareaType::class == $cfg['type']
                    || ChoiceType::class == $cfg['type']) {
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
                if (TextType::class == $cfg['type']
                    || TextareaType::class == $cfg['type']
                    || ChoiceType::class == $cfg['type']) {
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

    protected array $fileUploaded = [];

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

    protected ?Request $request = null;

    protected bool $isAdmin = false;

    public function init(?Request $request = null, bool $isAdmin = false)
    {
        $this->request = $request;
        $this->isAdmin = $isAdmin;
    }

    protected $contentSpec = [];

    /**
     * @param int $id
     * @param int $state
     */
    public function getContentSpec($id, $locale = null, ?Content $content = null, $state = ContentSpec::STATE_ACTIVE): ?ContentSpec
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

    public function getContentSpecs(?Content $content = null, $start = null, $limit = null, array $where = [], $state = ContentSpec::STATE_ACTIVE)
    {
        return $this->getContentSpecRespository()
                        ->getForHandler($this->contentHandler->getId(), $state, $this->hasContentSpecificContent() ? $content : null, $where, [$this->orderField() => $this->isReversePositionOrder() ? 'DESC' : 'ASC'], $start, $limit);
    }

    public function getTotalContentSpec(?Content $content = null, array $where = [], $state = ContentSpec::STATE_ACTIVE)
    {
        return $this->getContentSpecRespository()
                        ->countForHandler($this->contentHandler->getId(), $state, $this->hasContentSpecificContent() ? $content : null, $where);
    }

    public function isWrapped(): bool|string
    {
        return false;
    }

    public function isWrappedAs(): bool|string
    {
        return false;
    }

    protected ?Sharable $sharable = null;

    protected function setSharable(Sharable $sharable): void
    {
        $this->sharable = $sharable;
    }

    public function getSharable(): ?Sharable
    {
        return $this->sharable;
    }

    protected $preparedView;

    public function prepareView(Content $content, ?ContentSpec $handlerContent = null, ?string $handlerAction = null): Response|array
    {
        if (is_null($this->preparedView)) {
            $this->preparedView = $this->_prepareView($content, $handlerContent, $handlerAction);
        }

        return $this->preparedView;
    }

    abstract protected function _prepareView(Content $content, ?ContentSpec $handlerContent = null, ?string $handlerAction = null): Response|array;

    protected $preparedHomeView;

    public function prepareHomeView(Content $content): array
    {
        if (is_null($this->preparedHomeView)) {
            $this->preparedHomeView = $this->_prepareHomeView($content);
        }

        return $this->preparedHomeView;
    }

    protected function _prepareHomeView(Content $content): array
    {
        return [];
    }
}
