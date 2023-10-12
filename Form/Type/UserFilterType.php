<?php

namespace NyroDev\NyroCmsBundle\Form\Type;

use NyroDev\NyroCmsBundle\Services\AdminService;
use NyroDev\UtilityBundle\Form\Type;
use Symfony\Component\Form\FormBuilderInterface;

class UserFilterType extends Type\AbstractFilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($this->generateUrl('nyrocms_admin_data_user'))
            ->add('id', Type\FilterIntType::class, ['label' => $this->trans('admin.user.id')])
            ->add('email', Type\FilterType::class, ['label' => $this->trans('admin.user.email')])
            ->add('firstname', Type\FilterType::class, ['label' => $this->trans('admin.user.firstname')])
            ->add('lastname', Type\FilterType::class, ['label' => $this->trans('admin.user.lastname')])
            ->add('userType', Type\FilterChoiceType::class, [
                'label' => $this->trans('admin.user.userType'),
                'choiceOptions' => [
                    'choices' => array_flip($this->get(AdminService::class)->getUserTypeChoices()),
                ],
            ])
            ->add('valid', Type\FilterBoolType::class, ['label' => $this->trans('admin.user.valid')])
        ;
        parent::buildForm($builder, $options);
    }
}
