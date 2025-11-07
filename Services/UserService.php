<?php

namespace NyroDev\NyroCmsBundle\Services;

use DateTime;
use Exception;
use NyroDev\NyroCmsBundle\Model\User;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Services\AbstractService as NyroDevAbstractService;
use NyroDev\UtilityBundle\Services\FormService;
use NyroDev\UtilityBundle\Services\MemberService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
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

class UserService extends NyroDevAbstractService
{
    public function __construct(
        private readonly NyrodevService $nyrodevService,
        private readonly MemberService $memberService,
        private readonly FormService $formService,
        private readonly DbAbstractService $dbService,
        private readonly NyroCmsService $nyroCmsService,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function handleAddUser(User $user, ?string $locale = null, string $place = 'nyrocms_admin'): void
    {
        $now = new DateTime();
        if (
            $user->getValid()
            && (!$user->getValidStart() || $user->getValidStart() <= $now)
            && (!$user->getValidEnd() || $user->getValidEnd() >= $now)
        ) {
            // user is valid, we can send it an email
            $this->sendWelcomeEmail($user, $locale, $place);
        }
    }

    public function sendWelcomeEmail(User $user, ?string $locale = null, string $place = 'nyrocms_admin'): void
    {
        $passwordKey = $this->nyrodevService->randomStr(32);
        $end = new DateTime('+1month');

        $user->setPasswordKey($passwordKey);
        $user->setPasswordKeyEnd($end);
        $this->dbService->flush();

        $subject = $this->trans('nyrocms.welcome.email.subject');
        $email = (new TemplatedEmail())
            ->textTemplate('@NyroDevNyroCms/email/welcome.text.php')
            ->htmlTemplate('@NyroDevNyroCms/email/welcome.html.php')
            ->to($user->getEmail())
            ->subject($subject)
            ->context([
                'subject' => $subject,
                'user' => $user,
                'url' => $this->generateUrl($place.'_welcome', [
                    'id' => $user->getId(),
                    'key' => $user->getPasswordKey(),
                ], true),
            ]);

        $this->nyroCmsService->sendEmail($email);
    }

    public function sendChangedPasswordEmail(User $user): void
    {
        $subject = $this->trans('nyrocms.changedPassword.email.subject');
        $email = (new TemplatedEmail())
            ->textTemplate('@NyroDevNyroCms/email/changedPassword.text.php')
            ->htmlTemplate('@NyroDevNyroCms/email/changedPassword.html.php')
            ->to($user->getEmail())
            ->subject($subject)
            ->context([
                'subject' => $subject,
                'user' => $user,
            ]);

        $this->nyroCmsService->sendEmail($email);
    }

    public function handleForgot(string $place, Request $request, ?string $id = null, ?string $key = null, bool $welcome = false): array
    {
        $ret = [
            'step' => 1,
            'notFound' => false,
            'sent' => false,
            'form' => null,
            'welcome' => $welcome,
            'trKey' => $welcome ? 'welcome' : 'forgot',
        ];
        $repo = $this->dbService->getUserRepository();

        if ($id || $welcome) {
            $ret['step'] = 2;
            $user = $repo->find($id);

            if ($user && $welcome && 'dummy' != $user->getPassword()) {
                $user = null;
            }

            $now = new DateTime();
            if ($user && $user->getPasswordKey() == $key && $user->getPasswordKeyEnd() >= $now) {
                $passwordOptions = [
                    'wc' => 'nyro-password',
                    'icon' => NyroCmsService::ICON_PATH.'#password',
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'wcHtml' => '<span slot="show">'.$this->nyrodevService->getIconHelper()->getIcon(NyroCmsService::ICON_PATH.'#hide').'</span>'.
                        '<span slot="hide">'.$this->nyrodevService->getIconHelper()->getIcon(NyroCmsService::ICON_PATH.'#show').'</span>',
                ];

                $form = $this->formService->getFormFactory()->createBuilder()
                    ->add('password', RepeatedType::class, [
                        'type' => PasswordType::class,
                        'first_options' => [
                            ...$passwordOptions,
                            'label' => $this->trans('admin.user.password'),
                            'attr' => ['placeholder' => $this->trans('admin.user.newPassword')],
                        ],
                        'second_options' => [
                            ...$passwordOptions,
                            'label' => $this->trans('admin.user.passwordConfirm'),
                            'attr' => ['placeholder' => $this->trans('admin.user.passwordConfirm')],
                        ],
                        'required' => true,
                        'invalid_message' => $this->trans('admin.user.samePassword'),
                    ])
                    ->add('submit', SubmitType::class, [
                        'label' => $this->trans('admin.misc.send'),
                        'icon' => NyroCmsService::ICON_PATH.'#password',
                        'cancelUrl' => $this->container->get(NyrodevService::class)->generateUrl($place.'_login'),
                        'cancelIcon' => NyroCmsService::ICON_PATH.'#reset',
                    ])
                    ->getForm();

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $data = $form->getData();

                    $password = $this->passwordHasher->hashPassword($user, $data['password']);
                    $user->setPassword($password);
                    $user->setPasswordKey(null);
                    $user->setPasswordKeyEnd(null);
                    $this->dbService->flush();

                    $this->sendChangedPasswordEmail($user);

                    $ret['sent'] = true;
                }

                $ret['form'] = $form->createView();
            } else {
                $ret['notFound'] = true;
            }
        } else {
            $form = $this->formService->getFormFactory()->createBuilder()
                ->add('email', EmailType::class, [
                    'label' => $this->trans('admin.user.email'),
                    'constraints' => [
                        new NotBlank(),
                        new Email(),
                    ],
                    'icon' => NyroCmsService::ICON_PATH.'#email',
                    'attr' => [
                        'placeholder' => $this->trans('admin.user.email'),
                    ],
                ])
                ->add('submit', SubmitType::class, [
                    'label' => $this->trans('admin.misc.send'),
                    'icon' => NyroCmsService::ICON_PATH.'#mailOut',
                    'cancelUrl' => $this->container->get(NyrodevService::class)->generateUrl($place.'_login'),
                    'cancelIcon' => NyroCmsService::ICON_PATH.'#reset',
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
                    $passwordKey = $this->nyrodevService->randomStr(32);
                    $end = new DateTime('+2day');

                    $user->setPasswordKey($passwordKey);
                    $user->setPasswordKeyEnd($end);
                    $this->dbService->flush();

                    $subject = $this->trans('nyrocms.forgot.email.subject');
                    $email = (new TemplatedEmail())
                        ->textTemplate('@NyroDevNyroCms/email/forgot.text.php')
                        ->htmlTemplate('@NyroDevNyroCms/email/forgot.html.php')
                        ->to($user->getEmail())
                        ->subject($subject)
                        ->context([
                            'subject' => $subject,
                            'user' => $user,
                            'url' => $this->generateUrl($place.'_forgot', [
                                'id' => $user->getId(),
                                'key' => $user->getPasswordKey(),
                            ], true),
                        ]);

                    $this->nyroCmsService->sendEmail($email);
                    $ret['sent'] = true;
                } else {
                    $ret['notFound'] = true;
                }
            }

            $ret['form'] = $form->createView();
        }

        return $ret;
    }

