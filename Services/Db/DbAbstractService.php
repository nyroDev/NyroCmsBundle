<?php

namespace NyroDev\NyroCmsBundle\Services\Db;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use NyroDev\NyroCmsBundle\Repository\ContentHandlerRepositoryInterface;
use NyroDev\NyroCmsBundle\Repository\ContentRepositoryInterface;
use NyroDev\NyroCmsBundle\Repository\ContentSpecRepositoryInterface;
use NyroDev\NyroCmsBundle\Repository\TemplateCategoryRepositoryInterface;
use NyroDev\NyroCmsBundle\Repository\TemplateRepositoryInterface;
use NyroDev\NyroCmsBundle\Repository\TooltipRepositoryInterface;
use NyroDev\NyroCmsBundle\Repository\UserRepositoryInterface;
use NyroDev\NyroCmsBundle\Repository\UserRoleRepositoryInterface;
use NyroDev\UtilityBundle\Services\AbstractService as AbstractServiceSrc;
use NyroDev\UtilityBundle\Services\Db\DbAbstractService as NyroDevDbService;

abstract class DbAbstractService extends AbstractServiceSrc
{
    public function __construct(
        protected readonly NyroDevDbService $nyrodevDbService,
    ) {
    }

    public function getNamespace(): string
    {
        return $this->getParameter('nyrocms.model.namespace');
    }

    public function getClass(string $name, bool $namespaced = true): string
    {
        $tmp = explode('\\', $name);
        $converter = new \Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter();
        $paramKey = $converter->normalize($tmp[count($tmp) - 1]);

        return ($namespaced ? $this->getNamespace().'\\' : '').$this->getParameter('nyrocms.model.classes.'.$paramKey, $name);
    }

    public function isA(object $object, string $name): bool
    {
        return is_a($object, $this->getClass($name));
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->nyrodevDbService->getObjectManager();
    }

    public function getUserRepository(): UserRepositoryInterface
    {
        return $this->getRepository('user');
    }

    public function getUserRoleRepository(): UserRoleRepositoryInterface
    {
        return $this->getRepository('user_role');
    }

    public function getContentRepository(): ContentRepositoryInterface
    {
        return $this->getRepository('content');
    }

    public function getContentSpecRepository(): ContentSpecRepositoryInterface
    {
        return $this->getRepository('content_spec');
    }

    public function getContentHandlerRepository(): ContentHandlerRepositoryInterface
    {
        return $this->getRepository('content_handler');
    }

    public function getTemplateRepository(): TemplateRepositoryInterface
    {
        return $this->getRepository('template');
    }

    public function getTemplateCategoryRepository(): TemplateCategoryRepositoryInterface
    {
        return $this->getRepository('template_category');
    }

    public function getTooltipRepository(): TooltipRepositoryInterface
    {
        return $this->getRepository('tooltip');
    }

    public function getRepository(object|string $name): ObjectRepository
    {
        return $this->nyrodevDbService->getRepository($this->getClass($name));
    }

    public function getNew(object|string $name, bool $persist = true)
    {
        return $this->nyrodevDbService->getNew($this->getRepository($name), $persist);
    }

    public function persist($object): void
    {
        $this->nyrodevDbService->persist($object);
    }

    public function remove($object): void
    {
        $this->nyrodevDbService->remove($object);
    }

    public function refresh($object): void
    {
        $this->nyrodevDbService->refresh($object);
    }

    public function flush(): void
    {
        $this->nyrodevDbService->flush();
    }
}
