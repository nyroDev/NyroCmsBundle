<?php

namespace NyroDev\NyroCmsBundle\Form\Type;

use NyroDev\UtilityBundle\Services\Traits\ContainerInterfaceServiceableTrait;
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
    use ContainerInterfaceServiceableTrait;

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('contacts')
            ->setAllowedTypes('contacts', ['array']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (count($options['contacts']) > 1) {
            $choices = [];
            foreach ($options['contacts'] as $k => $v) {
                $choices[$k] = $this->trans($v['name']);
            }

            $builder->add('dest', ChoiceType::class, [
                'label' => $this->trans('nyrocms.handler.contact.dest'),
                'placeholder' => '',
                'choices' => array_flip($choices),
                'required' => true,
            ]);
        }

        $builder
            ->add('lastname', TextType::class, [
                'label' => $this->trans('nyrocms.handler.contact.lastname'),
                'attr' => [
                    'placeholder' => $this->trans('nyrocms.handler.contact.lastnamePlaceholder'),
                ],
                'constraints' => [
                    new Constraints\NotBlank(),
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => $this->trans('nyrocms.handler.contact.firstname'),
                'attr' => [
                    'placeholder' => $this->trans('nyrocms.handler.contact.firstnamePlaceholder'),
                ],
                'constraints' => [
                    new Constraints\NotBlank(),
                ],
            ])
            ->add('company', TextType::class, [
                'label' => $this->trans('nyrocms.handler.contact.company'),
                'attr' => [
                    'placeholder' => $this->trans('nyrocms.handler.contact.companyPlaceholder'),
                ],
                'required' => false,
            ])
            ->add('phone', TextType::class, [
                'label' => $this->trans('nyrocms.handler.contact.phone'),
                'attr' => [
                    'placeholder' => $this->trans('nyrocms.handler.contact.phonePlaceholder'),
                ],
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => $this->trans('nyrocms.handler.contact.email'),
                'attr' => [
                    'placeholder' => $this->trans('nyrocms.handler.contact.emailPlaceholder'),
                ],
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Email(),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => $this->trans('nyrocms.handler.contact.message'),
                'attr' => [
                    'placeholder' => $this->trans('nyrocms.handler.contact.messagePlaceholder'),
                ],
                'constraints' => [
                    new Constraints\NotBlank(),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->trans('nyrocms.handler.contact.send'),
            ]);
    }

    public function getBlockPrefix()
    {
        return 'contact';
    }
}
