<?php

namespace NyroDev\NyroCmsBundle\Handler;

use NyroDev\NyroCmsBundle\Form\Type\ContactMessageFilterType;
use NyroDev\NyroCmsBundle\Form\Type\ContactType;
use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\NyroCmsBundle\Services\NyroCmsService;
use NyroDev\UtilityBundle\Services\FormService;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Contact extends AbstractHandler
{
    public function hasComposer()
    {
        return false;
    }

    public function hasValidDates()
    {
        return false;
    }

    public function hasFeatured()
    {
        return false;
    }

    public function hasStateInvisible()
    {
        return false;
    }

    public function isReversePositionOrder()
    {
        return false;
    }

    public function saveInDb()
    {
        return false;
    }

    public function getAllowedParams()
    {
        return [
            'sent',
        ];
    }

    public function getOtherAdminRoutes()
    {
        $ret = null;
        if ($this->saveInDb()) {
            $ret = [
                'contactMessage' => [
                    'route' => 'nyrocms_admin_data_contactMessage',
                    'routePrm' => [
                        'chid' => $this->contentHandler->getId(),
                    ],
                    'name' => $this->contentHandler->getName().' '.$this->trans('admin.contactMessage.viewTitle'),
                ],
            ];
        }

        return $ret;
    }

    public function getAdminMessageListFields()
    {
        return [
            'id',
            'dest',
            'firstname',
            'lastname',
            'email',
            'inserted',
        ];
    }

    public function getAdminMessageFilterType()
    {
        return ContactMessageFilterType::class;
    }

    public function getAdminMessageExportFields()
    {
        return [
            'id',
            'dest',
            'firstname',
            'lastname',
            'company',
            'phone',
            'email',
            'message',
            'inserted',
        ];
    }

    protected $validatedEmails;

    protected function getFormFields($action)
    {
        $ret = [];
        if ($this->contentHandler->getHasAdmin()) {
            $ret['emails'] = [
                'type' => TextareaType::class,
                'translatable' => false,
                'label' => $this->trans('nyrocms.handler.contact.emails'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Callback([
                        'callback' => function ($data, ExecutionContextInterface $context) {
                            $emails = array_filter(array_map('trim', preg_split('/[\ \n\,;]+/', $data)));
                            $errors = [];
                            foreach ($emails as $email) {
                                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                    $errors[] = $email;
                                }
                            }

                            if (count($errors)) {
                                $context->buildViolation('nyrocms.handler.contact.emailsError')
                                    ->setParameter('%emails%', implode(', ', $errors))
                                    ->setTranslationDomain('messages')
                                    ->atPath('emails')
                                    ->addViolation();
                            } else {
                                $this->validatedEmails = implode(', ', $emails);
                            }
                        },
                    ]),
                ],
                'position' => ['before' => 'state'],
            ];
        }

        return $ret;
    }

    public function flushClb($action, ContentSpec $row, Form $form)
    {
        parent::flushClb($action, $row, $form);
        $content = $row->getContent();
        $content['emails'] = $this->validatedEmails;
        $row->setContent($content);
    }

    protected function getEmails(Content $content)
    {
        $ret = [];

        if ($this->contentHandler->getHasAdmin()) {
            foreach ($this->getContentSpecs($content) as $spec) {
                $ret['spec_'.$spec->getId()] = [
                    'emails' => array_map('trim', explode(',', $spec->getInContent('emails'))),
                    'name' => $spec->getTitle(),
                ];
            }
        }

        if (0 == count($ret)) {
            $ret = [
                'contact' => [
                    'emails' => [$this->trans('nyrocms.handler.contact.defaultTo.email')],
                    'name' => $this->trans('nyrocms.handler.contact.defaultTo.name'),
                ],
            ];
        }

        return $ret;
    }

    protected function getFormType(Content $content)
    {
        return ContactType::class;
    }

    protected function getFormOptions(Content $content)
    {
        return [];
    }

    protected function _prepareView(Content $content, ContentSpec $handlerContent = null, $handlerAction = null)
    {
        $contactEmails = $this->getEmails($content);

        $form = $this->get('form.factory')->create($this->getFormType($content), null, array_merge([
            'attr' => [
                'id' => 'contactForm',
                'class' => 'publicForm',
            ],
            'contacts' => $contactEmails,
        ], $this->getFormOptions($content)));
        $this->get(FormService::class)->addDummyCaptcha($form);

        /* @var $form \Symfony\Component\Form\Form */
        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $subject = $this->trans('nyrocms.handler.contact.subject');
            $message = [];
            $message[] = '<h1>'.$subject.'</h1>';
            $message[] = '<p>';

            $data = $form->getData();

            if (isset($data['dest']) && isset($contactEmails[$data['dest']])) {
                $to = $contactEmails[$data['dest']]['emails'];
                $emailName = $contactEmails[$data['dest']]['name'];
            } else {
                $to = $contactEmails[key($contactEmails)]['emails'];
                $emailName = $contactEmails[key($contactEmails)]['name'];
            }

            $view = $form->createView();

            $saveInDb = $this->saveInDb();
            if ($saveInDb) {
                $contactMessage = $this->get(DbAbstractService::class)->getNew('contact_message');
                $contactMessage->setContentHandler($this->contentHandler);
                $contactMessage->setDest($emailName);
                $accessor = PropertyAccess::createPropertyAccessor();
            }

            foreach ($view as $k => $field) {
                /* @var $field \Symfony\Component\Form\FormView */
                $v = $field->vars['value'];
                if ('dest' == $k && $v) {
                    $v = $contactEmails[$v]['name'];
                }
                if ('_token' != $k && $v && $field->vars['label']) {
                    $message[] = '<strong>'.$field->vars['label'].'</strong> : '.nl2br($v).'<br />';
                    if ($saveInDb && $accessor->isWritable($contactMessage, $k)) {
                        $accessor->setValue($contactMessage, $k, $v);
                    }
                }
            }
            $message[] = '</p>';

            $this->sendEmail($to, $subject, implode("\n", $message), $data['email'], null, $content);

            if ($saveInDb) {
                $this->get(DbAbstractService::class)->flush();
            }

            return new RedirectResponse($this->get(NyroCmsService::class)->getUrlFor($content, false, ['sent' => 1]));
        }

        $view = '@NyroDevNyroCms/Handler/contact';

        return [
            'view' => $view.'.html.php',
            'vars' => [
                'content' => $content,
                'form' => $form->createView(),
                'sent' => $this->request->query->getBoolean('sent'),
                'isAdmin' => $this->isAdmin,
            ],
        ];
    }

    protected function sendEmail($to, $subject, $content, $from = null, $locale = null, Content $dbContent = null)
    {
        return $this->get(NyroCmsService::class)->sendEmail($to, $subject, $content, $from, $locale, $dbContent);
    }
}
