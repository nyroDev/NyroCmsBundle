<?php
namespace NyroDev\NyroCmsBundle\Form\Type;

use NyroDev\UtilityBundle\Form\Type\AbstractFilterType;
use Symfony\Component\Form\FormBuilderInterface;

class UserFilterType extends AbstractFilterType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
			->setAction($this->generateUrl('nyrocms_admin_data_user'))
			->add('id', 'filter_int', array('label'=>$this->trans('admin.user.id')))
			->add('email', 'filter', array('label'=>$this->trans('admin.user.email')))
			->add('firstname', 'filter', array('label'=>$this->trans('admin.user.firstname')))
			->add('lastname', 'filter', array('label'=>$this->trans('admin.user.lastname')))
			->add('userType', 'filter_choice', array(
				'label'=>$this->trans('admin.user.userType'),
				'choiceOptions'=>array(
					'choices'=>$this->get('nyrocms_admin')->getUserTypeChoices()
				)
			))
			->add('valid', 'filter_bool', array('label'=>$this->trans('admin.user.valid')))
			;
		parent::buildForm($builder, $options);
    }

    public function getName() {
        return 'user_filter_type';
	}

}