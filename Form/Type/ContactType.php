<?php

namespace NyroDev\NyroCmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType {

    /**
     * @var ContainerInterface
     */
    protected $container;
	
	public function __construct($container) {
		$this->container = $container;
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver
			->setRequired('contacts')
			->setAllowedTypes('contacts', array('array'));
    }
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		if (count($options['contacts']) > 1) {
			$choices = array();
			foreach($options['contacts'] as $k=>$v)
				$choices[$k] = $this->container->get('nyrodev')->trans($v['name']);
			
			$builder->add('to', 'choice', array(
				'label'=>$this->container->get('nyrodev')->trans('nyrocms.handler.contact.to'),
				'placeholder'=>'',
				'choices'=>$choices,
				'required'=>true
			));
		}
		
		$builder
            ->add('lastname', 'text', array(
				'label'=>$this->container->get('nyrodev')->trans('nyrocms.handler.contact.lastname'),
				'constraints'=>array(
					new Constraints\NotBlank()
				)
            ))
            ->add('firstname', 'text', array(
				'label'=>$this->container->get('nyrodev')->trans('nyrocms.handler.contact.firstname'),
				'constraints'=>array(
					new Constraints\NotBlank()
				)
            ))
            ->add('company', 'text', array(
				'label'=>$this->container->get('nyrodev')->trans('nyrocms.handler.contact.company'),
				'required'=>false,
            ))
            ->add('phone', 'text', array(
				'label'=>$this->container->get('nyrodev')->trans('nyrocms.handler.contact.phone'),
				'required'=>false,
            ))
            ->add('email', 'email', array(
				'label'=>$this->container->get('nyrodev')->trans('nyrocms.handler.contact.email'),
				'constraints'=>array(
					new Constraints\NotBlank(),
					new Constraints\Email(),
				)
            ))
            ->add('message', 'textarea', array(
				'label'=>$this->container->get('nyrodev')->trans('nyrocms.handler.contact.message'),
				'constraints'=>array(
					new Constraints\NotBlank()
				)
            ))
			->add('submit', 'submit', array(
				'label'=>$this->container->get('nyrodev')->trans('nyrocms.handler.contact.send'),
			));
	}

	public function getBlockPrefix() {
		return 'contact';
	}

}