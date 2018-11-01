<?php

namespace NyroDev\NyroCmsBundle\Services;

use NyroDev\UtilityBundle\Services\AbstractService;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\User;
use Symfony\Component\HttpFoundation\Request;

class UserService extends AbstractService
{
    public function handleAddUser(User $user)
    {
        $now = new \DateTime();
        if (
            $user->getValid()
                &&
            (!$user->getValidStart() || $user->getValidStart() <= $now)
                &&
            (!$user->getValidEnd() || $user->getValidEnd() >= $now)
            ) {
            // user is valid, we can send it an email
            $this->sendWelcomeEmail($user);
        }
    }

    public function sendWelcomeEmail(User $user)
    {
        $passwordKey = $this->get('nyrodev')->randomStr(32);
        $end = new \DateTime('+1month');

        $user->setPasswordKey($passwordKey);
        $user->setPasswordKeyEnd($end);
        $this->get('nyrocms_db')->flush();

        $this->get('nyrocms')->sendEmail($user->getEmail(), $this->trans('nyrocms.welcome.email.subject'), nl2br($this->trans('nyrocms.welcome.email.content', array(
            '%name%' => $user->getFirstname().' '.$user->getLastName(),
            '%url%' => $this->generateUrl('nyrocms_admin_welcome', array(
                'id' => $user->getId(),
                'key' => $user->getPasswordKey(),
            ), true),
        ))));
    }

    public function sendChangedPasswordEmail(User $user)
    {
        return $this->get('nyrocms')->sendEmail($user->getEmail(), $this->trans('nyrocms.changedPassword.email.subject'), nl2br($this->trans('nyrocms.changedPassword.email.content', array(
                '%name%' => $user->getFirstname().' '.$user->getLastName(),
            ))));
    }

