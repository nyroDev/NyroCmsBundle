<?php

namespace NyroDev\NyroCmsBundle\Form\Type;

use NyroDev\UtilityBundle\Services\MainService as nyroDevService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ContactType extends AbstractType
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('contacts')
            ->setAllowedTypes('contacts', array('array'));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (count($options['contacts']) > 1) {
            $choices = array();
            foreach ($options['contacts'] as $k => $v) {
                $choices[$k] = $this->container->get(nyroDevService::class)->trans($v['name']);
            }

            $builder->add('dest', ChoiceType::class, array(
                'label' => $this->container->get(nyroDevService::class)->trans('nyrocms.handler.contact.dest'),
                'placeholder' => '',
                'choices' => $choices,
                'required' => true,
            ));
        }

        $builder
            ->add('lastname', TextType::class, array(
                'label' => $this->container->get(nyroDevService::class)->trans('nyrocms.handler.contact.lastname'),
                'constraints' => array(
                    new Constraints\NotBlank(),
                ),
            ))
            ->add('firstname', TextType::class, array(
                'label' => $this->container->get(nyroDevService::class)->trans('nyrocms.handler.contact.firstname'),
                'constraints' => array(
                    new Constraints\NotBlank(),
                ),
            ))
            ->add('company', TextType::class, array(
                'label' => $this->container->get(nyroDevService::class)->trans('nyrocms.handler.contact.company'),
                'required' => false,
            ))
            ->add('phone', TextType::class, array(
                'label' => $this->container->get(nyroDevService::class)->trans('nyrocms.handler.contact.phone'),
                'required' => false,
            ))
            ->add('email', EmailType::class, array(
                'label' => $this->container->get(nyroDevService::class)->trans('nyrocms.handler.contact.email'),
                'constraints' => array(
                    new Constraints\NotBlank(),
                    new Constraints\Email(),
                ),
            ))
            ->add('message', TextareaType::class, array(
                'label' => $this->container->get(nyroDevService::class)->trans('nyrocms.handler.contact.message'),
                'constraints' => array(
                    new Constraints\NotBlank(),
                ),
            ))
            ->add('submit', SubmitType::class, array(
                'label' => $this->container->get(nyroDevService::class)->trans('nyrocms.handler.contact.send'),
            ));
    }

    public function getBlockPrefix()
    {
        return 'contact';
    }
}
