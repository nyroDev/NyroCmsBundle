<?php
namespace NyroDev\NyroCmsBundle\Form\Type;

use NyroDev\UtilityBundle\Form\Type;
use Symfony\Component\Form\FormBuilderInterface;


class UserFilterType extends Type\AbstractFilterType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
			->setAction($this->generateUrl('nyrocms_admin_data_user'))
			->add('id', Type\FilterIntType::class, array('label'=>$this->trans('admin.user.id')))
			->add('email', Type\FilterType::class, array('label'=>$this->trans('admin.user.email')))
			->add('firstname', Type\FilterType::class, array('label'=>$this->trans('admin.user.firstname')))
			->add('lastname', Type\FilterType::class, array('label'=>$this->trans('admin.user.lastname')))
			->add('userType', Type\FilterChoiceType::class, array(
				'label'=>$this->trans('admin.user.userType'),
				'choiceOptions'=>array(
					'choices'=>$this->get('nyrocms_admin')->getUserTypeChoices()
				)
			))
			->add('valid', Type\FilterBoolType::class, array('label'=>$this->trans('admin.user.valid')))
			;
		parent::buildForm($builder, $options);
    }

}