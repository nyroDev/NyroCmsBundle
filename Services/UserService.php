<?php

namespace NyroDev\NyroCmsBundle\Services;

use DateTime;
use Exception;
use NyroDev\NyroCmsBundle\Model\User;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Services\AbstractService as nyroDevAbstractService;
use NyroDev\UtilityBundle\Services\FormService;
use NyroDev\UtilityBundle\Services\MemberService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserService extends nyroDevAbstractService
{
    protected $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function handleAddUser(User $user, $locale = null, $place = 'admin')
    {
        $now = new DateTime();
        if (
            $user->getValid()
                &&
            (!$user->getValidStart() || $user->getValidStart() <= $now)
                &&
            (!$user->getValidEnd() || $user->getValidEnd() >= $now)
        ) {
            // user is valid, we can send it an email
            $this->sendWelcomeEmail($user, $locale, $place);
        }
    }

    public function sendWelcomeEmail(User $user, $locale = null, $place = 'admin')
    {
        $passwordKey = $this->get(NyrodevService::class)->randomStr(32);
        $end = new DateTime('+1month');

        $user->setPasswordKey($passwordKey);
        $user->setPasswordKeyEnd($end);
        $this->get(DbAbstractService::class)->flush();

        $this->get(NyroCmsService::class)->sendEmail($user->getEmail(), $this->trans('nyrocms.welcome.email.subject'), nl2br($this->trans('nyrocms.welcome.email.content', [
            '%name%' => $user->getFirstname().' '.$user->getLastName(),
            '%url%' => $this->generateUrl('nyrocms_'.$place.'_welcome', [
                'id' => $user->getId(),
                'key' => $user->getPasswordKey(),
            ], true),
        ])), null, $locale);
    }

    public function sendChangedPasswordEmail(User $user)
    {
        return $this->get(NyroCmsService::class)->sendEmail($user->getEmail(), $this->trans('nyrocms.changedPassword.email.subject'), nl2br($this->trans('nyrocms.changedPassword.email.content', [
            '%name%' => $user->getFirstname().' '.$user->getLastName(),
        ])));
    }

    public function handleForgot($place, Request $request, $id, $key, $welcome = false)
    {
        $ret = [
            'step' => 1,
            'notFound' => false,
            'sent' => false,
            'form' => null,
            'welcome' => $welcome,
        ];
        $repo = $this->get(DbAbstractService::class)->getUserRepository();

        if ($id || $welcome) {
            $ret['step'] = 2;
            $user = $repo->find($id);

            if ($user && $welcome && 'dummy' != $user->getPassword()) {
                $user = null;
            }

            $now = new DateTime();
            if ($user && $user->getPasswordKey() == $key && $user->getPasswordKeyEnd() >= $now) {
                $form = $this->get(FormService::class)->getFormFactory()->createBuilder()
                    ->add('password', RepeatedType::class, [
                        'type' => PasswordType::class,
                        'first_options' => [
                            'label' => $this->trans('admin.user.password'),
                            'attr' => ['placeholder' => $this->trans('admin.user.newPassword')],
                            'constraints' => [
                                new NotBlank(),
                            ],
                        ],
                        'second_options' => [
                            'label' => $this->trans('admin.user.passwordConfirm'),
                            'attr' => ['placeholder' => $this->trans('admin.user.passwordConfirm')],
                            'constraints' => [
                                new NotBlank(),
                            ],
                        ],
                        'required' => true,
                        'invalid_message' => $this->trans('admin.user.samePassword'),
                    ])
                    ->add('submit', SubmitType::class, [
                        'label' => $this->trans('admin.misc.send'),
                    ])
                    ->getForm();

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $data = $form->getData();

                    $password = $this->passwordHasher->hashPassword($user, $data['password']);
                    $user->setPassword($password);
                    $user->setPasswordKey(null);
                    $user->setPasswordKeyEnd(null);
                    $this->get(DbAbstractService::class)->flush();

                    $this->sendChangedPasswordEmail($user);

                    $ret['sent'] = true;
                }

                $ret['form'] = $form->createView();
            } else {
                $ret['notFound'] = true;
            }
        } else {
            $form = $this->get(FormService::class)->getFormFactory()->createBuilder()
                ->add('email', EmailType::class, [
                    'label' => $this->trans('admin.user.email'),
                    'constraints' => [
                        new NotBlank(),
                        new Email(),
                    ],
                    'attr' => ['placeholder' => $this->trans('admin.user.email')],
                ])
                ->add('submit', SubmitType::class, [
                    'label' => $this->trans('admin.misc.send'),
                ])
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                try {
                    $user = $repo->loadUserByIdentifier($data['email']);
                } catch (Exception $e) {
                    $user = null;
                }
                if ($user) {
                    $passwordKey = $this->get(NyrodevService::class)->randomStr(32);
                    $end = new DateTime('+2day');

                    $user->setPasswordKey($passwordKey);
                    $user->setPasswordKeyEnd($end);
                    $this->get(DbAbstractService::class)->flush();

                    $this->get(NyroCmsService::class)->sendEmail($user->getEmail(), $this->trans('nyrocms.forgot.email.subject'), nl2br($this->trans('nyrocms.forgot.email.content', [
                        '%name%' => $user->getFirstname().' '.$user->getLastName(),
                        '%url%' => $this->generateUrl('nyrocms_'.$place.'_forgot', [
                            'id' => $user->getId(),
                            'key' => $user->getPasswordKey(),
                        ], true),
                    ])));
                    $ret['sent'] = true;
                } else {
                    $ret['notFound'] = true;
                }
            }

            $ret['form'] = $form->createView();
        }

        return $ret;
    }

    public function handleAccount($place, Request $request)
    {
        $this->get(NyroCmsService::class)->setActiveIds(['account' => 'account']);
        $ret = [
            'fields' => false,
            'password' => false,
        ];

        $user = $this->get(MemberService::class)->getUser();
        $fields = [
            'email',
            'firstname',
            'lastname',
        ];

        $form = $this->get(FormService::class)->getFormFactory()->createNamedBuilder('fields', FormType::class, $user);
        foreach ($fields as $f) {
            $form->add($f, null, [
                'label' => $this->trans('admin.user.'.$f),
            ]);
        }
        $form->add('submit', SubmitType::class, [
            'label' => $this->trans('admin.misc.send'),
        ]);

        $formFields = $form->getForm();

        $formPassword = $this->get(FormService::class)->getFormFactory()->createNamedBuilder('password', FormType::class, $user)
            ->add('curPassword', PasswordType::class, [
                'label' => $this->trans('admin.user.curPassword'),
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new UserPassword([
                        'message' => $this->trans('admin.user.wrongPassword'),
                    ]),
                ], ])
            ->add('password', RepeatedType::class, [
                'mapped' => false,
                'first_name' => 'pwd1',
                'second_name' => 'pwd2',
                'constraints' => [new NotBlank()],
                'required' => true,
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => $this->trans('admin.user.newPassword'),
                ],
                'second_options' => [
                    'label' => $this->trans('admin.user.passwordConfirm'),
                ],
                'invalid_message' => $this->trans('admin.user.samePassword'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->trans('admin.misc.send'),
            ])
            ->getForm();

        $formFields->handleRequest($request);
        $formPassword->handleRequest($request);
        if ($formFields->isSubmitted() && $formFields->isValid()) {
            $this->get(DbAbstractService::class)->flush();
            $ret['fields'] = true;
        } elseif ($formPassword->isSubmitted() && $formPassword->isValid()) {
            $newPassword = $formPassword->get('password')->getData();
            $password = $this->passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($password);
            $user->setPasswordKey(null);
            $user->setPasswordKeyEnd(null);
            $this->get(DbAbstractService::class)->flush();

            $this->sendChangedPasswordEmail($user);
            $ret['password'] = true;
        }

        if (!$ret['fields']) {
            $ret['fields'] = $formFields->createView();
        }
        if (!$ret['password']) {
            $ret['password'] = $formPassword->createView();
        }

        return $ret;
    }
}
