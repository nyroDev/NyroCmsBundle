<?php

namespace NyroDev\NyroCmsBundle\Controller\Traits;

use NyroDev\NyroCmsBundle\Services\AdminService;
use NyroDev\NyroCmsBundle\Services\ComposerService;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\NyroCmsBundle\Services\NyroCmsService;
use NyroDev\NyroCmsBundle\Services\UserService;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait SubscribedServiceTrait
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'nyrocms' => '?'.NyroCmsService::class,
            NyroCmsService::class => '?'.NyroCmsService::class,
            'nyrocms_composer' => '?'.ComposerService::class,
            ComposerService::class => '?'.ComposerService::class,
            'nyrocms_user' => '?'.UserService::class,
            UserService::class => '?'.UserService::class,
            'nyrocms_admin' => '?'.AdminService::class,
            AdminService::class => '?'.AdminService::class,
            'nyrocms_db' => '?'.DbAbstractService::class,
            DbAbstractService::class => '?'.DbAbstractService::class,
            'validator' => '?'.ValidatorInterface::class,
        ]);
    }
}
