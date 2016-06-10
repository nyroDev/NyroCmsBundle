<?php

namespace NyroDev\NyroCmsBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class FileTypeExtension extends AbstractTypeExtension
{
    protected $nyrocms;

    public function __construct($nyrocms)
    {
        $this->nyrocms = $nyrocms;
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return FileType::class;
    }

    /**
     * Pass the image URL to the view.
     *
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $data = $form->getParent()->getData();
        if ($data instanceof ContentSpec) {
            $tmpName = explode('_', $form->getName());
            if (count($tmpName) === 3) {
                $currentFile = null;
                foreach ($data->getTranslations() as $tr) {
                    if ($tr->getLocale() == $tmpName[1] && ($tr->getField() == 'content' || $tr->getField() == 'data')) {
                        $contents = json_decode($tr->getContent(), true);
                        if (isset($contents[$tmpName[2]])) {
                            $currentFile = $contents[$tmpName[2]];
                        }
                    }
                }
            } else {
                $currentFile = $data->getInContent($form->getName());
                if (!$currentFile) {
                    $currentFile = $data->getInData($form->getName());
                }
            }
            if ($currentFile) {
                $currentFileWeb = $this->nyrocms->getHandler($data->getContentHandler())->getUploadDir().'/'.$currentFile;
                $view->vars['currentFile'] = $currentFileWeb;
                $view->vars['showDelete'] = $options['showDelete'] && is_string($options['showDelete']) ? $options['showDelete'] : false;
            }
        }
    }
}
