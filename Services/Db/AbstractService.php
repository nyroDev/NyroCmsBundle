<?php

namespace NyroDev\NyroCmsBundle\Services\Db;

use NyroDev\UtilityBundle\Services\AbstractService as AbstractServiceSrc;

abstract class AbstractService extends AbstractServiceSrc
{
    public function __construct($container)
    {
        parent::__construct($container);
    }

    public function getNamespace()
    {
        return $this->getParameter('nyroCms.model.namespace');
    }

    public function getClass($name, $namespaced = true)
    {
        $tmp = explode('\\', $name);
        $converter = new \Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter();
        $paramKey = $converter->normalize($tmp[count($tmp) - 1]);

        return ($namespaced ? $this->getNamespace().'\\' : '').$this->getParameter('nyroCms.model.classes.'.$paramKey, $name);
    }

    public function isA($object, $name)
    {
        return is_a($object, $this->getClass($name));
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->get('nyrodev_db')->getObjectManager();
    }

    /**
     * @return \NyroDev\NyroCmsBundle\Repository\UserRepositoryInterface
     */
    public function getUserRepository()
    {
        return $this->getRepository('user');
    }

    /**
     * @return \NyroDev\NyroCmsBundle\Repository\UserRoleRepositoryInterface
     */
    public function getUserRoleRepository()
    {
        return $this->getRepository('user_role');
    }

    /**
     * @return \NyroDev\NyroCmsBundle\Repository\ContentRepositoryInterface
     */
    public function getContentRepository()
    {
        return $this->getRepository('content');
    }

    /**
     * @return \NyroDev\NyroCmsBundle\Repository\ContentSpecRepositoryInterface
     */
    public function getContentSpecRepository()
    {
        return $this->getRepository('content_spec');
    }

    /**
     * @return \NyroDev\NyroCmsBundle\Repository\ContentHandlerRepositoryInterface
     */
    public function getContentHandlerRepository()
    {
        return $this->getRepository('content_handler');
    }

    /**
     * @param string $name class name
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository($name)
    {
        return $this->get('nyrodev_db')->getRepository($this->getClass($name));
    }

    public function getNew($name, $persist = true)
    {
        return $this->get('nyrodev_db')->getNew($this->getRepository($name), $persist);
    }

    public function persist($object)
    {
        $this->get('nyrodev_db')->persist($object);
    }

    public function remove($object)
    {
        $this->get('nyrodev_db')->remove($object);
    }

    public function refresh($object)
    {
        $this->get('nyrodev_db')->refresh($object);
    }

    public function flush()
    {
        $this->get('nyrodev_db')->flush();
    }
}
