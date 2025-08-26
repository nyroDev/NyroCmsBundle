<?php

namespace NyroDev\NyroCmsBundle\Form\Type;

use NyroDev\NyroCmsBundle\Services\NyroCmsService;
use NyroDev\UtilityBundle\Form\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TooltipFilterType extends Type\AbstractFilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setAction($this->generateUrl('nyrocms_admin_data_tooltip'))
            ->add('title', Type\FilterType::class, ['label' => $this->trans('admin.tooltip.title')])
            ->add('ident', Type\FilterType::class, ['label' => $this->trans('admin.tooltip.ident')])
            ->add('content', Type\FilterType::class, ['label' => $this->trans('admin.tooltip.content')])
        ;
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'submitOptions' => [
                'icon' => NyroCmsService::ICON_PATH.'#filter',
                'cancelUrl' => $this->generateUrl('nyrocms_admin_data_tooltip', ['clearFilter' => 1]),
                'cancelIcon' => NyroCmsService::ICON_PATH.'#reset',
                'cancelText' => $this->trans('admin.misc.clearFilter'),
            ],
        ]);
    }
}