    public function handleForgot($place, Request $request, $id, $key, $welcome = false)
    {
        $ret = array(
            'step' => 1,
            'notFound' => false,
            'sent' => false,
            'form' => null,
            'welcome' => $welcome,
        );
        $repo = $this->get('nyrocms_db')->getUserRepository();

        if ($id || $welcome) {
            $ret['step'] = 2;
            $user = $repo->find($id);

            if ($user && $welcome && 'dummy' != $user->getSalt()) {
                $user = null;
            }

            $now = new \DateTime();
            if ($user && $user->getPasswordKey() == $key && $user->getPasswordKeyEnd() >= $now) {
                $form = $this->get('form.factory')->createBuilder('form')
                    ->add('password', 'repeated', array(
                        'type' => 'password',
                        'first_options' => array(
                            'label' => $this->trans('admin.user.password'),
                            'attr' => array('placeholder' => $this->trans('admin.user.newPassword')),
                            'constraints' => array(
                                new \Symfony\Component\Validator\Constraints\NotBlank(),
                            ),
                        ),
                        'second_options' => array(
                            'label' => $this->trans('admin.user.passwordConfirm'),
                            'attr' => array('placeholder' => $this->trans('admin.user.passwordConfirm')),
                            'constraints' => array(
                                new \Symfony\Component\Validator\Constraints\NotBlank(),
                            ),
                        ),
                        'required' => true,
                        'invalid_message' => $this->trans('admin.user.samePassword'),
                    ))
                    ->add('submit', 'submit', array(
                        'label' => $this->trans('admin.misc.send'),
                    ))
                    ->getForm();
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $data = $form->getData();

                    $salt = sha1(uniqid());
                    $password = $this->get('security.encoder_factory')->getEncoder($user)->encodePassword($data['password'], $salt);
                    $user->setPassword($password);
                    $user->setSalt($salt);
                    $user->setPasswordKey(null);
                    $user->setPasswordKeyEnd(null);
                    $this->get('nyrocms_db')->flush();

                    $this->sendChangedPasswordEmail($user);

                    $ret['sent'] = true;
                }

                $ret['form'] = $form->createView();
            } else {
                $ret['notFound'] = true;
            }
        } else {
            $form = $this->get('form.factory')->createBuilder('form')
                ->add('email', 'email', array(
                    'label' => $this->trans('admin.user.email'),
                    'constraints' => array(
                        new \Symfony\Component\Validator\Constraints\NotBlank(),
                        new \Symfony\Component\Validator\Constraints\Email(),
                    ),
                    'attr' => array('placeholder' => $this->trans('admin.user.email')),
                    ))
                ->add('submit', 'submit', array(
                    'label' => $this->trans('admin.misc.send'),
                ))
                ->getForm();

            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                try {
                    $user = $repo->loadUserByUsername($data['email']);
                } catch (\Exception $e) {
                    $user = null;
                }
                if ($user) {
                    $passwordKey = $this->get('nyrodev')->randomStr(32);
                    $end = new \DateTime('+2day');

                    $user->setPasswordKey($passwordKey);
                    $user->setPasswordKeyEnd($end);
                    $this->get('nyrocms_db')->flush();

                    $this->get('nyrocms')->sendEmail($user->getEmail(), $this->trans('nyrocms.forgot.email.subject'), nl2br($this->trans('nyrocms.forgot.email.content', array(
                        '%name%' => $user->getFirstname().' '.$user->getLastName(),
                        '%url%' => $this->generateUrl('nyrocms_'.$place.'_forgot', array(
                            'id' => $user->getId(),
                            'key' => $user->getPasswordKey(),
                        ), true),
                    ))));
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
        $this->get('nyrocms')->setActiveIds(array('account' => 'account'));
        $ret = array(
            'fields' => false,
            'password' => false,
        );

        $user = $this->get('nyrodev_member')->getUser();
        $fields = array(
            'email',
            'firstname',
            'lastname',
        );

        $form = $this->get('form.factory')->createNamedBuilder('fields', 'form', $user);
        foreach ($fields as $f) {
            $form->add($f, null, array(
                'label' => $this->trans('admin.user.'.$f),
            ));
        }
        $form->add('submit', 'submit', array(
            'label' => $this->trans('admin.misc.send'),
        ));

        $formFields = $form->getForm();

        $formPassword = $this->get('form.factory')->createNamedBuilder('password', 'form', $user)
            ->add('curPassword', 'password', array(
                    'label' => $this->trans('admin.user.curPassword'),
                    'required' => true,
                    'mapped' => false,
                    'constraints' => array(
                        new \Symfony\Component\Validator\Constraints\NotBlank(),
                        new \Symfony\Component\Security\Core\Validator\Constraints\UserPassword(array(
                            'message' => $this->trans('admin.user.wrongPassword'),
                        )),
                    ), ))
            ->add('password', 'repeated', array(
                    'mapped' => false,
                    'first_name' => 'pwd1',
                    'second_name' => 'pwd2',
                    'constraints' => array(new \Symfony\Component\Validator\Constraints\NotBlank()),
                    'required' => true,
                    'type' => 'password',
                    'first_options' => array(
                        'label' => $this->trans('admin.user.newPassword'),
                    ),
                    'second_options' => array(
                        'label' => $this->trans('admin.user.passwordConfirm'),
                    ),
                    'invalid_message' => $this->trans('admin.user.samePassword'),
                ))
            ->add('submit', 'submit', array(
                'label' => $this->trans('admin.misc.send'),
            ))
            ->getForm();

        $formFields->handleRequest($request);
        $formPassword->handleRequest($request);
        if ($formFields->isValid()) {
            $this->get('nyrocms_db')->flush();
            $ret['fields'] = true;
        } elseif ($formPassword->isValid()) {
            $newPassword = $formPassword->get('password')->getData();
            $salt = sha1(uniqid());
            $password = $this->get('security.encoder_factory')->getEncoder($user)->encodePassword($newPassword, $salt);
            $user->setPassword($password);
            $user->setSalt($salt);
            $user->setPasswordKey(null);
            $user->setPasswordKeyEnd(null);
            $this->get('nyrocms_db')->flush();

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
