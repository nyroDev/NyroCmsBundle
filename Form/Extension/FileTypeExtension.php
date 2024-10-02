<?php

namespace NyroDev\NyroCmsBundle\Form\Extension;

use NyroDev\NyroCmsBundle\Model\ContentSpec;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class FileTypeExtension extends AbstractTypeExtension
{
    protected $nyrocms;

    public function __construct($nyrocms)
    {
        $this->nyrocms = $nyrocms;
    }

    public static function getExtendedTypes(): iterable
    {
        return [
            FileType::class,
        ];
    }

    /**
     * Pass the image URL to the view.
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $data = $form->getParent()->getData();
        if ($data instanceof ContentSpec) {
            $tmpName = explode('_', $form->getName());
            if (3 === count($tmpName)) {
                $currentFile = null;
                foreach ($data->getTranslations() as $tr) {
                    if ($tr->getLocale() == $tmpName[1] && ('content' == $tr->getField() || 'data' == $tr->getField())) {
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
