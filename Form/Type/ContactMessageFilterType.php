<?php

namespace NyroDev\NyroCmsBundle\Form\Type;

use NyroDev\UtilityBundle\Form\Type;
use Symfony\Component\Form\FormBuilderInterface;

class ContactMessageFilterType extends Type\AbstractFilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $pathInfo = $this->get('nyrocms')->getPathInfo();
        $builder
            ->setAction($this->generateUrl('nyrocms_admin_data_contactMessage', array('chid' => $pathInfo['routePrm']['chid'])))
            ->add('id', Type\FilterIntType::class, array('label' => $this->trans('admin.contactMessage.id')))
            ->add('dest', Type\FilterType::class, array('label' => $this->trans('admin.contactMessage.dest')))
            ->add('lastname', Type\FilterType::class, array('label' => $this->trans('admin.contactMessage.lastname')))
            ->add('firstname', Type\FilterType::class, array('label' => $this->trans('admin.contactMessage.firstname')))
            ->add('email', Type\FilterType::class, array('label' => $this->trans('admin.contactMessage.email')))
            ->add('inserted', Type\FilterRangeDateType::class, array(
                'label' => $this->trans('admin.contactMessage.inserted'),
                'valueOptions' => array('options' => $this->get('nyrocms')->getDateFormOptions()),
            ))
            ;
        parent::buildForm($builder, $options);
    }
}
