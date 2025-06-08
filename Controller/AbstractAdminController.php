<?php

namespace NyroDev\NyroCmsBundle\Controller;

use NyroDev\NyroCmsBundle\Model\ContentHandler;
use NyroDev\NyroCmsBundle\Services\AdminService;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\NyroCmsBundle\Services\NyroCmsService;
use NyroDev\UtilityBundle\Controller\AbstractAdminController as SrcAbstractAdminController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints;

class AbstractAdminController extends SrcAbstractAdminController
{
    use Traits\SubscribedServiceTrait;

    protected function canAdminContentHandler(ContentHandler $contentHandler): void
    {
        $canAdmin = false;
        $nyrocmsAdmin = $this->get(AdminService::class);
        foreach ($contentHandler->getContents() as $content) {
            $canAdmin = $canAdmin || $nyrocmsAdmin->canAdmin($content);
        }

        if (!$canAdmin) {
            throw $this->createAccessDeniedException();
        }
    }

    protected ?Form $translationForm;
    protected ?string $translationPrefix;
    protected array $translationFields = [];
    protected ?array $translations;

    protected function translationFormClb(string $action, object $row, FormBuilder $form): void
    {
        if (count($this->translationFields)) {
            $langs = $this->get(NyroCmsService::class)->getLocaleNames($row);
            $defaultLocale = $this->get(NyroCmsService::class)->getDefaultLocale($row);
            unset($langs[$defaultLocale]);

            $this->translations = [];
            foreach ($row->getTranslations() as $tr) {
                if (!isset($this->translations[$tr->getLocale()])) {
                    $this->translations[$tr->getLocale()] = [];
                }
                $this->translations[$tr->getLocale()][$tr->getField()] = $tr;
            }

            /* @var $form \Ivory\OrderedForm\Builder\OrderedFormBuilder */

            $propertyAccess = PropertyAccess::createPropertyAccessor();
            foreach ($langs as $lg => $lang) {
                foreach ($this->translationFields as $field => $options) {
                    $type = $options['type'];
                    unset($options['type']);
                    $fieldName = 'lang_'.$lg.'_'.$field;

                    if (isset($options['required']) && $options['required']) {
                        $options['constraints'] = [new Constraints\NotBlank()];
                    }

                    $form->add($fieldName, $type, array_merge([
                        'label' => $this->trans('admin.'.$this->translationPrefix.'.'.$field).' '.strtoupper($lg),
                        'mapped' => false,
                        'data' => isset($this->translations[$lg]) && isset($this->translations[$lg][$field]) ? $this->translations[$lg][$field]->getContent() : $propertyAccess->getValue($row, $field),
                        'position' => ['after' => $field],
                    ], $options));
                }
            }
        }
    }

    protected function translationFlushClb(string $action, object $row, Form $form): void
    {
        $this->translationForm = $form;
    }

    protected function translationAfterFlushClb(Response $response, string $action, object $row): void
    {
        if (count($this->translationFields)) {
            $langs = $this->get(NyroCmsService::class)->getLocaleNames($row);
            $defaultLocale = $this->get(NyroCmsService::class)->getDefaultLocale($row);
            unset($langs[$defaultLocale]);

            $om = $this->get(DbAbstractService::class)->getObjectManager();
            $propertyAccess = PropertyAccess::createPropertyAccessor();

            foreach ($langs as $lg => $lang) {
                $row->setTranslatableLocale($lg);
                $om->refresh($row);

                foreach ($this->translationFields as $field => $options) {
                    $fieldName = 'lang_'.$lg.'_'.$field;
                    $propertyAccess->setValue($row, $field, $this->translationForm->get($fieldName)->getData());
                }

                $om->flush();
            }
        }
    }
}
