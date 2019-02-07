<?php

namespace NyroDev\NyroCmsBundle\Controller;

use NyroDev\NyroCmsBundle\Services\AdminService;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\NyroCmsBundle\Services\NyroCmsService;
use NyroDev\UtilityBundle\Controller\AbstractAdminController as SrcAbstractAdminController;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints;

class AbstractAdminController extends SrcAbstractAdminController
{
    use Traits\SubscribedServiceTrait;

    protected function canAdminContentHandler(\NyroDev\NyroCmsBundle\Model\ContentHandler $contentHandler)
    {
        $canAdmin = false;
        $nyrocmsAdmin = $this->get(AdminService::class);
        foreach ($contentHandler->getContents() as $content) {
            $canAdmin = $canAdmin || $nyrocmsAdmin->canAdminContent($content);
        }

        if (!$canAdmin) {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * @var \Symfony\Component\Form\Form
     */
    protected $translationForm;
    protected $translationPrefix;
    protected $translationFields = array();
    protected $translations;

    protected function translationFormClb($action, $row, \Symfony\Component\Form\FormBuilder $form)
    {
        if (count($this->translationFields)) {
            $langs = $this->get(NyroCmsService::class)->getLocaleNames($row);
            $defaultLocale = $this->get(NyroCmsService::class)->getDefaultLocale($row);
            unset($langs[$defaultLocale]);

            $this->translations = array();
            foreach ($row->getTranslations() as $tr) {
                if (!isset($this->translations[$tr->getLocale()])) {
                    $this->translations[$tr->getLocale()] = array();
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
                        $options['constraints'] = array(new Constraints\NotBlank());
                    }

                    $form->add($fieldName, $type, array_merge(array(
                        'label' => $this->trans('admin.'.$this->translationPrefix.'.'.$field).' '.strtoupper($lg),
                        'mapped' => false,
                        'data' => isset($this->translations[$lg]) && isset($this->translations[$lg][$field]) ? $this->translations[$lg][$field]->getContent() : $propertyAccess->getValue($row, $field),
                        'position' => array('after' => $field),
                    ), $options));
                }
            }
        }
    }

    protected function translationFlushClb($action, $row, $form)
    {
        $this->translationForm = $form;
    }

    protected function translationAfterFlushClb($response, $action, $row)
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