    public function handleAccount(string $place, Request $request): array
    {
        $this->nyroCmsService->setActiveIds(['account' => 'account']);
        $ret = [
            'fields' => false,
            'password' => false,
        ];

        $user = $this->memberService->getEntityUser();

        $fields = [
            'email',
            'firstname',
            'lastname',
        ];

        $form = $this->formService->getFormFactory()->createNamedBuilder('fields', FormType::class, $user);
        foreach ($fields as $f) {
            $options = [
                'label' => $this->trans('admin.user.'.$f),
                'row_attr' => [
                    'class' => 'form_row_100',
                ],
            ];
            if ('email' === $f) {
                $options['icon'] = NyroCmsService::ICON_PATH.'#email';
            }
            $form->add($f, null, $options);
        }
        $form->add('submit', SubmitType::class, [
            'label' => $this->trans('admin.misc.send'),
            'icon' => NyroCmsService::ICON_PATH.'#save',
        ]);

        $formFields = $form->getForm();

        $passwordOptions = [
            'wc' => 'nyro-password',
            'icon' => NyroCmsService::ICON_PATH.'#password',
            'row_attr' => [
                'class' => 'form_row_100',
            ],
            'wcHtml' => '<span slot="show">'.$this->nyrodevService->getIconHelper()->getIcon(NyroCmsService::ICON_PATH.'#hide').'</span>'.
                '<span slot="hide">'.$this->nyrodevService->getIconHelper()->getIcon(NyroCmsService::ICON_PATH.'#show').'</span>',
        ];

        $formPassword = $this->formService->getFormFactory()->createNamedBuilder('password', FormType::class, $user)
            ->add('curPassword', PasswordType::class, [
                ...$passwordOptions,
                'label' => $this->trans('admin.user.curPassword'),
                'row_attr' => [
                    'class' => 'form_row_100',
                ],
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
                    ...$passwordOptions,
                    'label' => $this->trans('admin.user.newPassword'),
                ],
                'second_options' => [
                    ...$passwordOptions,
                    'label' => $this->trans('admin.user.passwordConfirm'),
                ],
                'invalid_message' => $this->trans('admin.user.samePassword'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->trans('admin.misc.send'),
                'icon' => NyroCmsService::ICON_PATH.'#save',
            ])
            ->getForm();

        $formFields->handleRequest($request);
        $formPassword->handleRequest($request);
        if ($formFields->isSubmitted() && $formFields->isValid()) {
            $this->dbService->flush();
            $ret['fields'] = true;
        } elseif ($formPassword->isSubmitted() && $formPassword->isValid()) {
            $newPassword = $formPassword->get('password')->getData();
            $password = $this->passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($password);
            $user->setPasswordKey(null);
            $user->setPasswordKeyEnd(null);
            $this->dbService->flush();

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
