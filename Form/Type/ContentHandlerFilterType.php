<?php

namespace NyroDev\NyroCmsBundle\Form\Type;

use NyroDev\UtilityBundle\Form\Type;
use Symfony\Component\Form\FormBuilderInterface;

class ContentHandlerFilterType extends Type\AbstractFilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setAction($this->generateUrl('nyrocms_admin_data_contentHandler'))
            ->add('id', Type\FilterIntType::class, ['label' => $this->trans('admin.contentHandler.id')])
            ->add('name', Type\FilterType::class, ['label' => $this->trans('admin.contentHandler.name')])
            ->add('class', Type\FilterType::class, ['label' => $this->trans('admin.contentHandler.class')])
            ->add('hasAdmin', Type\FilterBoolType::class, ['label' => $this->trans('admin.contentHandler.hasAdmin')])
        ;
        parent::buildForm($builder, $options);
    }
}
