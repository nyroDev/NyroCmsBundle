<?php

namespace NyroDev\NyroCmsBundle\Handler;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\UtilityBundle\Controller\AbstractAdminController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints;

class Files extends AbstractHandler
{
    public function useDateSpec(): bool
    {
        return true;
    }

    public function orderField(): string
    {
        return 'dateSpec';
    }

    public function hasIntro(): bool
    {
        return true;
    }

    public function isIntroRequired(): bool
    {
        return true;
    }

    public function hasMoveActions(): bool
    {
        return false;
    }

    public function hasComposer(): bool
    {
        return false;
    }

    public function hasContentSpecUrl(): bool
    {
        return false;
    }

    public function hasValidDates(): bool
    {
        return false;
    }

    public function hasStateInvisible(): bool
    {
        return false;
    }

    protected function getFormFields(string $action): array
    {
        $isAdd = AbstractAdminController::ADD == $action;

        return [
            'file' => [
                'type' => FileType::class,
                'translatable' => true,
                'label' => $this->trans('nyrocms.handler.files.file'),
                'required' => $isAdd,
                'constraints' => array_filter([
                    $isAdd ? new Constraints\NotBlank() : null,
                    new Constraints\File(),
                ]),
            ],
        ];
    }

    protected function _prepareView(Content $content, ContentSpec $handlerContent = null, ?string $handlerAction = null): array
    {
        $view = '@NyroDevNyroCms/Handler/files';
        $vars = [
            'content' => $content,
        ];

        $vars['results'] = $this->getContentSpecs($content);
        $vars['uploadDir'] = $this->getUploadDir();

        return [
            'view' => $view.'.html.php',
            'vars' => $vars,
        ];
    }
}
