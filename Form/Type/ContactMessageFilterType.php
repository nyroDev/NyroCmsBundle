<?php

namespace NyroDev\NyroCmsBundle\Form\Type;

use NyroDev\NyroCmsBundle\Services\NyroCmsService;
use NyroDev\UtilityBundle\Form\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactMessageFilterType extends Type\AbstractFilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $pathInfo = $this->get(NyroCmsService::class)->getPathInfo();
        $builder
            ->setAction($this->generateUrl('nyrocms_admin_data_contactMessage', ['chid' => $pathInfo['routePrm']['chid']]))
            ->add('id', Type\FilterIntType::class, ['label' => $this->trans('admin.contactMessage.id')])
            ->add('dest', Type\FilterType::class, ['label' => $this->trans('admin.contactMessage.dest')])
            ->add('lastname', Type\FilterType::class, ['label' => $this->trans('admin.contactMessage.lastname')])
            ->add('firstname', Type\FilterType::class, ['label' => $this->trans('admin.contactMessage.firstname')])
            ->add('email', Type\FilterType::class, ['label' => $this->trans('admin.contactMessage.email')])
            ->add('inserted', Type\FilterRangeDateType::class, [
                'label' => $this->trans('admin.contactMessage.inserted'),
                'valueOptions' => ['options' => $this->get(NyroCmsService::class)->getDateFormOptions()],
            ])
        ;
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'submitOptions' => [
                'icon' => NyroCmsService::ICON_PATH.'#filter',
                'cancelUrl' => $this->generateUrl('nyrocms_admin_data_contactMessage', ['clearFilter' => 1]),
                'cancelIcon' => NyroCmsService::ICON_PATH.'#reset',
                'cancelText' => $this->trans('admin.misc.clearFilter'),
            ],
        ]);
    }
}
